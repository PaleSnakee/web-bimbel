@extends('layouts.admin')
@section('title', 'Try Out Online')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">AKADEMIK</p>
            <h1>Try Out Online</h1>
            <p>Kelola jadwal, peserta kelas, dan status Try Out.</p>
        </div>
        <a class="button button-primary" href="{{ route('admin.tryouts.create') }}">+ Tambah Try Out</a>
    </section>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
    <section class="panel">
        <form class="filter-bar" method="GET" action="{{ route('admin.tryouts.index') }}">
            <input name="search" type="search" value="{{ request('search') }}" placeholder="Cari judul Try Out">
            <select name="subject_id">
                <option value="">Semua mata pelajaran</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string) request('subject_id') === (string) $subject->id)>{{ $subject->code }} —
                        {{ $subject->name }}</option>
                @endforeach
            </select>
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
            <select name="status">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <input type="date" name="start_date" value="{{ request('start_date') }}" aria-label="Tanggal mulai">
            <input type="date" name="end_date" value="{{ request('end_date') }}" aria-label="Tanggal akhir">
            <button class="button button-primary" type="submit">Filter</button>
            @if (request()->hasAny(['search', 'subject_id', 'class_room_id', 'tutor_id', 'status', 'start_date', 'end_date']))
                <a class="button button-secondary" href="{{ route('admin.tryouts.index') }}">Reset</a>
            @endif
        </form>
        @if ($tryouts->count())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Mata Pelajaran</th>
                            <th>Tutor</th>
                            <th>Kelas</th>
                            <th>Pelaksanaan</th>
                            <th>Durasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tryouts as $tryout)
                            <tr>
                                <td><strong>{{ $tryout->title }}</strong></td>
                                <td>{{ $tryout->subject->name }}</td>
                                <td>{{ $tryout->tutor?->user?->name ?? 'Admin' }}</td>
                                <td>{{ $tryout->class_rooms_count }}</td>
                                <td>{{ $tryout->start_at->format('d M Y H:i') }}<br>{{ $tryout->end_at->format('d M Y H:i') }}
                                </td>
                                <td>{{ $tryout->duration_minutes }} menit</td>
                                <td><span
                                        class="badge {{ $tryout->status === 'published' ? 'badge-active' : ($tryout->status === 'cancelled' ? 'badge-inactive' : '') }}">{{ ucfirst($tryout->status) }}</span>
                                </td>
                                <td>
                                    <div class="table-actions"><a
                                            href="{{ route('admin.tryouts.show', $tryout) }}">Detail</a>
                                        @if ($tryout->status === 'draft')
                                            <a href="{{ route('admin.tryouts.edit', $tryout) }}">Edit</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $tryouts->links() }}</div>
        @else
            <div class="empty-state"><strong>Belum ada Try Out</strong>
                <p>Tambahkan Try Out untuk mengatur ujian online berikutnya.</p><a class="button button-primary"
                    href="{{ route('admin.tryouts.create') }}">+ Tambah Try Out</a>
            </div>
        @endif
    </section>
@endsection
