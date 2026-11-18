<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Hasil Try Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('tutor.dashboard') }}">El Renaissance</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-light btn-sm" type="submit">Logout</button>
            </form>
        </div>
    </nav>

    <main class="container py-4">
        <div class="mb-4">
            <h1 class="h3 mb-1">Rekap Hasil Try Out</h1>
            <p class="text-muted mb-0">Lihat hasil Try Out yang menjadi tanggung jawab Anda.</p>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form class="row g-2" method="GET" action="{{ route('tutor.tryout-results.index') }}">
                    <div class="col-md-3">
                        <input class="form-control" name="search" type="search" value="{{ request('search') }}"
                            placeholder="Cari nama siswa">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="tryout_id">
                            <option value="">Semua Try Out</option>
                            @foreach ($tryouts as $tryout)
                                <option value="{{ $tryout->id }}" @selected((string) request('tryout_id') === (string) $tryout->id)>{{ $tryout->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="class_room_id">
                            <option value="">Semua kelas</option>
                            @foreach ($classRooms as $classRoom)
                                <option value="{{ $classRoom->id }}" @selected((string) request('class_room_id') === (string) $classRoom->id)>{{ $classRoom->code }}
                                    — {{ $classRoom->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <button class="btn btn-dark" type="submit">Filter</button>
                        @if (request()->hasAny(['search', 'tryout_id', 'class_room_id']))
                            <a class="btn btn-outline-secondary"
                                href="{{ route('tutor.tryout-results.index') }}">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
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
                        @forelse ($results as $result)
                            <tr>
                                <td><strong>{{ $result->student->user->name }}</strong><br><small
                                        class="text-muted">{{ $result->student->nis }}</small></td>
                                <td>{{ $result->student->classRoom?->name ?? '-' }}</td>
                                <td>{{ $result->tryout->title }}<br><small
                                        class="text-muted">{{ $result->tryout->subject?->name }}</small></td>
                                <td><strong>{{ rtrim(rtrim($result->final_score, '0'), '.') }}</strong></td>
                                <td>{{ $result->correct_answers }} / {{ $result->incorrect_answers }} /
                                    {{ $result->unanswered_questions }}</td>
                                <td>{{ $result->started_at->format('d M Y H:i') }}<br>{{ ($result->submitted_at ?? $result->graded_at)->format('d M Y H:i') }}
                                </td>
                                <td>{{ $result->status === 'submitted' ? 'Dikumpulkan' : 'Waktu habis' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 text-center text-muted">Belum ada hasil Try Out.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $results->links() }}</div>
    </main>
</body>

</html>
