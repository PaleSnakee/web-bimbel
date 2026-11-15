<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Soal Try Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container"><a class="navbar-brand" href="{{ route('tutor.tryouts.show', $tryout) }}">El Renaissance</a></div>
    </nav>
    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="h3 mb-1">Bank Soal: {{ $tryout->title }}</h1>
                <p class="text-muted mb-0">{{ $tryout->subject->code }} — {{ $tryout->subject->name }} · {{ ucfirst($tryout->status) }}</p>
            </div>
            <div>
                <a class="btn btn-outline-secondary" href="{{ route('tutor.tryouts.show', $tryout) }}">Kembali</a>
                @if ($tryout->status === 'draft')
                    <a class="btn btn-dark" href="{{ route('tutor.tryouts.questions.create', $tryout) }}">+ Tambah Soal</a>
                @endif
            </div>
        </div>
        @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <div class="card shadow-sm">
            @if ($questions->count())
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Urutan</th><th>Pertanyaan dan Opsi</th><th>Bobot</th><th>Aksi</th></tr></thead>
                        <tbody>
                            @foreach ($questions as $question)
                                <tr>
                                    <td>{{ $question->order_number }}</td>
                                    <td><strong>{{ $question->question_text }}</strong><ol type="A">@foreach ($question->options as $option)<li>{{ $option->option_text }} @if($option->is_correct)<strong>(Jawaban benar)</strong>@endif</li>@endforeach</ol></td>
                                    <td>{{ $question->points }}</td>
                                    <td>
                                        @if ($tryout->status === 'draft')
                                            <a class="btn btn-sm btn-outline-dark" href="{{ route('tutor.tryouts.questions.edit', [$tryout, $question]) }}">Edit</a>
                                            <form class="d-inline" method="POST" action="{{ route('tutor.tryouts.questions.destroy', [$tryout, $question]) }}" onsubmit="return confirm('Hapus soal ini?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button></form>
                                        @else - @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">{{ $questions->links() }}</div>
            @else
                <div class="card-body text-center text-muted py-5">
                    <strong>Belum ada soal</strong><p class="mb-3">Tambahkan soal pilihan ganda untuk Try Out ini.</p>
                    @if ($tryout->status === 'draft')<a class="btn btn-dark" href="{{ route('tutor.tryouts.questions.create', $tryout) }}">+ Tambah Soal</a>@endif
                </div>
            @endif
        </div>
    </main>
</body>
</html>