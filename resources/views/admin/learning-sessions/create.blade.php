@extends('layouts.admin')
@section('title', 'Tambah Sesi Pembelajaran')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">AKADEMIK / SESI PEMBELAJARAN</p>
            <h1>Tambah Sesi Pembelajaran</h1>
            <p>Catat kegiatan belajar aktual dari jadwal pembelajaran yang aktif.</p>
        </div>
    </section>

    <section class="panel form-panel">
        <form method="POST" action="{{ route('admin.learning-sessions.store') }}">
            @csrf
            @include('admin.learning-sessions._form')
        </form>
    </section>
@endsection