<?php

namespace App\Services;

use App\Models\Student;
use App\Models\TryoutAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubmitTryoutAttemptService
{
    public function submit(Student $student, TryoutAttempt $attempt): TryoutAttempt
    {
        $attempt = DB::transaction(function () use ($student, $attempt) {
            $attempt = TryoutAttempt::query()
                ->whereKey($attempt->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($attempt->status === 'submitted') {
                return $attempt;
            }

            if ($attempt->status === 'expired') {
                return null;
            }

            if (now()->gte($attempt->expires_at)) {
                $attempt->update(['status' => 'expired']);

                return null;
            }

            $attempt->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            return $attempt->fresh();
        });

        if (! $attempt) {
            throw ValidationException::withMessages([
                'attempt' => 'Waktu pengerjaan Try Out telah habis.',
            ]);
        }

        return $attempt;
    }
}