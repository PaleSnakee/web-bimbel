@extends('layouts.admin')
@section('title', 'Manajemen Siswa')
@section('content')
<section class="page-heading">
    <div>
        <p class="eyebrow">DATA MASTER</p>
        <h1>Manajemen Siswa</h1>
        <p>Kelola data siswa, kelas, dan orang tua/wali.</p>
    </div>
    <a class="button button-primary" href="{{ route('admin.students.create') }}">+ Tambah Siswa</a>
</section>

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

<section class="panel">
    <form class="filter-bar" method="GET" action="{{ route('admin.students.index') }}">
        <input name="search" type="search" value="{{ request('search') }}" placeholder="Cari nama, NIS, atau email">
        <select name="education_level">
            <option value="">Semua jenjang</option>
            @foreach ($educationLevels as $level)
                <option value="{{ $level }}" @selected(request('education_level') === $level)>{{ $level }}</option>
            @endforeach
        </select>
        <select name="class_room_id">
            <option value="">Semua kelas</option>
            @foreach ($classRooms as $classRoom)
                <option value="{{ $classRoom->id }}" @selected((string) request('class_room_id') === (string) $classRoom->id)>{{ $classRoom->name }} ({{ $classRoom->code }})</option>
            @endforeach
        </select>
        <select name="status">
            <option value="">Semua status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status === 'active' ? 'Aktif' : 'Nonaktif' }}</option>
            @endforeach
        </select>
        <button class="button button-primary" type="submit">Filter</button>
        @if (request()->filled('search') || request()->filled('education_level') || request()->filled('class_room_id') || request()->filled('status'))
            <a class="button button-secondary" href="{{ route('admin.students.index') }}">Reset</a>
        @endif
    </form>

    @if ($students->count())
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>No.</th><th>NIS</th><th>Nama Siswa</th><th>Jenjang</th><th>Kelas</th><th>Orang Tua / Wali</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td>{{ $students->firstItem() + $loop->index }}</td>
                            <td><strong>{{ $student->nis }}</strong></td>
                            <td>{{ $student->user->name }}</td>
                            <td>{{ $student->education_level }}</td>
                            <td>{{ $student->classRoom?->name ?: 'Belum ditetapkan' }}</td>
                            <td>{{ $student->guardians->pluck('user.name')->implode(', ') ?: 'Belum terhubung' }}</td>
                            <td><span class="badge {{ $student->user->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $student->user->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td><div class="table-actions">
                                <a href="{{ route('admin.students.show', $student) }}">Detail</a>
                                <a href="{{ route('admin.students.edit', $student) }}">Edit</a>
                                @if ($student->user->status === 'active')
                                    <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Nonaktifkan akun siswa {{ $student->user->name }}? Data, kelas, dan relasi wali tetap disimpan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Nonaktifkan</button>
                                    </form>
                                @endif
                            </div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $students->links() }}</div>
    @else
        <div class="empty-state">
            <span class="empty-icon">♙</span><strong>Belum ada data siswa</strong>
            <p>Tambahkan siswa untuk mengelola data akademik dan hubungan orang tua/wali.</p>
            <a class="button button-primary" href="{{ route('admin.students.create') }}">+ Tambah Siswa</a>
        </div>
    @endif
</section>
@endsection