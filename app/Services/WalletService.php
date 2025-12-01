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
     * Registra una ganancia (win) y verifica el logro de ganancias acumuladas (earnings_100).
     */
    public function creditEarnings(User $user, float $amount, string $description = 'Ganancia de juego')
    {
        if ($amount <= 0) {
            throw new \Exception("Monto inválido para ganancia");
        }

        $wallet = $this->getOrCreateWallet($user);
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
        // ⚠️ CORRECCIÓN CLAVE: Redondeamos la suma para evitar errores de punto flotante (float)
        $totalEarnings = round(floatval(Transaction::where('user_id', $user->id)
                                                  ->where('type', 'win') 
                                                  ->sum('amount')), 2);
        
        // B. Diagnóstico: Verifica este valor en storage/logs/laravel.log
        Log::info("✅ Diagnóstico de Ganancias Acumuladas. Usuario {$user->id}: S/{$totalEarnings}");


        // C. Chequear si se superó el umbral (S/100)
        if ($totalEarnings >= 100.00) {
            
            // ------------------------------------------
            // ⚠️ CORRECCIÓN: Usamos try-catch para capturar fallos de instanciación
            // ------------------------------------------
            try {
                $achievementController = new AchievementController();
                $achievementController->unlock($user, 'earnings_100');
                Log::info("¡🎉 Logro 'earnings_100' desbloqueado para {$user->id}!");
            } catch (\Throwable $e) {
                // Si falla, lo registramos. ¡ESTA LÍNEA DEBE APARECER SI HAY UN ERROR!
                Log::error("🔴 ERROR CRÍTICO al desbloquear 'earnings_100': " . $e->getMessage());
            }
        }
        
        return $wallet; 
    }


    public function getBalance(User $user)
    {
        return $this->getOrCreateWallet($user)->balance;
    }
}