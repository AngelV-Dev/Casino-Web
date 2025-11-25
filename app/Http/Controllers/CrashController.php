<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WalletService;
use App\Models\CrashGame;
use Illuminate\Support\Facades\Auth;

class CrashController extends Controller
{
    protected $wallet;

    public function __construct(WalletService $wallet)
    {
        $this->wallet = $wallet;
    }

    // === START GAME ===
    public function start(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.1'
        ]);

        $user = Auth::user();
        $amount = floatval($request->amount);

        // 1. Verificar fondos
        if ($this->wallet->getBalance($user) < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Fondos insuficientes'
            ]);
        }

        // 2. Cobrar apuesta
        $this->wallet->withdraw($user, $amount, 'Apuesta Crash');

        // 3. Generar punto de explosión aleatorio
        $crashAt = $this->generateCrashPoint();

        // 4. Registrar juego
        CrashGame::create([
            'user_id' => $user->id,
            'bet_amount' => $amount,
            'crash_point' => $crashAt,
        ]);

        return response()->json([
            'success' => true,
            'new_balance' => $this->wallet->getBalance($user),
            'crash_at' => $crashAt
        ]);
    }

    // === CASHOUT ===
    public function cashout(Request $request)
    {
        $request->validate([
            'cashout_at' => 'required|numeric|min:1'
        ]);

        $cashoutAt = floatval($request->cashout_at);
        $user = Auth::user();

        // Última jugada que aún no tiene cashout
        $game = CrashGame::where('user_id', $user->id)
                         ->whereNull('cashout_at')
                         ->latest()
                         ->first();

        if (!$game) {
            return response()->json([
                'success' => false,
                'message' => 'No hay una apuesta activa.'
            ]);
        }

        // Si ya crasheó en el backend = perder
        if ($cashoutAt >= $game->crash_point) {
            // perdió
            $game->cashout_at = null;
            $game->profit = null;
            $game->save();

            return response()->json([
                'success' => false,
                'message' => 'El juego ya crasheó.'
            ]);
        }

        // Calcular ganancia
        $profit = round($game->bet_amount * $cashoutAt, 2);

        // Registrar cashout
        $game->cashout_at = $cashoutAt;
        $game->profit = $profit;
        $game->save();

        // Pagar al usuario
        $this->wallet->deposit($user, $profit, 'Cashout Crash');

        return response()->json([
            'success' => true,
            'new_balance' => $this->wallet->getBalance($user)
        ]);
    }

    // === HISTORIAL ===
    public function history()
    {
        return CrashGame::where('user_id', Auth::id())
                        ->orderBy('id', 'desc')
                        ->limit(20)
                        ->get();
    }

    // === GENERADOR DE CRASH ===
    private function generateCrashPoint()
    {
        return round(mt_rand(101, 5000) / 100, 2); 
        // desde 1.01x hasta 50.00x
    }
}
