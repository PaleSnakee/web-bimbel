@extends('layouts.parent')

@section('title', 'Hasil Try Out Anak')
@section('page_title', 'Hasil Try Out')

@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">MONITORING EVALUASI</p>
            <h1>Hasil Try Out Anak</h1>
            <p>Daftar seluruh pengerjaan Try Out yang telah diselesaikan dan dinilai oleh sistem.</p>
        </div>
        <span class="today">{{ now()->translatedFormat('l, d F Y') }}</span>
    </section>

    <section class="panel">
        <div class="panel-heading" style="margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h2>Daftar Nilai Try Out</h2>
                <p>Data penilaian Try Out berdasarkan snapshot hasil pengerjaan anak.</p>
            </div>

            @if ($children->count() > 1)
                <div>
                    <form method="GET" action="{{ route('parent.tryouts.index') }}" style="display: flex; align-items: center; gap: 8px;">
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

        @if ($tryoutAttempts->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Anak</th>
                            <th>Judul Try Out</th>
                            <th>Mata Pelajaran</th>
                            <th>Tanggal Selesai</th>
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
                                    <strong style="color: #111827;">{{ $attempt->student?->user?->name }}</strong>
                                    <small style="display: block; color: var(--muted); font-size: 11px;">
                                        {{ $attempt->student?->classRoom?->name ?? 'Belum ada kelas' }}
                                    </small>
                                </td>
                                <td>{{ $attempt->tryout?->title }}</td>
                                <td>{{ $attempt->tryout?->subject?->code }} - {{ $attempt->tryout?->subject?->name }}</td>
                                <td>{{ ($attempt->submitted_at ?? $attempt->graded_at)?->translatedFormat('d M Y, H:i') }}</td>
                                <td>{{ $attempt->total_questions }} soal</td>
                                <td>
                                    <span style="color: #157a4d; font-weight: 600;">{{ $attempt->correct_answers }}B</span> /
                                    <span style="color: #c63d3d; font-weight: 600;">{{ $attempt->incorrect_answers }}S</span> /
                                    <span style="color: #667085; font-weight: 600;">{{ $attempt->unanswered_questions }}K</span>
                                    <small style="display: block; font-size: 10px; color: var(--muted);">(Benar / Salah / Kosong)</small>
                                </td>
                                <td>{{ $attempt->earned_points }} / {{ $attempt->total_points }}</td>
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
                    {{ $tryoutAttempts->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <span class="empty-icon">✏</span>
                <strong>Belum ada riwayat hasil Try Out</strong>
                <p>Belum ada pengerjaan Try Out yang diselesaikan dan dinilai untuk anak Anda.</p>
            </div>
        @endif
    </section>
@endsection
