<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;

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

        // Registrar transacción incluyendo wallet_id y description
        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'type' => 'deposit',
            'description' => $description,
            'balance_before' => $balanceBefore,
            'balance_after' => $wallet->balance
        ]);

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

        // Registrar transacción incluyendo wallet_id y description
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

    public function getBalance(User $user)
    {
        return $this->getOrCreateWallet($user)->balance;
    }
}
