@extends('layouts.parent')

@section('title', 'Riwayat Absensi Anak')
@section('page_title', 'Riwayat Absensi')

@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">PRESENSI PEMBELAJARAN</p>
            <h1>Riwayat Absensi Anak</h1>
            <p>Catatan kehadiran anak Anda pada seluruh sesi pembelajaran tatap muka/bimbingan.</p>
        </div>
        <span class="today">{{ now()->translatedFormat('l, d F Y') }}</span>
    </section>

    <section class="panel">
        <div class="panel-heading" style="margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h2>Presensi Sesi Belajar</h2>
                <p>Status kehadiran berdasarkan rekaman data absensi tutor dan admin bimbel.</p>
            </div>

            @if ($children->count() > 1)
                <div>
                    <form method="GET" action="{{ route('parent.attendances.index') }}" style="display: flex; align-items: center; gap: 8px;">
                        <label for="student_id" style="font-size: 12px; color: var(--muted); font-weight: 500;">Pilih Anak:</label>
                        <select name="student_id" id="student_id" onchange="this.form.submit()"
                            style="padding: 6px 12px; border: 1px solid var(--line); border-radius: 6px; font-size: 12px; background: #fff; color: var(--ink);">
                            <option value="">Semua Anak</option>
                            @foreach ($children as $child)
                                <option value="{{ $child->id }}" @selected($selectedChildId === $child->id)>
                                    {{ $child->user?->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @endif
        </div>

        @if ($attendances->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Anak</th>
                            <th>Tanggal Sesi</th>
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
                                    <strong style="color: #111827;">{{ $attendance->student?->user?->name }}</strong>
                                    <small style="display: block; color: var(--muted); font-size: 11px;">
                                        {{ $attendance->student?->classRoom?->name ?? 'Belum ada kelas' }}
                                    </small>
                                </td>
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
                    {{ $attendances->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <span class="empty-icon">📋</span>
                <strong>Belum ada catatan presensi</strong>
                <p>Belum ada rekaman kehadiran sesi pembelajaran untuk anak Anda.</p>
            </div>
        @endif
    </section>
@endsection
