@extends('layouts.admin')
@section('title', 'Manajemen Orang Tua')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">DATA MASTER</p>
            <h1>Manajemen Orang Tua</h1>
            <p>Kelola akun dan profil orang tua siswa.</p>
        </div>
        <a class="button button-primary" href="{{ route('admin.parents.create') }}">+ Tambah Orang Tua</a>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="panel">
        <form class="filter-bar" method="GET" action="{{ route('admin.parents.index') }}">
            <input name="search" type="search" value="{{ request('search') }}"
                placeholder="Cari nama, email, atau nomor telepon">
            <select name="status">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>
                        {{ $status === 'active' ? 'Aktif' : 'Nonaktif' }}</option>
                @endforeach
            </select>
            <button class="button button-primary" type="submit">Filter</button>
            @if (request()->filled('search') || request()->filled('status'))
                <a class="button button-secondary" href="{{ route('admin.parents.index') }}">Reset</a>
            @endif
        </form>

        @if ($guardians->count())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Orang Tua</th>
                            <th>Email</th>
                            <th>Nomor Telepon</th>
                            <th>Jumlah Anak Terdaftar</th>
                            <th>Status Akun</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($guardians as $guardian)
                            <tr>
                                <td>{{ $guardians->firstItem() + $loop->index }}</td>
                                <td><strong>{{ $guardian->user->name }}</strong></td>
                                <td>{{ $guardian->user->email }}</td>
                                <td>{{ $guardian->phone ?: '—' }}</td>
                                <td>{{ $guardian->students_count }}</td>
                                <td><span
                                        class="badge {{ $guardian->user->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $guardian->user->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.parents.show', $guardian) }}">Detail</a>
                                        <a href="{{ route('admin.parents.edit', $guardian) }}">Edit</a>
                                        @if ($guardian->user->status === 'active')
                                            <form method="POST" action="{{ route('admin.parents.destroy', $guardian) }}"
                                                onsubmit="return confirm('Nonaktifkan akun orang tua {{ $guardian->user->name }}? Akun tidak dapat login, tetapi profil dan relasi anak tetap disimpan.');">
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
            <p class="module-note">Jumlah anak akan tersedia setelah modul Manajemen Siswa dan relasi siswa–orang tua
                diimplementasikan.</p>
            <div class="pagination-wrap">{{ $guardians->links() }}</div>
        @else
            <div class="empty-state">
                <span class="empty-icon">♙</span>
                <strong>Belum ada data orang tua</strong>
                <p>Tambahkan akun orang tua untuk mulai mengelola wali siswa.</p>
                <a class="button button-primary" href="{{ route('admin.parents.create') }}">+ Tambah Orang Tua</a>
            </div>
        @endif
    </section>
@endsection
