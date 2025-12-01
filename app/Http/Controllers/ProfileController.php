<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Friend;
use App\Models\UserFavoriteGame;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Ver perfil público
     */
    public function show(Request $request)
    {
        $userId = $request->user()->id;

        // Obtener amigos aceptados
        $friends = Friend::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('friend_id', $userId);
            })
            ->where('status', 'accepted')
            ->with(['user', 'friendUser'])
            ->limit(20)
            ->get();

        return Inertia::render('Profile/Show', [
            'friends' => $friends,
            'recent_activity' => [],
        ]);
    }

    /**
     * Editar perfil
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Actualizar información del perfil
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Seleccionar avatar
     */
    public function selectAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => 'required|string',
        ]);

        $user = $request->user();
        $user->avatar = $request->avatar;
        $user->save();

        // Forzar actualización de la sesión
        $request->session()->put('user', $user);

        return Redirect::back();
    }

    /**
     * Seleccionar banner
     */
    public function selectBanner(Request $request): RedirectResponse
    {
        $request->validate([
            'banner' => 'required|string',
        ]);

        $user = $request->user();
        $user->banner = $request->banner;
        $user->save();

        // Forzar actualización de la sesión
        $request->session()->put('user', $user);

        return Redirect::back();
    }

    /**
     * Actualizar biografía
     */
    public function updateBio(Request $request): RedirectResponse
    {
        $request->validate([
            'bio' => 'nullable|string|max:200',
        ]);

        $user = $request->user();
        $user->bio = $request->bio;
        $user->save();

        // Forzar actualización de la sesión
        $request->session()->put('user', $user);

        return Redirect::back();
    }

    /**
     * Eliminar cuenta
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Seleccionar juego favorito
     */
    public function setFavoriteGame(Request $request)
    {
    $request->validate([
        'game_key' => 'required|string|max:50',
        'game_name' => 'required|string|max:100',
        'game_image' => 'required|string|max:255',
    ]);

    $user = $request->user();

    // Definir lista de juegos disponibles
    $availableGames = [
        'high_flyer' => ['name' => 'High Flyer', 'image' => '/images/slots/high.png'],
        'crash' => ['name' => 'Crash', 'image' => '/images/slots/crash.png'],
        'mines' => ['name' => 'Mines', 'image' => '/images/slots/minas.png'],
        'plinko' => ['name' => 'Plinko', 'image' => '/images/slots/plinko.png'],
        'wheel' => ['name' => 'Wheel', 'image' => '/images/slots/wheel.png'],
        'gates_olympus' => ['name' => 'Gates Olympus', 'image' => '/images/slots/olimpus.png'],
        'sugar_rush' => ['name' => 'Sugar Rush', 'image' => '/images/slots/sugar.png'],
    ];

    $gameKey = $request->game_key;
    
    if (!array_key_exists($gameKey, $availableGames)) {
        return back()->with('error', 'Juego no válido');
    }

    $game = $availableGames[$gameKey];

    // Actualizar o crear juego favorito
    $user->setFavoriteGame($gameKey, $game['name'], $game['image']);

    return back()->with('success', "¡{$game['name']} establecido como favorito!");
    }

    public function getActivitiesJson(Request $request)
    {
        $user = $request->user();
        
        // Asumiendo que tu modelo User tiene una relación `activities()`:
        $activities = $user->activities()
            ->latest() // O las ordenas como necesites (por fecha más reciente)
            ->limit(20)
            ->get();
            
        // Devuelve el JSON con la misma estructura que tu frontend esperaba
        return response()->json(['activities' => $activities]);
    }

    /**
     * Obtener juego favorito con estadísticas
     */
    public function getFavoriteGame(Request $request)
    {
    $user = $request->user();
    $favorite = $user->favoriteGame;

    if (!$favorite) {
        return response()->json(['favoriteGame' => null]);
    }

    // Obtener logros desbloqueados en este juego
    $achievements = $user->activities()
        ->where('game_name', $favorite->game_name)
        ->where('activity_type', 'game_played')
        ->count();

    $wins = $user->activities()
        ->where('game_name', $favorite->game_name)
        ->where('won', true)
        ->count();

    return response()->json([
        'favoriteGame' => [
            'key' => $favorite->game_key,
            'name' => $favorite->game_name,
            'image' => $favorite->game_image,
            'hoursPlayed' => $favorite->hours_played,
            'gamesPlayed' => $favorite->games_played,
            'gamesWon' => $favorite->games_won,
            'totalActivities' => $achievements,
            'totalWins' => $wins
        ]
    ]);
    }

    /**
     * Actualizar estadísticas de juego favorito
     */
    public function updateFavoriteGameStats(Request $request)
    {
    $user = $request->user();
    $favorite = $user->favoriteGame;

    if (!$favorite) {
        return response()->json(['error' => 'Sin juego favorito'], 404);
    }

    $request->validate([
        'hours_played' => 'nullable|integer|min:0',
        'games_played' => 'nullable|integer|min:0',
        'games_won' => 'nullable|integer|min:0',
    ]);

    if ($request->has('hours_played')) {
        $favorite->hours_played += $request->hours_played;
    }
    if ($request->has('games_played')) {
        $favorite->games_played += $request->games_played;
    }
    if ($request->has('games_won')) {
        $favorite->games_won += $request->games_won;
    }

    $favorite->save();

    return response()->json(['success' => true, 'favorite' => $favorite]);
    }
}