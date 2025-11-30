<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserActivity;
use App\Models\UserFavoriteGame;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener el primer usuario (o crear uno de prueba)
        $user = User::first();
        
        if (!$user) {
            $user = User::create([
                'name' => 'Usuario Prueba',
                'email' => 'prueba@test.com',
                'password' => bcrypt('password123'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
        }

        // ========== LOGROS ==========
        $achievements = [
            [
                'achievement_key' => 'account_created',
                'title' => '🎉 ¡Bienvenido al Casino!',
                'description' => 'Creaste tu cuenta',
                'icon' => 'welcome.png',
                'unlocked_at' => now()->subDays(10),
            ],
            [
                'achievement_key' => 'first_deposit',
                'title' => '💰 Primera Recarga',
                'description' => 'Realizaste tu primer depósito',
                'icon' => 'deposit.png',
                'unlocked_at' => now()->subDays(8),
            ],
            [
                'achievement_key' => 'earnings_100',
                'title' => '💵 Cien Soles',
                'description' => 'Ganaste más de S/100',
                'icon' => 'earnings.png',
                'unlocked_at' => now()->subDays(5),
            ],
        ];

        foreach ($achievements as $ach) {
            $exists = $user->achievements()->where('achievement_key', $ach['achievement_key'])->exists();
            if (!$exists) {
                $user->achievements()->create($ach);
            }
        }

        // ========== ACTIVIDADES ==========
        $activities = [
            [
                'activity_type' => 'game_played',
                'game_name' => 'High Flyer',
                'description' => 'Jugaste High Flyer',
                'amount' => 150.00,
                'won' => true,
                'happened_at' => now()->subHours(2),
            ],
            [
                'activity_type' => 'game_played',
                'game_name' => 'Crash',
                'description' => 'Jugaste Crash',
                'amount' => 75.50,
                'won' => true,
                'happened_at' => now()->subHours(4),
            ],
            [
                'activity_type' => 'game_played',
                'game_name' => 'Mines',
                'description' => 'Jugaste Mines',
                'amount' => 50.00,
                'won' => false,
                'happened_at' => now()->subHours(6),
            ],
            [
                'activity_type' => 'game_played',
                'game_name' => 'Plinko',
                'description' => 'Jugaste Plinko',
                'amount' => 200.00,
                'won' => true,
                'happened_at' => now()->subDays(1),
            ],
            [
                'activity_type' => 'deposit',
                'game_name' => null,
                'description' => 'Recarga de S/500',
                'amount' => 500.00,
                'won' => false,
                'happened_at' => now()->subDays(3),
            ],
        ];

        foreach ($activities as $act) {
            $exists = $user->activities()->where('game_name', $act['game_name'])
                ->where('happened_at', $act['happened_at'])->exists();
            if (!$exists) {
                $user->activities()->create($act);
            }
        }

        // ========== JUEGO FAVORITO ==========
        $existsFav = $user->favoriteGame;
        if (!$existsFav) {
            $user->favoriteGame()->create([
                'game_key' => 'high_flyer',
                'game_name' => 'High Flyer',
                'game_image' => '/images/slots/high.png',
                'hours_played' => 25,
                'games_played' => 150,
                'games_won' => 95,
            ]);
        }

        echo "✅ Datos de prueba creados para usuario: {$user->name}\n";
    }
}