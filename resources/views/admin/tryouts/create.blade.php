@extends('layouts.admin')
@section('title', 'Tambah Try Out')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">TRY OUT ONLINE</p>
            <h1>Tambah Try Out</h1>
            <p>Try Out baru akan disimpan sebagai draft.</p>
        </div><a class="button button-secondary" href="{{ route('admin.tryouts.index') }}">Kembali</a>
    </section>
    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif
    <section class="panel">
        <form method="POST" action="{{ route('admin.tryouts.store') }}">@csrf @include('admin.tryouts._form')<div
                class="table-actions"><a class="button button-secondary"
                    href="{{ route('admin.tryouts.index') }}">Batal</a><button class="button button-primary"
                    type="submit">Simpan Draft</button></div>
        </form>
    </section>
@endsection
