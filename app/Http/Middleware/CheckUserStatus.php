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
        $user = Auth::user();

        if ($user && !$user->isActive()) {
            Auth::logout();
            
            $message = $user->isSuspended() 
                ? 'Tu cuenta ha sido suspendida. Razón: ' . $user->suspension_reason
                : 'Tu cuenta ha sido baneada permanentemente.';
            
            return redirect()->route('login')
                ->with('error', $message);
        }

        return $next($request);
    }
}