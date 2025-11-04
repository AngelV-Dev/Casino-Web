<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamamos a los seeders específicos
        $this->call([
            UserSeeder::class, // Crea super admin, moderadores, soporte y usuarios de prueba
            GameSeeder::class, // Crea los juegos iniciales
        ]);
    }
}
