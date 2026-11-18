<?php

namespace App\Services;

use App\Models\TryoutAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradeTryoutAttemptService
{
    public function grade(TryoutAttempt $attempt): TryoutAttempt
    {
        return DB::transaction(function () use ($attempt) {
            $attempt = TryoutAttempt::query()
                ->whereKey($attempt->id)
                ->lockForUpdate()
                ->firstOrFail();

            return $this->gradeLocked($attempt);
        });
    }

    public function finalizeExpired(TryoutAttempt $attempt): TryoutAttempt
    {
        return DB::transaction(function () use ($attempt) {
            $attempt = TryoutAttempt::query()
                ->whereKey($attempt->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($attempt->status === 'in_progress' && now()->gte($attempt->expires_at)) {
                $attempt->update(['status' => 'expired']);
                $attempt->refresh();
            }

            return in_array($attempt->status, ['submitted', 'expired'], true)
                ? $this->gradeLocked($attempt)
                : $attempt;
        });
    }

    public function gradeLocked(TryoutAttempt $attempt): TryoutAttempt
    {
        if ($attempt->graded_at) {
            return $attempt;
        }

        if (! in_array($attempt->status, ['submitted', 'expired'], true)) {
            throw ValidationException::withMessages([
                'attempt' => 'Attempt Try Out belum dapat dinilai.',
            ]);
        }

        $questions = $attempt->tryout->questions()
            ->with([
                'options:id,tryout_question_id,is_correct',
                'attemptAnswers' => fn ($query) => $query
                    ->where('tryout_attempt_id', $attempt->id)
                    ->select([
                        'id',
                        'tryout_attempt_id',
                        'tryout_question_id',
                        'tryout_question_option_id',
                    ]),
            ])
            ->orderBy('order_number')
            ->get(['id', 'tryout_id', 'points', 'order_number']);

        $totalQuestions = $questions->count();
        $answeredQuestions = 0;
        $correctAnswers = 0;
        $incorrectAnswers = 0;
        $unansweredQuestions = 0;
        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($questions as $question) {
            $totalPoints += $question->points;
            $answer = $question->attemptAnswers->first();

            if (! $answer?->tryout_question_option_id) {
                $unansweredQuestions++;

                continue;
            }

            $answeredQuestions++;
            $isCorrect = $question->options
                ->where('id', $answer->tryout_question_option_id)
                ->contains('is_correct', true);

            if (! $isCorrect) {
                $incorrectAnswers++;

                continue;
            }

            $correctAnswers++;
            $earnedPoints += $question->points;
        }

        $finalScore = $totalPoints > 0
            ? number_format(($earnedPoints * 100) / $totalPoints, 2, '.', '')
            : '0.00';

        $attempt->update([
            'total_questions' => $totalQuestions,
            'answered_questions' => $answeredQuestions,
            'correct_answers' => $correctAnswers,
            'incorrect_answers' => $incorrectAnswers,
            'unanswered_questions' => $unansweredQuestions,
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            'final_score' => $finalScore,
            'graded_at' => now(),
        ]);

        return $attempt->fresh();
    }
}