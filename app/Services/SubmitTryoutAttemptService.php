<?php

namespace App\Services;

use App\Models\Student;
use App\Models\TryoutAttempt;
use Illuminate\Support\Facades\DB;

class SubmitTryoutAttemptService
{
    public function __construct(
        private readonly GradeTryoutAttemptService $gradingService,
    ) {
    }

    public function submit(Student $student, TryoutAttempt $attempt): TryoutAttempt
    {
        return DB::transaction(function () use ($student, $attempt) {
            $attempt = TryoutAttempt::query()
                ->whereKey($attempt->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($attempt->status === 'in_progress' && now()->gte($attempt->expires_at)) {
                $attempt->update(['status' => 'expired']);
                $attempt->refresh();
            }

            if ($attempt->status === 'in_progress') {
                $attempt->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);
                $attempt->refresh();
            }

            return $this->gradingService->gradeLocked($attempt);
        });
    }
}