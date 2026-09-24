<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Tutor
        User::create([
            'name' => 'Tutor Demo',
            'email' => 'tutor@example.com',
            'password' => Hash::make('password'),
            'role' => 'tutor',
        ]);

        // Student
        User::create([
            'name' => 'Student Demo',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        // Parent
        User::create([
            'name' => 'Parent Demo',
            'email' => 'parent@example.com',
            'password' => Hash::make('password'),
            'role' => 'parent',
        ]);
    }
}
