@extends('layouts.admin')
@section('title', 'Sesi Pembelajaran')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">AKADEMIK</p>
            <h1>Sesi Pembelajaran</h1>
            <p>Catat kegiatan belajar aktual berdasarkan jadwal pembelajaran.</p>
        </div>
        <a class="button button-primary" href="{{ route('admin.learning-sessions.create') }}">+ Tambah Sesi</a>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="panel">
        <form class="filter-bar" method="GET" action="{{ route('admin.learning-sessions.index') }}">
            <input name="search" type="search" value="{{ request('search') }}"
                placeholder="Cari kelas, tutor, mata pelajaran, atau materi">
            <input name="session_date" type="date" value="{{ request('session_date') }}">
            <select name="class_room_id">
                <option value="">Semua kelas</option>
                @foreach ($classRooms as $classRoom)
                    <option value="{{ $classRoom->id }}" @selected((string) request('class_room_id') === (string) $classRoom->id)>
                        {{ $classRoom->code }} — {{ $classRoom->name }}
                    </option>
                @endforeach
            </select>
            <select name="tutor_id">
                <option value="">Semua tutor</option>
                @foreach ($tutors as $tutor)
                    <option value="{{ $tutor->id }}" @selected((string) request('tutor_id') === (string) $tutor->id)>
                        {{ $tutor->user->name }}
                    </option>
                @endforeach
            </select>
            <select name="subject_id">
                <option value="">Semua mata pelajaran</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string) request('subject_id') === (string) $subject->id)>
                        {{ $subject->code }} — {{ $subject->name }}
                    </option>
                @endforeach
            </select>
            <select name="status">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>
            <button class="button button-primary" type="submit">Filter</button>
            @if (request()->hasAny(['search', 'session_date', 'class_room_id', 'tutor_id', 'subject_id', 'status']))
                <a class="button button-secondary" href="{{ route('admin.learning-sessions.index') }}">Reset</a>
            @endif
        </form>

        @if ($sessions->count())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Kelas & Mata Pelajaran</th>
                            <th>Tutor</th>
                            <th>Jadwal</th>
                            <th>Waktu Sesi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sessions as $session)
                            @php($schedule = $session->learningSchedule)
                            <tr>
                                <td>{{ $sessions->firstItem() + $loop->index }}</td>
                                <td>{{ $session->session_date->format('d M Y') }}</td>
                                <td><strong>{{ $schedule->classRoom->code }}</strong><br>{{ $schedule->subject->name }}</td>
                                <td>{{ $schedule->tutor->user->name }}</td>
                                <td>{{ \App\Models\LearningSchedule::dayLabel($schedule->day_of_week) }}<br>{{ $schedule->start_time->format('H:i') }}–{{ $schedule->end_time->format('H:i') }}</td>
                                <td>{{ $session->start_time->format('H:i') }}–{{ $session->end_time->format('H:i') }}</td>
                                <td><span class="badge {{ $session->status === 'completed' ? 'badge-active' : ($session->status === 'cancelled' ? 'badge-inactive' : '') }}">{{ ucfirst($session->status) }}</span></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.learning-sessions.show', $session) }}">Detail</a>
                                        <a href="{{ route('admin.learning-sessions.edit', $session) }}">Edit</a>
                                        @if ($session->status === 'scheduled')
                                            <form method="POST" action="{{ route('admin.learning-sessions.update-status', $session) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit">Selesaikan</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.learning-sessions.update-status', $session) }}" onsubmit="return confirm('Batalkan sesi ini?');">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="cancelled">
                                                <button type="submit">Batalkan</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.learning-sessions.destroy', $session) }}" onsubmit="return confirm('Hapus sesi pembelajaran ini? Tindakan ini tidak dapat dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $sessions->links() }}</div>
        @else
            <div class="empty-state">
                <span class="empty-icon">▤</span>
                <strong>Belum ada sesi pembelajaran</strong>
                <p>Tambahkan sesi aktual berdasarkan jadwal pembelajaran yang aktif.</p>
                <a class="button button-primary" href="{{ route('admin.learning-sessions.create') }}">+ Tambah Sesi</a>
            </div>
        @endif
    </section>
@endsection