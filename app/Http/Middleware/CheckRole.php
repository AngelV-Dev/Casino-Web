<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Maneja la petición y verifica roles.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  mixed  ...$roles  Roles permitidos
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();

        // Si no está logueado, redirigir al login
        if (!$user) {
            return redirect()->route('login');
        }

        // Si no tiene ninguno de los roles permitidos, abortar con 403
        if (!$user->hasAnyRole($roles)) {
            abort(403, 'No tienes permisos para acceder a esta sección');
        }

        return $next($request);
    }
}
