<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassRoomController extends Controller
{
    public function index(Request $request): View
    {
        $query = ClassRoom::query();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('education_level')) {
            $query->where('education_level', $request->input('education_level'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $classRooms = $query->latest()->paginate(10);
        $classRooms->appends($request->query());

        return view('admin.classes.index', [
            'classRooms' => $classRooms,
            'educationLevels' => ClassRoom::EDUCATION_LEVELS,
            'statuses' => ClassRoom::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('admin.classes.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        ClassRoom::create($this->validatedData($request));

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function show(ClassRoom $classRoom): View
    {
        return view('admin.classes.show', [
            'classRoom' => $classRoom,
            'enrolledStudentsCount' => $classRoom->students()->count(),
        ]);
    }

    public function edit(ClassRoom $classRoom): View
    {
        return view('admin.classes.edit', array_merge([
            'classRoom' => $classRoom,
        ], $this->formData()));
    }

    public function update(Request $request, ClassRoom $classRoom): RedirectResponse
    {
        $classRoom->update($this->validatedData($request, $classRoom));

        return redirect()->route('admin.classes.show', $classRoom)
            ->with('success', 'Informasi kelas berhasil diperbarui.');
    }

    public function destroy(ClassRoom $classRoom): RedirectResponse
    {
        // Relasi siswa, tutor, absensi, nilai, dan riwayat akademik belum ada
        // pada skema aplikasi. Saat relasi tersedia, pemeriksaan penggunaan kelas
        // wajib ditambahkan di sini sebelum penghapusan diizinkan.
        $classRoom->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }

    private function validatedData(Request $request, ?ClassRoom $classRoom = null): array
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('class_rooms', 'code')->ignore($classRoom),
            ],
            'name' => ['required', 'string', 'max:150'],
            'education_level' => ['required', Rule::in(ClassRoom::EDUCATION_LEVELS)],
            'grade_level' => ['required', 'string', 'max:20'],
            'capacity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(ClassRoom::STATUSES)],
        ]);

        if (! in_array($data['grade_level'], ClassRoom::gradeOptions($data['education_level']), true)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'grade_level' => 'Tingkat kelas tidak sesuai dengan jenjang pendidikan yang dipilih.',
            ]);
        }

        return $data;
    }

    private function formData(): array
    {
        return [
            'educationLevels' => ClassRoom::EDUCATION_LEVELS,
            'statuses' => ClassRoom::STATUSES,
            'gradeOptions' => [
                'PAUD/TK' => ClassRoom::gradeOptions('PAUD/TK'),
                'SD' => ClassRoom::gradeOptions('SD'),
                'SMP' => ClassRoom::gradeOptions('SMP'),
            ],
        ];
    }
}
