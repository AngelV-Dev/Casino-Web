<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;

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
            'balance' => $wallet->balance ?? 0
        ]);
    }

    // Depositar dinero
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $wallet = $this->walletService->deposit(auth()->user(), $request->amount);

        return Inertia::render('Wallet/Index', [
            'balance' => $wallet->balance,
            'message' => 'Depósito exitoso',
        ]);
    }



    // Retirar dinero
    public function withdraw(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);

        $wallet = $this->walletService->withdraw(Auth::user(), (float)$request->amount);

        return response()->json([
            'balance' => $wallet->balance
        ]);
    }
}
