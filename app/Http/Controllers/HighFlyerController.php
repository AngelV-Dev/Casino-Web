<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HighFlyerController extends Controller
{
    public function index()
    {
        return inertia('HighFlyer');
    }

    public function startGame(Request $request)
    {
        try {
            $request->validate([
                'bet' => 'required|numeric|min:1',
            ]);

            $user = Auth::user();
            $betAmount = (float) $request->bet;

            // Obtener o crear wallet
            $wallet = $user->wallet;
            
            if (!$wallet) {
                $wallet = $user->wallet()->create(['balance' => 0]);
            }

            // Validar saldo suficiente
            if ($wallet->balance < $betAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente'
                ], 400);
            }

            // Restar apuesta
            $wallet->balance -= $betAmount;
            $wallet->save();

            // ❌ COMENTADO TEMPORALMENTE - Arreglar después
            /*
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'bet',
                'amount' => -$betAmount,
                'description' => 'Apuesta en High Flyer',
            ]);
            */

            Log::info('Apuesta realizada', [
                'user_id' => $user->id,
                'bet' => $betAmount,
                'new_balance' => $wallet->balance
            ]);

            return response()->json([
                'success' => true,
                'new_balance' => $wallet->balance,
                'message' => '¡Vuelo iniciado!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en startGame: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cashOut(Request $request)
    {
        try {
            $request->validate([
                'multiplier' => 'required|numeric|min:1',
                'winnings' => 'required|numeric|min:0',
            ]);

            $user = Auth::user();
            $wallet = $user->wallet;
            $winnings = (float) $request->winnings;

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet no encontrado'
                ], 400);
            }

            // Sumar ganancias
            $wallet->balance += $winnings;
            $wallet->save();

            // ❌ COMENTADO TEMPORALMENTE - Arreglar después
            /*
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'win',
                'amount' => $winnings,
                'description' => "Ganancia en High Flyer ({$request->multiplier}x)",
            ]);
            */

            Log::info('Cashout exitoso', [
                'user_id' => $user->id,
                'winnings' => $winnings,
                'new_balance' => $wallet->balance
            ]);

            return response()->json([
                'success' => true,
                'new_balance' => $wallet->balance,
                'message' => '¡Retiro exitoso!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en cashOut: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }

    public function gameCrashed(Request $request)
    {
        Log::info('Juego crashed', [
            'user_id' => Auth::id(),
            'crash_at' => $request->crash_at
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Juego terminado'
        ]);
    }
}