@extends('layouts.admin')
@section('title', 'Detail Try Out')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">TRY OUT ONLINE</p>
            <h1>{{ $tryout->title }}</h1>
            <p>{{ $tryout->subject->code }} — {{ $tryout->subject->name }}</p>
        </div><a class="button button-secondary" href="{{ route('admin.tryouts.index') }}">Kembali</a>
    </section>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <section class="panel">
        <div class="form-grid">
            <div class="form-group"><strong>Tutor Pengelola</strong>
                <p>{{ $tryout->tutor?->user?->name ?? 'Admin' }}</p>
            </div>
            <div class="form-group"><strong>Status</strong>
                <p>{{ ucfirst($tryout->status) }}</p>
            </div>
            <div class="form-group"><strong>Mulai</strong>
                <p>{{ $tryout->start_at->format('d M Y H:i') }}</p>
            </div>
            <div class="form-group"><strong>Selesai</strong>
                <p>{{ $tryout->end_at->format('d M Y H:i') }}</p>
            </div>
            <div class="form-group"><strong>Durasi</strong>
                <p>{{ $tryout->duration_minutes }} menit</p>
            </div>
            <div class="form-group"><strong>Kelas Peserta</strong>
                <p>{{ $tryout->classRooms->count() }} kelas</p>
            </div>
        </div>
        @if ($tryout->description)
            <p><strong>Deskripsi</strong><br>{{ $tryout->description }}</p>
        @endif
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Kelas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tryout->classRooms as $classRoom)
                        <tr>
                            <td>{{ $classRoom->code }}</td>
                            <td>{{ $classRoom->name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-actions">
            @if ($tryout->status === 'draft')
                <a href="{{ route('admin.tryouts.edit', $tryout) }}">Edit</a>
                <form method="POST" action="{{ route('admin.tryouts.update-status', $tryout) }}">@csrf
                    @method('PATCH')<input type="hidden" name="status" value="published"><button
                        type="submit">Publikasikan</button></form>
                <form method="POST" action="{{ route('admin.tryouts.update-status', $tryout) }}"
                    onsubmit="return confirm('Batalkan Try Out ini?');">@csrf @method('PATCH')<input type="hidden"
                        name="status" value="cancelled"><button type="submit">Batalkan</button></form>
                <form method="POST" action="{{ route('admin.tryouts.destroy', $tryout) }}"
                    onsubmit="return confirm('Hapus draft Try Out ini?');">@csrf @method('DELETE')<button
                        type="submit">Hapus</button></form>
            @elseif($tryout->status === 'published')
                <form method="POST" action="{{ route('admin.tryouts.update-status', $tryout) }}">@csrf
                    @method('PATCH')<input type="hidden" name="status" value="completed"><button
                        type="submit">Selesaikan</button></form>
                <form method="POST" action="{{ route('admin.tryouts.update-status', $tryout) }}"
                    onsubmit="return confirm('Batalkan Try Out ini?');">@csrf @method('PATCH')<input type="hidden"
                        name="status" value="cancelled"><button type="submit">Batalkan</button></form>
            @endif
        </div>
    </section>
@endsection
