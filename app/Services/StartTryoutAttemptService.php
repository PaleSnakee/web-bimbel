<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StartTryoutAttemptService
{
    public function __construct(
        private readonly StudentTryoutAccessService $accessService,
    ) {
    }

    public function start(Student $student, Tryout $tryout): TryoutAttempt
    {
        $attempt = DB::transaction(function () use ($student, $tryout) {
            $tryout = Tryout::query()
                ->with('questions:id,tryout_id')
                ->lockForUpdate()
                ->findOrFail($tryout->id);

            $this->accessService->ensureEligible($student, $tryout);

            $attempt = TryoutAttempt::query()
                ->where('tryout_id', $tryout->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->first();

            if ($attempt) {
                if ($attempt->status === 'in_progress' && now()->gte($attempt->expires_at)) {
                    $attempt->update(['status' => 'expired']);

                    return null;
                }

                return $attempt->status === 'in_progress' ? $attempt : null;
            }

            if ($tryout->questions->isEmpty()) {
                throw ValidationException::withMessages([
                    'tryout' => 'Try Out belum memiliki soal untuk dikerjakan.',
                ]);
            }

            $startedAt = now();
            $expiresAt = $startedAt->copy()
                ->addMinutes($tryout->duration_minutes)
                ->min($tryout->end_at);

            $attempt = TryoutAttempt::create([
                'tryout_id' => $tryout->id,
                'student_id' => $student->id,
                'started_at' => $startedAt,
                'expires_at' => $expiresAt,
                'status' => 'in_progress',
            ]);

            $attempt->answers()->createMany(
                $tryout->questions->map(fn ($question) => [
                    'tryout_question_id' => $question->id,
                    'tryout_question_option_id' => null,
                ])->all()
            );

            return $attempt;
        });

        if (! $attempt) {
            throw ValidationException::withMessages([
                'tryout' => 'Anda telah menyelesaikan atau melewati batas waktu Try Out ini.',
            ]);
        }

        return $attempt;
    }
}