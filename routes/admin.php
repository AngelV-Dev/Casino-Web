<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\GameController;

// Middleware para asegurarnos que solo el admin accede
Route::middleware(['auth', 'checkrole:admin'])->group(function () {
    
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Gestión de usuarios
    Route::resource('/admin/users', UserController::class);

    // Gestión de juegos
    Route::resource('/admin/games', GameController::class);
});
