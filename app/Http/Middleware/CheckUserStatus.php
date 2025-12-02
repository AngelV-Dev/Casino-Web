<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        // Revisamos si hay usuario y si NO está activo
        if (Auth::check() && !Auth::user()->isActive()) {
            $user = Auth::user();
            
            // 1. Cerrar sesión
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            // 2. Preparar los datos para el Modal (ESTO ES LO NUEVO)
            $banInfo = [
                'type' => $user->status, // 'banned' o 'suspended'
                'title' => $user->status === 'banned' ? '⛔ CUENTA BANEADA' : '⏳ CUENTA SUSPENDIDA',
                'message' => $user->status === 'banned' 
                    ? 'Tu cuenta ha sido suspendida permanentemente.' 
                    : 'Tu acceso ha sido restringido temporalmente.',
                'reason' => $user->suspension_reason ?? 'Sin motivo especificado',
            ];
            
            // 3. Redirigir enviando 'ban_info'
            return redirect()->route('login')->with('ban_info', $banInfo);
        }

        return $next($request);
    }
}