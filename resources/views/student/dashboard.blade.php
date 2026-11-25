@extends('layouts.student')

@section('title', 'Dashboard Siswa')
@section('page_title', 'Dashboard')

@section('content')
    {{-- 1. Welcome Section --}}
    <section class="page-heading">
        <div>
            <p class="eyebrow">DASHBOARD SISWA</p>
            <h1>Selamat Datang, {{ $student->user->name }}!</h1>
            <p>
                @if ($student->classRoom)
                    Kelas <strong>{{ $student->classRoom->name }}</strong> ({{ $student->education_level }})
                @else
                    Belum terdaftar di kelas aktif
                @endif
                · Tetap semangat belajar dan raih prestasi terbaikmu bersama El Renaissance.
            </p>
        </div>
        <span class="today">{{ now()->translatedFormat('l, d F Y') }}</span>
    </section>

    {{-- 2. Statistik Akademik --}}
    <section class="stat-grid" aria-label="Statistik Akademik Siswa">
        <article class="stat-card">
            <span class="stat-icon blue">▤</span>
            <div>
                <p>Try Out Tersedia</p>
                <strong>{{ number_format($statistics['available_tryouts']) }}</strong>
                <small>Untuk kelas {{ $student->classRoom?->name ?? 'Anda' }}</small>
            </div>
        </article>

        <article class="stat-card">
            <span class="stat-icon green">✓</span>
            <div>
                <p>Sudah Dikerjakan</p>
                <strong>{{ number_format($statistics['completed_tryouts']) }}</strong>
                <small>Pengerjaan selesai</small>
            </div>
        </article>

        <article class="stat-card">
            <span class="stat-icon orange">⏳</span>
            <div>
                <p>Belum Dikerjakan</p>
                <strong>{{ number_format($statistics['pending_tryouts']) }}</strong>
                <small>Perlu diselesaikan</small>
            </div>
        </article>

        <article class="stat-card">
            <span class="stat-icon purple">★</span>
            <div>
                <p>Rata-rata Nilai</p>
                <strong>{{ $statistics['average_score'] !== null ? number_format($statistics['average_score'], 1) : '-' }}</strong>
                <small>{{ $statistics['graded_attempts_count'] > 0 ? $statistics['graded_attempts_count'] . ' attempt dinilai' : 'Belum ada nilai' }}</small>
            </div>
        </article>
    </section>

    {{-- 3. Try Out Terbaru --}}
    <section class="panel" style="margin-bottom: 24px;" aria-labelledby="heading-tryout-terbaru">
        <div class="panel-heading" style="margin-bottom: 16px;">
            <div>
                <h2 id="heading-tryout-terbaru">Try Out Terbaru</h2>
                <p>Daftar Try Out yang relevan dan tersedia untuk kelas Anda.</p>
            </div>
            <a class="button button-secondary" href="{{ route('student.tryouts.index') }}" style="font-size: 12px; padding: 6px 14px;">
                Lihat Semua Try Out →
            </a>
        </div>

        @if ($recentTryouts->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Judul Try Out</th>
                            <th>Mata Pelajaran</th>
                            <th>Durasi</th>
                            <th>Waktu Pelaksanaan</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentTryouts as $tryout)
                            @php
                                $attempt = $tryout->attempts->first();
                                $now = now();
                                $isStarted = $now->gte($tryout->start_at);
                                $isEnded = $now->gte($tryout->end_at);

                                $statusLabel = match ($attempt?->status) {
                                    'in_progress' => 'Sedang Dikerjakan',
                                    'submitted' => 'Selesai',
                                    'expired' => 'Waktu Habis',
                                    default => ($isEnded ? 'Berakhir' : ($isStarted ? 'Tersedia' : 'Belum Dimulai')),
                                };

                                $badgeClass = match ($attempt?->status) {
                                    'in_progress' => 'badge-warning',
                                    'submitted' => 'badge-active',
                                    'expired' => 'badge-inactive',
                                    default => ($isEnded ? 'badge-inactive' : ($isStarted ? 'badge-active' : 'badge-inactive')),
                                };
                            @endphp
                            <tr>
                                <td>
                                    <strong style="color: #111827; font-weight: 600;">{{ $tryout->title }}</strong>
                                    @if ($tryout->description)
                                        <small style="display: block; color: var(--muted); font-size: 11px; margin-top: 2px;">
                                            {{ Str::limit($tryout->description, 60) }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $tryout->subject->code }} - {{ $tryout->subject->name }}</td>
                                <td>{{ $tryout->duration_minutes }} Menit</td>
                                <td>
                                    <div>{{ $tryout->start_at->translatedFormat('d M Y, H:i') }}</div>
                                    <small style="color: var(--muted); font-size: 11px;">s/d {{ $tryout->end_at->translatedFormat('d M Y, H:i') }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        @if ($attempt?->status === 'in_progress')
                                            <a class="button button-primary" style="font-size: 11px; padding: 5px 10px;"
                                                href="{{ route('student.tryouts.attempts.show', [$tryout, $attempt]) }}">
                                                Lanjutkan
                                            </a>
                                        @elseif ($attempt && in_array($attempt->status, ['submitted', 'expired'], true))
                                            <a class="button button-secondary" style="font-size: 11px; padding: 5px 10px;"
                                                href="{{ route('student.tryouts.attempts.result', [$tryout, $attempt]) }}">
                                                Lihat Hasil
                                            </a>
                                        @else
                                            <a class="button button-secondary" style="font-size: 11px; padding: 5px 10px;"
                                                href="{{ route('student.tryouts.show', $tryout) }}">
                                                Lihat Detail
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <span class="empty-icon">⌁</span>
                <strong>Belum ada Try Out tersedia</strong>
                <p>Try Out yang dipublikasikan untuk kelas Anda akan muncul di bagian ini.</p>
            </div>
        @endif
    </section>

    {{-- 4. Riwayat Pengerjaan --}}
    <section class="panel" id="riwayat-pengerjaan" style="margin-bottom: 24px;" aria-labelledby="heading-riwayat">
        <div class="panel-heading" style="margin-bottom: 16px;">
            <div>
                <h2 id="heading-riwayat">Riwayat Pengerjaan</h2>
                <p>Daftar pengerjaan Try Out Anda beserta status dan nilai akhir.</p>
            </div>
        </div>

        @if ($recentAttempts->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Try Out</th>
                            <th>Mata Pelajaran</th>
                            <th>Tanggal Pengerjaan</th>
                            <th>Status Pengerjaan</th>
                            <th>Nilai Akhir</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentAttempts as $attempt)
                            @php
                                $statusBadge = match ($attempt->status) {
                                    'in_progress' => 'badge-warning',
                                    'submitted' => 'badge-active',
                                    'expired' => 'badge-inactive',
                                    default => 'badge-inactive',
                                };

                                $statusText = match ($attempt->status) {
                                    'in_progress' => 'Sedang Dikerjakan',
                                    'submitted' => 'Selesai',
                                    'expired' => 'Waktu Habis',
                                    default => ucfirst($attempt->status),
                                };
                            @endphp
                            <tr>
                                <td>
                                    <strong style="color: #111827; font-weight: 600;">{{ $attempt->tryout?->title ?? '-' }}</strong>
                                </td>
                                <td>
                                    {{ $attempt->tryout?->subject?->code ?? '-' }} - {{ $attempt->tryout?->subject?->name ?? '-' }}
                                </td>
                                <td>
                                    {{ $attempt->started_at ? $attempt->started_at->translatedFormat('d M Y, H:i') : '-' }}
                                </td>
                                <td>
                                    <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                                </td>
                                <td>
                                    @if ($attempt->graded_at && $attempt->final_score !== null)
                                        <strong style="color: #111827; font-size: 13px;">{{ rtrim(rtrim($attempt->final_score, '0'), '.') }}</strong>
                                        <small style="color: var(--muted); font-size: 10px;">/ 100</small>
                                    @else
                                        <span style="color: var(--muted);">-</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        @if ($attempt->status === 'in_progress' && $attempt->tryout)
                                            <a class="button button-primary" style="font-size: 11px; padding: 5px 10px;"
                                                href="{{ route('student.tryouts.attempts.show', [$attempt->tryout, $attempt]) }}">
                                                Lanjutkan
                                            </a>
                                        @elseif ($attempt->tryout && in_array($attempt->status, ['submitted', 'expired'], true))
                                            <a class="button button-secondary" style="font-size: 11px; padding: 5px 10px;"
                                                href="{{ route('student.tryouts.attempts.result', [$attempt->tryout, $attempt]) }}">
                                                Lihat Hasil
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($recentAttempts->hasPages())
                <div class="pagination-wrap">
                    {{ $recentAttempts->fragment('riwayat-pengerjaan')->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <span class="empty-icon">⌁</span>
                <strong>Belum ada riwayat pengerjaan</strong>
                <p>Anda belum mengerjakan Try Out apapun. Silakan pilih Try Out yang tersedia untuk mulai berlatih.</p>
            </div>
        @endif
    </section>

    {{-- 5. Informasi Hasil Try Out --}}
    <section class="panel" id="informasi-hasil" style="margin-bottom: 24px;" aria-labelledby="heading-informasi-hasil">
        <div class="panel-heading" style="margin-bottom: 16px;">
            <div>
                <h2 id="heading-informasi-hasil">Informasi Hasil Try Out</h2>
                <p>Ringkasan performa pengerjaan Try Out yang sudah dinilai.</p>
            </div>
            @if ($latestGradedAttempt)
                <span class="today" style="font-size: 11px; padding: 6px 10px;">
                    Terakhir dinilai: {{ $latestGradedAttempt->graded_at->translatedFormat('d M Y, H:i') }}
                </span>
            @endif
        </div>

        @if ($latestGradedAttempt)
            <div style="background: #f8fafc; border: 1px solid var(--line); border-radius: 9px; padding: 18px; margin-bottom: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
                    <div>
                        <span class="badge badge-active" style="margin-bottom: 6px;">Pengerjaan Terakhir</span>
                        <h3 style="margin: 4px 0 2px; font-size: 17px; color: #111827;">{{ $latestGradedAttempt->tryout?->title }}</h3>
                        <p style="margin: 0; color: var(--muted); font-size: 12px;">
                            {{ $latestGradedAttempt->tryout?->subject?->code }} - {{ $latestGradedAttempt->tryout?->subject?->name }}
                            · Selesai: {{ ($latestGradedAttempt->submitted_at ?? $latestGradedAttempt->graded_at)?->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>

                    <div style="display: flex; align-items: baseline; gap: 4px; background: #fff; padding: 8px 16px; border: 1px solid var(--line); border-radius: 8px;">
                        <span style="font-size: 11px; color: var(--muted); text-transform: uppercase; font-weight: 600; margin-right: 6px;">Nilai Akhir:</span>
                        <strong style="font-size: 24px; color: var(--blue); line-height: 1;">{{ rtrim(rtrim($latestGradedAttempt->final_score, '0'), '.') }}</strong>
                        <small style="font-size: 11px; color: var(--muted);">/ 100</small>
                    </div>
                </div>

                <div class="metric-grid" style="margin-top: 16px;">
                    <div class="metric-box">
                        <p>Jawaban Benar</p>
                        <strong style="color: #157a4d;">{{ $latestGradedAttempt->correct_answers }} soal</strong>
                    </div>
                    <div class="metric-box">
                        <p>Jawaban Salah</p>
                        <strong style="color: #c63d3d;">{{ $latestGradedAttempt->incorrect_answers }} soal</strong>
                    </div>
                    <div class="metric-box">
                        <p>Jawaban Kosong</p>
                        <strong style="color: #667085;">{{ $latestGradedAttempt->unanswered_questions }} soal</strong>
                    </div>
                    <div class="metric-box">
                        <p>Poin Diperoleh</p>
                        <strong style="color: var(--blue);">{{ $latestGradedAttempt->earned_points }} / {{ $latestGradedAttempt->total_points }}</strong>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 16px; justify-content: flex-end; flex-wrap: wrap;">
                    <a class="button button-secondary" style="font-size: 12px; padding: 7px 14px;"
                        href="{{ route('student.tryouts.attempts.result', [$latestGradedAttempt->tryout, $latestGradedAttempt]) }}">
                        Lihat Detail Hasil
                    </a>
                    <a class="button button-primary" style="font-size: 12px; padding: 7px 14px;"
                        href="{{ route('student.tryouts.attempts.review', [$latestGradedAttempt->tryout, $latestGradedAttempt]) }}">
                        Lihat Pembahasan
                    </a>
                </div>
            </div>

            @if ($gradingSummary['total_graded'] > 1)
                <div style="padding: 12px 16px; background: #fff; border: 1px solid var(--line); border-radius: 8px; font-size: 12px; color: var(--muted); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <span>Akumulasi Seluruh Try Out Dinilai ({{ $gradingSummary['total_graded'] }} attempt):</span>
                    <span style="color: #111827; font-weight: 500;">
                        <strong style="color: #157a4d;">{{ $gradingSummary['correct_answers'] }}</strong> benar ·
                        <strong style="color: #c63d3d;">{{ $gradingSummary['incorrect_answers'] }}</strong> salah ·
                        <strong style="color: #667085;">{{ $gradingSummary['unanswered_questions'] }}</strong> kosong
                        (dari total {{ $gradingSummary['total_questions'] }} soal)
                    </span>
                </div>
            @endif
        @else
            <div class="empty-state compact">
                <span class="empty-icon">◎</span>
                <strong>Belum ada Try Out yang dinilai</strong>
                <p>Selesaikan Try Out untuk melihat ringkasan hasil, rincian jawaban benar/salah, dan pembahasan lengkap Anda di sini.</p>
            </div>
        @endif
    </section>
@endsection
