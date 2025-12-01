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
use App\Http\Controllers\AchievementController;
use App\Http\Controllers\Admin\GameController; 
use App\Http\Controllers\DiceGameController;
use App\Http\Controllers\SlotGameController; // Importado de la nueva versión
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HighFlyerController;
use Illuminate\Support\Facades\Auth; // Añadido para las funciones anónimas

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
| Estas rutas son accesibles por cualquier visitante sin iniciar sesión.
*/

// Página Principal (Landing Page)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),        // Verifica si la ruta login existe para mostrar el botón
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
    Route::get('/admins', [UserManagementController::class, 'manageAdmins']);    // Ver lista
    Route::post('/admins', [UserManagementController::class, 'createAdmin']);    // Crear nuevo admin
    Route::delete('/admins/{user}', [UserManagementController::class, 'deleteAdmin']); // Eliminar admin
    
    // Configuración global del sitio
    Route::get('/settings', [UserManagementController::class, 'settings']);
});

// 2. ZONA ADMIN & SUPER ADMIN (Gestión de usuarios común)
Route::middleware(['auth', 'role:super_admin,admin'])->prefix('admin')->group(function () {
    Route::get('/users', [UserManagementController::class, 'index']);             // Listar usuarios
    Route::post('/users', [UserManagementController::class, 'store']);            // Crear usuario manual
    Route::put('/users/{user}', [UserManagementController::class, 'update']);     // Editar usuario
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy']); // Eliminar usuario
    
    // Acciones críticas sobre usuarios
    Route::put('/users/{user}/role', [UserManagementController::class, 'updateRole']); // Cambiar rol
    Route::put('/users/{user}/suspend', [UserManagementController::class, 'suspend']); // Suspender temporalmente
    Route::put('/users/{user}/ban', [UserManagementController::class, 'ban']);         // Banear permanentemente
});

// 3. ZONA MODERADORES (Control de contenido y comportamiento)
Route::middleware(['auth', 'role:moderator,admin,super_admin'])->prefix('moderator')->group(function () {
    Route::get('/users', [ModerationController::class, 'index']);                 // Ver usuarios para moderar
    Route::put('/users/{user}/suspend', [ModerationController::class, 'suspend']); // Acción rápida de suspensión
    
    // Gestión de Tickets (Nivel 1)
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply']);
});

// 4. ZONA SOPORTE (Atención al cliente)
Route::middleware(['auth', 'role:support,moderator,admin,super_admin'])->prefix('support')->group(function () {
    Route::get('/tickets', [TicketController::class, 'myTickets']);                 // Mis tickets asignados
    Route::post('/tickets/{ticket}/reply', [TicketController::class, 'reply']);     // Responder
    Route::put('/tickets/{ticket}/close', [TicketController::class, 'close']);      // Cerrar ticket resuelto
});

// ========== EJEMPLO CON PERMISOS ESPECÍFICOS ==========
Route::middleware(['auth', 'permission:manage_users'])->group(function () {
    Route::post('/users', [UserManagementController::class, 'store']);
});

// Dashboard privado para usuarios logueados
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ========== GRUPO: PERFIL DE USUARIO Y DATOS AJAX ==========
Route::middleware('auth')->group(function () {

    // Rutas de edición de perfil (Se mantienen)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de actualización de perfil
    Route::post('/profile/select-avatar', [ProfileController::class, 'selectAvatar'])->name('profile.select-avatar');
    Route::post('/profile/select-banner', [ProfileController::class, 'selectBanner'])->name('profile.select-banner');
    Route::post('/profile/update-bio', [ProfileController::class, 'updateBio'])->name('profile.update-bio');

    // Rutas de Juego Favorito (Se mantienen)
    Route::post('/profile/set-favorite-game', [ProfileController::class, 'setFavoriteGame'])->name('profile.set-favorite-game');
    Route::get('/profile/favorite-game', [ProfileController::class, 'getFavoriteGame'])->name('profile.get-favorite-game');
    Route::post('/profile/update-favorite-stats', [ProfileController::class, 'updateFavoriteGameStats'])->name('profile.update-favorite-stats');

    // 🌟 RUTAS DE DATOS DE PERFIL (Controladores)
    Route::get('/user-data/achievements', [AchievementController::class, 'getUserAchievements'])->name('user.achievements.json');
    Route::get('/user-data/activities', [ProfileController::class, 'getActivitiesJson'])->name('user.activities.json');

    // Desbloquear logro manualmente (Controlador)
    Route::post('/api/achievements/{key}/unlock', [AchievementController::class, 'unlock'])->name('api.unlock-achievement');
});

