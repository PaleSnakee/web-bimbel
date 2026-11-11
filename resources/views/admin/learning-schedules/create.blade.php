@extends('layouts.admin')
@section('title', 'Tambah Jadwal Pembelajaran')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">AKADEMIK / JADWAL PEMBELAJARAN</p>
            <h1>Tambah Jadwal Pembelajaran</h1>
            <p>Hubungkan kelas, tutor, dan mata pelajaran dalam jadwal yang berlaku.</p>
        </div>
    </section>

    <section class="panel form-panel">
        <form method="POST" action="{{ route('admin.learning-schedules.store') }}">
            @csrf
            @include('admin.learning-schedules._form')
        </form>
    </section>
@endsection