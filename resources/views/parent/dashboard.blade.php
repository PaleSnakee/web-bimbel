@extends('layouts.parent')

@section('title', 'Dashboard Orang Tua')
@section('page_title', 'Dashboard')

@section('content')
    {{-- 1. Welcome Section --}}
    <section class="page-heading">
        <div>
            <p class="eyebrow">DASHBOARD ORANG TUA</p>
            <h1>Selamat Datang, {{ auth()->user()->name }}!</h1>
            <p>Pantau perkembangan akademik, hasil Try Out, dan riwayat absensi anak Anda di El Renaissance.</p>
        </div>
        <span class="today">{{ now()->translatedFormat('l, d F Y') }}</span>
    </section>

    {{-- 2. Statistik Anak --}}
    <section class="stat-grid" aria-label="Statistik Anak">
        <article class="stat-card">
            <span class="stat-icon blue">👥</span>
            <div>
                <p>Total Anak Terdaftar</p>
                <strong>{{ number_format($statistics['total_children']) }}</strong>
                <small>Terhubung dengan akun Anda</small>
            </div>
        </article>

        <article class="stat-card">
            <span class="stat-icon green">✓</span>
            <div>
                <p>Try Out Diselesaikan</p>
                <strong>{{ number_format($statistics['completed_tryouts']) }}</strong>
                <small>Seluruh anak Anda</small>
            </div>
        </article>

        <article class="stat-card">
            <span class="stat-icon purple">★</span>
            <div>
                <p>Rata-rata Nilai Try Out</p>
                <strong>{{ $statistics['average_score'] !== null ? number_format($statistics['average_score'], 1) : '-' }}</strong>
                <small>{{ $statistics['graded_attempts_count'] > 0 ? $statistics['graded_attempts_count'] . ' attempt dinilai' : 'Belum ada nilai' }}</small>
            </div>
        </article>

        <article class="stat-card">
            <span class="stat-icon orange">📋</span>
            <div>
                <p>Kehadiran Sesi Belajar</p>
                <strong>{{ number_format($statistics['attendance_present']) }}</strong>
                <small>Dari {{ number_format($statistics['attendance_total']) }} total sesi absensi</small>
            </div>
        </article>
    </section>

    {{-- 3. Daftar Anak --}}
    <section class="panel" style="margin-bottom: 24px;" aria-labelledby="heading-daftar-anak">
        <div class="panel-heading" style="margin-bottom: 16px;">
            <div>
                <h2 id="heading-daftar-anak">Data Anak</h2>
                <p>Siswa yang terdaftar sebagai tanggungan Anda di El Renaissance.</p>
            </div>
            @if ($children->isNotEmpty())
                <a class="button button-secondary" href="{{ route('parent.children.index') }}" style="font-size: 12px; padding: 6px 14px;">
                    Lihat Data Anak →
                </a>
            @endif
        </div>

        @if ($children->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Lengkap Anak</th>
                            <th>NIS</th>
                            <th>Kelas</th>
                            <th>Jenjang</th>
                            <th>Status Akun</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($children as $child)
                            <tr>
                                <td>
                                    <strong style="color: #111827; font-weight: 600;">{{ $child->user?->name }}</strong>
                                </td>
                                <td>{{ $child->nis ?? '-' }}</td>
                                <td>{{ $child->classRoom?->name ?? 'Belum ditentukan' }}</td>
                                <td>{{ $child->education_level }}</td>
                                <td>
                                    <span class="badge {{ $child->user?->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                        {{ $child->user?->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        <a class="button button-secondary" style="font-size: 11px; padding: 5px 12px;"
                                            href="{{ route('parent.children.show', $child) }}">
                                            Lihat Detail Perkembangan
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <span class="empty-icon">👥</span>
                <strong>Belum ada anak yang terhubung</strong>
                <p>Akun Anda belum terhubung dengan data siswa manapun. Silakan hubungi pihak bimbingan belajar untuk menghubungkan akun anak Anda.</p>
            </div>
        @endif
    </section>

    {{-- 4. Hasil Try Out Terbaru Anak --}}
    <section class="panel" style="margin-bottom: 24px;" aria-labelledby="heading-tryout-terbaru">
        <div class="panel-heading" style="margin-bottom: 16px;">
            <div>
                <h2 id="heading-tryout-terbaru">Hasil Try Out Terbaru Anak</h2>
                <p>Ringkasan pengerjaan Try Out terbaru yang telah dinilai.</p>
            </div>
            @if ($recentTryouts->isNotEmpty())
                <a class="button button-secondary" href="{{ route('parent.tryouts.index') }}" style="font-size: 12px; padding: 6px 14px;">
                    Lihat Semua Hasil Try Out →
                </a>
            @endif
        </div>

        @if ($recentTryouts->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Anak</th>
                            <th>Judul Try Out</th>
                            <th>Mata Pelajaran</th>
                            <th>Tanggal Selesai</th>
                            <th>Nilai Akhir</th>
                            <th style="text-align: right;">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentTryouts as $attempt)
                            <tr>
                                <td>
                                    <strong style="color: #111827;">{{ $attempt->student?->user?->name }}</strong>
                                    <small style="display: block; color: var(--muted); font-size: 11px;">
                                        {{ $attempt->student?->classRoom?->name ?? '-' }}
                                    </small>
                                </td>
                                <td>{{ $attempt->tryout?->title }}</td>
                                <td>{{ $attempt->tryout?->subject?->code }} - {{ $attempt->tryout?->subject?->name }}</td>
                                <td>{{ ($attempt->submitted_at ?? $attempt->graded_at)?->translatedFormat('d M Y, H:i') }}</td>
                                <td>
                                    <strong style="font-size: 14px; color: var(--blue);">
                                        {{ rtrim(rtrim($attempt->final_score, '0'), '.') }}
                                    </strong>
                                    <small style="color: var(--muted); font-size: 10px;">/ 100</small>
                                </td>
                                <td style="text-align: right;">
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        <a class="button button-secondary" style="font-size: 11px; padding: 5px 10px;"
                                            href="{{ route('parent.children.show', $attempt->student_id) }}#riwayat-tryout">
                                            Rincian
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
                <span class="empty-icon">✏</span>
                <strong>Belum ada riwayat hasil Try Out</strong>
                <p>Hasil Try Out yang telah diselesaikan dan dinilai oleh sistem akan muncul di sini.</p>
            </div>
        @endif
    </section>

    {{-- 5. Riwayat Kehadiran Terbaru --}}
    <section class="panel" style="margin-bottom: 24px;" aria-labelledby="heading-absensi-terbaru">
        <div class="panel-heading" style="margin-bottom: 16px;">
            <div>
                <h2 id="heading-absensi-terbaru">Riwayat Kehadiran Belajar Terbaru</h2>
                <p>Catatan kehadiran pada sesi pembelajaran tatap muka/bimbingan.</p>
            </div>
            @if ($recentAttendances->isNotEmpty())
                <a class="button button-secondary" href="{{ route('parent.attendances.index') }}" style="font-size: 12px; padding: 6px 14px;">
                    Lihat Semua Riwayat Absensi →
                </a>
            @endif
        </div>

        @if ($recentAttendances->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Anak</th>
                            <th>Tanggal Sesi</th>
                            <th>Mata Pelajaran & Tutor</th>
                            <th>Status Kehadiran</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentAttendances as $attendance)
                            @php
                                $statusBadge = match ($attendance->status) {
                                    'present' => 'badge-active',
                                    'sick' => 'badge-warning',
                                    'permission' => 'badge-info',
                                    'absent' => 'badge-inactive',
                                    default => 'badge-inactive',
                                };

                                $statusLabel = match ($attendance->status) {
                                    'present' => 'Hadir',
                                    'sick' => 'Sakit',
                                    'permission' => 'Izin',
                                    'absent' => 'Tidak Hadir',
                                    default => ucfirst($attendance->status),
                                };
                            @endphp
                            <tr>
                                <td>
                                    <strong style="color: #111827;">{{ $attendance->student?->user?->name }}</strong>
                                </td>
                                <td>
                                    {{ $attendance->learningSession?->session_date ? $attendance->learningSession->session_date->translatedFormat('d M Y') : '-' }}
                                    @if ($attendance->learningSession?->start_time)
                                        <small style="display: block; color: var(--muted); font-size: 11px;">
                                            {{ $attendance->learningSession->start_time->format('H:i') }} - {{ $attendance->learningSession->end_time->format('H:i') }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $attendance->learningSession?->learningSchedule?->subject?->name ?? '-' }}</div>
                                    <small style="color: var(--muted); font-size: 11px;">
                                        Tutor: {{ $attendance->learningSession?->learningSchedule?->tutor?->user?->name ?? '-' }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                                </td>
                                <td>
                                    <span style="color: var(--muted); font-size: 12px;">{{ $attendance->notes ?: '-' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state compact">
                <span class="empty-icon">📋</span>
                <strong>Belum ada catatan absensi</strong>
                <p>Presensi sesi pembelajaran anak Anda akan tercatat dan ditampilkan di bagian ini.</p>
            </div>
        @endif
    </section>
@endsection
