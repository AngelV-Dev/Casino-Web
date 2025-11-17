<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Wallet;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SUPER ADMIN (No se puede eliminar)
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@casino.com',
            'password' => Hash::make('superadmin123'),
            'role' => 'super_admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        Wallet::create([
            'user_id' => $superAdmin->id,
            'balance' => 1000000.00,
            'currency' => 'USD',
        ]);

        UserProfile::create([
            'user_id' => $superAdmin->id,
            'avatar' => 'superadmin.png',
            'title' => 'Super Administrador',
            'background_color' => '#1a1a2e',
        ]);

        // 2. ADMIN SECUNDARIO
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@casino.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        Wallet::create([
            'user_id' => $admin->id,
            'balance' => 100000.00,
            'currency' => 'USD',
        ]);

        UserProfile::create([
            'user_id' => $admin->id,
            'avatar' => 'admin.png',
            'title' => 'Administrador',
            'background_color' => '#1a1a2e',
        ]);

        // 3. MODERADOR
        $moderator = User::create([
            'name' => 'Moderador',
            'email' => 'moderator@casino.com',
            'password' => Hash::make('moderator123'),
            'role' => 'moderator',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        Wallet::create([
            'user_id' => $moderator->id,
            'balance' => 10000.00,
            'currency' => 'USD',
        ]);

        UserProfile::create([
            'user_id' => $moderator->id,
            'avatar' => 'moderator.png',
            'title' => 'Moderador',
            'background_color' => '#1a1a2e',
        ]);

        // 4. SOPORTE
        $support = User::create([
            'name' => 'Soporte',
            'email' => 'support@casino.com',
            'password' => Hash::make('support123'),
            'role' => 'support',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        Wallet::create([
            'user_id' => $support->id,
            'balance' => 5000.00,
            'currency' => 'USD',
        ]);

        UserProfile::create([
            'user_id' => $support->id,
            'avatar' => 'support.png',
            'title' => 'Agente de Soporte',
            'background_color' => '#1a1a2e',
        ]);

        // 5. USUARIO DEMO
        $user = User::create([
            'name' => 'Usuario Demo',
            'email' => 'demo@casino.com',
            'password' => Hash::make('demo123'),
            'role' => 'user',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        Wallet::create([
            'user_id' => $user->id,
            'balance' => 1000.00,
            'currency' => 'USD',
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'avatar' => 'default.png',
            'background_color' => '#1a1a2e',
        ]);
    }
}