<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('users')
            ->where('role', 'tutor')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('tutors')
                    ->whereColumn('tutors.user_id', 'users.id');
            })
            ->orderBy('id')
            ->each(function ($user) use ($now) {
                DB::table('tutors')->insert([
                    'user_id' => $user->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        // Profil lama tidak dihapus agar rollback tidak menghilangkan data tutor.
    }
};