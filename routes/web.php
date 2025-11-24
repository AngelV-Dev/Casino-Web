<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Moderator\ModerationController;
use App\Http\Controllers\Support\TicketController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\WalletController;
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

// ========== EJEMPLO CON PERMISOS ESPECÍFICOS ==========
Route::middleware(['auth', 'permission:manage_users'])->group(function () {
Route::post('/users', [UserManagementController::class, 'store']);
});

// Dashboard privado para usuarios logueados
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ========== PERFIL DE USUARIO ==========
Route::middleware('auth')->group(function () {

// Ver perfil
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

// Editar perfil
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

// Guardar avatar elegido
Route::post('/profile/select-avatar', [ProfileController::class, 'selectAvatar'])
    ->name('profile.select-avatar');
});

// Wallet
Route::middleware(['auth'])->group(function () {
    Route::get('/wallet', [WalletController::class, 'show'])->name('wallet.show');
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
});

// Rutas de autenticación de Breeze
require __DIR__.'/auth.php';

