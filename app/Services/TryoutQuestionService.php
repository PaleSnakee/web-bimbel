<?php

namespace App\Services;

use App\Models\Tryout;
use App\Models\TryoutQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TryoutQuestionService
{
    private const REQUIRED_LABELS = ['A', 'B', 'C', 'D'];

    public function create(Tryout $tryout, array $data): TryoutQuestion
    {
        $this->ensureEditableTryout($tryout);
        $this->validateQuestionData($tryout, $data);

        return DB::transaction(function () use ($tryout, $data) {
            $question = $tryout->questions()->create([
                'question_text' => $data['question_text'],
                'points' => $data['points'],
                'order_number' => $data['order_number'],
            ]);

            $this->syncOptions($question, $data['options']);

            return $question->load('options');
        });
    }

    public function update(TryoutQuestion $question, array $data): TryoutQuestion
    {
        $question->loadMissing('tryout');
        $this->ensureEditableTryout($question->tryout);
        $this->validateQuestionData($question->tryout, $data, $question);

        return DB::transaction(function () use ($question, $data) {
            $question->update([
                'question_text' => $data['question_text'],
                'points' => $data['points'],
                'order_number' => $data['order_number'],
            ]);

            $question->options()->delete();
            $this->syncOptions($question, $data['options']);

            return $question->fresh('options');
        });
    }

    public function delete(TryoutQuestion $question): void
    {
        $question->loadMissing('tryout');
        $this->ensureEditableTryout($question->tryout);

        DB::transaction(fn () => $question->delete());
    }

    private function validateQuestionData(Tryout $tryout, array $data, ?TryoutQuestion $ignoreQuestion = null): void
    {
        $labels = collect($data['options'])->pluck('option_label')->map(fn ($label) => strtoupper(trim($label)))->all();

        if (count($labels) !== 4 || array_diff(self::REQUIRED_LABELS, $labels) || array_diff($labels, self::REQUIRED_LABELS)) {
            throw ValidationException::withMessages([
                'options' => 'Soal harus memiliki tepat empat pilihan berlabel A, B, C, dan D.',
            ]);
        }

        if (collect($data['options'])->where('is_correct', true)->count() !== 1) {
            throw ValidationException::withMessages([
                'options' => 'Tepat satu pilihan harus ditetapkan sebagai jawaban benar.',
            ]);
        }

        $orderQuery = $tryout->questions()->where('order_number', $data['order_number']);
        if ($ignoreQuestion) {
            $orderQuery->whereKeyNot($ignoreQuestion->id);
        }

        if ($orderQuery->exists()) {
            throw ValidationException::withMessages([
                'order_number' => 'Nomor urut soal sudah digunakan pada Try Out ini.',
            ]);
        }
    }

    private function syncOptions(TryoutQuestion $question, array $options): void
    {
        foreach ($options as $option) {
            $question->options()->create([
                'option_label' => strtoupper(trim($option['option_label'])),
                'option_text' => $option['option_text'],
                'is_correct' => (bool) $option['is_correct'],
                'order_number' => $option['order_number'],
            ]);
        }
    }

    private function ensureEditableTryout(Tryout $tryout): void
    {
        if ($tryout->status !== 'draft') {
            throw ValidationException::withMessages([
                'tryout' => 'Soal hanya dapat dikelola pada Try Out berstatus draft.',
            ]);
        }

        if ($tryout->attempts()->exists()) {
            throw ValidationException::withMessages([
                'tryout' => 'Soal tidak dapat diubah karena Try Out telah memiliki riwayat pengerjaan siswa.',
            ]);
        }
    }
}
