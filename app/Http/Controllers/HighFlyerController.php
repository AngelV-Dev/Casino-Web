<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HighFlyerController extends Controller
{
    public function index()
    {
        return view('games.high-flyer');
    }

    public function startGame(Request $request)
    {
        $user = Auth::user();
        $betAmount = $request->input('bet');

        // Validar saldo suficiente
        if ($user->balance < $betAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo insuficiente'
            ]);
        }

        // Restar apuesta del saldo
        $user->balance -= $betAmount;
        $user->save();

        return response()->json([
            'success' => true,
            'new_balance' => $user->balance,
            'message' => '¡Vuelo iniciado!'
        ]);
    }

    public function cashOut(Request $request)
    {
        $user = Auth::user();
        $multiplier = $request->input('multiplier');
        $winnings = $request->input('winnings');

        // Sumar ganancias
        $user->balance += $winnings;
        $user->save();

        return response()->json([
            'success' => true,
            'new_balance' => $user->balance,
            'message' => '¡Retiro exitoso!'
        ]);
    }

    public function gameCrashed(Request $request)
    {
        // Registrar la pérdida (opcional)
        return response()->json([
            'success' => true,
            'message' => 'Juego terminado'
        ]);
    }
}