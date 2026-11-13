<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container"><a class="navbar-brand" href="{{ route('tutor.dashboard') }}">El Renaissance</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button
                    class="btn btn-outline-light btn-sm">Logout</button></form>
        </div>
    </nav>
    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Absensi Siswa</h1>
                <p class="text-muted mb-0">Kelola absensi pada sesi pembelajaran Anda.</p>
            </div><a class="btn btn-outline-secondary" href="{{ route('tutor.dashboard') }}">Dashboard</a>
        </div>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kelas</th>
                            <th>Mata Pelajaran</th>
                            <th>Waktu</th>
                            <th>Status Sesi</th>
                            <th>Absensi</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                            @php($schedule = $session->learningSchedule)
                            <tr>
                                <td>{{ $session->session_date->format('d M Y') }}</td>
                                <td>{{ $schedule->classRoom->code }} — {{ $schedule->classRoom->name }}</td>
                                <td>{{ $schedule->subject->name }}</td>
                                <td>{{ $session->start_time->format('H:i') }}–{{ $session->end_time->format('H:i') }}
                                </td>
                                <td>{{ ucfirst($session->status) }}</td>
                                <td>{{ $session->status === 'cancelled' ? 'Dibatalkan' : ($session->attendances_count > 0 ? 'Sudah diisi (' . $session->attendances_count . ')' : 'Belum diisi') }}
                                </td>
                                <td>
                                    @if ($session->status !== 'cancelled')
                                        <a class="btn btn-sm btn-dark"
                                            href="{{ route('tutor.attendances.edit', $session) }}">Isi / Ubah</a>
                                    @endif
                                </td>
                        </tr>@empty<tr>
                                <td colspan="7" class="text-center text-muted py-4">Belum ada sesi pembelajaran untuk
                                    Anda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-3">{{ $sessions->links() }}</div>
    </main>
</body>

</html>
