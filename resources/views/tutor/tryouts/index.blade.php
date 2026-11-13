<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Try Out Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('tutor.dashboard') }}">El Renaissance</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-light btn-sm">Logout</button></form>
        </div>
    </nav>
    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div><h1 class="h3 mb-1">Try Out Online</h1><p class="text-muted mb-0">Kelola Try Out yang Anda buat atau kelola.</p></div>
            <a class="btn btn-dark" href="{{ route('tutor.tryouts.create') }}">+ Tambah Try Out</a>
        </div>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <div class="card shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead><tr><th>Judul</th><th>Mata Pelajaran</th><th>Kelas</th><th>Pelaksanaan</th><th>Durasi</th><th>Status</th><th></th></tr></thead>
            <tbody>@forelse($tryouts as $tryout)<tr><td><strong>{{ $tryout->title }}</strong></td><td>{{ $tryout->subject->name }}</td><td>{{ $tryout->class_rooms_count }}</td><td>{{ $tryout->start_at->format('d M Y H:i') }}</td><td>{{ $tryout->duration_minutes }} menit</td><td>{{ ucfirst($tryout->status) }}</td><td><a class="btn btn-sm btn-outline-dark" href="{{ route('tutor.tryouts.show', $tryout) }}">Detail</a></td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-4">Belum ada Try Out.</td></tr>@endforelse</tbody>
        </table></div></div>
        <div class="mt-3">{{ $tryouts->links() }}</div>
    </main>
</body>
</html>
