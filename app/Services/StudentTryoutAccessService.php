<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Tryout;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class StudentTryoutAccessService
{
    public function studentFor(User $user): Student
    {
        $student = $user->student()->with('classRoom')->first();

        if (! $user->isStudent() || $user->status !== 'active' || ! $student || ! $student->class_room_id || $student->classRoom?->status !== 'active') {
            abort(403, 'Profil siswa atau kelas tidak valid.');
        }

        return $student;
    }

    public function ensureEligible(Student $student, Tryout $tryout, ?Carbon $now = null): void
    {
        $now ??= now();

        if ($tryout->status !== 'published') {
            throw ValidationException::withMessages(['tryout' => 'Try Out belum tersedia untuk dikerjakan.']);
        }

        if (! $tryout->classRooms()->whereKey($student->class_room_id)->exists()) {
            abort(404);
        }

        if ($now->lt($tryout->start_at)) {
            throw ValidationException::withMessages(['tryout' => 'Try Out belum dimulai.']);
        }

        if ($now->gte($tryout->end_at)) {
            throw ValidationException::withMessages(['tryout' => 'Masa pengerjaan Try Out telah berakhir.']);
        }
    }
}