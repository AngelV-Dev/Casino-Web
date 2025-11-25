<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Moderator\ModerationController;
use App\Http\Controllers\Support\TicketController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CrashController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Página principal pública
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// ========== RUTAS PARA SUPER ADMIN ==========
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::get('/admins', [UserManagementController::class, 'manageAdmins']);
    Route::post('/admins', [UserManagementController::class, 'createAdmin']);
    Route::delete('/admins/{user}', [UserManagementController::class, 'deleteAdmin']);
    Route::get('/settings', [UserManagementController::class, 'settings']);
});

// ========== RUTAS PARA ADMIN Y SUPER ADMIN ==========
Route::middleware(['auth', 'role:super_admin,admin'])->prefix('admin')->group(function () {
    Route::get('/users', [UserManagementController::class, 'index']);
    Route::post('/users', [UserManagementController::class, 'store']);
    Route::put('/users/{user}', [UserManagementController::class, 'update']);
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy']);
    Route::put('/users/{user}/role', [UserManagementController::class, 'updateRole']);
    Route::put('/users/{user}/suspend', [UserManagementController::class, 'suspend']);
    Route::put('/users/{user}/ban', [UserManagementController::class, 'ban']);
});

// ========== RUTAS PARA MODERADORES ==========
Route::middleware(['auth', 'role:moderator,admin,super_admin'])->prefix('moderator')->group(function () {
    Route::get('/users', [ModerationController::class, 'index']);
    Route::put('/users/{user}/suspend', [ModerationController::class, 'suspend']);
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply']);
});

// ========== RUTAS PARA SOPORTE ==========
Route::middleware(['auth', 'role:support,moderator,admin,super_admin'])->prefix('support')->group(function () {
    Route::get('/tickets', [TicketController::class, 'myTickets']);
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply']);
    Route::put('/tickets/{ticket}/close', [TicketController::class, 'close']);
});

// ========== Ruta con permisos específicos ==========
Route::middleware(['auth', 'permission:manage_users'])->group(function () {
    Route::post('/users', [UserManagementController::class, 'store']);
});

// Dashboard privado
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ========== PERFIL ==========
Route::middleware('auth')->group(function () {

    // Perfil
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Guardar avatar
    Route::post('/profile/select-avatar', [ProfileController::class, 'selectAvatar'])
        ->name('profile.select-avatar');
});

// ========== WALLET ==========
Route::middleware(['auth'])->group(function () {
    Route::get('/wallet', [WalletController::class, 'show'])->name('wallet.show');
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
});

// ========== CRASH GAME ==========
Route::middleware(['auth'])->group(function () {

    // Página principal del juego (VIEW)
    Route::get('/crash', function () {
        return Inertia::render('Crash');
    })->name('crash.index');

    // API del juego
    Route::post('/crash/start', [CrashController::class, 'start'])->name('crash.start');
    Route::post('/crash/cashout', [CrashController::class, 'cashout'])->name('crash.cashout');
    Route::get('/crash/history', [CrashController::class, 'history'])->name('crash.history');
});

// Breeze auth routes
require __DIR__.'/auth.php';
