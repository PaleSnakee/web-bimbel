@extends('layouts.admin')
@section('title', 'Detail Jadwal Pembelajaran')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">AKADEMIK / JADWAL PEMBELAJARAN</p>
            <h1>Detail Jadwal Pembelajaran</h1>
            <p>{{ $learningSchedule->classRoom->name }} · {{ \App\Models\LearningSchedule::dayLabel($learningSchedule->day_of_week) }} · {{ $learningSchedule->start_time->format('H:i') }}–{{ $learningSchedule->end_time->format('H:i') }}</p>
        </div>
        <div class="table-actions">
            <a class="button button-secondary" href="{{ route('admin.learning-schedules.index') }}">Kembali</a>
            <a class="button button-primary" href="{{ route('admin.learning-schedules.edit', $learningSchedule) }}">Edit Jadwal</a>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="panel">
        <div class="detail-grid">
            <div class="detail-item"><span>Kelas</span><strong>{{ $learningSchedule->classRoom->code }} — {{ $learningSchedule->classRoom->name }}</strong></div>
            <div class="detail-item"><span>Tutor</span><strong>{{ $learningSchedule->tutor->user->name }}</strong></div>
            <div class="detail-item"><span>Mata Pelajaran</span><strong>{{ $learningSchedule->subject->code }} — {{ $learningSchedule->subject->name }}</strong></div>
            <div class="detail-item"><span>Hari</span><strong>{{ \App\Models\LearningSchedule::dayLabel($learningSchedule->day_of_week) }}</strong></div>
            <div class="detail-item"><span>Waktu</span><strong>{{ $learningSchedule->start_time->format('H:i') }}–{{ $learningSchedule->end_time->format('H:i') }}</strong></div>
            <div class="detail-item"><span>Periode Berlaku</span><strong>{{ $learningSchedule->effective_from->format('d M Y') }} sampai {{ $learningSchedule->effective_until?->format('d M Y') ?? 'tanpa batas' }}</strong></div>
            <div class="detail-item"><span>Status</span><strong><span class="badge {{ $learningSchedule->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $learningSchedule->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></strong></div>
            <div class="detail-item"><span>Keterangan</span><strong>{{ $learningSchedule->notes ?: '—' }}</strong></div>
        </div>

        <div class="form-actions">
            @if ($learningSchedule->status === 'active')
                <form method="POST" action="{{ route('admin.learning-schedules.deactivate', $learningSchedule) }}" onsubmit="return confirm('Nonaktifkan jadwal ini?');">
                    @csrf
                    <button class="button button-secondary" type="submit">Nonaktifkan Jadwal</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.learning-schedules.activate', $learningSchedule) }}">
                    @csrf
                    <button class="button button-primary" type="submit">Aktifkan Jadwal</button>
                </form>
            @endif
        </div>
    </section>
@endsection