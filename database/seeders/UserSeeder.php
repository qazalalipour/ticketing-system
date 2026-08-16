<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin Level 1',
            'email' => 'admin1@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN_LEVEL_1,
        ]);

        User::create([
            'name' => 'Admin Level 2',
            'email' => 'admin2@example.com',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN_LEVEL_2,
        ]);

        User::factory(5)->create();
    }
}
