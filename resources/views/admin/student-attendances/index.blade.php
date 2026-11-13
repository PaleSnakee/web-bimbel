@extends('layouts.admin')
@section('title', 'Absensi Siswa')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">AKADEMIK</p>
            <h1>Absensi Siswa</h1>
            <p>Pantau kehadiran siswa berdasarkan sesi pembelajaran aktual.</p>
        </div>
    </section>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <section class="panel">
        <form class="filter-bar" method="GET" action="{{ route('admin.student-attendances.index') }}">
            <input type="date" name="session_date" value="{{ request('session_date') }}">
            <select name="class_room_id">
                <option value="">Semua kelas</option>
                @foreach ($classRooms as $classRoom)
                    <option value="{{ $classRoom->id }}" @selected((string) request('class_room_id') === (string) $classRoom->id)>{{ $classRoom->code }} —
                        {{ $classRoom->name }}</option>
                @endforeach
            </select>
            <select name="tutor_id">
                <option value="">Semua tutor</option>
                @foreach ($tutors as $tutor)
                    <option value="{{ $tutor->id }}" @selected((string) request('tutor_id') === (string) $tutor->id)>{{ $tutor->user->name }}</option>
                @endforeach
            </select>
            <select name="subject_id">
                <option value="">Semua mata pelajaran</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string) request('subject_id') === (string) $subject->id)>{{ $subject->code }} —
                        {{ $subject->name }}</option>
                @endforeach
            </select>
            <select name="status">
                <option value="">Semua status hadir</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button class="button button-primary" type="submit">Filter</button>
            @if (request()->hasAny(['session_date', 'class_room_id', 'tutor_id', 'subject_id', 'status']))
                <a class="button button-secondary" href="{{ route('admin.student-attendances.index') }}">Reset</a>
            @endif
        </form>
        @if ($attendances->count())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Kelas & Mapel</th>
                            <th>Tutor</th>
                            <th>Status</th>
                            <th>Pencatat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendances as $attendance)
                            @php($schedule = $attendance->learningSession->learningSchedule)<tr>
                                <td>{{ $attendance->learningSession->session_date->format('d M Y') }}</td>
                                <td><strong>{{ $attendance->student->nis }}</strong><br>{{ $attendance->student->user->name }}
                                </td>
                                <td>{{ $schedule->classRoom->code }}<br>{{ $schedule->subject->name }}</td>
                                <td>{{ $schedule->tutor->user->name }}</td>
                                <td>{{ ucfirst($attendance->status) }}</td>
                                <td>{{ $attendance->recorder?->name ?? '-' }}</td>
                                <td><a href="{{ route('admin.student-attendances.show', $attendance->learningSession) }}">Detail
                                        sesi</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $attendances->links() }}</div>
        @else
            <div class="empty-state"><strong>Belum ada data absensi</strong>
                <p>Data akan tampil setelah tutor atau admin mencatat absensi sesi.</p>
            </div>
        @endif
    </section>
@endsection
