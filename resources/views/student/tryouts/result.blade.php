@extends('layouts.student')

@section('title', 'Hasil Try Out')

@section('content')
    <section class="student-card tryout-result">
        <p class="eyebrow">HASIL TRY OUT</p>
        <h1>{{ $tryout->title }}</h1>
        <p class="student-subject">{{ $tryout->subject?->name }} · {{ $attempt->student->user?->name }}</p>

        <div class="result-score">
            <span>Nilai Akhir</span>
            <strong>{{ rtrim(rtrim($attempt->final_score, '0'), '.') }}</strong>
            <small>dari 100</small>
        </div>

        <dl class="student-meta student-meta-detail result-meta">
            <div><dt>Status</dt><dd>{{ $attempt->status === 'submitted' ? 'Dikumpulkan' : 'Waktu habis' }}</dd></div>
            <div><dt>Mulai</dt><dd>{{ $attempt->started_at->format('d M Y H:i') }}</dd></div>
            <div><dt>Selesai</dt><dd>{{ ($attempt->submitted_at ?? $attempt->graded_at)?->format('d M Y H:i') }}</dd></div>
            <div><dt>Dinilai</dt><dd>{{ $attempt->graded_at->format('d M Y H:i') }}</dd></div>
        </dl>

        <div class="result-summary" aria-label="Ringkasan hasil Try Out">
            <div><span>Total soal</span><strong>{{ $attempt->total_questions }}</strong></div>
            <div><span>Benar</span><strong>{{ $attempt->correct_answers }}</strong></div>
            <div><span>Salah</span><strong>{{ $attempt->incorrect_answers }}</strong></div>
            <div><span>Kosong</span><strong>{{ $attempt->unanswered_questions }}</strong></div>
            <div><span>Poin diperoleh</span><strong>{{ $attempt->earned_points }}</strong></div>
            <div><span>Poin maksimal</span><strong>{{ $attempt->total_points }}</strong></div>
        </div>

        <div class="attempt-actions">
            <a class="button button-secondary" href="{{ route('student.tryouts.index') }}">Daftar Try Out</a>
            <a class="button button-primary" href="{{ route('student.tryouts.attempts.review', [$tryout, $attempt]) }}">Lihat Pembahasan</a>
        </div>
    </section>
@endsection