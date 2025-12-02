<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

// --- CONTROLADORES BÁSICOS ---
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\AchievementController;

// --- CONTROLADORES DE JUEGOS ---
use App\Http\Controllers\Games\CrocodileTeethController;
use App\Http\Controllers\Games\RouletteController;
use App\Http\Controllers\CrashController;
use App\Http\Controllers\HighFlyerController;
use App\Http\Controllers\DiceGameController;
use App\Http\Controllers\SlotGameController;

// --- CONTROLADORES DE ADMIN Y TICKETS ---
use App\Http\Controllers\TicketController;                 // Para el Usuario Normal
use App\Http\Controllers\Admin\TicketManagementController; // Para el Staff (Soporte/Admin)
use App\Http\Controllers\Admin\UserManagementController;   // Para Gestión de Usuarios

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

/*
|--------------------------------------------------------------------------
| GRUPO AUTENTICADO (DASHBOARD, JUEGOS, PERFIL)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'banned'])->group(function () {
    
    // 👤 PERFIL
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // AJAX Perfil
    Route::post('/profile/select-avatar', [ProfileController::class, 'selectAvatar'])->name('profile.select-avatar');
    Route::post('/profile/select-banner', [ProfileController::class, 'selectBanner'])->name('profile.select-banner');
    Route::post('/profile/update-bio', [ProfileController::class, 'updateBio'])->name('profile.update-bio');
    Route::post('/profile/set-favorite-game', [ProfileController::class, 'setFavoriteGame'])->name('profile.set-favorite-game');
    Route::get('/profile/favorite-game', [ProfileController::class, 'getFavoriteGame'])->name('profile.get-favorite-game');
    
    // Datos JSON
    Route::get('/user-data/achievements', [AchievementController::class, 'getUserAchievements'])->name('user.achievements.json');
    Route::get('/user-data/activities', [ProfileController::class, 'getActivitiesJson'])->name('user.activities.json');

    // 💰 WALLET
    Route::get('/wallet', [WalletController::class, 'show'])->name('wallet.show');
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');

    // 🎮 JUEGOS
    Route::prefix('games')->group(function () {
        // Cocodrilo 🐊
        Route::get('/crocodile-teeth', [CrocodileTeethController::class, 'index'])->name('games.crocodile-teeth');
        Route::post('/crocodile-teeth/start', [CrocodileTeethController::class, 'startGame'])->name('games.crocodile-teeth.start');
        Route::post('/crocodile-teeth/click', [CrocodileTeethController::class, 'clickTooth'])->name('games.crocodile-teeth.click');
        Route::post('/crocodile-teeth/cashout', [CrocodileTeethController::class, 'cashOut'])->name('games.crocodile-teeth.cashout');
        
        // Ruleta 🎡
        Route::get('/roulette', [RouletteController::class, 'index'])->name('games.roulette');
        Route::post('/roulette/spin', [RouletteController::class, 'spin'])->name('games.roulette.spin');
    });

    // Otros Juegos
    Route::get('/tragamonedas', [SlotGameController::class, 'index'])->name('tragamonedas.index');
    Route::post('/slots/spin', [SlotGameController::class, 'spin'])->name('slots.spin');
    Route::get('/dice', [DiceGameController::class, 'index'])->name('dice.index');
    Route::post('/dice/play', [DiceGameController::class, 'play'])->name('dice.play');
    Route::get('/dice/history', [DiceGameController::class, 'history'])->name('dice.history');
    Route::get('/high-flyer', function () { return Inertia::render('Games/HighFlyer', ['user' => Auth::user()]); })->name('high-flyer');
    Route::post('/games/high-flyer/start', [HighFlyerController::class, 'startGame']);
    Route::post('/games/high-flyer/cashout', [HighFlyerController::class, 'cashOut']);
    Route::post('/games/high-flyer/crash', [HighFlyerController::class, 'gameCrashed']);

    // Vistas Estáticas
    Route::get('/slots', function () { return inertia('Slots'); })->name('slots');
    Route::get('/crash', function () { return inertia('Crash'); })->name('crash');
    Route::get('/juegosturbo', function () { return inertia('Juegosturbo'); })->name('juegosturbo');
    Route::get('/ruleta', function () { return inertia('Ruleta'); })->name('ruleta');
    Route::get('/comunidad', function () { return inertia('Comunidad'); })->name('comunidad');
    Route::get('/settings', function () { return inertia('Settings'); })->name('settings');

    // 🎫 TICKETS (VISTA DEL USUARIO)
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::post('/', [TicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [TicketController::class, 'reply'])->name('reply');
        Route::put('/{ticket}/close', [TicketController::class, 'close'])->name('close');
    });
});

/*
|--------------------------------------------------------------------------
| 👮‍♂️ ZONA STAFF (ADMINISTRACIÓN)
|--------------------------------------------------------------------------
*/
// Acceso general para cualquier miembro del staff
Route::middleware(['auth', 'role:super_admin,admin,moderator,support'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // 1. GESTIÓN DE TICKETS (Todos los del staff pueden ver y responder)
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketManagementController::class, 'index'])->name('index');
        Route::get('/statistics', [TicketManagementController::class, 'statistics'])->name('statistics');
        Route::get('/{ticket}', [TicketManagementController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [TicketManagementController::class, 'reply'])->name('reply');
        Route::put('/{ticket}/status', [TicketManagementController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{ticket}', [TicketManagementController::class, 'destroy'])->name('destroy');
    });

    // 2. GESTIÓN DE USUARIOS - GRUPO A (PODER TOTAL)
    // Solo Super Admin y Admin: Pueden crear, editar datos sensibles, borrar y cambiar roles.
    Route::middleware('role:super_admin,admin')->prefix('users')->name('users.')->group(function () {
        Route::post('/', [UserManagementController::class, 'store'])->name('store');   // Crear usuario
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update'); // Editar (Nombre/Email)
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy'); // Eliminar
        Route::put('/{user}/role', [UserManagementController::class, 'updateRole'])->name('update-role'); // Cambiar Rol
    });

    // 3. GESTIÓN DE USUARIOS - GRUPO B (MODERACIÓN)
    // Incluye Moderadores: Pueden ver la lista y aplicar castigos (Ban/Suspend), pero no editar datos.
    Route::middleware('role:super_admin,admin,moderator')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index'); // Ver la tabla
        Route::put('/{user}/suspend', [UserManagementController::class, 'suspend'])->name('suspend');
        Route::put('/{user}/ban', [UserManagementController::class, 'ban'])->name('ban');
        Route::put('/{user}/activate', [UserManagementController::class, 'activate'])->name('activate');
    });
    
    // 4. CONFIGURACIÓN GLOBAL (Solo Dios)
    Route::middleware('role:super_admin')->group(function() {
        // Route::get('/settings', ...); // Futuras configuraciones globales
    });
});

require __DIR__.'/auth.php';