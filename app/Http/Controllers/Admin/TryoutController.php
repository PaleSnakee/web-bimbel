<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Tryout;
use App\Models\Tutor;
use App\Services\TryoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TryoutController extends Controller
{
    public function index(Request $request): View
    {
        $query = Tryout::query()->with([
            'subject:id,code,name',
            'tutor.user:id,name',
            'classRooms:id,code,name',
        ])->withCount('classRooms');

        if ($search = trim((string) $request->input('search'))) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        if ($request->filled('tutor_id')) {
            $query->where('tutor_id', $request->input('tutor_id'));
        }

        if ($request->filled('class_room_id')) {
            $query->whereHas('classRooms', fn ($q) => $q->whereKey($request->input('class_room_id')));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('start_at', '<=', $request->input('end_date'));
        }

        $tryouts = $query->latest('start_at')->paginate(10)->appends($request->query());

        return view('admin.tryouts.index', array_merge(compact('tryouts'), $this->filterData()));
    }

    public function create(): View
    {
        return view('admin.tryouts.create', $this->formData());
    }

    public function store(Request $request, TryoutService $service): RedirectResponse
    {
        $data = $this->validatedData($request, true);
        $tryout = $service->validateAndSave($data);

        return redirect()->route('admin.tryouts.show', $tryout)
            ->with('success', 'Try Out berhasil ditambahkan sebagai draft.');
    }

    public function show(Tryout $tryout): View
    {
        $tryout->load(['subject', 'tutor.user', 'classRooms']);

        return view('admin.tryouts.show', compact('tryout'));
    }

    public function edit(Tryout $tryout): View
    {
        $this->ensureEditable($tryout);

        return view('admin.tryouts.edit', array_merge(compact('tryout'), $this->formData()));
    }

    public function update(Request $request, Tryout $tryout, TryoutService $service): RedirectResponse
    {
        $this->ensureEditable($tryout);
        $data = $this->validatedData($request);
        $service->validateAndSave($data, $tryout);

        return redirect()->route('admin.tryouts.show', $tryout)
            ->with('success', 'Try Out berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Tryout $tryout): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Tryout::STATUSES)],
        ]);

        $allowedTransitions = [
            'draft' => ['published', 'cancelled'],
            'published' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];

        if (! in_array($data['status'], $allowedTransitions[$tryout->status], true)) {
            throw ValidationException::withMessages([
                'status' => 'Perubahan status Try Out tidak valid.',
            ]);
        }

        $tryout->update(['status' => $data['status']]);

        return redirect()->route('admin.tryouts.show', $tryout)
            ->with('success', 'Status Try Out berhasil diperbarui.');
    }

    public function destroy(Tryout $tryout): RedirectResponse
    {
        if ($tryout->status !== 'draft') {
            return redirect()->route('admin.tryouts.index')
                ->with('error', 'Hanya Try Out berstatus draft yang dapat dihapus.');
        }

        $tryout->delete();

        return redirect()->route('admin.tryouts.index')
            ->with('success', 'Try Out berhasil dihapus.');
    }

    private function validatedData(Request $request, bool $creating = false): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'tutor_id' => ['nullable', 'integer', Rule::exists('tutors', 'id')],
            'class_room_ids' => ['required', 'array', 'min:1'],
            'class_room_ids.*' => ['required', 'integer', 'distinct', Rule::exists('class_rooms', 'id')],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
        ]);

        if ($data['tutor_id'] ?? null) {
            $tutor = Tutor::query()->with('user')->findOrFail($data['tutor_id']);

            if ($tutor->user?->role !== 'tutor' || $tutor->user?->status !== 'active') {
                throw ValidationException::withMessages([
                    'tutor_id' => 'Tutor pengelola tidak aktif atau tidak valid.',
                ]);
            }

            if (! $tutor->subjects()->whereKey($data['subject_id'])->exists()) {
                throw ValidationException::withMessages([
                    'subject_id' => 'Tutor pengelola tidak mengampu mata pelajaran ini.',
                ]);
            }
        }

        $data['status'] = 'draft';

        return $data;
    }

    private function ensureEditable(Tryout $tryout): void
    {
        if ($tryout->status !== 'draft') {
            abort(403, 'Hanya Try Out berstatus draft yang dapat diubah.');
        }
    }

    private function formData(): array
    {
        return [
            'classRooms' => ClassRoom::query()->where('status', 'active')->orderBy('name')->get(['id', 'code', 'name']),
            'subjects' => Subject::query()->where('status', 'active')->orderBy('name')->get(['id', 'code', 'name']),
            'tutors' => Tutor::query()->with('user:id,name')->whereHas('user', fn ($q) => $q->where('role', 'tutor')->where('status', 'active'))->orderBy('id')->get(),
            'statuses' => Tryout::STATUSES,
        ];
    }

    private function filterData(): array
    {
        return [
            'classRooms' => ClassRoom::query()->orderBy('name')->get(['id', 'code', 'name']),
            'subjects' => Subject::query()->orderBy('name')->get(['id', 'code', 'name']),
            'tutors' => Tutor::query()->with('user:id,name')->orderBy('id')->get(),
            'statuses' => Tryout::STATUSES,
        ];
    }
}