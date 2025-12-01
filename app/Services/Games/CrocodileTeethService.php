<?php
namespace App\Services\Games;

use App\Models\User;
use App\Models\GameSession;
use App\Services\WalletService;
use Illuminate\Support\Facades\Cache;

class CrocodileTeethService
{
    private $user;
    private $walletService;
    private $totalTeeth = 20;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->walletService = new WalletService();
    }

    /**
     * Iniciar una nueva partida
     */
    public function startGame(float $betAmount, int $redTeeth)
    {
        // Validaciones
        if ($betAmount <= 0 || $betAmount > 10000) {
            throw new \Exception('Apuesta inválida');
        }

        if ($redTeeth < 1 || $redTeeth > 19) {
            throw new \Exception('Cantidad de dientes rojos inválida (1-19)');
        }

        // Verificar saldo
        $balance = $this->walletService->getBalance($this->user);
        if ($balance < $betAmount) {
            throw new \Exception('Saldo insuficiente');
        }

        // Descontar apuesta
        $this->walletService->withdraw($this->user, $betAmount, 'Apuesta en Crocodile Teeth');

        // Generar posiciones de dientes rojos (aleatoriamente)
        $redPositions = $this->generateRedPositions($redTeeth);

        // Crear sesión de juego
        $session = GameSession::create([
            'user_id' => $this->user->id,
            'game_id' => $this->getGameId(),
            'bet_amount' => $betAmount,
            'win_amount' => 0,
            'result' => 'playing',
            'game_data' => json_encode([
                'red_teeth' => $redTeeth,
                'red_positions' => $redPositions,
                'clicked_teeth' => [],
                'current_multiplier' => 1.0,
                'safe_teeth_clicked' => 0,
            ]),
        ]);

        // Guardar en cache temporal (para validación rápida)
        Cache::put(
            "crocodile_game_{$this->user->id}",
            [
                'session_id' => $session->id,
                'red_positions' => $redPositions,
                'bet_amount' => $betAmount,
                'red_teeth' => $redTeeth,
                'clicked_teeth' => [],
            ],
            now()->addMinutes(30)
        );

        return [
            'session_id' => $session->id,
            'bet_amount' => $betAmount,
            'red_teeth' => $redTeeth,
            'current_multiplier' => 1.0,
            'potential_win' => $betAmount,
        ];
    }

    /**
     * Click en un diente
     */
    public function clickTooth(int $sessionId, int $toothPosition)
    {
        // Validar posición
        if ($toothPosition < 0 || $toothPosition >= $this->totalTeeth) {
            throw new \Exception('Posición de diente inválida');
        }

        // Obtener datos del cache
        $gameData = Cache::get("crocodile_game_{$this->user->id}");
        if (!$gameData || $gameData['session_id'] !== $sessionId) {
            throw new \Exception('Sesión de juego no encontrada o expirada');
        }

        // Verificar si el diente ya fue clickeado
        if (in_array($toothPosition, $gameData['clicked_teeth'])) {
            throw new \Exception('Este diente ya fue clickeado');
        }

        // Agregar a la lista de clickeados
        $gameData['clicked_teeth'][] = $toothPosition;

        // Verificar si es un diente rojo (BOMBA)
        $isRed = in_array($toothPosition, $gameData['red_positions']);

        if ($isRed) {
            // PERDIÓ - Tocó un diente rojo
            return $this->handleLoss($sessionId, $gameData, $toothPosition);
        } else {
            // SEGURO - Diente blanco
            return $this->handleSafeTooth($sessionId, $gameData, $toothPosition);
        }
    }

    /**
     * Cash Out (retirar ganancias)
     */
    public function cashOut(int $sessionId)
    {
        $gameData = Cache::get("crocodile_game_{$this->user->id}");
        if (!$gameData || $gameData['session_id'] !== $sessionId) {
            throw new \Exception('Sesión de juego no encontrada');
        }

        $session = GameSession::findOrFail($sessionId);
        $sessionData = json_decode($session->game_data, true);

        // Calcular ganancia actual
        $currentMultiplier = $this->calculateMultiplier(
            count($gameData['clicked_teeth']),
            $gameData['red_teeth']
        );

        $winAmount = $gameData['bet_amount'] * $currentMultiplier;

        // Actualizar sesión
        $session->update([
            'win_amount' => $winAmount,
            'result' => 'win',
            'game_data' => json_encode(array_merge($sessionData, [
                'cashed_out' => true,
                'final_multiplier' => $currentMultiplier,
                'clicked_teeth' => $gameData['clicked_teeth'],
            ])),
        ]);

        // Acreditar ganancia
        $this->walletService->creditEarnings(
            $this->user,
            $winAmount,
            "Ganancia en Crocodile Teeth (Cash Out)"
        );

        // --- CORRECCIÓN: COMENTADO PARA EVITAR ERROR 500 ---
        /*
        $this->user->recordActivity(
            'game_win',
            "Ganó S/{$winAmount} en Crocodile Teeth (Cash Out)",
            'Crocodile Teeth',
            $winAmount,
            true
        );
        */

        // Limpiar cache
        Cache::forget("crocodile_game_{$this->user->id}");

        return [
            'status' => 'cashed_out',
            'win_amount' => $winAmount,
            'multiplier' => $currentMultiplier,
            'new_balance' => $this->walletService->getBalance($this->user),
        ];
    }

    /**
     * Manejar diente seguro (blanco)
     */
    private function handleSafeTooth(int $sessionId, array $gameData, int $toothPosition)
    {
        $safeTeethClicked = count($gameData['clicked_teeth']);
        $currentMultiplier = $this->calculateMultiplier($safeTeethClicked, $gameData['red_teeth']);
        $potentialWin = $gameData['bet_amount'] * $currentMultiplier;

        // Actualizar cache
        Cache::put("crocodile_game_{$this->user->id}", $gameData, now()->addMinutes(30));

        // Actualizar sesión en BD
        $session = GameSession::findOrFail($sessionId);
        $sessionData = json_decode($session->game_data, true);
        $sessionData['clicked_teeth'] = $gameData['clicked_teeth'];
        $sessionData['safe_teeth_clicked'] = $safeTeethClicked;
        $sessionData['current_multiplier'] = $currentMultiplier;
        $session->update(['game_data' => json_encode($sessionData)]);

        // Verificar si ganó completamente (clickeó todos los blancos)
        $maxSafeTeeth = $this->totalTeeth - $gameData['red_teeth'];
        if ($safeTeethClicked >= $maxSafeTeeth) {
            return $this->handleMaxWin($sessionId, $gameData, $currentMultiplier);
        }

        return [
            'status' => 'safe',
            'tooth_position' => $toothPosition,
            'is_red' => false,
            'safe_teeth_clicked' => $safeTeethClicked,
            'current_multiplier' => round($currentMultiplier, 2),
            'potential_win' => round($potentialWin, 2),
            'clicked_teeth' => $gameData['clicked_teeth'],
            'can_cash_out' => true,
        ];
    }

    /**
     * Manejar pérdida (tocó diente rojo)
     */
    private function handleLoss(int $sessionId, array $gameData, int $toothPosition)
    {
        $session = GameSession::findOrFail($sessionId);
        $sessionData = json_decode($session->game_data, true);

        // Actualizar sesión
        $session->update([
            'win_amount' => 0,
            'result' => 'loss',
            'game_data' => json_encode(array_merge($sessionData, [
                'clicked_teeth' => $gameData['clicked_teeth'],
                'lost_on_tooth' => $toothPosition,
            ])),
        ]);

        // --- CORRECCIÓN: COMENTADO PARA EVITAR ERROR 500 ---
        /*
        $this->user->recordActivity(
            'game_loss',
            "Perdió S/{$gameData['bet_amount']} en Crocodile Teeth",
            'Crocodile Teeth',
            $gameData['bet_amount'],
            false
        );
        */

        // Limpiar cache
        Cache::forget("crocodile_game_{$this->user->id}");

        return [
            'status' => 'lost',
            'tooth_position' => $toothPosition,
            'is_red' => true,
            'red_positions' => $gameData['red_positions'], // Mostrar todas las bombas
            'clicked_teeth' => $gameData['clicked_teeth'],
            'bet_lost' => $gameData['bet_amount'],
            'new_balance' => $this->walletService->getBalance($this->user),
        ];
    }

    /**
     * Manejar victoria máxima (clickeó todos los blancos)
     */
    private function handleMaxWin(int $sessionId, array $gameData, float $finalMultiplier)
    {
        $winAmount = $gameData['bet_amount'] * $finalMultiplier;

        $session = GameSession::findOrFail($sessionId);
        $session->update([
            'win_amount' => $winAmount,
            'result' => 'win',
        ]);

        // Acreditar ganancia
        $this->walletService->creditEarnings(
            $this->user,
            $winAmount,
            "Ganancia MÁXIMA en Crocodile Teeth"
        );

        // --- CORRECCIÓN: COMENTADO PARA EVITAR ERROR 500 ---
        /*
        $this->user->recordActivity(
            'game_win',
            "¡Victoria perfecta! Ganó S/{$winAmount} en Crocodile Teeth",
            'Crocodile Teeth',
            $winAmount,
            true
        );
        */

        // Limpiar cache
        Cache::forget("crocodile_game_{$this->user->id}");

        return [
            'status' => 'max_win',
            'win_amount' => $winAmount,
            'multiplier' => $finalMultiplier,
            'clicked_teeth' => $gameData['clicked_teeth'],
            'new_balance' => $this->walletService->getBalance($this->user),
        ];
    }

    /**
     * Calcular multiplicador actual
     */
    private function calculateMultiplier(int $safeTeethClicked, int $redTeeth): float
    {
        $multiplier = 1.0;
        $safeTeeth = $this->totalTeeth - $redTeeth;

        for ($i = 0; $i < $safeTeethClicked; $i++) {
            $remaining = $this->totalTeeth - $i;
            $safeRemaining = $safeTeeth - $i;
            $multiplier *= $remaining / $safeRemaining;
        }

        return $multiplier;
    }

    /**
     * Generar posiciones aleatorias de dientes rojos
     */
    private function generateRedPositions(int $count): array
    {
        $positions = range(0, $this->totalTeeth - 1);
        shuffle($positions);
        return array_slice($positions, 0, $count);
    }

    /**
     * Obtener ID del juego desde la BD
     */
    private function getGameId(): int
    {
        $game = \App\Models\Game::where('slug', 'crocodile-teeth')->first();
        return $game ? $game->id : 1; // Fallback
    }
}