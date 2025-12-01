<?php

namespace App\Http\Controllers;

use App\Models\DiceBet;
use App\Models\Transaction; // ⬅️ ¡IMPORTANTE! Asegúrate de que este modelo existe
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DiceGameController extends Controller
{
    /**
     * Muestra la página del juego Dice
     */
    public function index()
    {
        $user = Auth::user();
        
        // Obtener historial del usuario y FORMATEAR los datos
        $history = DiceBet::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($bet) {
                return [
                    'id' => $bet->id,
                    'bet_amount' => (float) $bet->bet_amount,
                    'target_number' => (float) $bet->target_number,
                    'direction' => $bet->direction,
                    'result_number' => (float) $bet->result_number,
                    'multiplier' => (float) $bet->multiplier,
                    'profit' => (float) $bet->profit,
                    'payout' => (float) $bet->payout,
                    'is_win' => (bool) $bet->is_win,
                    'created_at' => $bet->created_at->toISOString()
                ];
            });
        
        return Inertia::render('Dice', [
            'user' => $user,
            'balance' => (float) $this->getUserBalance($user),
            'history' => $history
        ]);
    }

    /**
     * Procesa una apuesta
     */
    public function play(Request $request)
    {
        try {
            // Validar datos
            $validated = $request->validate([
                'bet_amount' => 'required|numeric|min:0.10|max:10000',
                'target' => 'required|numeric|min:1.01|max:98.99',
                'direction' => 'required|in:under,over'
            ]);

            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }
            
            // Obtener saldo actual y wallet ⬅️ DEFINIR VARIABLES AQUI
            $wallet = $user->wallet;
            $currentBalanceBeforeBet = $this->getUserBalance($user);
            $currentBalance = $currentBalanceBeforeBet;
            
            // Verificar saldo suficiente
            if ($currentBalance < $validated['bet_amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente. Tu saldo: S/ ' . number_format($currentBalance, 2)
                ], 400);
            }

            DB::beginTransaction();

            // Generar resultado provably fair
            $serverSeed = $this->generateSeed(32);
            $clientSeed = $this->generateSeed(16);
            $nonce = DiceBet::where('user_id', $user->id)->count() + 1;
            
            // Generar número aleatorio (0.00 - 99.99)
            $resultNumber = $this->generateResult($serverSeed, $clientSeed, $nonce);
            
            // Calcular multiplicador
            $multiplier = $this->calculateMultiplier($validated['target'], $validated['direction']);
            
            // Verificar si ganó
            $isWin = $this->checkWin($resultNumber, $validated['target'], $validated['direction']);
            
            // Calcular ganancia
            $payout = $isWin ? $validated['bet_amount'] * $multiplier : 0;
            $profit = $payout - $validated['bet_amount'];
            
            // Crear registro de apuesta
            $bet = DiceBet::create([
                'user_id' => $user->id,
                'bet_amount' => $validated['bet_amount'],
                'target_number' => $validated['target'],
                'direction' => $validated['direction'],
                'result_number' => $resultNumber,
                'multiplier' => $multiplier,
                'payout' => $payout,
                'profit' => $profit,
                'is_win' => $isWin,
                'server_seed' => $serverSeed,
                'client_seed' => $clientSeed,
                'nonce' => $nonce
            ]);
            
            // ===============================================
            // 💰 REGISTRO DE TRANSACCIONES DETALLADAS
            // ===============================================
            
            // 1. REGISTRAR DEDUCCIÓN DE APUESTA (WITHDRAW)
            $balanceAfterWithdraw = $currentBalanceBeforeBet - $validated['bet_amount'];
            
            $this->createWalletTransaction(
                $user->id,
                $wallet->id,
                'withdraw',
                $validated['bet_amount'],
                'Apuesta en Dice Game', 
                $currentBalanceBeforeBet,
                $balanceAfterWithdraw
            );
            
            $newBalance = $balanceAfterWithdraw; // Saldo temporal después del withdraw

            // 2. REGISTRAR GANANCIA (WIN), SOLO SI GANÓ
            if ($isWin) {
                $winAmount = $payout; // Usamos el Payout completo para replicar tu imagen (Monto Apostado + Ganancia)
                
                $balanceAfterWin = $balanceAfterWithdraw + $winAmount;

                $this->createWalletTransaction(
                    $user->id,
                    $wallet->id,
                    'win',
                    $winAmount,
                    'Ganancia en Dice Game (Payout)',
                    $balanceAfterWithdraw,
                    $balanceAfterWin
                );
                
                $newBalance = $balanceAfterWin;
            }
            
            // 3. ACTUALIZAR SALDO FINAL EN LA WALLET
            $user->wallet->balance = $newBalance;
            $user->wallet->save(); // ⬅️ Sintaxis corregida (sin punto y coma antes)
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'result' => [
                    'result_number' => round($resultNumber, 2),
                    'is_win' => $isWin,
                    'multiplier' => round($multiplier, 2),
                    'payout' => round($payout, 2),
                    'profit' => round($profit, 2),
                    'new_balance' => round($newBalance, 2)
                ]
            ]);
            
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log del error para debugging
            Log::error('Error en Dice Game:', [
                'user_id' => Auth::id(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la apuesta',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Obtiene el historial de apuestas
     */
    public function history()
    {
        $bets = DiceBet::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return response()->json($bets);
    }

    // ========================================
    // MÉTODOS PRIVADOS DE LÓGICA DEL JUEGO
    // ========================================

    /**
     * Crea un registro de transacción de wallet ⬅️ NUEVO MÉTODO
     */
    private function createWalletTransaction($userId, $walletId, $type, $amount, $description, $balanceBefore, $balanceAfter)
    {
        Transaction::create([
            'user_id' => $userId,
            'wallet_id' => $walletId,
            'type' => $type, // 'withdraw' o 'win'
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $description,
            'reference' => null, 
            'status' => 'completed',
        ]);
    }

    /**
     * Obtiene el saldo del usuario desde la tabla wallets
     */
    private function getUserBalance($user)
    {
        // Si el usuario no tiene wallet, crear uno
        if (!$user->wallet) {
            $user->wallet()->create([
                'balance' => 0.00,
                'currency' => 'USD'
            ]);
        }
        
        return $user->wallet->balance;
    }

    /**
     * Actualiza el saldo del usuario en la tabla wallets ⬅️ ESTE MÉTODO YA NO SE USA DIRECTAMENTE EN PLAY()
     */
    private function updateUserBalance($user, $amount)
    {
        if (!$user->wallet) {
            throw new \Exception('El usuario no tiene una wallet');
        }
        
        $user->wallet->balance += $amount;
        $user->wallet->save();
        
        return $user->wallet->balance;
    }

    /**
     * Genera un seed aleatorio
     */
    private function generateSeed($length)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Genera el resultado usando provably fair
     */
    private function generateResult($serverSeed, $clientSeed, $nonce)
    {
        $hash = hash_hmac('sha256', "{$clientSeed}:{$nonce}", $serverSeed);
        $lucky = hexdec(substr($hash, 0, 8));
        $result = ($lucky % 10000) / 100;
        
        return round($result, 2);
    }

    /**
     * Calcula el multiplicador según el objetivo
     */
    private function calculateMultiplier($target, $direction, $houseEdge = 1.0)
    {
        $winChance = $direction === 'under' ? $target : (100 - $target);
        
        if ($winChance <= 0) {
            return 0;
        }
        
        $multiplier = (100 / $winChance) * (1 - ($houseEdge / 100));
        
        return round($multiplier, 4);
    }

    /**
     * Verifica si el usuario ganó
     */
    private function checkWin($result, $target, $direction)
    {
        if ($direction === 'under') {
            return $result < $target;
        }
        
        return $result > $target;
    }
}