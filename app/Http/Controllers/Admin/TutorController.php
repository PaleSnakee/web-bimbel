<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TutorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Tutor::query()
            ->with(['user', 'subjects:id,name'])
            ->whereHas('user', fn ($userQuery) => $userQuery->where('role', 'tutor'));

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($tutorQuery) use ($search) {
                $tutorQuery->where('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->whereHas('user', fn ($userQuery) => $userQuery->where('status', $request->input('status')));
        }

        $tutors = $query->latest()->paginate(10);
        $tutors->appends($request->query());

        return view('admin.tutors.index', [
            'tutors' => $tutors,
            'statuses' => ['active', 'inactive'],
        ]);
    }

    public function create(): View
    {
        return view('admin.tutors.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'tutor',
                'status' => $data['status'],
            ]);

            $tutor = $user->tutor()->create($this->profileData($data));
            $tutor->subjects()->sync($data['subject_ids'] ?? []);
        });

        return redirect()->route('admin.tutors.index')
            ->with('success', 'Akun tutor berhasil ditambahkan.');
    }

    public function show(Tutor $tutor): View
    {
        $tutor->load(['user', 'subjects']);

        abort_unless($tutor->user->role === 'tutor', 404);

        return view('admin.tutors.show', compact('tutor'));
    }

    public function edit(Tutor $tutor): View
    {
        $tutor->load(['user', 'subjects']);

        abort_unless($tutor->user->role === 'tutor', 404);

        return view('admin.tutors.edit', array_merge(compact('tutor'), $this->formData()));
    }

    public function update(Request $request, Tutor $tutor): RedirectResponse
    {
        $tutor->load('user');
        abort_unless($tutor->user->role === 'tutor', 404);

        $data = $this->validatedData($request, $tutor->user, false);

        DB::transaction(function () use ($data, $tutor) {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => $data['status'],
            ];

            if (! empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            $tutor->user->update($userData);
            $tutor->update($this->profileData($data));
            $tutor->subjects()->sync($data['subject_ids'] ?? []);
        });

        return redirect()->route('admin.tutors.show', $tutor)
            ->with('success', 'Informasi tutor berhasil diperbarui.');
    }

    public function destroy(Tutor $tutor): RedirectResponse
    {
        $tutor->load('user');
        abort_unless($tutor->user->role === 'tutor', 404);

        $tutor->user->update(['status' => 'inactive']);

        return redirect()->route('admin.tutors.index')
            ->with('success', 'Akun tutor berhasil dinonaktifkan. Riwayat dan profil tutor tetap disimpan.');
    }

    private function validatedData(Request $request, ?User $user = null, bool $creating = true): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => $creating
                ? ['required', 'string', 'min:8', 'confirmed']
                : ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', Rule::in(Tutor::GENDERS)],
            'address' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['integer', Rule::exists('subjects', 'id')],
        ]);
    }

    private function profileData(array $data): array
    {
        return [
            'phone' => $data['phone'] ?? null,
            'gender' => $data['gender'] ?? null,
            'address' => $data['address'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
        ];
    }

    private function formData(): array
    {
        return [
            'subjects' => Subject::query()
                ->where('status', 'active')
                ->orderBy('education_level')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'education_level']),
            'statuses' => ['active', 'inactive'],
            'genders' => Tutor::GENDERS,
        ];
    }
}