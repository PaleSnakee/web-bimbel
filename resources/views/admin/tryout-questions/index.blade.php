@extends('layouts.admin')

@section('title', 'Bank Soal Try Out')

@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">TRY OUT ONLINE</p>
            <h1>Bank Soal: {{ $tryout->title }}</h1>
            <p>{{ $tryout->subject->code }} — {{ $tryout->subject->name }} · Status: {{ ucfirst($tryout->status) }}</p>
        </div>
        <div class="table-actions">
            <a class="button button-secondary" href="{{ route('admin.tryouts.show', $tryout) }}">Kembali</a>
            @if ($tryout->status === 'draft')
                <a class="button button-primary" href="{{ route('admin.tryouts.questions.create', $tryout) }}">+ Tambah Soal</a>
            @endif
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="panel">
        @if ($questions->count())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Urutan</th>
                            <th>Pertanyaan dan Opsi</th>
                            <th>Bobot</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($questions as $question)
                            <tr>
                                <td>{{ $question->order_number }}</td>
                                <td>
                                    <strong>{{ $question->question_text }}</strong>
                                    <ol type="A">
                                        @foreach ($question->options as $option)
                                            <li>
                                                {{ $option->option_text }}
                                                @if ($option->is_correct)
                                                    <strong>(Jawaban benar)</strong>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ol>
                                </td>
                                <td>{{ $question->points }}</td>
                                <td>
                                    @if ($tryout->status === 'draft')
                                        <div class="table-actions">
                                            <a href="{{ route('admin.tryouts.questions.edit', [$tryout, $question]) }}">Edit</a>
                                            <form method="POST"
                                                action="{{ route('admin.tryouts.questions.destroy', [$tryout, $question]) }}"
                                                onsubmit="return confirm('Hapus soal ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit">Hapus</button>
                                            </form>
                                        </div>
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap">{{ $questions->links() }}</div>
        @else
            <div class="empty-state">
                <strong>Belum ada soal</strong>
                <p>Tambahkan soal pilihan ganda untuk Try Out ini.</p>
                @if ($tryout->status === 'draft')
                    <a class="button button-primary" href="{{ route('admin.tryouts.questions.create', $tryout) }}">+ Tambah Soal</a>
                @endif
            </div>
        @endif
    </section>
@endsection