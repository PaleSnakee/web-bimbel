<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Services\GradeTryoutAttemptService;
use App\Services\SaveTryoutAnswerService;
use App\Services\StartTryoutAttemptService;
use App\Services\StudentTryoutAccessService;
use App\Services\SubmitTryoutAttemptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TryoutController extends Controller
{
    public function index(Request $request, StudentTryoutAccessService $accessService): View
    {
        $student = $accessService->studentFor($request->user());

        $tryouts = Tryout::query()
            ->with(['subject:id,code,name'])
            ->withCount('questions')
            ->with([
                'attempts' => fn ($query) => $query
                    ->where('student_id', $student->id)
                    ->select(['id', 'tryout_id', 'status', 'started_at', 'expires_at', 'submitted_at']),
            ])
            ->where('status', 'published')
            ->whereHas('classRooms', fn ($query) => $query->whereKey($student->class_room_id))
            ->latest('start_at')
            ->paginate(10);

        return view('student.tryouts.index', compact('tryouts'));
    }

    public function show(Request $request, Tryout $tryout, StudentTryoutAccessService $accessService): View
    {
        $student = $accessService->studentFor($request->user());

        $accessService->ensureEligible($student, $tryout);

        $tryout->load('subject')->loadCount('questions');
        $attempt = $tryout->attempts()
            ->where('student_id', $student->id)
            ->first();

        return view('student.tryouts.show', compact('tryout', 'attempt'));
    }

    public function start(
        Request $request,
        Tryout $tryout,
        StudentTryoutAccessService $accessService,
        StartTryoutAttemptService $service,
    ): RedirectResponse {
        $student = $accessService->studentFor($request->user());
        $attempt = $service->start($student, $tryout);

        return redirect()->route('student.tryouts.attempts.show', [$tryout, $attempt]);
    }

    public function attempt(
        Request $request,
        Tryout $tryout,
        TryoutAttempt $attempt,
        StudentTryoutAccessService $accessService,
        GradeTryoutAttemptService $gradingService,
    ): View|RedirectResponse {
        $student = $accessService->studentFor($request->user());
        $attempt = $this->ownedAttempt($student->id, $tryout->id, $attempt->id);
        $attempt = $gradingService->finalizeExpired($attempt);

        if ($attempt->status !== 'in_progress') {
            return redirect()->route('student.tryouts.attempts.result', [$tryout, $attempt]);
        }

        $tryout->load([
            'subject:id,code,name',
            'questions' => fn ($query) => $query
                ->select(['id', 'tryout_id', 'question_text', 'order_number'])
                ->with(['options' => fn ($optionQuery) => $optionQuery
                    ->select(['id', 'tryout_question_id', 'option_label', 'option_text', 'order_number'])]),
        ]);

        $answers = $attempt->answers()
            ->get(['id', 'tryout_attempt_id', 'tryout_question_id', 'tryout_question_option_id'])
            ->keyBy('tryout_question_id');

        return view('student.tryouts.attempt', compact('tryout', 'attempt', 'answers'));
    }

    public function saveAnswer(
        Request $request,
        Tryout $tryout,
        TryoutAttempt $attempt,
        StudentTryoutAccessService $accessService,
        SaveTryoutAnswerService $service,
    ): JsonResponse {
        $student = $accessService->studentFor($request->user());

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'option_id' => ['required', 'integer'],
        ]);

        $attempt = $this->ownedAttempt($student->id, $tryout->id, $attempt->id);
        $answer = $service->save($student, $attempt, $data['question_id'], $data['option_id']);

        return response()->json([
            'message' => 'Jawaban berhasil disimpan.',
            'question_id' => $answer->tryout_question_id,
            'option_id' => $answer->tryout_question_option_id,
        ]);
    }

    public function submit(
        Request $request,
        Tryout $tryout,
        TryoutAttempt $attempt,
        StudentTryoutAccessService $accessService,
        SubmitTryoutAttemptService $service,
    ): RedirectResponse {
        $student = $accessService->studentFor($request->user());
        $attempt = $this->ownedAttempt($student->id, $tryout->id, $attempt->id);
        $attempt = $service->submit($student, $attempt);

        return redirect()->route('student.tryouts.attempts.result', [$tryout, $attempt])
            ->with('success', 'Jawaban Try Out berhasil dinilai.');
    }

    public function result(
        Request $request,
        Tryout $tryout,
        TryoutAttempt $attempt,
        StudentTryoutAccessService $accessService,
        GradeTryoutAttemptService $gradingService,
    ): View {
        $student = $accessService->studentFor($request->user());
        $attempt = $this->ownedAttempt($student->id, $tryout->id, $attempt->id);
        $attempt = $gradingService->finalizeExpired($attempt);

        abort_unless(in_array($attempt->status, ['submitted', 'expired'], true) && $attempt->graded_at, 404);

        $tryout->load('subject:id,code,name');
        $attempt->load('student.user:id,name');

        return view('student.tryouts.result', compact('tryout', 'attempt'));
    }

    public function review(
        Request $request,
        Tryout $tryout,
        TryoutAttempt $attempt,
        StudentTryoutAccessService $accessService,
        GradeTryoutAttemptService $gradingService,
    ): View {
        $student = $accessService->studentFor($request->user());
        $attempt = $this->ownedAttempt($student->id, $tryout->id, $attempt->id);
        $attempt = $gradingService->finalizeExpired($attempt);

        abort_unless(in_array($attempt->status, ['submitted', 'expired'], true) && $attempt->graded_at, 404);

        $tryout->load([
            'subject:id,code,name',
            'questions' => fn ($query) => $query
                ->select(['id', 'tryout_id', 'question_text', 'points', 'order_number'])
                ->with([
                    'options' => fn ($optionQuery) => $optionQuery
                        ->select(['id', 'tryout_question_id', 'option_label', 'option_text', 'is_correct', 'order_number']),
                    'attemptAnswers' => fn ($answerQuery) => $answerQuery
                        ->where('tryout_attempt_id', $attempt->id)
                        ->select(['id', 'tryout_attempt_id', 'tryout_question_id', 'tryout_question_option_id']),
                ]),
        ]);

        return view('student.tryouts.review', compact('tryout', 'attempt'));
    }

    private function ownedAttempt(int $studentId, int $tryoutId, int $attemptId): TryoutAttempt
    {
        return TryoutAttempt::query()
            ->whereKey($attemptId)
            ->where('student_id', $studentId)
            ->where('tryout_id', $tryoutId)
            ->firstOrFail();
    }
}