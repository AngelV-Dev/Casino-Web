<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    /**
     * Definiciones de logros disponibles
     */
    public static function getDefinitions()
    {
        return [
            'account_created' => [
                'title' => '🎉 ¡Bienvenido al Casino!',
                'description' => 'Creaste tu cuenta',
                'icon' => 'welcome.png'
            ],
            'first_deposit' => [
                'title' => '💰 Primera Recarga',
                'description' => 'Realizaste tu primer depósito',
                'icon' => 'deposit.png'
            ],
            'win_streak_5' => [
                'title' => '🔥 Racha de 5',
                'description' => 'Ganaste 5 juegos consecutivos',
                'icon' => 'streak.png'
            ],
            'earnings_100' => [
                'title' => '💵 Cien Soles',
                'description' => 'Ganaste más de S/100',
                'icon' => 'earnings.png'
            ],
            'played_50_games' => [
                'title' => '🎮 Jugador Dedicado',
                'description' => 'Jugaste 50 partidas',
                'icon' => 'games.png'
            ]
        ];
    }

    /**
     * Obtener definición de logro
     */
    public static function getDefinition($key)
    {
        return self::getDefinitions()[$key] ?? null;
    }

    /**
     * Desbloquear logro específico
     */
    public function unlock(User $user, $achievementKey)
    {
        $definition = self::getDefinition($achievementKey);
        
        if (!$definition) {
            return response()->json(['error' => 'Logro no encontrado'], 404);
        }

        // Asumiendo que el modelo User tiene un método unlockAchievement
        $achievement = $user->unlockAchievement(
            $achievementKey,
            $definition['title'],
            $definition['description'],
            $definition['icon']
        );

        return response()->json([
            'success' => true,
            'achievement' => $achievement,
            'message' => "¡Logro desbloqueado: {$definition['title']}!"
        ]);
    }

    /**
     * Obtener todos los logros del usuario (CORREGIDO)
     */
    public function getUserAchievements(User $user)
    {
        $unlockedAchievements = $user->achievements()->get();
        $definitions = self::getDefinitions();
        
        $allAchievements = collect($definitions)->map(function ($def, $key) use ($unlockedAchievements) {
            $unlocked = $unlockedAchievements->where('achievement_key', $key)->first();
            
            return [
                'key' => $key,
                'title' => $def['title'],
                'description' => $def['description'],
                'icon' => $def['icon'],
                'unlocked' => !!$unlocked,
                'unlocked_at' => $unlocked?->unlocked_at, // Usar 'unlocked_at' para consistencia con la DB
                // Nota: se cambió 'unlockedAt' a 'unlocked_at' para coincidir con la convención de DB, 
                // aunque el frontend podría manejar ambos.
            ];
        })
        // LA CORRECCIÓN CLAVE: Agrupar la colección por la clave 'key' para que devuelva un OBJETO.
        ->keyBy('key') 
        ->toArray();

        return response()->json([
            'achievements' => $allAchievements,
            'totalUnlocked' => $unlockedAchievements->count()
        ]);
    }
}