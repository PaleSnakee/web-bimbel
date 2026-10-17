@extends('layouts.admin')
@section('title', 'Tambah Mata Pelajaran')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">DATA MASTER / MATA PELAJARAN</p>
            <h1>Tambah Mata Pelajaran</h1>
            <p>Lengkapi informasi mata pelajaran yang akan dikelola.</p>
        </div>
    </section>

    <section class="panel form-panel">
        <form method="POST" action="{{ route('admin.subjects.store') }}">
            @csrf
            @include('admin.subjects._form')
        </form>
    </section>
@endsection
