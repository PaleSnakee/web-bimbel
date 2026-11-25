@extends('layouts.parent')

@section('title', 'Perkembangan ' . $child->user?->name)
@section('page_title', 'Detail Perkembangan Anak')

@section('content')
    <div style="margin-bottom: 16px;">
        <a href="{{ route('parent.children.index') }}" class="button button-secondary" style="font-size: 12px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 6px;">
            ← Kembali ke Data Anak
        </a>
    </div>

    {{-- Header Profile Anak --}}
    <section class="page-heading">
        <div>
            <p class="eyebrow">PERKEMBANGAN AKADEMIK ANAK</p>
            <h1>{{ $child->user?->name }}</h1>
            <p>
                NIS: <strong>{{ $child->nis ?? '-' }}</strong> ·
                Kelas: <strong>{{ $child->classRoom?->name ?? 'Belum ditentukan' }}</strong> ·
                Jenjang: <strong>{{ $child->education_level }}</strong> ·
                Status: <span class="badge {{ $child->user?->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $child->user?->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span>
            </p>
        </div>
        <span class="today">{{ now()->translatedFormat('l, d F Y') }}</span>
    </section>

    {{-- Ringkasan Hasil Akademik --}}
    <section class="stat-grid" aria-label="Ringkasan Akademik">
        <article class="stat-card">
            <span class="stat-icon green">✓</span>
            <div>
                <p>Try Out Diselesaikan</p>
                <strong>{{ number_format($childStats['completed_tryouts']) }}</strong>
                <small>Attempt selesai</small>
            </div>
        </article>

        <article class="stat-card">
            <span class="stat-icon purple">★</span>
            <div>
                <p>Rata-rata Nilai Try Out</p>
                <strong>{{ $childStats['average_score'] !== null ? number_format($childStats['average_score'], 1) : '-' }}</strong>
                <small>{{ $childStats['graded_attempts_count'] > 0 ? $childStats['graded_attempts_count'] . ' try out dinilai' : 'Belum ada nilai' }}</small>
            </div>
        </article>

        <article class="stat-card">
            <span class="stat-icon blue">📋</span>
            <div>
                <p>Presensi Kehadiran</p>
                <strong>{{ number_format($childStats['attendance_present']) }}</strong>
                <small>Dari {{ number_format($childStats['attendance_total']) }} sesi absensi</small>
            </div>
        </article>

        <article class="stat-card">
            <span class="stat-icon orange">📚</span>
            <div>
                <p>Mata Pelajaran Jenjang</p>
                <strong>{{ number_format($subjects->count()) }}</strong>
                <small>Jenjang {{ $child->education_level }}</small>
            </div>
        </article>
    </section>

    {{-- Mata Pelajaran yang Tersedia --}}
    <section class="panel" style="margin-bottom: 24px;" aria-labelledby="heading-mapel">
        <div class="panel-heading" style="margin-bottom: 14px;">
            <div>
                <h2 id="heading-mapel">Mata Pelajaran Tersedia (Jenjang {{ $child->education_level }})</h2>
                <p>Mata pelajaran yang dipelajari dan tersedia pada kurikulum bimbingan belajar.</p>
            </div>
        </div>

        @if ($subjects->isNotEmpty())
            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                @foreach ($subjects as $subject)
                    <div style="background: #f8fafc; border: 1px solid var(--line); border-radius: 7px; padding: 7px 12px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                        <span style="font-weight: 600; color: var(--blue);">{{ $subject->code }}</span>
                        <span style="color: var(--ink);">{{ $subject->name }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color: var(--muted); font-size: 13px; margin: 0;">Belum ada data mata pelajaran untuk jenjang ini.</p>
        @endif
    </section>

    {{-- Riwayat Try Out Anak --}}
    <section class="panel" id="riwayat-tryout" style="margin-bottom: 24px;" aria-labelledby="heading-riwayat-tryout">
        <div class="panel-heading" style="margin-bottom: 16px;">
            <div>
                <h2 id="heading-riwayat-tryout">Riwayat dan Hasil Try Out</h2>
                <p>Daftar pengerjaan Try Out yang telah diselesaikan dan dinilai.</p>
            </div>
        </div>

        @if ($tryoutAttempts->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Judul Try Out</th>
                            <th>Mata Pelajaran</th>
                            <th>Tanggal Pengerjaan</th>
                            <th>Total Soal</th>
                            <th>Rincian Jawaban</th>
                            <th>Poin / Maks</th>
                            <th>Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tryoutAttempts as $attempt)
                            <tr>
                                <td>
                                    <strong style="color: #111827; font-weight: 600;">{{ $attempt->tryout?->title }}</strong>
                                </td>
                                <td>{{ $attempt->tryout?->subject?->code }} - {{ $attempt->tryout?->subject?->name }}</td>
                                <td>{{ ($attempt->submitted_at ?? $attempt->graded_at)?->translatedFormat('d M Y, H:i') }}</td>
                                <td>{{ $attempt->total_questions }} soal</td>
                                <td>
                                    <span style="color: #157a4d; font-weight: 600;">{{ $attempt->correct_answers }}B</span> /
                                    <span style="color: #c63d3d; font-weight: 600;">{{ $attempt->incorrect_answers }}S</span> /
                                    <span style="color: #667085; font-weight: 600;">{{ $attempt->unanswered_questions }}K</span>
                                    <small style="display: block; font-size: 10px; color: var(--muted);">(Benar / Salah / Kosong)</small>
                                </td>
                                <td>
                                    {{ $attempt->earned_points }} / {{ $attempt->total_points }}
                                </td>
                                <td>
                                    <strong style="font-size: 15px; color: var(--blue);">
                                        {{ rtrim(rtrim($attempt->final_score, '0'), '.') }}
                                    </strong>
                                    <small style="color: var(--muted); font-size: 10px;">/ 100</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($tryoutAttempts->hasPages())
                <div class="pagination-wrap">
                    {{ $tryoutAttempts->fragment('riwayat-tryout')->links() }}
                </div>
            @endif
        @else
            <div class="empty-state compact">
                <span class="empty-icon">✏</span>
                <strong>Belum ada riwayat Try Out</strong>
                <p>Anak Anda belum menyelesaikan Try Out yang telah dinilai.</p>
            </div>
        @endif
    </section>

    {{-- Riwayat Absensi Anak --}}
    <section class="panel" id="riwayat-absensi" style="margin-bottom: 24px;" aria-labelledby="heading-riwayat-absensi">
        <div class="panel-heading" style="margin-bottom: 16px;">
            <div>
                <h2 id="heading-riwayat-absensi">Riwayat Absensi Pembelajaran</h2>
                <p>Presensi sesi pembelajaran tatap muka/bimbingan belajar.</p>
            </div>
        </div>

        @if ($attendances->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal Pembelajaran</th>
                            <th>Mata Pelajaran</th>
                            <th>Nama Tutor</th>
                            <th>Status Kehadiran</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendances as $attendance)
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
                                    {{ $attendance->learningSession?->session_date ? $attendance->learningSession->session_date->translatedFormat('d M Y') : '-' }}
                                    @if ($attendance->learningSession?->start_time)
                                        <small style="display: block; color: var(--muted); font-size: 11px;">
                                            {{ $attendance->learningSession->start_time->format('H:i') }} - {{ $attendance->learningSession->end_time->format('H:i') }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $attendance->learningSession?->learningSchedule?->subject?->name ?? '-' }}</td>
                                <td>{{ $attendance->learningSession?->learningSchedule?->tutor?->user?->name ?? '-' }}</td>
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

            @if ($attendances->hasPages())
                <div class="pagination-wrap">
                    {{ $attendances->fragment('riwayat-absensi')->links() }}
                </div>
            @endif
        @else
            <div class="empty-state compact">
                <span class="empty-icon">📋</span>
                <strong>Belum ada data kehadiran</strong>
                <p>Data absensi sesi pembelajaran anak Anda akan ditampilkan di sini.</p>
            </div>
        @endif
    </section>
@endsection
