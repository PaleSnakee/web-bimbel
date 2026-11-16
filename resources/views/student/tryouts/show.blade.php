@extends('layouts.student')

@section('title', $tryout->title)

@section('content')
    <a class="student-back" href="{{ route('student.tryouts.index') }}">← Kembali ke Try Out</a>
    <section class="student-card student-detail-card">
        <p class="eyebrow">TRY OUT ONLINE</p>
        <h1>{{ $tryout->title }}</h1>
        <p class="student-subject">{{ $tryout->subject->code }} · {{ $tryout->subject->name }}</p>
        @if ($tryout->description)
            <p class="student-description">{{ $tryout->description }}</p>
        @endif

        <dl class="student-meta student-meta-detail">
            <div><dt>Durasi</dt><dd>{{ $tryout->duration_minutes }} menit</dd></div>
            <div><dt>Jumlah soal</dt><dd>{{ $tryout->questions_count }} soal</dd></div>
            <div><dt>Waktu mulai</dt><dd>{{ $tryout->start_at->format('d M Y H:i') }}</dd></div>
            <div><dt>Waktu selesai</dt><dd>{{ $tryout->end_at->format('d M Y H:i') }}</dd></div>
        </dl>

        <div class="student-rules">
            <h2>Ketentuan pengerjaan</h2>
            <ul>
                <li>Timer dimulai setelah Anda menekan tombol mulai.</li>
                <li>Jawaban tersimpan otomatis ketika pilihan jawaban dipilih.</li>
                <li>Jawaban tidak dapat diubah setelah dikumpulkan atau waktu habis.</li>
            </ul>
        </div>

        @if ($attempt?->status === 'in_progress')
            <a class="button button-primary" href="{{ route('student.tryouts.attempts.show', [$tryout, $attempt]) }}">Lanjutkan pengerjaan</a>
        @elseif ($attempt)
            <div class="student-notice">Pengerjaan ini berstatus: <strong>{{ $attempt->status === 'submitted' ? 'Sudah dikumpulkan' : 'Waktu habis' }}</strong>.</div>
        @else
            <form method="POST" action="{{ route('student.tryouts.start', $tryout) }}" onsubmit="return confirm('Timer akan dimulai dan tidak dapat diulang. Mulai Try Out sekarang?');">
                @csrf
                <button class="button button-primary" type="submit">Mulai Try Out</button>
            </form>
        @endif
    </section>
@endsection