<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WalletService; // Importamos el servicio
use Illuminate\Support\Facades\Auth; // Necesario para obtener el usuario actual

class GameController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        // Inyección de dependencias del servicio de billetera
        $this->walletService = $walletService;
    }

    /**
     * Simula el final de una partida donde el usuario gana.
     */
    public function simulateWin(Request $request)
    {
        $user = Auth::user();

        // 1. Validar la cantidad ganada (simulada)
        $request->validate([
            'win_amount' => 'required|numeric|min:0.01'
        ]);

        $winAmount = $request->win_amount; // La cantidad que envías en la petición

        // 2. AÑADIR GANANCIA Y VERIFICAR LOGROS
        // Llamamos al método creditEarnings que creamos en WalletService.
        // Este método se encarga de:
        // a) Registrar la transacción de tipo 'win'.
        // b) Sumar el total de ganancias.
        // c) Desbloquear 'earnings_100' si totalEarnings >= 100.
        try {
            $wallet = $this->walletService->creditEarnings($user, $winAmount, 'Ganancia de juego simulada');
            
            return response()->json([
                'success' => true,
                'message' => "Ganaste S/{$winAmount}. Saldo actual: S/{$wallet->balance}",
                'balance' => $wallet->balance,
                'user_id' => $user->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la ganancia: ' . $e->getMessage()
            ], 500);
        }
    }
}