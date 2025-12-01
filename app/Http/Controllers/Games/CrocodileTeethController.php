<?php
namespace App\Http\Controllers\Games;

use App\Http\Controllers\Controller;
use App\Services\Games\CrocodileTeethService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CrocodileTeethController extends Controller
{
    /**
     * Mostrar la página del juego
     */
    public function index()
    {
        $user = Auth::user();
        
        return Inertia::render('Games/CrocodileTeeth', [
            'balance' => (float) ($user->wallet->balance ?? 0),
            'user' => $user,
        ]);
    }

    /**
     * Iniciar nueva partida
     */
    public function startGame(Request $request)
    {
        $request->validate([
            'bet_amount' => 'required|numeric|min:0.10|max:10000',
            'red_teeth' => 'required|integer|min:1|max:19',
        ]);

        try {
            $service = new CrocodileTeethService(Auth::user());
            $result = $service->startGame(
                $request->bet_amount,
                $request->red_teeth
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Click en un diente
     */
    public function clickTooth(Request $request)
    {
        $request->validate([
            'session_id' => 'required|integer',
            'tooth_position' => 'required|integer|min:0|max:19',
        ]);

        try {
            $service = new CrocodileTeethService(Auth::user());
            $result = $service->clickTooth(
                $request->session_id,
                $request->tooth_position
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cash Out
     */
    public function cashOut(Request $request)
    {
        $request->validate([
            'session_id' => 'required|integer',
        ]);

        try {
            $service = new CrocodileTeethService(Auth::user());
            $result = $service->cashOut($request->session_id);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}