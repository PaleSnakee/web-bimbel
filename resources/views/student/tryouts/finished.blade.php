@extends('layouts.student')

@section('title', 'Pengerjaan Selesai')

@section('content')
    <section class="student-card student-finished">
        <p class="eyebrow">TRY OUT ONLINE</p>
        <h1>Pengerjaan selesai</h1>
        <p>
            @if ($attempt->status === 'submitted')
                Jawaban untuk <strong>{{ $tryout->title }}</strong> telah berhasil dikumpulkan pada
                {{ $attempt->submitted_at?->format('d M Y H:i') }}.
            @else
                Waktu pengerjaan <strong>{{ $tryout->title }}</strong> telah habis.
            @endif
        </p>
        <p class="student-description">
            Jawaban tidak dapat diubah kembali. Hasil penilaian akan tersedia sesuai kebijakan bimbingan belajar.
        </p>
        <a class="button button-primary" href="{{ route('student.tryouts.index') }}">Kembali ke daftar Try Out</a>
    </section>
@endsection
