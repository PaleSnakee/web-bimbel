@extends('layouts.admin')
@section('title', 'Detail Kelas')
@section('content')
<section class="page-heading"><div><p class="eyebrow">DATA MASTER / MANAJEMEN KELAS</p><h1>{{ $classRoom->name }}</h1><p>Detail informasi kelas {{ $classRoom->code }}.</p></div><div class="heading-actions"><a class="button button-secondary" href="{{ route('admin.classes.index') }}">Kembali</a><a class="button button-primary" href="{{ route('admin.classes.edit', $classRoom) }}">Edit Kelas</a></div></section>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<section class="detail-grid">
    <article class="panel"><div class="panel-heading"><div><h2>Informasi Kelas</h2><p>Data utama kelas bimbingan belajar.</p></div><span class="badge {{ $classRoom->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $classRoom->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></div>
    <dl class="detail-list"><div><dt>Kode Kelas</dt><dd>{{ $classRoom->code }}</dd></div><div><dt>Nama Kelas</dt><dd>{{ $classRoom->name }}</dd></div><div><dt>Jenjang Pendidikan</dt><dd>{{ $classRoom->education_level }}</dd></div><div><dt>Tingkat/Kelas</dt><dd>{{ $classRoom->grade_level }}</dd></div><div><dt>Kapasitas Siswa</dt><dd>{{ $classRoom->capacity }} siswa</dd></div><div><dt>Siswa Terdaftar</dt><dd>{{ $enrolledStudentsCount }} siswa</dd></div><div class="detail-full"><dt>Deskripsi</dt><dd>{{ $classRoom->description ?: 'Tidak ada deskripsi.' }}</dd></div></dl></article>
    <article class="panel"><div class="panel-heading"><div><h2>Penugasan Akademik</h2><p>Siswa dan tutor yang terkait dengan kelas.</p></div></div><div class="empty-state compact"><span class="empty-icon">⌁</span><strong>Relasi belum tersedia</strong><p>Model dan relasi siswa/tutor belum tersedia pada skema saat ini, sehingga tidak ada data yang ditampilkan.</p></div></article>
</section>
@endsection
