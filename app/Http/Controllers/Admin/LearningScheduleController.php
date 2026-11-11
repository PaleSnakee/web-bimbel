<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\LearningSchedule;
use App\Models\Subject;
use App\Models\Tutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LearningScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $query = LearningSchedule::query()->with([
            'classRoom:id,code,name',
            'tutor.user:id,name',
            'subject:id,code,name',
        ]);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->whereHas('classRoom', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                    ->orWhereHas('tutor.user', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('subject', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }

        foreach (['class_room_id', 'tutor_id', 'subject_id', 'status'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        $schedules = $query->orderBy('day_of_week')->orderBy('start_time')->paginate(10);
        $schedules->appends($request->query());

        return view('admin.learning-schedules.index', array_merge([
            'schedules' => $schedules,
        ], $this->filterData()));
    }

    public function create(): View
    {
        return view('admin.learning-schedules.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        LearningSchedule::create($data);

        return redirect()->route('admin.learning-schedules.index')
            ->with('success', 'Jadwal pembelajaran berhasil ditambahkan.');
    }

    public function show(LearningSchedule $learningSchedule): View
    {
        $learningSchedule->load(['classRoom', 'tutor.user', 'subject']);

        return view('admin.learning-schedules.show', compact('learningSchedule'));
    }

    public function edit(LearningSchedule $learningSchedule): View
    {
        return view('admin.learning-schedules.edit', array_merge(compact('learningSchedule'), $this->formData()));
    }

    public function update(Request $request, LearningSchedule $learningSchedule): RedirectResponse
    {
        $learningSchedule->update($this->validatedData($request, $learningSchedule));

        return redirect()->route('admin.learning-schedules.show', $learningSchedule)
            ->with('success', 'Jadwal pembelajaran berhasil diperbarui.');
    }

    public function activate(LearningSchedule $learningSchedule): RedirectResponse
    {
        $data = [
            'class_room_id' => $learningSchedule->class_room_id,
            'tutor_id' => $learningSchedule->tutor_id,
            'subject_id' => $learningSchedule->subject_id,
            'day_of_week' => $learningSchedule->day_of_week,
            'start_time' => $learningSchedule->start_time->format('H:i'),
            'end_time' => $learningSchedule->end_time->format('H:i'),
            'effective_from' => $learningSchedule->effective_from->format('Y-m-d'),
            'effective_until' => $learningSchedule->effective_until?->format('Y-m-d'),
            'status' => 'active',
        ];
        $this->validateScheduleData($data, $learningSchedule);

        $learningSchedule->update(['status' => 'active']);

        return redirect()->route('admin.learning-schedules.index')
            ->with('success', 'Jadwal pembelajaran berhasil diaktifkan.');
    }

    public function deactivate(LearningSchedule $learningSchedule): RedirectResponse
    {
        $learningSchedule->update(['status' => 'inactive']);

        return redirect()->route('admin.learning-schedules.index')
            ->with('success', 'Jadwal pembelajaran berhasil dinonaktifkan.');
    }

    public function destroy(LearningSchedule $learningSchedule): RedirectResponse
    {
        if ($learningSchedule->learningSessions()->exists()) {
            return redirect()->route('admin.learning-schedules.index')
                ->with('error', 'Jadwal pembelajaran tidak dapat dihapus karena sudah memiliki riwayat sesi.');
        }

        $learningSchedule->delete();

        return redirect()->route('admin.learning-schedules.index')
            ->with('success', 'Jadwal pembelajaran berhasil dihapus.');
    }

    private function validatedData(Request $request, ?LearningSchedule $learningSchedule = null): array
    {
        $data = $request->validate([
            'class_room_id' => ['required', 'integer', Rule::exists('class_rooms', 'id')],
            'tutor_id' => ['required', 'integer', Rule::exists('tutors', 'id')],
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'day_of_week' => ['required', Rule::in(LearningSchedule::DAYS_OF_WEEK)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'effective_from' => ['required', 'date'],
            'effective_until' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', Rule::in(LearningSchedule::STATUSES)],
            'notes' => ['nullable', 'string'],
        ]);

        $this->validateScheduleData($data, $learningSchedule);

        return $data;
    }

    private function validateScheduleData(array $data, ?LearningSchedule $ignoreSchedule = null): void
    {
        $classRoom = ClassRoom::findOrFail($data['class_room_id']);
        $tutor = Tutor::query()->with('user')->findOrFail($data['tutor_id']);
        $subject = Subject::findOrFail($data['subject_id']);

        if ($classRoom->status !== 'active') {
            throw ValidationException::withMessages(['class_room_id' => 'Kelas yang dipilih tidak aktif.']);
        }

        if ($tutor->user?->role !== 'tutor' || $tutor->user?->status !== 'active') {
            throw ValidationException::withMessages(['tutor_id' => 'Tutor yang dipilih tidak aktif atau tidak valid.']);
        }

        if ($subject->status !== 'active') {
            throw ValidationException::withMessages(['subject_id' => 'Mata pelajaran yang dipilih tidak aktif.']);
        }

        if (! $tutor->subjects()->whereKey($subject->id)->exists()) {
            throw ValidationException::withMessages(['subject_id' => 'Tutor yang dipilih belum memiliki assignment mata pelajaran ini.']);
        }

        if ($data['status'] !== 'active') {
            return;
        }

        foreach (['tutor_id' => 'tutor_id', 'class_room_id' => 'class_room_id'] as $field => $column) {
            $conflictQuery = LearningSchedule::query()
                ->where('status', 'active')
                ->where($column, $data[$field])
                ->where('day_of_week', $data['day_of_week'])
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->where('effective_from', '<=', $data['effective_until'] ?? '9999-12-31')
                ->where(function ($query) use ($data) {
                    $query->whereNull('effective_until')
                        ->orWhere('effective_until', '>=', $data['effective_from']);
                });

            if ($ignoreSchedule) {
                $conflictQuery->whereKeyNot($ignoreSchedule->getKey());
            }

            if ($conflictQuery->exists()) {
                $label = $field === 'tutor_id' ? 'Tutor' : 'Kelas';
                throw ValidationException::withMessages([
                    $field => "{$label} sudah memiliki jadwal aktif yang bentrok pada hari, jam, dan periode berlaku yang sama.",
                ]);
            }
        }
    }

    private function formData(): array
    {
        return array_merge($this->filterData(), [
            'daysOfWeek' => LearningSchedule::DAYS_OF_WEEK,
        ]);
    }

    private function filterData(): array
    {
        return [
            'classRooms' => ClassRoom::query()->where('status', 'active')->orderBy('name')->get(['id', 'code', 'name']),
            'tutors' => Tutor::query()->with('user:id,name')->whereHas('user', fn ($q) => $q->where('role', 'tutor')->where('status', 'active'))->orderBy('id')->get(),
            'subjects' => Subject::query()->where('status', 'active')->orderBy('name')->get(['id', 'code', 'name']),
            'statuses' => LearningSchedule::STATUSES,
        ];
    }
}
