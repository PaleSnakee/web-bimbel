<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\TryoutQuestion;
use App\Services\TryoutQuestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TryoutQuestionController extends Controller
{
    public function index(Tryout $tryout): View
    {
        $tryout->load(['subject', 'tutor.user']);
        $questions = $tryout->questions()->with('options')->paginate(15);

        return view('admin.tryout-questions.index', compact('tryout', 'questions'));
    }

    public function create(Tryout $tryout): View
    {
        $this->ensureDraft($tryout);

        return view('admin.tryout-questions.create', compact('tryout'));
    }

    public function store(Request $request, Tryout $tryout, TryoutQuestionService $service): RedirectResponse
    {
        $question = $service->create($tryout, $this->validatedData($request));

        return redirect()->route('admin.tryouts.questions.index', $tryout)
            ->with('success', "Soal nomor {$question->order_number} berhasil ditambahkan.");
    }

    public function edit(Tryout $tryout, TryoutQuestion $question): View
    {
        $question = $this->questionForTryout($tryout, $question);
        $this->ensureDraft($tryout);
        $question->load('options');

        return view('admin.tryout-questions.edit', compact('tryout', 'question'));
    }

    public function update(Request $request, Tryout $tryout, TryoutQuestion $question, TryoutQuestionService $service): RedirectResponse
    {
        $question = $this->questionForTryout($tryout, $question);
        $service->update($question, $this->validatedData($request));

        return redirect()->route('admin.tryouts.questions.index', $tryout)
            ->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroy(Tryout $tryout, TryoutQuestion $question, TryoutQuestionService $service): RedirectResponse
    {
        $question = $this->questionForTryout($tryout, $question);
        $service->delete($question);

        return redirect()->route('admin.tryouts.questions.index', $tryout)
            ->with('success', 'Soal berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'question_text' => ['required', 'string'],
            'points' => ['required', 'integer', 'min:1'],
            'order_number' => ['required', 'integer', 'min:1'],
            'correct_option' => ['required', 'string', Rule::in(['A', 'B', 'C', 'D'])],
            'options' => ['required', 'array', 'size:4'],
            'options.*.option_label' => ['required', 'string', 'max:10', 'distinct', Rule::in(['A', 'B', 'C', 'D'])],
            'options.*.option_text' => ['required', 'string'],
            'options.*.is_correct' => ['required', 'boolean'],
            'options.*.order_number' => ['required', 'integer', 'min:1', 'distinct'],
        ]);

        $correctOptions = collect($data['options'])->where('is_correct', true);

        if ($correctOptions->count() !== 1 || $correctOptions->first()['option_label'] !== $data['correct_option']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'options' => 'Tepat satu pilihan harus ditetapkan sebagai jawaban benar.',
            ]);
        }

        unset($data['correct_option']);

        return $data;
    }

    private function questionForTryout(Tryout $tryout, TryoutQuestion $question): TryoutQuestion
    {
        return $tryout->questions()->whereKey($question->id)->firstOrFail();
    }

    private function ensureDraft(Tryout $tryout): void
    {
        abort_unless($tryout->status === 'draft', 403);
    }
};