<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Soal Try Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('tutor.tryouts.questions.index', $tryout) }}">El Renaissance</a>
        </div>
    </nav>
    <main class="container py-4">
        <h1 class="h3">Edit Soal Nomor {{ $question->order_number }}</h1>
        <p class="text-muted">{{ $tryout->title }}</p>

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('tutor.tryouts.questions.update', [$tryout, $question]) }}"
            class="card shadow-sm p-4">
            @csrf
            @method('PUT')
            @include('tutor.tryout-questions._form')

            <div class="mt-4 d-flex gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('tutor.tryouts.questions.index', $tryout) }}">Batal</a>
                <button class="btn btn-dark" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </main>
</body>
</html>