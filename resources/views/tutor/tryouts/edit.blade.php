<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Try Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('tutor.tryouts.index') }}">El Renaissance</a>
        </div>
    </nav>
    <main class="container py-4">
        <h1 class="h3">Edit Try Out</h1>
        <p class="text-muted">Perbarui Try Out yang masih berstatus draft.</p>

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('tutor.tryouts.update', $tryout) }}" class="card shadow-sm p-4">
            @csrf
            @method('PUT')
            @include('tutor.tryouts._form')

            <div class="mt-4 d-flex gap-2">
                <a class="btn btn-outline-secondary" href="{{ route('tutor.tryouts.show', $tryout) }}">Batal</a>
                <button class="btn btn-dark" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </main>
</body>
</html>