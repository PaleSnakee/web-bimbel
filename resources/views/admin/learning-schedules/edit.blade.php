@extends('layouts.admin')
@section('title', 'Edit Jadwal Pembelajaran')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">AKADEMIK / JADWAL PEMBELAJARAN</p>
            <h1>Edit Jadwal Pembelajaran</h1>
            <p>Perbarui informasi, waktu, atau periode berlaku jadwal.</p>
        </div>
    </section>

    <section class="panel form-panel">
        <form method="POST" action="{{ route('admin.learning-schedules.update', $learningSchedule) }}">
            @csrf
            @method('PUT')
            @include('admin.learning-schedules._form')
        </form>
    </section>
@endsection