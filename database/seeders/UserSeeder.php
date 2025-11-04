<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario principal (Super Admin)
        User::create([
            'name' => 'Angelo',
            'email' => 'angel@casino.com',
            'password' => Hash::make('tecsup'),
            'role' => 'admin',
        ]);
    }
}
