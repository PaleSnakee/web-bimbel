<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Tryout;
use App\Services\TryoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TryoutController extends Controller
{
    public function index(Request $request): View
    {
        $tutor = $request->user()->tutor;
        abort_unless($tutor, 403);

        $tryouts = Tryout::query()
            ->with(['subject:id,code,name', 'classRooms:id,code,name'])
            ->withCount('classRooms')
            ->where('tutor_id', $tutor->id)
            ->latest('start_at')
            ->paginate(10)
            ->appends($request->query());

        return view('tutor.tryouts.index', compact('tryouts'));
    }

    public function create(Request $request): View
    {
        return view('tutor.tryouts.create', $this->formData($request));
    }

    public function store(Request $request, TryoutService $service): RedirectResponse
    {
        $tutor = $request->user()->tutor;
        abort_unless($tutor, 403);

        $tryout = $service->validateAndSave($this->validatedData($request), null, $tutor);

        return redirect()->route('tutor.tryouts.show', $tryout)
            ->with('success', 'Try Out berhasil ditambahkan sebagai draft.');
    }

    public function show(Request $request, Tryout $tryout): View
    {
        $tryout = $this->ownedTryout($request, $tryout);
        $tryout->load(['subject', 'classRooms']);

        return view('tutor.tryouts.show', compact('tryout'));
    }

    public function edit(Request $request, Tryout $tryout): View
    {
        $tryout = $this->ownedTryout($request, $tryout);
        abort_unless($tryout->status === 'draft', 403);

        return view('tutor.tryouts.edit', array_merge(compact('tryout'), $this->formData($request)));
    }

    public function update(Request $request, Tryout $tryout, TryoutService $service): RedirectResponse
    {
        $tryout = $this->ownedTryout($request, $tryout);
        abort_unless($tryout->status === 'draft', 403);

        $service->validateAndSave($this->validatedData($request), $tryout, $request->user()->tutor);

        return redirect()->route('tutor.tryouts.show', $tryout)
            ->with('success', 'Try Out berhasil diperbarui.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'class_room_ids' => ['required', 'array', 'min:1'],
            'class_room_ids.*' => ['required', 'integer', 'distinct', Rule::exists('class_rooms', 'id')],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
        ]);

        $data['status'] = 'draft';

        return $data;
    }

    private function ownedTryout(Request $request, Tryout $tryout): Tryout
    {
        $tutor = $request->user()->tutor;
        abort_unless($tutor, 403);

        return Tryout::query()
            ->whereKey($tryout->id)
            ->where('tutor_id', $tutor->id)
            ->firstOrFail();
    }

    private function formData(Request $request): array
    {
        $tutor = $request->user()->tutor;
        abort_unless($tutor, 403);

        $subjects = $tutor->subjects()->where('status', 'active')->orderBy('name')->get(['subjects.id', 'code', 'name']);
        $classRoomIds = $tutor->learningSchedules()->where('status', 'active')->pluck('class_room_id')->unique();

        return [
            'subjects' => $subjects,
            'classRooms' => ClassRoom::query()->where('status', 'active')->whereIn('id', $classRoomIds)->orderBy('name')->get(['id', 'code', 'name']),
        ];
    }
}