// Wallet
Route::middleware(['auth'])->group(function () {
    Route::get('/wallet', [WalletController::class, 'show'])->name('wallet.show');          // Ver saldo
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');    // Depositar
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw'); // Retirar
});

// GRUPO: JUEGOS (Casino)
Route::middleware(['auth'])->group(function () {
    // === Rutas de Vistas (Inertia) ===
    Route::get('/crash', function () {
        return inertia('Crash');
    })->name('crash');

    Route::get('/juegosturbo', function () {
        return inertia('Juegosturbo');
    })->name('juegosturbo');

    Route::get('/ruleta', function () {
        return inertia('Ruleta');
    })->name('ruleta');

    Route::get('/comunidad', function () {
        return inertia('Comunidad');
    })->name('comunidad');

    Route::get('/settings', function () {
        return inertia('Settings');
    })->name('settings');

    // === RUTAS DEL JUEGO SLOTS/TRAGAMONEDAS (Unificado) ===
    Route::get('/slots', [SlotGameController::class, 'index'])->name('slots.index'); // Usamos el controlador para la vista
    Route::post('/slots/spin', [SlotGameController::class, 'spin'])->name('slots.spin'); // API para girar

    // === RUTAS DEL JUEGO DICE (Unificado) ===
    Route::get('/dice', [DiceGameController::class, 'index'])->name('dice.index'); // Usamos el controlador para la vista
    Route::post('/dice/play', [DiceGameController::class, 'play'])->name('dice.play'); // API para jugar
    Route::get('/dice/history', [DiceGameController::class, 'history'])->name('dice.history'); // Historial de apuestas

    // === RUTAS CRASH/HIGH FLYER (Unificado) ===
    // Página del juego (HighFlyer)
    Route::get('/high-flyer', function () {
        return inertia('Games/HighFlyer', [
            'user' => Auth::user()
        ]);
    })->name('high-flyer');

    // Rutas API High Flyer
    Route::post('/games/high-flyer/start', [HighFlyerController::class, 'startGame']);
    Route::post('/games/high-flyer/cashout', [HighFlyerController::class, 'cashOut']);
    Route::post('/games/high-flyer/crash', [HighFlyerController::class, 'gameCrashed']);

    // Rutas API Crash (Común)
    Route::post('/crash/start', [CrashController::class, 'start'])->name('crash.start');
    Route::post('/crash/cashout', [CrashController::class, 'cashout'])->name('crash.cashout');
    Route::get('/crash/history', [CrashController::class, 'history'])->name('crash.history');

    // Ruta de simulación (siempre mantener)
    Route::post('/game/simulate-win', [GameController::class, 'simulateWin'])->middleware('auth:sanctum');

    // === RUTAS DE API DE PERFIL/ACTIVIDAD (Sección Duplicada - Ahora limpia) ===
    // Usamos funciones anónimas solo para las APIs que tenían conflicto en ese formato
    Route::get('/api/user/achievements', function () {
        $user = Auth::user();
        return app(App\Http\Controllers\AchievementController::class)->getUserAchievements($user);
    })->name('api.achievements');

    Route::get('/api/user/activities', function () {
        $user = Auth::user();
        $activities = $user->activities()->limit(20)->get();
        return response()->json(['activities' => $activities]);
    })->name('api.activities');

    Route::post('/api/achievements/{key}/unlock-api', function ($key) {
        $user = Auth::user();
        return app(App\Http\Controllers\AchievementController::class)->unlock($user, $key);
    })->name('api.unlock-achievement-api-call');
});

/*
|--------------------------------------------------------------------------
| RUTAS DE AUTENTICACIÓN (Laravel Breeze)
|--------------------------------------------------------------------------
| Login, Registro, Reset Password, Email Verification, etc.
*/
require __DIR__.'/auth.php';