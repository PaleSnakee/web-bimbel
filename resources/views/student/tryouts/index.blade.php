@extends('layouts.student')

@section('title', 'Try Out')

@section('content')
    <div class="student-page-heading">
        <div>
            <p class="eyebrow">TRY OUT ONLINE</p>
            <h1>Try Out Saya</h1>
            <p>Daftar Try Out yang tersedia untuk kelas Anda.</p>
        </div>
    </div>

    <div class="student-card-list">
        @forelse ($tryouts as $tryout)
            @php
                $attempt = $tryout->attempts->first();
                $attemptLabel = match ($attempt?->status) {
                    'in_progress' => 'Sedang dikerjakan',
                    'submitted' => 'Sudah dikumpulkan',
                    'expired' => 'Waktu habis',
                    default => 'Belum dikerjakan',
                };
            @endphp
            <article class="student-card">
                <div class="student-card-header">
                    <div>
                        <h2>{{ $tryout->title }}</h2>
                        <p>{{ $tryout->subject->code }} · {{ $tryout->subject->name }}</p>
                    </div>
                    <span class="student-badge">{{ $attemptLabel }}</span>
                </div>
                @if ($tryout->description)
                    <p class="student-description">{{ $tryout->description }}</p>
                @endif
                <dl class="student-meta">
                    <div><dt>Durasi</dt><dd>{{ $tryout->duration_minutes }} menit</dd></div>
                    <div><dt>Mulai</dt><dd>{{ $tryout->start_at->format('d M Y H:i') }}</dd></div>
                    <div><dt>Selesai</dt><dd>{{ $tryout->end_at->format('d M Y H:i') }}</dd></div>
                    <div><dt>Jumlah soal</dt><dd>{{ $tryout->questions_count }} soal</dd></div>
                </dl>
                <a class="button button-primary" href="{{ route('student.tryouts.show', $tryout) }}">Lihat detail</a>
            </article>
        @empty
            <div class="student-empty">
                <strong>Belum ada Try Out tersedia.</strong>
                <p>Try Out yang dipublikasikan untuk kelas Anda akan ditampilkan di halaman ini.</p>
            </div>
        @endforelse
    </div>

    @if ($tryouts->hasPages())
        <div class="pagination-wrap">{{ $tryouts->links() }}</div>
    @endif
@endsection