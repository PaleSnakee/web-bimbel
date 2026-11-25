<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\LearningSession;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TryoutAttempt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->isTutor() && $user->status === 'active', 403, 'Akses tidak diizinkan.');

        $tutor = $user->tutor;
        abort_unless($tutor, 403, 'Profil tutor tidak ditemukan.');

        // Mata pelajaran yang diampu tutor
        $subjects = $tutor->subjects()->orderBy('name')->get();

        // 1. Jadwal Mengajar Aktif Tutor
        $activeSchedules = $tutor->learningSchedules()
            ->with(['classRoom:id,code,name,education_level', 'subject:id,code,name'])
            ->where('status', 'active')
            ->orderByRaw("
                CASE day_of_week
                    WHEN 'monday' THEN 1
                    WHEN 'tuesday' THEN 2
                    WHEN 'wednesday' THEN 3
                    WHEN 'thursday' THEN 4
                    WHEN 'friday' THEN 5
                    WHEN 'saturday' THEN 6
                    WHEN 'sunday' THEN 7
                    ELSE 8
                END
            ")
            ->orderBy('start_time')
            ->get();

        $totalActiveSchedules = $activeSchedules->count();

        // 2. Total Sesi Pembelajaran Tutor
        $sessionsQuery = LearningSession::query()
            ->whereHas('learningSchedule', fn ($q) => $q->where('tutor_id', $tutor->id));

        $totalSessions = (clone $sessionsQuery)->count();
        $scheduledSessionsCount = (clone $sessionsQuery)->where('status', 'scheduled')->count();
        $completedSessionsCount = (clone $sessionsQuery)->where('status', 'completed')->count();
        $cancelledSessionsCount = (clone $sessionsQuery)->where('status', 'cancelled')->count();

        $todayDate = now()->toDateString();

        // Sesi pembelajaran terdekat / terbaru
        $recentSessions = (clone $sessionsQuery)
            ->with([
                'learningSchedule.classRoom:id,code,name',
                'learningSchedule.subject:id,code,name',
            ])
            ->withCount('attendances')
            ->orderByRaw("CASE WHEN status = 'scheduled' AND session_date >= '{$todayDate}' THEN 0 ELSE 1 END")
            ->orderBy('session_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->limit(5)
            ->get();

        // 3. Total Siswa Unik yang Diajar dari kelas yang memiliki jadwal aktif tutor
        $activeClassRoomIds = $activeSchedules->pluck('class_room_id')->unique()->filter()->all();

        $totalStudentsTaught = ! empty($activeClassRoomIds)
            ? Student::query()
                ->whereIn('class_room_id', $activeClassRoomIds)
                ->whereHas('user', fn ($q) => $q->where('status', 'active'))
                ->distinct()
                ->count('id')
            : 0;

        // 4. Total Absensi yang Dicatat oleh Akun Tutor Ini
        $totalRecordedAttendances = $user->recordedAttendances()->count();

        // Ringkasan Absensi dari seluruh sesi milik tutor
        $attendanceAgg = StudentAttendance::query()
            ->whereHas('learningSession.learningSchedule', fn ($q) => $q->where('tutor_id', $tutor->id))
            ->selectRaw("
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = 'sick' THEN 1 ELSE 0 END) as sick_count,
                SUM(CASE WHEN status = 'permission' THEN 1 ELSE 0 END) as permission_count,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_count
            ")
            ->first();

        $attendanceSummary = [
            'present' => (int) ($attendanceAgg->present_count ?? 0),
            'sick' => (int) ($attendanceAgg->sick_count ?? 0),
            'permission' => (int) ($attendanceAgg->permission_count ?? 0),
            'absent' => (int) ($attendanceAgg->absent_count ?? 0),
            'total' => (int) ($attendanceAgg->total_count ?? 0),
        ];

        // 5. Statistik & Aktivitas Try Out Milik Tutor
        $tryoutsQuery = $tutor->tryouts();

        $totalTryouts = (clone $tryoutsQuery)->count();
        $draftTryouts = (clone $tryoutsQuery)->where('status', 'draft')->count();
        $publishedTryouts = (clone $tryoutsQuery)->where('status', 'published')->count();
        $completedTryouts = (clone $tryoutsQuery)->where('status', 'completed')->count();

        $tryoutStats = [
            'total' => $totalTryouts,
            'draft' => $draftTryouts,
            'published' => $publishedTryouts,
            'completed' => $completedTryouts,
        ];

        $resultsQuery = TryoutAttempt::query()
            ->whereNotNull('graded_at')
            ->whereHas('tryout', fn ($q) => $q->where('tutor_id', $tutor->id));

        $gradedAttemptsCount = (clone $resultsQuery)->count();
        $averageTryoutScore = (clone $resultsQuery)->avg('final_score');

        $recentTryoutResults = (clone $resultsQuery)
            ->with([
                'tryout:id,title,subject_id',
                'tryout.subject:id,code,name',
                'student.user:id,name',
                'student.classRoom:id,code,name',
            ])
            ->latest('graded_at')
            ->limit(5)
            ->get();

        $statistics = [
            'active_schedules' => $totalActiveSchedules,
            'total_sessions' => $totalSessions,
            'students_taught' => $totalStudentsTaught,
            'recorded_attendances' => $totalRecordedAttendances,
        ];

        return view('tutor.dashboard', compact(
            'tutor',
            'subjects',
            'statistics',
            'activeSchedules',
            'scheduledSessionsCount',
            'completedSessionsCount',
            'cancelledSessionsCount',
            'recentSessions',
            'attendanceSummary',
            'tryoutStats',
            'gradedAttemptsCount',
            'averageTryoutScore',
            'recentTryoutResults'
        ));
    }
}
