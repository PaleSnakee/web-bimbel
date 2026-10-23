@extends('layouts.admin')
@section('title', 'Manajemen Kelas')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">DATA MASTER</p>
            <h1>Manajemen Kelas</h1>
            <p>Kelola data kelas bimbingan belajar El Renaissance.</p>
        </div>
        <a class="button button-primary" href="{{ route('admin.classes.create') }}">+ Tambah Kelas</a>
    </section>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    <section class="panel">
        <form class="filter-bar" method="GET" action="{{ route('admin.classes.index') }}">
            <input name="search" type="search" value="{{ request('search') }}" placeholder="Cari nama atau kode kelas">
            <select name="education_level">
                <option value="">Semua jenjang</option>
                @foreach ($educationLevels as $level)
                    <option value="{{ $level }}" @selected(request('education_level') === $level)>{{ $level }}</option>
                @endforeach
            </select>
            <select name="status">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>
                        {{ $status === 'active' ? 'Aktif' : 'Nonaktif' }}</option>
                @endforeach
            </select>
            <button class="button button-primary" type="submit">Filter</button>
            @if (request()->filled('search') || request()->filled('education_level') || request()->filled('status'))
                <a class="button button-secondary" href="{{ route('admin.classes.index') }}">Reset</a>
            @endif
        </form>
        @if ($classRooms->count())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Kode Kelas</th>
                            <th>Nama Kelas</th>
                            <th>Jenjang</th>
                            <th>Tingkat</th>
                            <th>Kapasitas</th>
                            <th>Siswa Terdaftar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($classRooms as $classRoom)
                            <tr>
                                <td>{{ $classRooms->firstItem() + $loop->index }}</td>
                                <td><strong>{{ $classRoom->code }}</strong></td>
                                <td>{{ $classRoom->name }}</td>
                                <td>{{ $classRoom->education_level }}</td>
                                <td>{{ $classRoom->grade_level }}</td>
                                <td>{{ $classRoom->capacity }}</td>
                                <td>{{ $classRoom->students->count() }}</td>
                                <td><span
                                        class="badge {{ $classRoom->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $classRoom->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span>
                                </td>
                                <td>
                                    <div class="table-actions"><a
                                            href="{{ route('admin.classes.show', $classRoom) }}">Detail</a><a
                                            href="{{ route('admin.classes.edit', $classRoom) }}">Edit</a>
                                        <form method="POST" action="{{ route('admin.classes.destroy', $classRoom) }}"
                                            onsubmit="return confirm('Hapus kelas {{ $classRoom->code }}? Tindakan ini tidak dapat dibatalkan.');">
                                            @csrf @method('DELETE')<button type="submit">Hapus</button></form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $classRooms->links() }}</div>
        @else
            <div class="empty-state"><span class="empty-icon">▥</span><strong>Belum ada data kelas</strong>
                <p>Tambahkan kelas pertama untuk mulai mengelola kegiatan bimbingan belajar.</p><a
                    class="button button-primary" href="{{ route('admin.classes.create') }}">+ Tambah Kelas</a>
            </div>
        @endif
    </section>
@endsection
