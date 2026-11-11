<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\LearningSchedule;
use App\Models\LearningSession;
use App\Models\Subject;
use App\Models\Tutor;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LearningSessionController extends Controller
{
    public function index(Request $request): View
    {
        $query = LearningSession::query()->with([
            'learningSchedule.classRoom:id,code,name',
            'learningSchedule.tutor.user:id,name',
            'learningSchedule.subject:id,code,name',
        ]);

        if ($request->filled('session_date')) {
            $query->whereDate('session_date', $request->input('session_date'));
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('topic', 'like', "%{$search}%")
                    ->orWhereHas('learningSchedule.classRoom', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('learningSchedule.tutor.user', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('learningSchedule.subject', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('class_room_id')) {
            $query->whereHas('learningSchedule', fn ($q) => $q->where('class_room_id', $request->input('class_room_id')));
        }

        if ($request->filled('tutor_id')) {
            $query->whereHas('learningSchedule', fn ($q) => $q->where('tutor_id', $request->input('tutor_id')));
        }

        if ($request->filled('subject_id')) {
            $query->whereHas('learningSchedule', fn ($q) => $q->where('subject_id', $request->input('subject_id')));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $sessions = $query->latest('session_date')->orderBy('start_time')->paginate(10);
        $sessions->appends($request->query());

        return view('admin.learning-sessions.index', array_merge([
            'sessions' => $sessions,
        ], $this->filterData()));
    }

    public function create(): View
    {
        return view('admin.learning-sessions.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        LearningSession::create($this->validatedData($request));

        return redirect()->route('admin.learning-sessions.index')
            ->with('success', 'Sesi pembelajaran berhasil ditambahkan.');
    }

    public function show(LearningSession $learningSession): View
    {
        $learningSession->load([
            'learningSchedule.classRoom',
            'learningSchedule.tutor.user',
            'learningSchedule.subject',
        ]);

        return view('admin.learning-sessions.show', compact('learningSession'));
    }

    public function edit(LearningSession $learningSession): View
    {
        $learningSession->load('learningSchedule');

        return view('admin.learning-sessions.edit', array_merge(compact('learningSession'), $this->formData($learningSession)));
    }

    public function update(Request $request, LearningSession $learningSession): RedirectResponse
    {
        $data = $this->validatedData($request, $learningSession);

        if ($learningSession->status === 'completed' && $this->hasCompletedSessionImmutableChanges($learningSession, $data)) {
            throw ValidationException::withMessages([
                'learning_schedule_id' => 'Data jadwal, tanggal, waktu, dan status sesi yang sudah selesai tidak dapat diubah.',
            ]);
        }

        $learningSession->update($data);

        return redirect()->route('admin.learning-sessions.show', $learningSession)
            ->with('success', 'Sesi pembelajaran berhasil diperbarui.');
    }

    public function updateStatus(Request $request, LearningSession $learningSession): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(LearningSession::STATUSES)],
        ]);

        if ($learningSession->status === 'completed' && $data['status'] !== 'completed') {
            throw ValidationException::withMessages([
                'status' => 'Status sesi yang sudah selesai tidak dapat diubah.',
            ]);
        }

        $learningSession->update($data);

        return redirect()->route('admin.learning-sessions.show', $learningSession)
            ->with('success', 'Status sesi pembelajaran berhasil diperbarui.');
    }

    public function destroy(LearningSession $learningSession): RedirectResponse
    {
        $learningSession->delete();

        return redirect()->route('admin.learning-sessions.index')
            ->with('success', 'Sesi pembelajaran berhasil dihapus.');
    }

    private function validatedData(Request $request, ?LearningSession $learningSession = null): array
    {
        $data = $request->validate([
            'learning_schedule_id' => ['required', 'integer', Rule::exists('learning_schedules', 'id')],
            'session_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'status' => ['required', Rule::in(LearningSession::STATUSES)],
            'topic' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->validateSessionData($data, $learningSession);

        return $data;
    }

    private function validateSessionData(array $data, ?LearningSession $ignoreSession = null): void
    {
        $schedule = LearningSchedule::query()
            ->with(['classRoom', 'tutor.user', 'subject'])
            ->findOrFail($data['learning_schedule_id']);

        if ($schedule->status !== 'active') {
            throw ValidationException::withMessages([
                'learning_schedule_id' => 'Jadwal pembelajaran yang dipilih tidak aktif.',
            ]);
        }

        $this->validateScheduleResources($schedule);

        $sessionDate = Carbon::parse($data['session_date']);

        if ($sessionDate->englishDayOfWeek !== $schedule->day_of_week) {
            throw ValidationException::withMessages([
                'session_date' => 'Tanggal sesi harus sesuai dengan hari pada jadwal pembelajaran.',
            ]);
        }

        if ($sessionDate->lt($schedule->effective_from)
            || ($schedule->effective_until && $sessionDate->gt($schedule->effective_until))) {
            throw ValidationException::withMessages([
                'session_date' => 'Tanggal sesi berada di luar periode berlaku jadwal pembelajaran.',
            ]);
        }

        if ($data['start_time'] < $schedule->start_time->format('H:i')
            || $data['end_time'] > $schedule->end_time->format('H:i')) {
            throw ValidationException::withMessages([
                'start_time' => 'Waktu sesi harus berada dalam rentang jam jadwal pembelajaran.',
            ]);
        }

        $duplicateQuery = LearningSession::query()
            ->where('learning_schedule_id', $schedule->id)
            ->whereDate('session_date', $data['session_date']);

        if ($ignoreSession) {
            $duplicateQuery->whereKeyNot($ignoreSession->getKey());
        }

        if ($duplicateQuery->exists()) {
            throw ValidationException::withMessages([
                'session_date' => 'Jadwal ini sudah memiliki sesi pada tanggal yang dipilih.',
            ]);
        }

        if ($data['status'] === 'cancelled') {
            return;
        }

        foreach (['tutor_id', 'class_room_id'] as $field) {
            $conflictQuery = LearningSession::query()
                ->whereIn('status', ['scheduled', 'completed'])
                ->whereDate('session_date', $data['session_date'])
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->whereHas('learningSchedule', fn ($q) => $q->where($field, $schedule->{$field}));

            if ($ignoreSession) {
                $conflictQuery->whereKeyNot($ignoreSession->getKey());
            }

            if ($conflictQuery->exists()) {
                $label = $field === 'tutor_id' ? 'Tutor' : 'Kelas';

                throw ValidationException::withMessages([
                    'start_time' => "{$label} sudah memiliki sesi yang bentrok pada tanggal dan waktu tersebut.",
                ]);
            }
        }
    }

    private function validateScheduleResources(LearningSchedule $schedule): void
    {
        $classRoom = $schedule->classRoom;
        $tutor = $schedule->tutor;
        $subject = $schedule->subject;

        if (! $classRoom || $classRoom->status !== 'active') {
            throw ValidationException::withMessages([
                'learning_schedule_id' => 'Kelas pada jadwal pembelajaran tidak aktif.',
            ]);
        }

        if (! $tutor || $tutor->user?->role !== 'tutor' || $tutor->user?->status !== 'active') {
            throw ValidationException::withMessages([
                'learning_schedule_id' => 'Tutor pada jadwal pembelajaran tidak aktif.',
            ]);
        }

        if (! $subject || $subject->status !== 'active') {
            throw ValidationException::withMessages([
                'learning_schedule_id' => 'Mata pelajaran pada jadwal pembelajaran tidak aktif.',
            ]);
        }
    }

    private function hasCompletedSessionImmutableChanges(LearningSession $learningSession, array $data): bool
    {
        return (int) $data['learning_schedule_id'] !== $learningSession->learning_schedule_id
            || $data['session_date'] !== $learningSession->session_date->format('Y-m-d')
            || $data['start_time'] !== $learningSession->start_time->format('H:i')
            || $data['end_time'] !== $learningSession->end_time->format('H:i')
            || $data['status'] !== $learningSession->status;
    }

    private function formData(?LearningSession $learningSession = null): array
    {
        $schedules = LearningSchedule::query()
            ->with(['classRoom:id,code,name', 'tutor.user:id,name', 'subject:id,code,name'])
            ->where('status', 'active')
            ->orderBy('day_of_week')
            ->orderBy('start_time');

        if ($learningSession && $learningSession->learningSchedule?->status !== 'active') {
            $schedules->orWhereKey($learningSession->learning_schedule_id);
        }

        return array_merge($this->filterData(), [
            'schedules' => $schedules->get(),
        ]);
    }

    private function filterData(): array
    {
        return [
            'classRooms' => ClassRoom::query()->orderBy('name')->get(['id', 'code', 'name']),
            'tutors' => Tutor::query()->with('user:id,name')->whereHas('user', fn ($q) => $q->where('role', 'tutor'))->orderBy('id')->get(),
            'subjects' => Subject::query()->orderBy('name')->get(['id', 'code', 'name']),
            'statuses' => LearningSession::STATUSES,
        ];
    }
}