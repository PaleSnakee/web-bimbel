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
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $now = ($now ? $now->copy() : now())->setTimezone($timezone);

        if ($tryout->status !== 'published') {
            throw ValidationException::withMessages(['tryout' => 'Try Out belum tersedia untuk dikerjakan.']);
        }

        if (! $tryout->classRooms()->whereKey($student->class_room_id)->exists()) {
            abort(404);
        }

        $startAt = $tryout->start_at ? $tryout->start_at->copy()->setTimezone($timezone) : null;
        $endAt = $tryout->end_at ? $tryout->end_at->copy()->setTimezone($timezone) : null;

        if ($startAt && $now->lt($startAt)) {
            throw ValidationException::withMessages(['tryout' => 'Try Out belum dimulai.']);
        }

        if ($endAt && $now->gte($endAt)) {
            throw ValidationException::withMessages(['tryout' => 'Masa pengerjaan Try Out telah berakhir.']);
        }
    }
}