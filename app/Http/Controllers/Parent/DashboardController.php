<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use App\Models\TryoutAttempt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->isParent() && $user->status === 'active', 403, 'Akses tidak diizinkan.');

        $guardian = $user->guardian;
        $children = $guardian
            ? $guardian->students()
                ->with(['user', 'classRoom'])
                ->get()
            : collect();

        $childIds = $children->pluck('id')->all();

        // 1. Statistik Anak
        $totalChildren = $children->count();

        $totalCompletedTryouts = ! empty($childIds)
            ? TryoutAttempt::query()
                ->whereIn('student_id', $childIds)
                ->whereIn('status', ['submitted', 'expired'])
                ->count()
            : 0;

        $averageScore = ! empty($childIds)
            ? TryoutAttempt::query()
                ->whereIn('student_id', $childIds)
                ->whereNotNull('graded_at')
                ->whereIn('status', ['submitted', 'expired'])
                ->avg('final_score')
            : null;

        $gradedAttemptsCount = ! empty($childIds)
            ? TryoutAttempt::query()
                ->whereIn('student_id', $childIds)
                ->whereNotNull('graded_at')
                ->whereIn('status', ['submitted', 'expired'])
                ->count()
            : 0;

        $attendancePresentCount = ! empty($childIds)
            ? StudentAttendance::query()
                ->whereIn('student_id', $childIds)
                ->where('status', 'present')
                ->count()
            : 0;

        $attendanceTotalCount = ! empty($childIds)
            ? StudentAttendance::query()
                ->whereIn('student_id', $childIds)
                ->count()
            : 0;

        $statistics = [
            'total_children' => $totalChildren,
            'completed_tryouts' => $totalCompletedTryouts,
            'average_score' => $averageScore !== null ? (float) $averageScore : null,
            'graded_attempts_count' => $gradedAttemptsCount,
            'attendance_present' => $attendancePresentCount,
            'attendance_total' => $attendanceTotalCount,
        ];

        // 2. Hasil Try Out Terbaru Anak
        $recentTryouts = ! empty($childIds)
            ? TryoutAttempt::query()
                ->with([
                    'student.user:id,name',
                    'student.classRoom:id,name',
                    'tryout' => fn ($query) => $query->select(['id', 'subject_id', 'title', 'duration_minutes'])
                        ->with(['subject:id,code,name']),
                ])
                ->whereIn('student_id', $childIds)
                ->whereIn('status', ['submitted', 'expired'])
                ->whereNotNull('graded_at')
                ->latest('graded_at')
                ->limit(5)
                ->get()
            : collect();

        // 3. Riwayat Kehadiran Terbaru Anak
        $recentAttendances = ! empty($childIds)
            ? StudentAttendance::query()
                ->with([
                    'student.user:id,name',
                    'learningSession.learningSchedule.subject:id,code,name',
                    'learningSession.learningSchedule.tutor.user:id,name',
                ])
                ->whereIn('student_id', $childIds)
                ->latest('id')
                ->limit(5)
                ->get()
            : collect();

        return view('parent.dashboard', compact(
            'children',
            'statistics',
            'recentTryouts',
            'recentAttendances'
        ));
    }
}
