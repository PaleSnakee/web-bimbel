@extends('layouts.admin')
@section('title', 'Jadwal Pembelajaran')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">AKADEMIK</p>
            <h1>Jadwal Pembelajaran</h1>
            <p>Kelola jadwal kelas, tutor, dan mata pelajaran.</p>
        </div>
        <a class="button button-primary" href="{{ route('admin.learning-schedules.create') }}">+ Tambah Jadwal</a>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <section class="panel">
        <form class="filter-bar" method="GET" action="{{ route('admin.learning-schedules.index') }}">
            <input name="search" type="search" value="{{ request('search') }}"
                placeholder="Cari kelas, tutor, atau mata pelajaran">
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
                        {{ $status === 'active' ? 'Aktif' : 'Nonaktif' }}
                    </option>
                @endforeach
            </select>
            <button class="button button-primary" type="submit">Filter</button>
            @if (request()->hasAny(['search', 'class_room_id', 'tutor_id', 'subject_id', 'status']))
                <a class="button button-secondary" href="{{ route('admin.learning-schedules.index') }}">Reset</a>
            @endif
        </form>

        @if ($schedules->count())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Kelas</th>
                            <th>Tutor</th>
                            <th>Mata Pelajaran</th>
                            <th>Hari & Jam</th>
                            <th>Periode Berlaku</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schedules as $schedule)
                            <tr>
                                <td>{{ $schedules->firstItem() + $loop->index }}</td>
                                <td><strong>{{ $schedule->classRoom->code }}</strong><br>{{ $schedule->classRoom->name }}
                                </td>
                                <td>{{ $schedule->tutor->user->name }}</td>
                                <td><strong>{{ $schedule->subject->code }}</strong><br>{{ $schedule->subject->name }}</td>
                                <td>{{ \App\Models\LearningSchedule::dayLabel($schedule->day_of_week) }}<br>{{ $schedule->start_time->format('H:i') }}–{{ $schedule->end_time->format('H:i') }}
                                </td>
                                <td>{{ $schedule->effective_from->format('d M Y') }}<br>{{ $schedule->effective_until?->format('d M Y') ?? 'Tanpa batas' }}
                                </td>
                                <td><span
                                        class="badge {{ $schedule->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $schedule->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('admin.learning-schedules.show', $schedule) }}">Detail</a>
                                        <a href="{{ route('admin.learning-schedules.edit', $schedule) }}">Edit</a>
                                        @if ($schedule->status === 'active')
                                            <form method="POST"
                                                action="{{ route('admin.learning-schedules.deactivate', $schedule) }}"
                                                onsubmit="return confirm('Nonaktifkan jadwal ini? Jadwal tidak dapat digunakan untuk sesi pembelajaran baru.');">
                                                @csrf
                                                <button type="submit">Nonaktifkan</button>
                                            </form>
                                        @else
                                            <form method="POST"
                                                action="{{ route('admin.learning-schedules.activate', $schedule) }}">
                                                @csrf
                                                <button type="submit">Aktifkan</button>
                                            </form>
                                        @endif
                                        <form method="POST"
                                            action="{{ route('admin.learning-schedules.destroy', $schedule) }}"
                                            onsubmit="return confirm('Hapus jadwal ini? Tindakan ini tidak dapat dibatalkan.');">
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
            <div class="pagination-wrap">{{ $schedules->links() }}</div>
        @else
            <div class="empty-state">
                <span class="empty-icon">▤</span>
                <strong>Belum ada jadwal pembelajaran</strong>
                <p>Tambahkan jadwal untuk menghubungkan kelas, tutor, dan mata pelajaran.</p>
                <a class="button button-primary" href="{{ route('admin.learning-schedules.create') }}">+ Tambah Jadwal</a>
            </div>
        @endif
    </section>
@endsection
