@extends('layouts.admin')

@section('title', 'Rekap Hasil Try Out')

@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">AKADEMIK</p>
            <h1>Rekap Hasil Try Out</h1>
            <p>Lihat hasil penilaian Try Out siswa yang telah selesai.</p>
        </div>
    </section>

    <section class="panel">
        <form class="filter-bar" method="GET" action="{{ route('admin.tryout-results.index') }}">
            <input name="search" type="search" value="{{ request('search') }}" placeholder="Cari nama siswa">
            <select name="tryout_id">
                <option value="">Semua Try Out</option>
                @foreach ($tryouts as $tryout)
                    <option value="{{ $tryout->id }}" @selected((string) request('tryout_id') === (string) $tryout->id)>{{ $tryout->title }}</option>
                @endforeach
            </select>
            <select name="class_room_id">
                <option value="">Semua kelas</option>
                @foreach ($classRooms as $classRoom)
                    <option value="{{ $classRoom->id }}" @selected((string) request('class_room_id') === (string) $classRoom->id)>{{ $classRoom->code }} — {{ $classRoom->name }}</option>
                @endforeach
            </select>
            <input name="min_score" type="number" min="0" max="100" step="0.01" value="{{ request('min_score') }}" placeholder="Nilai minimum">
            <input name="max_score" type="number" min="0" max="100" step="0.01" value="{{ request('max_score') }}" placeholder="Nilai maksimum">
            <button class="button button-primary" type="submit">Filter</button>
            @if (request()->hasAny(['search', 'tryout_id', 'class_room_id', 'min_score', 'max_score']))
                <a class="button button-secondary" href="{{ route('admin.tryout-results.index') }}">Reset</a>
            @endif
        </form>

        @if ($results->count())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Try Out</th>
                            <th>Nilai</th>
                            <th>Benar / Salah / Kosong</th>
                            <th>Waktu Pengerjaan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($results as $result)
                            <tr>
                                <td><strong>{{ $result->student->user->name }}</strong><br><small>{{ $result->student->nis }}</small></td>
                                <td>{{ $result->student->classRoom?->name ?? '-' }}</td>
                                <td>{{ $result->tryout->title }}<br><small>{{ $result->tryout->subject?->name }}</small></td>
                                <td><strong>{{ rtrim(rtrim($result->final_score, '0'), '.') }}</strong></td>
                                <td>{{ $result->correct_answers }} / {{ $result->incorrect_answers }} / {{ $result->unanswered_questions }}</td>
                                <td>{{ $result->started_at->format('d M Y H:i') }}<br>{{ ($result->submitted_at ?? $result->graded_at)->format('d M Y H:i') }}</td>
                                <td><span class="badge {{ $result->status === 'submitted' ? 'badge-active' : 'badge-inactive' }}">{{ $result->status === 'submitted' ? 'Dikumpulkan' : 'Waktu habis' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $results->links() }}</div>
        @else
            <div class="empty-state">
                <strong>Belum ada hasil Try Out</strong>
                <p>Hasil akan muncul setelah peserta menyelesaikan dan sistem menilai Try Out.</p>
            </div>
        @endif
    </section>
@endsection