@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">OVERVIEW</p>
            <h1>Dashboard</h1>
            <p>Ringkasan aktivitas dan informasi bimbingan belajar.</p>
        </div><span class="today">{{ now()->translatedFormat('l, d F Y') }}</span>
    </section>
    <section class="stat-grid">
        <article class="stat-card"><span class="stat-icon blue">♙</span>
            <div>
                <p>Total Siswa</p><strong>{{ number_format($statistics['students']) }}</strong><small>Terdaftar pada
                    sistem</small>
            </div>
        </article>
        <article class="stat-card"><span class="stat-icon green">♧</span>
            <div>
                <p>Total Tutor</p><strong>{{ number_format($statistics['tutors']) }}</strong><small>Pengajar
                    terdaftar</small>
            </div>
        </article>
        <article class="stat-card"><span class="stat-icon orange">▥</span>
            <div>
                <p>Total Kelas</p><strong>—</strong><small>Modul kelas belum tersedia</small>
            </div>
        </article>
        <article class="stat-card"><span class="stat-icon purple">◷</span>
            <div>
                <p>Absensi Hari Ini</p><strong>—</strong><small>Modul absensi belum tersedia</small>
            </div>
        </article>
    </section>
    <section class="dashboard-grid">
        <article class="panel activity-panel">
            <div class="panel-heading">
                <div>
                    <h2>Aktivitas Terbaru</h2>
                    <p>Pembaruan terbaru pada bimbel</p>
                </div>
            </div>
            <div class="empty-state"><span class="empty-icon">⌁</span><strong>Belum ada aktivitas untuk ditampilkan</strong>
                <p>Aktivitas siswa, tutor, dan kelas akan muncul di sini saat modul terkait tersedia.</p>
            </div>
        </article>
        <article class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Akses Cepat</h2>
                    <p>Kelola data bimbel lebih cepat</p>
                </div>
            </div>
            <div class="quick-actions"><button disabled title="Halaman manajemen siswa belum tersedia">+ Tambah
                    Siswa</button><button disabled title="Halaman manajemen tutor belum tersedia">+ Tambah
                    Tutor</button><button disabled title="Halaman manajemen kelas belum tersedia">+ Tambah
                    Kelas</button><button disabled title="Halaman try out belum tersedia">+ Buat Try Out</button></div>
            <p class="module-note">Tautan akan aktif setelah modul tujuan dibuat.</p>
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
            <div class="attendance-list">
                @foreach (['Hadir' => 'success', 'Izin' => 'warning', 'Sakit' => 'blue', 'Alpa' => 'danger'] as $label => $color)
                    <div><span class="legend {{ $color }}"></span>{{ $label }}<strong>—</strong></div>
                @endforeach
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
                <p>Tambahkan modul try out untuk melihat ringkasannya.</p>
            </div>
        </article>
    </section>
@endsection
