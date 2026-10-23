@extends('layouts.admin')
@section('title', 'Edit Siswa')
@section('content')
<section class="page-heading">
    <div>
        <p class="eyebrow">DATA MASTER / MANAJEMEN SISWA</p>
        <h1>Edit Siswa</h1>
        <p>Perbarui profil, kelas, dan orang tua/wali {{ $student->user->name }}.</p>
    </div>
</section>

<section class="panel form-panel">
    <form method="POST" action="{{ route('admin.students.update', $student) }}">
        @csrf
        @method('PUT')
        @include('admin.students._form')
    </form>
</section>
@endsection