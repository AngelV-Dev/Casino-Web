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
use App\Http\Controllers\CrashController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HighFlyerController; 

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
    
    // ⬇️⬇️⬇️ AGREGAR ESTAS DOS RUTAS NUEVAS ⬇️⬇️⬇️
    Route::post('/profile/select-banner', [ProfileController::class, 'selectBanner'])
        ->name('profile.select-banner');
    
    Route::post('/profile/update-bio', [ProfileController::class, 'updateBio'])
        ->name('profile.update-bio');
});

// Wallet
Route::middleware(['auth'])->group(function () {
    Route::get('/wallet', [WalletController::class, 'show'])->name('wallet.show');          // Ver saldo
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');   // Depositar
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw'); // Retirar
});

// GRUPO: JUEGOS (Casino)
Route::get('/slots', function () {
    return inertia('Slots');
})->middleware('auth')->name('slots');

Route::get('/crash', function () {
    return inertia('Crash');
})->middleware('auth')->name('crash');

Route::get('/juegosturbo', function () {
    return inertia('Juegosturbo');
})->middleware('auth')->name('juegosturbo');

Route::get('/ruleta', function () {
    return inertia('Ruleta');
})->middleware('auth')->name('ruleta');

Route::get('/comunidad', function () {
    return inertia('Comunidad');
})->middleware('auth')->name('comunidad');

Route::get('/settings', function () {
    return inertia('Settings');
})->middleware('auth')->name('settings');


// ========== CRASH GAME ==========
// HIGH FLYER GAME
Route::middleware(['auth'])->group(function () {
    // Página del juego
    Route::get('/high-flyer', function () {
        return inertia('Games/HighFlyer', [
            'user' => Auth::user()
        ]);
    })->name('high-flyer');

    // ✅ Rutas API del juego (CON middleware auth)
    Route::post('/games/high-flyer/start', [HighFlyerController::class, 'startGame']);
    Route::post('/games/high-flyer/cashout', [HighFlyerController::class, 'cashOut']);
    Route::post('/games/high-flyer/crash', [HighFlyerController::class, 'gameCrashed']);
});

require __DIR__.'/auth.php';

