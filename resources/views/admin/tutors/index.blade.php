@extends('layouts.admin')
@section('title', 'Manajemen Tutor')
@section('content')
<section class="page-heading">
    <div>
        <p class="eyebrow">DATA MASTER</p>
        <h1>Manajemen Tutor</h1>
        <p>Kelola akun, profil, dan mata pelajaran yang dikuasai tutor.</p>
    </div>
    <a class="button button-primary" href="{{ route('admin.tutors.create') }}">+ Tambah Tutor</a>
</section>

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif

<section class="panel">
    <form class="filter-bar" method="GET" action="{{ route('admin.tutors.index') }}">
        <input name="search" type="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau nomor telepon">
        <select name="status">
            <option value="">Semua status</option>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status === 'active' ? 'Aktif' : 'Nonaktif' }}</option>
            @endforeach
        </select>
        <button class="button button-primary" type="submit">Filter</button>
        @if (request()->filled('search') || request()->filled('status'))
            <a class="button button-secondary" href="{{ route('admin.tutors.index') }}">Reset</a>
        @endif
    </form>

    @if ($tutors->count())
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Tutor</th>
                        <th>Email</th>
                        <th>Nomor Telepon</th>
                        <th>Jenis Kelamin</th>
                        <th>Mata Pelajaran Dikuasai</th>
                        <th>Status Akun</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tutors as $tutor)
                        <tr>
                            <td>{{ $tutors->firstItem() + $loop->index }}</td>
                            <td><strong>{{ $tutor->user->name }}</strong></td>
                            <td>{{ $tutor->user->email }}</td>
                            <td>{{ $tutor->phone ?: '—' }}</td>
                            <td>{{ $tutor->gender === 'male' ? 'Laki-laki' : ($tutor->gender === 'female' ? 'Perempuan' : '—') }}</td>
                            <td>{{ $tutor->subjects->pluck('name')->implode(', ') ?: '—' }}</td>
                            <td><span class="badge {{ $tutor->user->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $tutor->user->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.tutors.show', $tutor) }}">Detail</a>
                                    <a href="{{ route('admin.tutors.edit', $tutor) }}">Edit</a>
                                    @if ($tutor->user->status === 'active')
                                        <form method="POST" action="{{ route('admin.tutors.destroy', $tutor) }}" onsubmit="return confirm('Nonaktifkan akun tutor {{ $tutor->user->name }}? Tutor tidak dapat login, tetapi data dan riwayatnya tetap disimpan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">Nonaktifkan</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">{{ $tutors->links() }}</div>
    @else
        <div class="empty-state">
            <span class="empty-icon">♧</span>
            <strong>Belum ada data tutor</strong>
            <p>Tambahkan akun tutor untuk mulai mengelola pengajar bimbingan belajar.</p>
            <a class="button button-primary" href="{{ route('admin.tutors.create') }}">+ Tambah Tutor</a>
        </div>
    @endif
</section>
@endsection