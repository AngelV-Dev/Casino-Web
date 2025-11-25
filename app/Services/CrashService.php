<?php

namespace App\Services;

use App\Models\User;
use App\Models\CrashGame;
use App\Services\WalletService;

class CrashService
{
    protected $wallet;

    public function __construct()
    {
        $this->wallet = new WalletService();
    }

    // Iniciar una ronda
    public function startGame(User $user, float $betAmount)
    {
        if ($betAmount <= 0) {
            throw new \Exception("Monto inválido");
        }

        // Verificar saldo
        $balance = $this->wallet->getBalance($user);
        if ($balance < $betAmount) {
            throw new \Exception("Fondos insuficientes");
        }

        // Cobrar apuesta
        $this->wallet->withdraw($user, $betAmount, 'Apuesta Crash');

        // Generar multiplicador aleatorio (entre 1.00 y 20.00)
        $crashPoint = $this->generateCrashPoint();

        // Crear registro de juego
        return CrashGame::create([
            'user_id'  => $user->id,
            'bet'      => $betAmount,
            'crash_at' => $crashPoint,
            'status'   => 'running'
        ]);
    }

    // Usuario cobra antes del crash
    public function cashOut(CrashGame $game, float $multiplier)
    {
        if ($game->status !== 'running') {
            throw new \Exception("El juego ya terminó");
        }

        $profit = $game->bet * $multiplier;

        // Depositar ganancia
        $this->wallet->deposit($game->user, $profit, "Crash Cashout x{$multiplier}");

        $game->update([
            'status'      => 'cashed_out',
            'cashout_at'  => $multiplier,
            'profit'      => $profit
        ]);

        return $profit;
    }

    // Genera multiplicador (algoritmo simple)
    private function generateCrashPoint()
    {
        return round(mt_rand(100, 2000) / 100, 2);
    }
}
