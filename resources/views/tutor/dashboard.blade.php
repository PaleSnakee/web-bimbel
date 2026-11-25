@extends('layouts.tutor')

@section('title', 'Dashboard Tutor')
@section('page_title', 'Dashboard')

@section('content')
    {{-- 1. Welcome Section --}}
    <section class="page-heading">
        <div>
            <p class="eyebrow">DASHBOARD TUTOR</p>
            <h1>Selamat Datang, {{ auth()->user()->name }}!</h1>
            <p>
                Tutor
                @if ($subjects->isNotEmpty())
                    <strong>{{ $subjects->pluck('name')->implode(', ') }}</strong>
                @else
                    Bimbingan Belajar
                @endif
                · Pantau jadwal mengajar, aktivitas sesi pembelajaran, absensi siswa, dan Try Out Anda.
            </p>
        </div>
        <span class="today">{{ now()->translatedFormat('l, d F Y') }}</span>
    </section>

    {{-- 2. Statistik Mengajar --}}
    <section class="stat-grid" aria-label="Statistik Mengajar Tutor">
        <article class="stat-card">
            <span class="stat-icon blue">📅</span>
            <div>
                <p>Total Jadwal Mengajar</p>
                <strong>{{ number_format($statistics['active_schedules']) }}</strong>
                <small>Jadwal aktif rutin</small>
            </div>
        </article>

        <article class="stat-card">
            <span class="stat-icon green">⏱</span>
            <div>
                <p>Total Sesi Pembelajaran</p>
                <strong>{{ number_format($statistics['total_sessions']) }}</strong>
                <small>{{ number_format($completedSessionsCount) }} sesi telah selesai</small>
            </div>
        </article>

        <article class="stat-card">
            <span class="stat-icon purple">👥</span>
            <div>
                <p>Total Siswa yang Diajar</p>
                <strong>{{ number_format($statistics['students_taught']) }}</strong>
                <small>Siswa unik dari kelas aktif</small>
            </div>
        </article>

        <article class="stat-card">
            <span class="stat-icon orange">📋</span>
            <div>
                <p>Total Absensi Tercatat</p>
                <strong>{{ number_format($statistics['recorded_attendances']) }}</strong>
                <small>Presensi siswa direkam Anda</small>
            </div>
        </article>
    </section>

    {{-- 3. Jadwal Mengajar Terdekat --}}
    <section class="panel" id="jadwal-mengajar" style="margin-bottom: 24px;" aria-labelledby="heading-jadwal-mengajar">
        <div class="panel-heading" style="margin-bottom: 16px;">
            <div>
                <h2 id="heading-jadwal-mengajar">Jadwal Mengajar Aktif</h2>
                <p>Jadwal bimbingan rutin Anda yang aktif pada sistem.</p>
            </div>
        </div>

        @if ($activeSchedules->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Hari</th>
                            <th>Waktu Mengajar</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas & Jenjang</th>
                            <th>Status Jadwal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activeSchedules as $schedule)
                            <tr>
                                <td>
                                    <strong style="color: #111827; font-weight: 600;">
                                        {{ \App\Models\LearningSchedule::dayLabel($schedule->day_of_week) }}
                                    </strong>
                                </td>
                                <td>
                                    {{ $schedule->start_time ? $schedule->start_time->format('H:i') : '-' }} -
                                    {{ $schedule->end_time ? $schedule->end_time->format('H:i') : '-' }} WIB
                                </td>
                                <td>
                                    <strong style="color: #111827;">{{ $schedule->subject?->name }}</strong>
                                    <small style="display: block; color: var(--muted); font-size: 11px;">
                                        {{ $schedule->subject?->code }}
                                    </small>
                                </td>
                                <td>
                                    <div>{{ $schedule->classRoom?->name ?? '-' }}</div>
                                    <small style="color: var(--muted); font-size: 11px;">
                                        Jenjang {{ $schedule->classRoom?->education_level ?? '-' }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge {{ $schedule->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                        {{ $schedule->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <span class="empty-icon">📅</span>
                <strong>Belum ada jadwal mengajar aktif</strong>
                <p>Jadwal mengajar rutin yang ditugaskan kepada Anda akan ditampilkan di bagian ini.</p>
            </div>
        @endif
    </section>

    {{-- 4. Sesi Pembelajaran --}}
    <section class="panel" id="sesi-pembelajaran" style="margin-bottom: 24px;" aria-labelledby="heading-sesi-pembelajaran">
        <div class="panel-heading" style="margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h2 id="heading-sesi-pembelajaran">Sesi Pembelajaran Terdekat & Terbaru</h2>
                <p>Status sesi pembelajaran dan pencatatan presensi siswa.</p>
            </div>
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <span style="font-size: 11px; padding: 4px 8px; background: #eff8ff; color: #175cd3; border-radius: 6px; font-weight: 600;">
                    {{ $scheduledSessionsCount }} Terjadwal
                </span>
                <span style="font-size: 11px; padding: 4px 8px; background: #eaf9f1; color: #1b9560; border-radius: 6px; font-weight: 600;">
                    {{ $completedSessionsCount }} Selesai
                </span>
                @if ($cancelledSessionsCount > 0)
                    <span style="font-size: 11px; padding: 4px 8px; background: #fef3f2; color: #b42318; border-radius: 6px; font-weight: 600;">
                        {{ $cancelledSessionsCount }} Batal
                    </span>
                @endif
                <a class="button button-secondary" href="{{ route('tutor.attendances.index') }}" style="font-size: 12px; padding: 6px 12px;">
                    Daftar Sesi Absensi →
                </a>
            </div>
        </div>

        @if ($recentSessions->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal Sesi</th>
                            <th>Waktu & Topik</th>
                            <th>Mata Pelajaran & Kelas</th>
                            <th>Status Sesi</th>
                            <th>Presensi Terdata</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentSessions as $session)
                            @php
                                $sessionBadge = match ($session->status) {
                                    'completed' => 'badge-active',
                                    'scheduled' => 'badge-warning',
                                    'cancelled' => 'badge-inactive',
                                    default => 'badge-inactive',
                                };

                                $sessionStatusText = match ($session->status) {
                                    'completed' => 'Selesai',
                                    'scheduled' => 'Terjadwal',
                                    'cancelled' => 'Dibatalkan',
                                    default => ucfirst($session->status),
                                };
                            @endphp
                            <tr>
                                <td>
                                    <strong style="color: #111827;">
                                        {{ $session->session_date ? $session->session_date->translatedFormat('d M Y') : '-' }}
                                    </strong>
                                </td>
                                <td>
                                    <div>
                                        {{ $session->start_time ? $session->start_time->format('H:i') : '-' }} -
                                        {{ $session->end_time ? $session->end_time->format('H:i') : '-' }} WIB
                                    </div>
                                    @if ($session->topic)
                                        <small style="color: var(--muted); font-size: 11px; display: block; margin-top: 2px;">
                                            Topik: {{ $session->topic }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $session->learningSchedule?->subject?->name ?? '-' }}</strong>
                                    <small style="display: block; color: var(--muted); font-size: 11px;">
                                        {{ $session->learningSchedule?->classRoom?->name ?? '-' }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge {{ $sessionBadge }}">{{ $sessionStatusText }}</span>
                                </td>
                                <td>
                                    @if ($session->attendances_count > 0)
                                        <span style="color: #157a4d; font-weight: 600; font-size: 12px;">
                                            {{ $session->attendances_count }} siswa tercatat
                                        </span>
                                    @else
                                        <span style="color: var(--muted); font-size: 12px;">Belum dicatat</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        <a class="button button-secondary" style="font-size: 11px; padding: 5px 10px;"
                                            href="{{ route('tutor.attendances.edit', $session) }}">
                                            Kelola Absensi
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state compact">
                <span class="empty-icon">⏱</span>
                <strong>Belum ada sesi pembelajaran</strong>
                <p>Sesi pembelajaran yang terkait dengan jadwal mengajar Anda akan muncul di bagian ini.</p>
            </div>
        @endif
    </section>

    {{-- 5. Ringkasan Absensi --}}
    <section class="panel" style="margin-bottom: 24px;" aria-labelledby="heading-ringkasan-absensi">
        <div class="panel-heading" style="margin-bottom: 16px;">
            <div>
                <h2 id="heading-ringkasan-absensi">Ringkasan Absensi Siswa</h2>
                <p>Akumulasi kehadiran siswa dari seluruh sesi pembelajaran yang Anda ampu.</p>
            </div>
            <a class="button button-secondary" href="{{ route('tutor.attendances.index') }}" style="font-size: 12px; padding: 6px 12px;">
                Buka Kelola Absensi →
            </a>
        </div>

        @if ($attendanceSummary['total'] > 0)
            <div class="metric-grid">
                <div class="metric-box">
                    <p>Hadir</p>
                    <strong style="color: #157a4d;">{{ number_format($attendanceSummary['present']) }}</strong>
                    <small style="color: var(--muted); font-size: 10px;">Presensi hadir</small>
                </div>
                <div class="metric-box">
                    <p>Sakit</p>
                    <strong style="color: #b54708;">{{ number_format($attendanceSummary['sick']) }}</strong>
                    <small style="color: var(--muted); font-size: 10px;">Izin sakit</small>
                </div>
                <div class="metric-box">
                    <p>Izin</p>
                    <strong style="color: #175cd3;">{{ number_format($attendanceSummary['permission']) }}</strong>
                    <small style="color: var(--muted); font-size: 10px;">Izin keperluan</small>
                </div>
                <div class="metric-box">
                    <p>Tidak Hadir</p>
                    <strong style="color: #c63d3d;">{{ number_format($attendanceSummary['absent']) }}</strong>
                    <small style="color: var(--muted); font-size: 10px;">Tanpa keterangan</small>
                </div>
            </div>
        @else
            <div class="empty-state compact">
                <span class="empty-icon">📋</span>
                <strong>Belum ada data absensi tercatat</strong>
                <p>Data absensi siswa dari sesi pembelajaran Anda akan dirangkum di sini setelah Anda mengisi presensi.</p>
            </div>
        @endif
    </section>

    {{-- 6. Aktivitas Try Out & Hasil Siswa --}}
    <section class="panel" style="margin-bottom: 24px;" aria-labelledby="heading-tryout-tutor">
        <div class="panel-heading" style="margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h2 id="heading-tryout-tutor">Aktivitas Try Out</h2>
                <p>Status Try Out yang Anda buat dan ringkasan hasil pengerjaan siswa.</p>
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a class="button button-primary" href="{{ route('tutor.tryouts.create') }}" style="font-size: 12px; padding: 6px 12px;">
                    + Buat Try Out
                </a>
                <a class="button button-secondary" href="{{ route('tutor.tryouts.index') }}" style="font-size: 12px; padding: 6px 12px;">
                    Daftar Try Out
                </a>
                <a class="button button-secondary" href="{{ route('tutor.tryout-results.index') }}" style="font-size: 12px; padding: 6px 12px;">
                    Hasil Nilai Siswa →
                </a>
            </div>
        </div>

        {{-- Status Try Out --}}
        <div class="metric-grid" style="margin-bottom: 20px;">
            <div class="metric-box">
                <p>Total Try Out Dibuat</p>
                <strong style="color: var(--blue);">{{ number_format($tryoutStats['total']) }}</strong>
                <small style="color: var(--muted); font-size: 10px;">Milik tutor Anda</small>
            </div>
            <div class="metric-box">
                <p>Try Out Published</p>
                <strong style="color: #157a4d;">{{ number_format($tryoutStats['published']) }}</strong>
                <small style="color: var(--muted); font-size: 10px;">Aktif dikerjakan siswa</small>
            </div>
            <div class="metric-box">
                <p>Draft Try Out</p>
                <strong style="color: #b54708;">{{ number_format($tryoutStats['draft']) }}</strong>
                <small style="color: var(--muted); font-size: 10px;">Belum dipublikasikan</small>
            </div>
            <div class="metric-box">
                <p>Rata-rata Nilai Siswa</p>
                <strong style="color: #7856d9;">
                    {{ $averageTryoutScore !== null ? number_format($averageTryoutScore, 1) : '-' }}
                </strong>
                <small style="color: var(--muted); font-size: 10px;">{{ number_format($gradedAttemptsCount) }} pengerjaan dinilai</small>
            </div>
        </div>

        {{-- Hasil Siswa Terbaru --}}
        @if ($recentTryoutResults->isNotEmpty())
            <h3 style="font-size: 14px; margin: 18px 0 10px; color: #111827;">Pengerjaan Try Out Terbaru oleh Siswa</h3>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Judul Try Out</th>
                            <th>Mata Pelajaran</th>
                            <th>Waktu Selesai</th>
                            <th>Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentTryoutResults as $result)
                            <tr>
                                <td>
                                    <strong style="color: #111827;">{{ $result->student?->user?->name }}</strong>
                                </td>
                                <td>{{ $result->student?->classRoom?->name ?? '-' }}</td>
                                <td>{{ $result->tryout?->title }}</td>
                                <td>{{ $result->tryout?->subject?->name ?? '-' }}</td>
                                <td>{{ ($result->submitted_at ?? $result->graded_at)?->translatedFormat('d M Y, H:i') }}</td>
                                <td>
                                    <strong style="color: var(--blue); font-size: 14px;">
                                        {{ rtrim(rtrim($result->final_score, '0'), '.') }}
                                    </strong>
                                    <small style="color: var(--muted); font-size: 10px;">/ 100</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state compact">
                <span class="empty-icon">✏</span>
                <strong>Belum ada pengerjaan Try Out yang dinilai</strong>
                <p>Hasil Try Out yang dikerjakan oleh siswa pada Try Out yang Anda buat akan muncul di sini.</p>
            </div>
        @endif
    </section>
@endsection
