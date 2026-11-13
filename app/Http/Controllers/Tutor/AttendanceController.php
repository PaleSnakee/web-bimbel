<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\LearningSession;
use App\Models\StudentAttendance;
use App\Services\StudentAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $tutor = $request->user()->tutor;
        abort_unless($tutor, 403);

        $sessions = LearningSession::query()
            ->with([
                'learningSchedule.classRoom:id,code,name',
                'learningSchedule.subject:id,code,name',
            ])
            ->withCount('attendances')
            ->whereHas('learningSchedule', fn ($q) => $q->where('tutor_id', $tutor->id))
            ->latest('session_date')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('tutor.attendances.index', compact('sessions'));
    }

    public function edit(Request $request, LearningSession $learningSession): View
    {
        return view('tutor.attendances.edit', $this->formData($request, $learningSession));
    }

    public function update(Request $request, LearningSession $learningSession, StudentAttendanceService $service): RedirectResponse
    {
        $learningSession = $this->ownedSession($request, $learningSession);

        $data = $request->validate([
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'attendances.*.status' => ['required', Rule::in(StudentAttendance::STATUSES)],
            'attendances.*.notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $service->save($learningSession, $data['attendances'], $request->user());

        return redirect()->route('tutor.attendances.index')
            ->with('success', 'Absensi siswa berhasil disimpan.');
    }

    private function formData(Request $request, LearningSession $learningSession): array
    {
        $learningSession = $this->ownedSession($request, $learningSession);
        $learningSession->load([
            'learningSchedule.classRoom',
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

    private function ownedSession(Request $request, LearningSession $learningSession): LearningSession
    {
        $tutor = $request->user()->tutor;
        abort_unless($tutor, 403);

        return LearningSession::query()
            ->whereKey($learningSession->id)
            ->whereHas('learningSchedule', fn ($q) => $q->where('tutor_id', $tutor->id))
            ->firstOrFail();
    }
}
