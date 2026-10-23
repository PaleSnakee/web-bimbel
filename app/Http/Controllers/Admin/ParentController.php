<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ParentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Guardian::query()
            ->with('user')
            ->withCount('students')
            ->whereHas('user', fn ($userQuery) => $userQuery->where('role', 'parent'));

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($guardianQuery) use ($search) {
                $guardianQuery->where('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->whereHas('user', fn ($userQuery) => $userQuery
                ->where('status', $request->input('status')));
        }

        $guardians = $query->latest()->paginate(10);
        $guardians->appends($request->query());

        return view('admin.parents.index', [
            'guardians' => $guardians,
            'statuses' => ['active', 'inactive'],
        ]);
    }

    public function create(): View
    {
        return view('admin.parents.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'parent',
                'status' => $data['status'],
            ]);

            $user->guardian()->create($this->profileData($data));
        });

        return redirect()->route('admin.parents.index')
            ->with('success', 'Akun orang tua berhasil ditambahkan.');
    }

    public function show(Guardian $parent): View
    {
        $parent->load([
            'user',
            'students.user',
            'students.classRoom',
        ]);

        abort_unless($parent->user->role === 'parent', 404);

        return view('admin.parents.show', ['guardian' => $parent]);
    }

    public function edit(Guardian $parent): View
    {
        $parent->load('user');
        abort_unless($parent->user->role === 'parent', 404);

        return view('admin.parents.edit', array_merge(['guardian' => $parent], $this->formData()));
    }

    public function update(Request $request, Guardian $parent): RedirectResponse
    {
        $parent->load('user');
        abort_unless($parent->user->role === 'parent', 404);

        $data = $this->validatedData($request, $parent->user, false);

        DB::transaction(function () use ($data, $parent) {
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => $data['status'],
            ];

            if (! empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            $parent->user->update($userData);
            $parent->update($this->profileData($data));
        });

        return redirect()->route('admin.parents.show', $parent)
            ->with('success', 'Informasi orang tua berhasil diperbarui.');
    }

    public function destroy(Guardian $parent): RedirectResponse
    {
        $parent->load('user');
        abort_unless($parent->user->role === 'parent', 404);

        $parent->user->update(['status' => 'inactive']);

        return redirect()->route('admin.parents.index')
            ->with('success', 'Akun orang tua berhasil dinonaktifkan. Profil dan relasi anak tetap disimpan.');
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
            'address' => ['nullable', 'string'],
        ]);
    }

    private function profileData(array $data): array
    {
        return [
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ];
    }

    private function formData(): array
    {
        return ['statuses' => ['active', 'inactive']];
    }
}
