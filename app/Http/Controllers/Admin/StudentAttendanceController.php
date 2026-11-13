<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\LearningSession;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Tutor;
use App\Services\StudentAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $query = StudentAttendance::query()->with([
            'learningSession.learningSchedule.classRoom:id,code,name',
            'learningSession.learningSchedule.tutor.user:id,name',
            'learningSession.learningSchedule.subject:id,code,name',
            'student.user:id,name',
            'recorder:id,name',
        ]);

        if ($request->filled('session_date')) {
            $query->whereHas('learningSession', fn ($q) => $q->whereDate('session_date', $request->input('session_date')));
        }
        if ($request->filled('class_room_id')) {
            $query->whereHas('learningSession.learningSchedule', fn ($q) => $q->where('class_room_id', $request->input('class_room_id')));
        }
        if ($request->filled('tutor_id')) {
            $query->whereHas('learningSession.learningSchedule', fn ($q) => $q->where('tutor_id', $request->input('tutor_id')));
        }
        if ($request->filled('subject_id')) {
            $query->whereHas('learningSession.learningSchedule', fn ($q) => $q->where('subject_id', $request->input('subject_id')));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $attendances = $query->latest('id')->paginate(15)->appends($request->query());

        return view('admin.student-attendances.index', [
            'attendances' => $attendances,
            'classRooms' => ClassRoom::query()->orderBy('name')->get(['id', 'code', 'name']),
            'tutors' => Tutor::query()->with('user:id,name')->orderBy('id')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(['id', 'code', 'name']),
            'statuses' => StudentAttendance::STATUSES,
        ]);
    }

    public function show(LearningSession $learningSession): View
    {
        $learningSession->load([
            'learningSchedule.classRoom',
            'learningSchedule.tutor.user',
            'learningSchedule.subject',
            'attendances.student.user',
            'attendances.recorder',
        ]);

        $summary = $learningSession->attendances()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.student-attendances.show', compact('learningSession', 'summary'));
    }

    public function edit(LearningSession $learningSession): View
    {
        return view('admin.student-attendances.edit', $this->formData($learningSession));
    }

    public function update(Request $request, LearningSession $learningSession, StudentAttendanceService $service): RedirectResponse
    {
        $data = $request->validate([
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'attendances.*.status' => ['required', Rule::in(StudentAttendance::STATUSES)],
            'attendances.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service->save($learningSession, $data['attendances'], $request->user());

        return redirect()->route('admin.student-attendances.show', $learningSession)
            ->with('success', 'Absensi siswa berhasil diperbarui.');
    }

    private function formData(LearningSession $learningSession): array
    {
        $learningSession->load([
            'learningSchedule.classRoom',
            'learningSchedule.tutor.user',
            'learningSchedule.subject',
            'attendances',
        ]);

        $students = $learningSession->learningSchedule->classRoom->students()
            ->with('user:id,name,status')
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->orderBy('nis')
            ->get();

        return compact('learningSession', 'students');
    }
}
