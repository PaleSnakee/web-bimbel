<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Try Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container"><a class="navbar-brand" href="{{ route('tutor.tryouts.index') }}">El Renaissance</a></div>
    </nav>
    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h1 class="h3 mb-1">{{ $tryout->title }}</h1>
                <p class="text-muted mb-0">{{ $tryout->subject->code }} — {{ $tryout->subject->name }}</p>
            </div>
            <a class="btn btn-outline-secondary" href="{{ route('tutor.tryouts.index') }}">Kembali</a>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <div class="card shadow-sm"><div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><strong>Status</strong><p>{{ ucfirst($tryout->status) }}</p></div>
                <div class="col-md-4"><strong>Mulai</strong><p>{{ $tryout->start_at->format('d M Y H:i') }}</p></div>
                <div class="col-md-4"><strong>Selesai</strong><p>{{ $tryout->end_at->format('d M Y H:i') }}</p></div>
                <div class="col-md-4"><strong>Durasi</strong><p>{{ $tryout->duration_minutes }} menit</p></div>
                <div class="col-md-8"><strong>Kelas Peserta</strong><p>{{ $tryout->classRooms->pluck('name')->join(', ') }}</p></div>
            </div>
            @if($tryout->description)<hr><strong>Deskripsi</strong><p class="mb-0">{{ $tryout->description }}</p>@endif
            @if($tryout->status === 'draft')<hr><a class="btn btn-dark" href="{{ route('tutor.tryouts.edit', $tryout) }}">Edit Try Out</a>@endif
        </div></div>
    </main>
</body>
</html>