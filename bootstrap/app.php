<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // --- REGISTRO DE ALIASES (CORREGIDO) ---
        $middleware->alias([
            // Este es el guardia de roles (Para Admin/Moderator)
            // Asegúrate de que el archivo en App/Http/Middleware se llame RoleMiddleware.php
            'role' => \App\Http\Middleware\CheckRole::class,

            // Este es el guardia de baneo (Para sacar a usuarios suspendidos)
            // Apuntamos a tu archivo existente CheckUserStatus.php
            'banned' => \App\Http\Middleware\CheckUserStatus::class, 
            
            // (Opcional) Si usas permisos puntuales
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();