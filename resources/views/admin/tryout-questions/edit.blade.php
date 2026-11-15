@extends('layouts.admin')

@section('title', 'Edit Soal Try Out')

@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">BANK SOAL</p>
            <h1>Edit Soal Nomor {{ $question->order_number }}</h1>
            <p>{{ $tryout->title }}</p>
        </div>
        <a class="button button-secondary" href="{{ route('admin.tryouts.questions.index', $tryout) }}">Kembali</a>
    </section>

    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <section class="panel">
        <form method="POST" action="{{ route('admin.tryouts.questions.update', [$tryout, $question]) }}">
            @csrf
            @method('PUT')
            @include('admin.tryout-questions._form')

            <div class="table-actions">
                <a class="button button-secondary" href="{{ route('admin.tryouts.questions.index', $tryout) }}">Batal</a>
                <button class="button button-primary" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </section>
@endsection