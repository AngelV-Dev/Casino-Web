<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    // Mostrar wallet
    public function show()
{
    $wallet = Auth::user()->wallet;

    return Inertia::render('Wallet', [
        'balance' => $wallet ? floatval($wallet->balance) : 0
    ]);
}


    // Depositar dinero (JSON)
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $wallet = $this->walletService->deposit(Auth::user(), $request->amount);

            return response()->json([
                'success' => true,
                'balance' => $wallet->balance,
                'message' => 'Depósito exitoso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    // Retirar dinero (JSON)
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $wallet = $this->walletService->withdraw(Auth::user(), $request->amount);

            return response()->json([
                'success' => true,
                'balance' => $wallet->balance,
                'message' => 'Retiro exitoso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
