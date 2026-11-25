<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\TryoutAttempt;
use App\Services\GradeTryoutAttemptService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, GradeTryoutAttemptService $gradingService): View
    {
        $user = $request->user();
        $student = $user->student()->with(['classRoom', 'user'])->first();

        abort_unless($user->isStudent() && $user->status === 'active' && $student, 403, 'Profil siswa tidak valid.');

        // Finalisasi attempt kadaluarsa milik siswa ini
        $expiredAttempts = TryoutAttempt::query()
            ->where('student_id', $student->id)
            ->where('status', 'in_progress')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expiredAttempts as $expiredAttempt) {
            $gradingService->finalizeExpired($expiredAttempt);
        }

        $classRoomId = $student->class_room_id;
        $isClassActive = $student->classRoom?->status === 'active';

        // 1. Total Try Out yang tersedia untuk kelas siswa
        $availableTryoutsCount = ($classRoomId && $isClassActive)
            ? Tryout::query()
                ->where('status', 'published')
                ->whereHas('classRooms', fn ($query) => $query->whereKey($classRoomId))
                ->count()
            : 0;

        // 2. Total Try Out yang sudah dikerjakan (submitted / expired)
        $completedTryoutIds = TryoutAttempt::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['submitted', 'expired'])
            ->when($classRoomId && $isClassActive, function ($query) use ($classRoomId) {
                $query->whereHas('tryout', function ($tryoutQuery) use ($classRoomId) {
                    $tryoutQuery->where('status', 'published')
                        ->whereHas('classRooms', fn ($q) => $q->whereKey($classRoomId));
                });
            })
            ->pluck('tryout_id')
            ->unique();

        $completedTryoutsCount = $completedTryoutIds->count();

        // 3. Total Try Out yang belum dikerjakan
        $pendingTryoutsCount = max(0, $availableTryoutsCount - $completedTryoutsCount);

        // 4. Rata-rata nilai Try Out dari attempt yang sudah dinilai
        $gradedAttemptsQuery = TryoutAttempt::query()
            ->where('student_id', $student->id)
            ->whereNotNull('graded_at')
            ->whereIn('status', ['submitted', 'expired']);

        $averageScore = (clone $gradedAttemptsQuery)->avg('final_score');
        $gradedAttemptsCount = (clone $gradedAttemptsQuery)->count();

        $statistics = [
            'available_tryouts' => $availableTryoutsCount,
            'completed_tryouts' => $completedTryoutsCount,
            'pending_tryouts' => $pendingTryoutsCount,
            'average_score' => $averageScore !== null ? (float) $averageScore : null,
            'graded_attempts_count' => $gradedAttemptsCount,
        ];

        // 3. Try Out Terbaru (hanya yang relevan dengan kelas siswa)
        $recentTryouts = ($classRoomId && $isClassActive)
            ? Tryout::query()
                ->with(['subject:id,code,name'])
                ->withCount('questions')
                ->with([
                    'attempts' => fn ($query) => $query
                        ->where('student_id', $student->id)
                        ->select(['id', 'tryout_id', 'status', 'started_at', 'expires_at', 'submitted_at', 'final_score', 'graded_at']),
                ])
                ->where('status', 'published')
                ->whereHas('classRooms', fn ($query) => $query->whereKey($classRoomId))
                ->latest('start_at')
                ->limit(5)
                ->get()
            : collect();

        // 4. Riwayat Pengerjaan (hanya attempt milik siswa yang login)
        $recentAttempts = TryoutAttempt::query()
            ->with([
                'tryout' => fn ($query) => $query->select(['id', 'subject_id', 'title', 'duration_minutes', 'start_at', 'end_at'])
                    ->with(['subject:id,code,name']),
            ])
            ->where('student_id', $student->id)
            ->latest('started_at')
            ->paginate(5, ['*'], 'history_page');

        // 5. Informasi Hasil Try Out (snapshot grading attempt terakhir yang dinilai)
        $latestGradedAttempt = TryoutAttempt::query()
            ->with([
                'tryout' => fn ($query) => $query->select(['id', 'subject_id', 'title'])
                    ->with(['subject:id,code,name']),
            ])
            ->where('student_id', $student->id)
            ->whereNotNull('graded_at')
            ->whereIn('status', ['submitted', 'expired'])
            ->latest('graded_at')
            ->first();

        $gradingSummary = [
            'total_graded' => $gradedAttemptsCount,
            'total_questions' => (int) (clone $gradedAttemptsQuery)->sum('total_questions'),
            'correct_answers' => (int) (clone $gradedAttemptsQuery)->sum('correct_answers'),
            'incorrect_answers' => (int) (clone $gradedAttemptsQuery)->sum('incorrect_answers'),
            'unanswered_questions' => (int) (clone $gradedAttemptsQuery)->sum('unanswered_questions'),
            'total_points' => (int) (clone $gradedAttemptsQuery)->sum('total_points'),
            'earned_points' => (int) (clone $gradedAttemptsQuery)->sum('earned_points'),
        ];

        return view('student.dashboard', compact(
            'student',
            'statistics',
            'recentTryouts',
            'recentAttempts',
            'latestGradedAttempt',
            'gradingSummary'
        ));
    }
}
