<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        // Darle el logro de bienvenida a todos los usuarios existentes
        User::all()->each(function ($user) {
            if (!$user->achievements()->where('achievement_key', 'account_created')->exists()) {
                $user->unlockAchievement(
                    'account_created',
                    '🎉 ¡Bienvenido al Casino!',
                    'Creaste tu cuenta',
                    'achievement_welcome.png'
                );
            }
        });
    }
}