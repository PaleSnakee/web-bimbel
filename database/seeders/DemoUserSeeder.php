<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->createOrUpdateUser([
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'role' => 'admin',
            ]);

            $tutor = $this->createOrUpdateUser([
                'name' => 'Tutor Demo',
                'email' => 'tutor@example.com',
                'role' => 'tutor',
            ]);
            $tutor->tutor()->updateOrCreate([], []);

            $student = $this->createOrUpdateUser([
                'name' => 'Student Demo',
                'email' => 'student@example.com',
                'role' => 'student',
            ]);
            $student->student()->updateOrCreate([], [
                'nis' => 'DEMO-STUDENT-001',
                'education_level' => 'SD',
            ]);

            $parent = $this->createOrUpdateUser([
                'name' => 'Parent Demo',
                'email' => 'parent@example.com',
                'role' => 'parent',
            ]);
            $parent->guardian()->updateOrCreate([], []);
        });
    }

    /**
     * Create or update one demo account using its unique email address.
     *
     * @param  array{name: string, email: string, role: string}  $attributes
     */
    private function createOrUpdateUser(array $attributes): User
    {
        return User::updateOrCreate(
            ['email' => $attributes['email']],
            [
                'name' => $attributes['name'],
                'password' => Hash::make('password'),
                'role' => $attributes['role'],
                'status' => 'active',
            ],
        );
    }
}
