<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Http\Controllers\AchievementController; 
use Illuminate\Support\Facades\Log; 

class WalletService
{
    public function getOrCreateWallet(User $user)
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );
    }

    public function deposit(User $user, float $amount, string $description = 'Depósito')
    {
        if ($amount <= 0) {
            throw new \Exception("Monto inválido");
        }

        $wallet = $this->getOrCreateWallet($user);
        
        // 💡 CORRECCIÓN: Aseguramos que el saldo sea el actual de la DB antes de la transacción.
        $wallet->refresh(); 
        $balanceBefore = $wallet->balance;

        // Incrementar saldo
        $wallet->increment('balance', $amount);
        $wallet->refresh();

        // 1. Registrar transacción
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'type' => 'deposit',
            'description' => $description,
            'balance_before' => $balanceBefore,
            'balance_after' => $wallet->balance
        ]);
        
        // ------------------------------------------
        // LÓGICA DEL LOGRO 'first_deposit'
        // ------------------------------------------
        
        $totalDeposits = Transaction::where('user_id', $user->id)
                                    ->where('type', 'deposit')
                                    ->count();

        if ($totalDeposits === 1) {
            $achievementController = new AchievementController();
            $achievementController->unlock($user, 'first_deposit');
        }
        
        return $wallet;
    }

    public function withdraw(User $user, float $amount, string $description = 'Retiro')
    {
        if ($amount <= 0) {
            throw new \Exception("Monto inválido");
        }

        $wallet = $this->getOrCreateWallet($user);
        
        // 💡 CORRECCIÓN: Aseguramos que el saldo sea el actual de la DB antes de verificar fondos.
        $wallet->refresh(); 
        if ($wallet->balance < $amount) {
            throw new \Exception('Fondos insuficientes');
        }

        $balanceBefore = $wallet->balance;

        // Reducir saldo
        $wallet->decrement('balance', $amount);
        $wallet->refresh();

        // Registrar transacción
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'type' => 'withdraw',
            'description' => $description,
            'balance_before' => $balanceBefore,
            'balance_after' => $wallet->balance
        ]);

        return $wallet;
    }
    
    /**
     * Registra una apuesta (bet) y actualiza las métricas de juego (consecutive_wins).
     */
    public function debitBet(User $user, float $amount, string $description = 'Apuesta')
    {
        if ($amount <= 0) {
            throw new \Exception("Monto inválido para apuesta");
        }

        $wallet = $this->getOrCreateWallet($user);
        $wallet->refresh();
        
        if ($wallet->balance < $amount) {
            throw new \Exception('Fondos insuficientes para la apuesta');
        }

        $balanceBefore = $wallet->balance;

        // Reducir saldo (registrar la apuesta)
        $wallet->decrement('balance', $amount);
        $wallet->refresh();

        // Registrar transacción de apuesta
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'type' => 'bet', 
            'description' => $description,
            'balance_before' => $balanceBefore,
            'balance_after' => $wallet->balance
        ]);
        
        // Llama a la lógica de métricas: Apuesta es una derrota para la racha
        $this->updateGameMetrics($user, false); 

        return $wallet;
    }
    
    /**
     * Registra una ganancia (win) y verifica el logro de ganancias acumuladas (earnings_100).
     */
    public function creditEarnings(User $user, float $amount, string $description = 'Ganancia de juego')
    {
        if ($amount <= 0) {
            throw new \Exception("Monto inválido para ganancia");
        }

        $wallet = $this->getOrCreateWallet($user);
        
        // 💡 CORRECCIÓN: Aseguramos que el saldo sea el actual de la DB.
        $wallet->refresh();
        $balanceBefore = $wallet->balance;

        // 1. Aumentar saldo
        $wallet->increment('balance', $amount);
        $wallet->refresh();

        // 2. Registrar transacción de ganancia
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'type' => 'win', // <-- Valor de tipo de transacción de ganancia
            'description' => $description,
            'balance_before' => $balanceBefore,
            'balance_after' => $wallet->balance
        ]);
        
        // ------------------------------------------
        // LÓGICA DEL LOGRO 'earnings_100'
        // ------------------------------------------

        // A. Sumar todas las ganancias del usuario (usando 'win' en la BD)
        // ⚠️ Redondeamos la suma para evitar errores de punto flotante
        $totalEarnings = round(floatval(Transaction::where('user_id', $user->id)
                                             ->where('type', 'win') 
                                             ->sum('amount')), 2);
        
        // B. Diagnóstico: Verifica este valor en storage/logs/laravel.log
        Log::info("✅ Diagnóstico de Ganancias Acumuladas. Usuario {$user->id}: S/{$totalEarnings}");


        // C. Chequear si se superó el umbral (S/100)
        if ($totalEarnings >= 100.00) {
            
            try {
                $achievementController = new AchievementController();
                $achievementController->unlock($user, 'earnings_100');
                Log::info("¡🎉 Logro 'earnings_100' desbloqueado para {$user->id}!");
            } catch (\Throwable $e) {
                Log::error("🔴 ERROR CRÍTICO al desbloquear 'earnings_100': " . $e->getMessage());
            }
        }
        
        // ------------------------------------------
        // LÓGICA DE JUEGO ADICIONAL
        // ------------------------------------------

        // 💡 Llama al nuevo método para actualizar contadores
        $this->updateGameMetrics($user, true); // true = es una victoria

        return $wallet; 
    }
    
    /**
     * Actualiza los contadores de juego y verifica los logros de juego.
     * DEBE llamarse al final de cada partida (win o loss).
     */
    public function updateGameMetrics(User $user, bool $isWin)
    {
        // Forzamos la carga más reciente del modelo User para asegurar la actualización
        $user->refresh(); 
        $achievementController = new AchievementController();

        // 1. Lógica de Partidas Jugadas (Logro: 50 partidas)
        $user->games_played = $user->games_played + 1;

        if ($user->games_played === 50) {
            $achievementController->unlock($user, 'total_games_50');
        }

        // 2. Lógica de Victorias Consecutivas (Logro: 5 victorias)
        if ($isWin) {
            // Si ganó, incrementa la racha
            $user->consecutive_wins = $user->consecutive_wins + 1;
            
            if ($user->consecutive_wins === 5) {
                $achievementController->unlock($user, 'consecutive_wins_5');
            }
        } else {
            // Si perdió, resetea la racha a cero
            $user->consecutive_wins = 0;
        }

        // 3. Guardar las métricas actualizadas
        $user->save();

        Log::info("✅ Métricas de juego actualizadas para Usuario {$user->id}: Partidas jugadas={$user->games_played}, Racha={$user->consecutive_wins}");
    }


    public function getBalance(User $user)
    {
        return $this->getOrCreateWallet($user)->balance;
    }
}