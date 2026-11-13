@extends('layouts.admin')
@section('title', 'Edit Try Out')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">TRY OUT ONLINE</p>
            <h1>Edit Try Out</h1>
            <p>Perbarui data Try Out berstatus draft.</p>
        </div><a class="button button-secondary" href="{{ route('admin.tryouts.show', $tryout) }}">Kembali</a>
    </section>
    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif
    <section class="panel">
        <form method="POST" action="{{ route('admin.tryouts.update', $tryout) }}">@csrf @method('PUT')
            @include('admin.tryouts._form')<div class="table-actions"><a class="button button-secondary"
                    href="{{ route('admin.tryouts.show', $tryout) }}">Batal</a><button class="button button-primary"
                    type="submit">Simpan Perubahan</button></div>
        </form>
    </section>
@endsection
