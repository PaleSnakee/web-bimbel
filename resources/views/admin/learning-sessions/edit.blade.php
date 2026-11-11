@extends('layouts.admin')
@section('title', 'Edit Sesi Pembelajaran')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">AKADEMIK / SESI PEMBELAJARAN</p>
            <h1>Edit Sesi Pembelajaran</h1>
            <p>Perbarui informasi sesi aktual.</p>
        </div>
    </section>

    <section class="panel form-panel">
        <form method="POST" action="{{ route('admin.learning-sessions.update', $learningSession) }}">
            @csrf
            @method('PUT')
            @include('admin.learning-sessions._form')
        </form>
    </section>
@endsection