<?php

namespace App\Http\Controllers;

use App\Models\SlotBet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SlotGameController extends Controller
{
    // Símbolos del slot con sus pesos (probabilidad)
    private $symbols = [
        '🍒' => 25,  // Más común
        '🍋' => 20,
        '🍊' => 15,
        '⭐' => 12,
        '💎' => 10,
        '🎰' => 8,
        '💰' => 6,
        '7️⃣' => 4   // Más raro (jackpot)
    ];

    // Pagos por símbolo (multiplicador)
    private $payouts = [
        '🍒' => 2,
        '🍋' => 3,
        '🍊' => 5,
        '⭐' => 10,
        '💎' => 20,
        '🎰' => 50,
        '💰' => 100,
        '7️⃣' => 500  // JACKPOT!
    ];

    /**
     * Muestra la página del juego
     */
    public function index()
    {
        $user = Auth::user();
        
        $history = SlotBet::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($bet) {
                return [
                    'id' => $bet->id,
                    'bet_amount' => (float) $bet->bet_amount,
                    'lines' => $bet->lines,
                    'total_bet' => (float) $bet->total_bet,
                    'result' => $bet->result,
                    'winning_lines' => $bet->winning_lines,
                    'multiplier' => (float) $bet->multiplier,
                    'payout' => (float) $bet->payout,
                    'profit' => (float) $bet->profit,
                    'is_win' => (bool) $bet->is_win,
                    'created_at' => $bet->created_at->toISOString()
                ];
            });
        
        return Inertia::render('Tragamonedas', [
            'user' => $user,
            'balance' => (float) $this->getUserBalance($user),
            'history' => $history,
            'symbols' => array_keys($this->symbols),
            'payouts' => $this->payouts
        ]);
    }

    /**
     * Procesa un spin
     */
    public function spin(Request $request)
    {
        try {
            $validated = $request->validate([
                'bet_amount' => 'required|numeric|min:0.10|max:1000',
                'lines' => 'required|integer|in:1,3,5,9'
            ]);

            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $totalBet = $validated['bet_amount'] * $validated['lines'];
            $currentBalance = $this->getUserBalance($user);
            
            if ($currentBalance < $totalBet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente. Necesitas S/ ' . number_format($totalBet, 2)
                ], 400);
            }

            DB::beginTransaction();

            // Generar resultado provably fair
            $serverSeed = $this->generateSeed(32);
            $clientSeed = $this->generateSeed(16);
            $nonce = SlotBet::where('user_id', $user->id)->count() + 1;
            
            // Generar matriz de símbolos 3x3
            $result = $this->generateResult($serverSeed, $clientSeed, $nonce);
            
            // Verificar líneas ganadoras
            $winningData = $this->checkWinningLines($result, $validated['lines']);
            
            // Calcular pago
            $multiplier = $winningData['multiplier'];
            $payout = $multiplier > 0 ? $totalBet * $multiplier : 0;
            $profit = $payout - $totalBet;
            $isWin = $multiplier > 0;
            
            // Crear registro
            $bet = SlotBet::create([
                'user_id' => $user->id,
                'bet_amount' => $validated['bet_amount'],
                'lines' => $validated['lines'],
                'total_bet' => $totalBet,
                'result' => $result,
                'winning_lines' => $winningData['lines'],
                'multiplier' => $multiplier,
                'payout' => $payout,
                'profit' => $profit,
                'is_win' => $isWin,
                'server_seed' => $serverSeed,
                'client_seed' => $clientSeed,
                'nonce' => $nonce
            ]);
            
            // Actualizar saldo
            $newBalance = $this->updateUserBalance($user, -$totalBet + $payout);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'result' => [
                    'reels' => $result,
                    'winning_lines' => $winningData['lines'],
                    'multiplier' => round($multiplier, 2),
                    'payout' => round($payout, 2),
                    'profit' => round($profit, 2),
                    'is_win' => $isWin,
                    'new_balance' => round($newBalance, 2)
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error en Slot Game:', [
                'user_id' => Auth::id(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el spin',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Genera matriz de símbolos 3x3
     */
    private function generateResult($serverSeed, $clientSeed, $nonce)
    {
        $reels = [];
        
        for ($reel = 0; $reel < 3; $reel++) {
            $reels[$reel] = [];
            for ($row = 0; $row < 3; $row++) {
                $hash = hash_hmac('sha256', "{$clientSeed}:{$nonce}:{$reel}:{$row}", $serverSeed);
                $symbol = $this->getSymbolFromHash($hash);
                $reels[$reel][$row] = $symbol;
            }
        }
        
        return $reels;
    }

    /**
     * Convierte hash a símbolo basado en pesos
     */
    private function getSymbolFromHash($hash)
    {
        $number = hexdec(substr($hash, 0, 8));
        $totalWeight = array_sum($this->symbols);
        $random = $number % $totalWeight;
        
        $cumulative = 0;
        foreach ($this->symbols as $symbol => $weight) {
            $cumulative += $weight;
            if ($random < $cumulative) {
                return $symbol;
            }
        }
        
        return '🍒'; // Fallback
    }

    /**
     * Verifica líneas ganadoras
     */
    private function checkWinningLines($reels, $lines)
    {
        $winningLines = [];
        $totalMultiplier = 0;
        
        // Definir patrones de líneas
        $linePatterns = [
            1 => [[1, 1, 1]], // Solo línea central
            3 => [[0, 0, 0], [1, 1, 1], [2, 2, 2]], // 3 horizontales
            5 => [[0, 0, 0], [1, 1, 1], [2, 2, 2], [0, 1, 2], [2, 1, 0]], // + diagonales
            9 => [[0, 0, 0], [1, 1, 1], [2, 2, 2], [0, 1, 2], [2, 1, 0], 
                  [0, 0, 1], [1, 0, 0], [2, 2, 1], [1, 2, 2]] // Todas
        ];
        
        $patterns = $linePatterns[$lines];
        
        foreach ($patterns as $pattern) {
            $symbol1 = $reels[0][$pattern[0]];
            $symbol2 = $reels[1][$pattern[1]];
            $symbol3 = $reels[2][$pattern[2]];
            
            // Si los 3 símbolos son iguales
            if ($symbol1 === $symbol2 && $symbol2 === $symbol3) {
                $winningLines[] = $pattern;
                $totalMultiplier += $this->payouts[$symbol1];
            }
        }
        
        return [
            'lines' => $winningLines,
            'multiplier' => $totalMultiplier
        ];
    }

    /**
     * Obtiene el saldo del usuario
     */
    private function getUserBalance($user)
    {
        if (!$user->wallet) {
            $user->wallet()->create([
                'balance' => 0.00,
                'currency' => 'USD'
            ]);
        }
        
        return $user->wallet->balance;
    }

    /**
     * Actualiza el saldo del usuario
     */
    private function updateUserBalance($user, $amount)
    {
        if (!$user->wallet) {
            throw new \Exception('El usuario no tiene una wallet');
        }
        
        $user->wallet->balance += $amount;
        $user->wallet->save();
        
        return $user->wallet->balance;
    }

    /**
     * Genera un seed aleatorio
     */
    private function generateSeed($length)
    {
        return bin2hex(random_bytes($length));
    }
}