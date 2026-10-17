@extends('layouts.admin')
@section('title', 'Manajemen Mata Pelajaran')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">DATA MASTER</p>
            <h1>Manajemen Mata Pelajaran</h1>
            <p>Kelola data mata pelajaran El Renaissance.</p>
        </div>
        <a class="button button-primary" href="{{ route('admin.subjects.create') }}">+ Tambah Mata Pelajaran</a>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="panel">
        <form class="filter-bar" method="GET" action="{{ route('admin.subjects.index') }}">
            <input name="search" type="search" value="{{ request('search') }}"
                placeholder="Cari nama atau kode mata pelajaran">
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
                <a class="button button-secondary" href="{{ route('admin.subjects.index') }}">Reset</a>
            @endif
        </form>

        @if ($subjects->count())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Kode Mata Pelajaran</th>
                            <th>Nama Mata Pelajaran</th>
                            <th>Jenjang Pendidikan</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subjects as $subject)
                            <tr>
                                <td>{{ $subjects->firstItem() + $loop->index }}</td>
                                <td><strong>{{ $subject->code }}</strong></td>
                                <td>{{ $subject->name }}</td>
                                <td>{{ $subject->education_level }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($subject->description ?: '—', 70) }}</td>
                                <td>
                                    <span
                                        class="badge {{ $subject->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                        {{ $subject->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.subjects.show', $subject) }}">Detail</a>
                                        <a href="{{ route('admin.subjects.edit', $subject) }}">Edit</a>
                                        <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}"
                                            onsubmit="return confirm('Hapus mata pelajaran {{ $subject->code }}? Tindakan ini tidak dapat dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $subjects->links() }}</div>
        @else
            <div class="empty-state">
                <span class="empty-icon">▤</span>
                <strong>Belum ada mata pelajaran</strong>
                <p>Tambahkan mata pelajaran pertama untuk mulai mengelola data akademik.</p>
                <a class="button button-primary" href="{{ route('admin.subjects.create') }}">+ Tambah Mata Pelajaran</a>
            </div>
        @endif
    </section>
@endsection
