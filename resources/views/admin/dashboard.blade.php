@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">OVERVIEW</p>
            <h1>Dashboard</h1>
            <p>Ringkasan data bimbingan belajar yang terdaftar di sistem.</p>
        </div>
        <span class="today">{{ now()->translatedFormat('l, d F Y') }}</span>
    </section>

    <section class="stat-grid">
        <article class="stat-card"><span class="stat-icon blue">♙</span>
            <div>
                <p>Total Siswa</p>
                <strong>{{ number_format($statistics['students']) }}</strong><small>{{ number_format($statistics['active_students']) }}
                    akun aktif</small>
            </div>
        </article>
        <article class="stat-card"><span class="stat-icon green">♧</span>
            <div>
                <p>Total Tutor</p>
                <strong>{{ number_format($statistics['tutors']) }}</strong><small>{{ number_format($statistics['active_tutors']) }}
                    akun aktif</small>
            </div>
        </article>
        <article class="stat-card"><span class="stat-icon orange">▥</span>
            <div>
                <p>Total Kelas</p>
                <strong>{{ number_format($statistics['classes']) }}</strong><small>{{ number_format($statistics['active_classes']) }}
                    kelas aktif</small>
            </div>
        </article>
        <article class="stat-card"><span class="stat-icon purple">♟</span>
            <div>
                <p>Total Orang Tua</p>
                <strong>{{ number_format($statistics['parents']) }}</strong><small>{{ number_format($statistics['active_parents']) }}
                    akun aktif</small>
            </div>
        </article>
    </section>

    <section class="dashboard-grid">
        <article class="panel activity-panel">
            <div class="panel-heading">
                <div>
                    <h2>Aktivitas Terbaru</h2>
                    <p>Akun siswa, tutor, dan orang tua yang baru ditambahkan.</p>
                </div>
            </div>
            @if ($recentUsers->isNotEmpty())
                <div class="attendance-list">
                    @foreach ($recentUsers as $user)
                        <div>
                            <span
                                class="legend {{ $user->role === 'student' ? 'blue' : ($user->role === 'tutor' ? 'success' : 'warning') }}"></span>
                            <span><strong>{{ $user->name }}</strong> ditambahkan sebagai
                                {{ $user->role === 'student' ? 'siswa' : ($user->role === 'tutor' ? 'tutor' : 'orang tua') }}</span>
                            <small>{{ $user->created_at->translatedFormat('d M Y') }}</small>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state"><span class="empty-icon">⌁</span><strong>Belum ada aktivitas</strong>
                    <p>Aktivitas akun akan muncul saat data siswa, tutor, atau orang tua ditambahkan.</p>
                </div>
            @endif
        </article>
        <article class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Akses Cepat</h2>
                    <p>Kelola data bimbel lebih cepat.</p>
                </div>
            </div>
            <div class="quick-actions">
                <a class="button button-secondary" href="{{ route('admin.students.create') }}">+ Tambah Siswa</a>
                <a class="button button-secondary" href="{{ route('admin.tutors.create') }}">+ Tambah Tutor</a>
                <a class="button button-secondary" href="{{ route('admin.classes.create') }}">+ Tambah Kelas</a>
                <a class="button button-secondary" href="{{ route('admin.parents.create') }}">+ Tambah Orang Tua</a>
            </div>
        </article>
    </section>

    <section class="summary-grid">
        <article class="panel summary-card">
            <div class="panel-heading">
                <div>
                    <h2>Ringkasan Absensi</h2>
                    <p>Hari ini</p>
                </div><span class="panel-tag">Belum tersedia</span>
            </div>
            <div class="empty-state compact"><span class="empty-icon">⌁</span><strong>Modul absensi belum tersedia</strong>
                <p>Data absensi akan ditampilkan setelah modul Absensi dibuat.</p>
            </div>
        </article>
        <article class="panel summary-card">
            <div class="panel-heading">
                <div>
                    <h2>Ringkasan Try Out</h2>
                    <p>Status pelaksanaan</p>
                </div><span class="panel-tag">Belum tersedia</span>
            </div>
            <div class="empty-state compact"><span class="empty-icon">◎</span><strong>Belum ada data try out</strong>
                <p>Tambahkan modul Try Out untuk melihat ringkasannya.</p>
            </div>
        </article>
    </section>
@endsection
