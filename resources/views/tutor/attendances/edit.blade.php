<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Isi Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('tutor.attendances.index') }}">El Renaissance</a>
        </div>
    </nav>

    <main class="container py-4">
        @php($schedule = $learningSession->learningSchedule)

        <h1 class="h3">Isi Absensi</h1>
        <p class="text-muted">
            {{ $learningSession->session_date->format('d M Y') }} ·
            {{ $schedule->classRoom->name }} ·
            {{ $schedule->subject->name }}
        </p>

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        @if ($learningSession->status === 'cancelled')
            <div class="alert alert-warning">Sesi dibatalkan dan tidak dapat menerima absensi.</div>
        @else
            <form method="POST" action="{{ route('tutor.attendances.update', $learningSession) }}">
                @csrf
                @method('PUT')

                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>NIS</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $studentIndex => $student)
                                    @php($attendance = $learningSession->attendances->firstWhere('student_id', $student->id))
                                    <tr>
                                        <td>
                                            {{ $student->nis }}
                                            <input type="hidden" name="attendances[{{ $studentIndex }}][student_id]"
                                                value="{{ $student->id }}">
                                        </td>
                                        <td>{{ $student->user->name }}</td>
                                        <td>
                                            <select class="form-select"
                                                name="attendances[{{ $studentIndex }}][status]">
                                                @foreach (\App\Models\StudentAttendance::STATUSES as $status)
                                                    <option value="{{ $status }}" @selected(old("attendances.{$studentIndex}.status", $attendance?->status ?? 'present') === $status)>
                                                        {{ ucfirst($status) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input class="form-control" maxlength="1000"
                                                name="attendances[{{ $studentIndex }}][notes]"
                                                value="{{ old("attendances.{$studentIndex}.notes", $attendance?->notes) }}"
                                                placeholder="Opsional">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <a class="btn btn-outline-secondary" href="{{ route('tutor.attendances.index') }}">Batal</a>
                    <button class="btn btn-dark" type="submit">Simpan Absensi</button>
                </div>
            </form>
        @endif
    </main>
</body>

</html>
