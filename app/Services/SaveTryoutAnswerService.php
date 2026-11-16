<?php

namespace App\Services;

use App\Models\Student;
use App\Models\TryoutAttempt;
use App\Models\TryoutAttemptAnswer;
use App\Models\TryoutQuestion;
use App\Models\TryoutQuestionOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveTryoutAnswerService
{
    public function save(
        Student $student,
        TryoutAttempt $attempt,
        int $questionId,
        int $optionId,
    ): TryoutAttemptAnswer {
        $answer = DB::transaction(function () use ($student, $attempt, $questionId, $optionId) {
            $attempt = TryoutAttempt::query()
                ->whereKey($attempt->id)
                ->where('student_id', $student->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($attempt->status === 'in_progress' && now()->gte($attempt->expires_at)) {
                $attempt->update(['status' => 'expired']);

                return null;
            }

            $this->ensureWritable($attempt);

            $question = TryoutQuestion::query()
                ->whereKey($questionId)
                ->where('tryout_id', $attempt->tryout_id)
                ->firstOrFail();

            $option = TryoutQuestionOption::query()
                ->whereKey($optionId)
                ->where('tryout_question_id', $question->id)
                ->firstOrFail();

            $answer = TryoutAttemptAnswer::query()
                ->where('tryout_attempt_id', $attempt->id)
                ->where('tryout_question_id', $question->id)
                ->lockForUpdate()
                ->firstOrFail();

            $answer->update([
                'tryout_question_option_id' => $option->id,
                'answered_at' => now(),
            ]);

            return $answer->fresh('option');
        });

        if (! $answer) {
            throw ValidationException::withMessages([
                'attempt' => 'Waktu pengerjaan Try Out telah habis.',
            ]);
        }

        return $answer;
    }

    private function ensureWritable(TryoutAttempt $attempt): void
    {
        if ($attempt->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'attempt' => 'Attempt Try Out ini tidak dapat lagi diubah.',
            ]);
        }
    }
}