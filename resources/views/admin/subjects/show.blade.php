@extends('layouts.admin')
@section('title', 'Detail Mata Pelajaran')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">DATA MASTER / MATA PELAJARAN</p>
            <h1>{{ $subject->name }}</h1>
            <p>Detail informasi mata pelajaran {{ $subject->code }}.</p>
        </div>
        <div class="heading-actions">
            <a class="button button-secondary" href="{{ route('admin.subjects.index') }}">Kembali</a>
            <a class="button button-primary" href="{{ route('admin.subjects.edit', $subject) }}">Edit Mata Pelajaran</a>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="detail-grid">
        <article class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Informasi Mata Pelajaran</h2>
                    <p>Data utama mata pelajaran.</p>
                </div>
                <span class="badge {{ $subject->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                    {{ $subject->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <dl class="detail-list">
                <div>
                    <dt>Kode Mata Pelajaran</dt>
                    <dd>{{ $subject->code }}</dd>
                </div>
                <div>
                    <dt>Nama Mata Pelajaran</dt>
                    <dd>{{ $subject->name }}</dd>
                </div>
                <div>
                    <dt>Jenjang Pendidikan</dt>
                    <dd>{{ $subject->education_level }}</dd>
                </div>
                <div>
                    <dt>Status</dt>
                    <dd>{{ $subject->status === 'active' ? 'Aktif' : 'Nonaktif' }}</dd>
                </div>
                <div class="detail-full">
                    <dt>Deskripsi</dt>
                    <dd>{{ $subject->description ?: 'Tidak ada deskripsi.' }}</dd>
                </div>
            </dl>
        </article>

        <article class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Penggunaan Akademik</h2>
                    <p>Kelas dan data akademik yang terkait.</p>
                </div>
            </div>
            <div class="empty-state compact">
                <span class="empty-icon">⌁</span>
                <strong>Relasi belum tersedia</strong>
                <p>Relasi kelas, try out, dan nilai untuk mata pelajaran belum tersedia pada skema aplikasi saat ini.</p>
            </div>
        </article>
    </section>
@endsection
