<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    \App\Models\Game::create([
            'name' => 'Crocodile Teeth',
            'slug' => 'crocodile-teeth',
            'description' => 'Click en los dientes seguros sin tocar los rojos. A mayor riesgo, mayor recompensa.',
            'min_bet' => 0.10,
            'max_bet' => 10000.00,
            'is_active' => true,
        ]);
    }
}
