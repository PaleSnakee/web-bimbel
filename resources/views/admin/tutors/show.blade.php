@extends('layouts.admin')
@section('title', 'Detail Tutor')
@section('content')
<section class="page-heading">
    <div>
        <p class="eyebrow">DATA MASTER / MANAJEMEN TUTOR</p>
        <h1>{{ $tutor->user->name }}</h1>
        <p>Detail akun dan profil tutor.</p>
    </div>
    <div class="heading-actions">
        <a class="button button-secondary" href="{{ route('admin.tutors.index') }}">Kembali</a>
        <a class="button button-primary" href="{{ route('admin.tutors.edit', $tutor) }}">Edit Tutor</a>
    </div>
</section>

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<section class="detail-grid">
    <article class="panel">
        <div class="panel-heading">
            <div>
                <h2>Informasi Tutor</h2>
                <p>Data akun dan profil tutor.</p>
            </div>
            <span class="badge {{ $tutor->user->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $tutor->user->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span>
        </div>
        <dl class="detail-list">
            <div><dt>Nama Lengkap</dt><dd>{{ $tutor->user->name }}</dd></div>
            <div><dt>Email</dt><dd>{{ $tutor->user->email }}</dd></div>
            <div><dt>Nomor Telepon</dt><dd>{{ $tutor->phone ?: '—' }}</dd></div>
            <div><dt>Jenis Kelamin</dt><dd>{{ $tutor->gender === 'male' ? 'Laki-laki' : ($tutor->gender === 'female' ? 'Perempuan' : '—') }}</dd></div>
            <div><dt>Tanggal Lahir</dt><dd>{{ $tutor->date_of_birth?->translatedFormat('d F Y') ?: '—' }}</dd></div>
            <div><dt>Status Akun</dt><dd>{{ $tutor->user->status === 'active' ? 'Aktif' : 'Nonaktif' }}</dd></div>
            <div class="detail-full"><dt>Alamat</dt><dd>{{ $tutor->address ?: '—' }}</dd></div>
            <div class="detail-full"><dt>Mata Pelajaran Dikuasai</dt><dd>{{ $tutor->subjects->pluck('name')->implode(', ') ?: 'Belum ada mata pelajaran yang ditetapkan.' }}</dd></div>
        </dl>
    </article>

    <article class="panel">
        <div class="panel-heading">
            <div>
                <h2>Kelas yang Diampu</h2>
                <p>Penugasan kelas tutor.</p>
            </div>
        </div>
        <div class="empty-state compact">
            <span class="empty-icon">⌁</span>
            <strong>Relasi kelas belum tersedia</strong>
            <p>Skema penugasan tutor ke kelas belum ada pada aplikasi saat ini, sehingga tidak ada data kelas yang ditampilkan.</p>
        </div>
    </article>
</section>
@endsection