<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\TryoutAttempt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChildController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->isParent() && $user->status === 'active', 403, 'Akses tidak diizinkan.');

        $guardian = $user->guardian;
        $children = $guardian
            ? $guardian->students()
                ->with(['user', 'classRoom'])
                ->withCount([
                    'tryoutAttempts as completed_tryouts_count' => fn ($query) => $query->whereIn('status', ['submitted', 'expired']),
                    'attendances as present_attendances_count' => fn ($query) => $query->where('status', 'present'),
                ])
                ->get()
            : collect();

        return view('parent.children.index', compact('children'));
    }

    public function show(Request $request, Student $student): View
    {
        $user = $request->user();
        abort_unless($user->isParent() && $user->status === 'active', 403, 'Akses tidak diizinkan.');

        $child = $this->getOwnedChild($user, $student);

        // Mata pelajaran yang tersedia untuk jenjang pendidikan anak
        $subjects = Subject::query()
            ->where('education_level', $child->education_level)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Statistik akademik anak
        $completedTryoutsCount = $child->tryoutAttempts()
            ->whereIn('status', ['submitted', 'expired'])
            ->count();

        $averageScore = $child->tryoutAttempts()
            ->whereNotNull('graded_at')
            ->whereIn('status', ['submitted', 'expired'])
            ->avg('final_score');

        $gradedAttemptsCount = $child->tryoutAttempts()
            ->whereNotNull('graded_at')
            ->whereIn('status', ['submitted', 'expired'])
            ->count();

        $attendancePresentCount = $child->attendances()
            ->where('status', 'present')
            ->count();

        $attendanceTotalCount = $child->attendances()
            ->count();

        $childStats = [
            'completed_tryouts' => $completedTryoutsCount,
            'average_score' => $averageScore !== null ? (float) $averageScore : null,
            'graded_attempts_count' => $gradedAttemptsCount,
            'attendance_present' => $attendancePresentCount,
            'attendance_total' => $attendanceTotalCount,
        ];

        // Riwayat Try Out anak (hanya yang sudah selesai dan dinilai)
        $tryoutAttempts = $child->tryoutAttempts()
            ->with([
                'tryout.subject:id,code,name',
            ])
            ->whereIn('status', ['submitted', 'expired'])
            ->whereNotNull('graded_at')
            ->latest('graded_at')
            ->paginate(5, ['*'], 'tryout_page');

        // Riwayat Absensi anak
        $attendances = $child->attendances()
            ->with([
                'learningSession.learningSchedule.subject:id,code,name',
                'learningSession.learningSchedule.tutor.user:id,name',
            ])
            ->latest('id')
            ->paginate(5, ['*'], 'attendance_page');

        return view('parent.children.show', compact(
            'child',
            'subjects',
            'childStats',
            'tryoutAttempts',
            'attendances'
        ));
    }

    public function tryouts(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->isParent() && $user->status === 'active', 403, 'Akses tidak diizinkan.');

        $guardian = $user->guardian;
        $children = $guardian
            ? $guardian->students()->with('user')->get()
            : collect();

        $childIds = $children->pluck('id')->all();

        $selectedChildId = null;
        if ($request->filled('student_id')) {
            $requestedId = (int) $request->input('student_id');
            if (in_array($requestedId, $childIds, true)) {
                $selectedChildId = $requestedId;
            }
        }

        $query = TryoutAttempt::query()
            ->with([
                'student.user:id,name',
                'student.classRoom:id,name',
                'tryout.subject:id,code,name',
            ])
            ->whereIn('student_id', $childIds)
            ->whereIn('status', ['submitted', 'expired'])
            ->whereNotNull('graded_at');

        if ($selectedChildId) {
            $query->where('student_id', $selectedChildId);
        }

        $tryoutAttempts = ! empty($childIds)
            ? $query->latest('graded_at')->paginate(10)->appends($request->query())
            : TryoutAttempt::query()->whereRaw('1 = 0')->paginate(10);

        return view('parent.tryouts.index', compact('children', 'tryoutAttempts', 'selectedChildId'));
    }

    public function attendances(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->isParent() && $user->status === 'active', 403, 'Akses tidak diizinkan.');

        $guardian = $user->guardian;
        $children = $guardian
            ? $guardian->students()->with('user')->get()
            : collect();

        $childIds = $children->pluck('id')->all();

        $selectedChildId = null;
        if ($request->filled('student_id')) {
            $requestedId = (int) $request->input('student_id');
            if (in_array($requestedId, $childIds, true)) {
                $selectedChildId = $requestedId;
            }
        }

        $query = StudentAttendance::query()
            ->with([
                'student.user:id,name',
                'student.classRoom:id,name',
                'learningSession.learningSchedule.subject:id,code,name',
                'learningSession.learningSchedule.tutor.user:id,name',
            ])
            ->whereIn('student_id', $childIds);

        if ($selectedChildId) {
            $query->where('student_id', $selectedChildId);
        }

        $attendances = ! empty($childIds)
            ? $query->latest('id')->paginate(10)->appends($request->query())
            : StudentAttendance::query()->whereRaw('1 = 0')->paginate(10);

        return view('parent.attendances.index', compact('children', 'attendances', 'selectedChildId'));
    }

    private function getOwnedChild(object $user, Student $student): Student
    {
        $guardian = $user->guardian;
        abort_unless($guardian, 404, 'Profil orang tua tidak ditemukan.');

        $child = $guardian->students()
            ->with(['user', 'classRoom'])
            ->where('students.id', $student->id)
            ->first();

        abort_unless($child, 404, 'Data anak tidak ditemukan atau tidak terhubung dengan akun Anda.');

        return $child;
    }
}
