<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Subject::query();

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

        $subjects = $query->latest()->paginate(10);
        $subjects->appends($request->query());

        return view('admin.subjects.index', [
            'subjects' => $subjects,
            'educationLevels' => Subject::EDUCATION_LEVELS,
            'statuses' => Subject::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('admin.subjects.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        Subject::create($this->validatedData($request));

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function show(Subject $subject): View
    {
        return view('admin.subjects.show', [
            'subject' => $subject,
        ]);
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.edit', array_merge([
            'subject' => $subject,
        ], $this->formData()));
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $subject->update($this->validatedData($request, $subject));

        return redirect()->route('admin.subjects.show', $subject)
            ->with('success', 'Informasi mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        // Belum ada tabel try out, nilai, maupun relasi kelas-mata pelajaran.
        // Saat relasi akademik tersedia, tolak penghapusan jika subject masih
        // digunakan dan arahkan admin untuk mengubah status menjadi nonaktif.
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Subject $subject = null): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')->ignore($subject),
            ],
            'name' => ['required', 'string', 'max:150'],
            'education_level' => ['required', Rule::in(Subject::EDUCATION_LEVELS)],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(Subject::STATUSES)],
        ]);
    }

    private function formData(): array
    {
        return [
            'educationLevels' => Subject::EDUCATION_LEVELS,
            'statuses' => Subject::STATUSES,
        ];
    }
}
