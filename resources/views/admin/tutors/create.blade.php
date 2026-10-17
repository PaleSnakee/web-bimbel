@extends('layouts.admin')
@section('title', 'Tambah Tutor')
@section('content')
<section class="page-heading">
    <div>
        <p class="eyebrow">DATA MASTER / MANAJEMEN TUTOR</p>
        <h1>Tambah Tutor</h1>
        <p>Buat akun dan lengkapi profil tutor.</p>
    </div>
</section>

<section class="panel form-panel">
    <form method="POST" action="{{ route('admin.tutors.store') }}">
        @csrf
        @include('admin.tutors._form')
    </form>
</section>
@endsection