@extends('layouts.student')

@section('title', 'Pembahasan Try Out')

@section('content')
    <a class="student-back" href="{{ route('student.tryouts.attempts.result', [$tryout, $attempt]) }}">← Kembali ke hasil</a>

    <section class="student-page-heading">
        <div>
            <p class="eyebrow">PEMBAHASAN TRY OUT</p>
            <h1>{{ $tryout->title }}</h1>
            <p>{{ $tryout->subject?->name }}</p>
        </div>
    </section>

    <div class="review-list">
        @foreach ($tryout->questions as $question)
            @php
                $answer = $question->attemptAnswers->first();
                $selectedOption = $question->options->firstWhere('id', $answer?->tryout_question_option_id);
                $correctOption = $question->options->firstWhere('is_correct', true);
                $isCorrect = $selectedOption?->id === $correctOption?->id;
                $status = ! $selectedOption ? 'Kosong' : ($isCorrect ? 'Benar' : 'Salah');
            @endphp

            <article class="student-card review-card">
                <div class="review-card-header">
                    <span class="student-badge">Soal {{ $question->order_number }}</span>
                    <span class="review-status review-status-{{ strtolower($status) }}">{{ $status }}</span>
                </div>

                <h2>{{ $question->question_text }}</h2>
                <dl class="review-detail">
                    <div>
                        <dt>Jawaban Anda</dt>
                        <dd>{{ $selectedOption ? $selectedOption->option_label . '. ' . $selectedOption->option_text : 'Tidak dijawab' }}</dd>
                    </div>
                    <div>
                        <dt>Jawaban benar</dt>
                        <dd>{{ $correctOption?->option_label }}. {{ $correctOption?->option_text }}</dd>
                    </div>
                    <div>
                        <dt>Bobot poin</dt>
                        <dd>{{ $question->points }}</dd>
                    </div>
                    <div>
                        <dt>Poin diperoleh</dt>
                        <dd>{{ $isCorrect ? $question->points : 0 }}</dd>
                    </div>
                </dl>
            </article>
        @endforeach
    </div>
@endsection