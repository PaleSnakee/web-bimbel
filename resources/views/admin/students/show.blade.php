@extends('layouts.admin')
@section('title', 'Detail Siswa')
@section('content')
<section class="page-heading">
    <div>
        <p class="eyebrow">DATA MASTER / MANAJEMEN SISWA</p>
        <h1>{{ $student->user->name }}</h1>
        <p>Detail akun, profil, kelas, dan orang tua/wali siswa.</p>
    </div>
    <div class="heading-actions">
        <a class="button button-secondary" href="{{ route('admin.students.index') }}">Kembali</a>
        <a class="button button-primary" href="{{ route('admin.students.edit', $student) }}">Edit Siswa</a>
    </div>
</section>

@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<section class="detail-grid">
    <article class="panel">
        <div class="panel-heading">
            <div><h2>Informasi Siswa</h2><p>Identitas, akun, dan penempatan kelas saat ini.</p></div>
            <span class="badge {{ $student->user->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $student->user->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span>
        </div>
        <dl class="detail-list">
            <div><dt>NIS</dt><dd>{{ $student->nis }}</dd></div>
            <div><dt>Nama Lengkap</dt><dd>{{ $student->user->name }}</dd></div>
            <div><dt>Email</dt><dd>{{ $student->user->email }}</dd></div>
            <div><dt>Status</dt><dd>{{ $student->user->status === 'active' ? 'Aktif' : 'Nonaktif' }}</dd></div>
            <div><dt>Jenjang Pendidikan</dt><dd>{{ $student->education_level }}</dd></div>
            <div><dt>Kelas</dt><dd>{{ $student->classRoom ? $student->classRoom->name . ' (' . $student->classRoom->code . ')' : 'Belum ditetapkan' }}</dd></div>
            <div><dt>Jenis Kelamin</dt><dd>{{ $student->gender === 'male' ? 'Laki-laki' : ($student->gender === 'female' ? 'Perempuan' : '—') }}</dd></div>
            <div><dt>Tanggal Lahir</dt><dd>{{ $student->date_of_birth?->translatedFormat('d F Y') ?: '—' }}</dd></div>
            <div class="detail-full"><dt>Alamat</dt><dd>{{ $student->address ?: '—' }}</dd></div>
        </dl>
    </article>

    <article class="panel">
        <div class="panel-heading"><div><h2>Orang Tua / Wali</h2><p>Akun wali yang terhubung dengan siswa.</p></div></div>
        @if ($student->guardians->isNotEmpty())
            <dl class="detail-list">
                @foreach ($student->guardians as $guardian)
                    <div class="detail-full"><dt>{{ $guardian->user->name }}</dt><dd>{{ $guardian->user->email }}{{ $guardian->phone ? ' · ' . $guardian->phone : '' }}</dd></div>
                @endforeach
            </dl>
        @else
            <div class="empty-state compact"><span class="empty-icon">⌁</span><strong>Belum ada orang tua/wali terhubung</strong><p>Hubungkan akun orang tua melalui halaman edit siswa.</p></div>
        @endif
    </article>
</section>

<section class="detail-grid" style="margin-top:18px">
    <article class="panel"><div class="panel-heading"><div><h2>Absensi</h2><p>Ringkasan kehadiran siswa.</p></div></div><div class="empty-state compact"><span class="empty-icon">⌁</span><strong>Modul absensi belum tersedia</strong><p>Belum ada data absensi yang dapat ditampilkan.</p></div></article>
    <article class="panel"><div class="panel-heading"><div><h2>Nilai dan Try Out</h2><p>Ringkasan hasil akademik siswa.</p></div></div><div class="empty-state compact"><span class="empty-icon">⌁</span><strong>Modul nilai belum tersedia</strong><p>Belum ada data nilai atau try out yang dapat ditampilkan.</p></div></article>
</section>
@endsection