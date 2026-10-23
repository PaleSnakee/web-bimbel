<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Student::query()
            ->with(['user', 'classRoom', 'guardians.user'])
            ->whereHas('user', fn ($q) => $q->where('role', 'student'));

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('education_level')) {
            $query->where('education_level', $request->input('education_level'));
        }

        if ($request->filled('class_room_id')) {
            $query->where('class_room_id', $request->input('class_room_id'));
        }

        if ($request->filled('status')) {
            $query->whereHas('user', fn ($q) => $q->where('status', $request->input('status')));
        }

        $students = $query->latest()->paginate(10);
        $students->appends($request->query());

        return view('admin.students.index', [
            'students' => $students,
            'educationLevels' => ClassRoom::EDUCATION_LEVELS,
            'classRooms' => ClassRoom::where('status', 'active')->orderBy('name')->get(['id', 'name', 'code']),
            'statuses' => ['active', 'inactive'],
        ]);
    }

    public function create(): View
    {
        return view('admin.students.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $this->validateClassAssignment($data['class_room_id'] ?? null, $data['education_level']);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'student',
                'status' => $data['status'],
            ]);

            $student = $user->student()->create($this->studentData($data));
            $student->guardians()->sync($data['guardian_ids'] ?? []);
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'Akun siswa berhasil ditambahkan.');
    }

    public function show(Student $student): View
    {
        $student->load(['user', 'classRoom', 'guardians.user']);
        abort_unless($student->user->role === 'student', 404);

        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $student->load(['user', 'classRoom', 'guardians']);
        abort_unless($student->user->role === 'student', 404);

        return view('admin.students.edit', array_merge(compact('student'), $this->formData()));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $student->load('user');
        abort_unless($student->user->role === 'student', 404);

        $data = $this->validatedData($request, $student);

        if ($data['class_room_id'] ?? null) {
            $classChanged = (int) $data['class_room_id'] !== (int) $student->class_room_id;
            $educationLevelChanged = $data['education_level'] !== $student->education_level;

            if ($classChanged || $educationLevelChanged) {
                $this->validateClassAssignment($data['class_room_id'], $data['education_level'], $student);
            }
        }

        DB::transaction(function () use ($data, $student) {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => $data['status'],
            ];

            if (! empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            $student->user->update($userData);
            $student->update($this->studentData($data));
            $student->guardians()->sync($data['guardian_ids'] ?? []);
        });

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->load('user');
        abort_unless($student->user->role === 'student', 404);

        $student->user->update(['status' => 'inactive']);

        return redirect()->route('admin.students.index')
            ->with('success', 'Akun siswa berhasil dinonaktifkan. Data dan riwayat siswa tetap disimpan.');
    }

    private function validateClassAssignment(?int $classRoomId, string $educationLevel, ?Student $student = null): void
    {
        if (! $classRoomId) {
            return;
        }

        $classRoom = ClassRoom::findOrFail($classRoomId);

        if ($classRoom->status !== 'active') {
            throw ValidationException::withMessages([
                'class_room_id' => 'Kelas yang dipilih tidak aktif.',
            ]);
        }

        if ($classRoom->education_level !== $educationLevel) {
            throw ValidationException::withMessages([
                'class_room_id' => 'Jenjang kelas harus sesuai dengan jenjang pendidikan siswa.',
            ]);
        }

        $enrolledStudents = $classRoom->students()
            ->when($student, fn ($query) => $query->whereKeyNot($student->getKey()))
            ->count();

        if ($enrolledStudents >= $classRoom->capacity) {
            throw ValidationException::withMessages([
                'class_room_id' => "Kelas {$classRoom->name} sudah penuh ({$classRoom->capacity} siswa). Pilih kelas lain atau tambah kapasitas kelas.",
            ]);
        }
    }

    private function validatedData(Request $request, ?Student $student = null): array
    {
        $creating = $student === null;

        $data = $request->validate([
            'nis' => ['required', 'string', 'max:50', Rule::unique('students', 'nis')->ignore($student)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student?->user_id)],
            'password' => $creating ? ['required', 'string', 'min:8', 'confirmed'] : ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'education_level' => ['required', Rule::in(ClassRoom::EDUCATION_LEVELS)],
            'gender' => ['nullable', Rule::in(Student::GENDERS)],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string'],
            'class_room_id' => ['nullable', 'integer', Rule::exists('class_rooms', 'id')],
            'guardian_ids' => ['nullable', 'array'],
            'guardian_ids.*' => ['integer', Rule::exists('parents', 'id')],
        ]);

        if (! empty($data['guardian_ids'])) {
            $validGuardianCount = Guardian::query()
                ->whereIn('id', $data['guardian_ids'])
                ->whereHas('user', fn ($query) => $query->where('role', 'parent'))
                ->count();

            if ($validGuardianCount !== count(array_unique($data['guardian_ids']))) {
                throw ValidationException::withMessages([
                    'guardian_ids' => 'Orang tua/wali yang dipilih tidak valid.',
                ]);
            }
        }

        return $data;
    }

    private function studentData(array $data): array
    {
        return [
            'nis' => $data['nis'],
            'education_level' => $data['education_level'],
            'class_room_id' => $data['class_room_id'] ?? null,
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'address' => $data['address'] ?? null,
        ];
    }

    private function formData(): array
    {
        return [
            'classRooms' => ClassRoom::where('status', 'active')
                ->withCount('students')
                ->orderBy('education_level')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'education_level', 'grade_level', 'capacity']),
            'guardians' => Guardian::with('user:id,name,email')
                ->whereHas('user', fn ($q) => $q->where('role', 'parent')->where('status', 'active'))
                ->orderBy('id')
                ->get(),
            'educationLevels' => ClassRoom::EDUCATION_LEVELS,
            'statuses' => ['active', 'inactive'],
            'genders' => Student::GENDERS,
        ];
    }
}