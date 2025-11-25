<?php

/**
 * ==========================================
 * LÓGICA DE RUTAS WEB (Laravel + Inertia)
 * ==========================================
 * Aquí se definen todas las URLs accesibles desde el navegador.
 * Se conectan las rutas con los Controladores y se renderizan las Vistas (Vue).
 */

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Moderator\ModerationController;
use App\Http\Controllers\Support\TicketController;
use App\Http\Controllers\WalletController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
| Estas rutas son accesibles por cualquier visitante sin iniciar sesión.
*/

// Página Principal (Landing Page)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),       // Verifica si la ruta login existe para mostrar el botón
        'canRegister' => Route::has('register'), // Verifica si el registro está activado
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| PANEL DE ADMINISTRACIÓN Y ROLES (Backoffice)
|--------------------------------------------------------------------------
| Rutas protegidas por Middleware de autenticación y roles específicos.
*/

// 1. ZONA SUPER ADMIN (Acceso total y configuración sensible)
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->group(function () {
    // Gestión de administradores
    Route::get('/admins', [UserManagementController::class, 'manageAdmins']);   // Ver lista
    Route::post('/admins', [UserManagementController::class, 'createAdmin']);   // Crear nuevo admin
    Route::delete('/admins/{user}', [UserManagementController::class, 'deleteAdmin']); // Eliminar admin
    
    // Configuración global del sitio
    Route::get('/settings', [UserManagementController::class, 'settings']);
});

// 2. ZONA ADMIN & SUPER ADMIN (Gestión de usuarios común)
Route::middleware(['auth', 'role:super_admin,admin'])->prefix('admin')->group(function () {
    Route::get('/users', [UserManagementController::class, 'index']);           // Listar usuarios
    Route::post('/users', [UserManagementController::class, 'store']);          // Crear usuario manual
    Route::put('/users/{user}', [UserManagementController::class, 'update']);   // Editar usuario
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy']); // Eliminar usuario
    
    // Acciones críticas sobre usuarios
    Route::put('/users/{user}/role', [UserManagementController::class, 'updateRole']); // Cambiar rol
    Route::put('/users/{user}/suspend', [UserManagementController::class, 'suspend']); // Suspender temporalmente
    Route::put('/users/{user}/ban', [UserManagementController::class, 'ban']);         // Banear permanentemente
});

// 3. ZONA MODERADORES (Control de contenido y comportamiento)
Route::middleware(['auth', 'role:moderator,admin,super_admin'])->prefix('moderator')->group(function () {
    Route::get('/users', [ModerationController::class, 'index']);               // Ver usuarios para moderar
    Route::put('/users/{user}/suspend', [ModerationController::class, 'suspend']); // Acción rápida de suspensión
    
    // Gestión de Tickets (Nivel 1)
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply']);
});

// 4. ZONA SOPORTE (Atención al cliente)
Route::middleware(['auth', 'role:support,moderator,admin,super_admin'])->prefix('support')->group(function () {
    Route::get('/tickets', [TicketController::class, 'myTickets']);             // Mis tickets asignados
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply']); // Responder
    Route::put('/tickets/{ticket}/close', [TicketController::class, 'close']);  // Cerrar ticket resuelto
});

// Ejemplo: Ruta protegida por un permiso específico ("manage_users")
Route::middleware(['auth', 'permission:manage_users'])->group(function () {
    Route::post('/users', [UserManagementController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| ZONA DE USUARIO (Frontend Privado)
|--------------------------------------------------------------------------
| Rutas para usuarios logueados (Jugadores).
*/

// Dashboard Principal
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


// GRUPO: PERFIL DE USUARIO
Route::middleware('auth')->group(function () {
    // Visualización del perfil público/privado
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    // Edición y Configuración
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');       // Formulario
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');     // Guardar cambios
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');  // Eliminar cuenta

    // Selección de Avatar (AJAX/Form)
    Route::post('/profile/select-avatar', [ProfileController::class, 'selectAvatar'])
        ->name('profile.select-avatar');
});

// GRUPO: BILLETERA Y ECONOMÍA (Wallet)
Route::middleware(['auth'])->group(function () {
    Route::get('/wallet', [WalletController::class, 'show'])->name('wallet.show');          // Ver saldo
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');   // Depositar
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw'); // Retirar
});

// GRUPO: JUEGOS (Casino)
Route::get('/slots', function () {
    return inertia('Slots');
})->middleware('auth')->name('slots');

/*
|--------------------------------------------------------------------------
| RUTAS DE AUTENTICACIÓN (Laravel Breeze)
|--------------------------------------------------------------------------
| Login, Registro, Reset Password, Email Verification, etc.
*/
require __DIR__.'/auth.php';