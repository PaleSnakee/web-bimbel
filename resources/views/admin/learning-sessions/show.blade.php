@extends('layouts.admin')
@section('title', 'Detail Sesi Pembelajaran')
@section('content')
    @php($schedule = $learningSession->learningSchedule)

    <section class="page-heading">
        <div>
            <p class="eyebrow">AKADEMIK / SESI PEMBELAJARAN</p>
            <h1>Detail Sesi Pembelajaran</h1>
            <p>{{ $schedule->classRoom->name }} · {{ $learningSession->session_date->format('d M Y') }} · {{ $learningSession->start_time->format('H:i') }}–{{ $learningSession->end_time->format('H:i') }}</p>
        </div>
        <div class="table-actions">
            <a class="button button-secondary" href="{{ route('admin.learning-sessions.index') }}">Kembali</a>
            <a class="button button-primary" href="{{ route('admin.learning-sessions.edit', $learningSession) }}">Edit Sesi</a>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <section class="panel">
        <div class="detail-grid">
            <div class="detail-item"><span>Kelas</span><strong>{{ $schedule->classRoom->code }} — {{ $schedule->classRoom->name }}</strong></div>
            <div class="detail-item"><span>Tutor</span><strong>{{ $schedule->tutor->user->name }}</strong></div>
            <div class="detail-item"><span>Mata Pelajaran</span><strong>{{ $schedule->subject->code }} — {{ $schedule->subject->name }}</strong></div>
            <div class="detail-item"><span>Hari Jadwal</span><strong>{{ \App\Models\LearningSchedule::dayLabel($schedule->day_of_week) }}</strong></div>
            <div class="detail-item"><span>Waktu Jadwal</span><strong>{{ $schedule->start_time->format('H:i') }}–{{ $schedule->end_time->format('H:i') }}</strong></div>
            <div class="detail-item"><span>Tanggal Sesi</span><strong>{{ $learningSession->session_date->format('d M Y') }}</strong></div>
            <div class="detail-item"><span>Waktu Sesi Aktual</span><strong>{{ $learningSession->start_time->format('H:i') }}–{{ $learningSession->end_time->format('H:i') }}</strong></div>
            <div class="detail-item"><span>Status</span><strong><span class="badge {{ $learningSession->status === 'completed' ? 'badge-active' : ($learningSession->status === 'cancelled' ? 'badge-inactive' : '') }}">{{ ucfirst($learningSession->status) }}</span></strong></div>
            <div class="detail-item"><span>Materi</span><strong>{{ $learningSession->topic ?: '—' }}</strong></div>
            <div class="detail-item"><span>Catatan</span><strong>{{ $learningSession->notes ?: '—' }}</strong></div>
        </div>

        @if ($learningSession->status === 'scheduled')
            <div class="form-actions">
                <form method="POST" action="{{ route('admin.learning-sessions.update-status', $learningSession) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button class="button button-primary" type="submit">Tandai Selesai</button>
                </form>
                <form method="POST" action="{{ route('admin.learning-sessions.update-status', $learningSession) }}" onsubmit="return confirm('Batalkan sesi ini?');">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button class="button button-secondary" type="submit">Batalkan Sesi</button>
                </form>
            </div>
        @endif
    </section>
@endsection