@extends('layouts.admin')
@section('title', 'Tambah Siswa')
@section('content')
<section class="page-heading">
    <div>
        <p class="eyebrow">DATA MASTER / MANAJEMEN SISWA</p>
        <h1>Tambah Siswa</h1>
        <p>Buat akun, profil, penempatan kelas, dan hubungan orang tua/wali siswa.</p>
    </div>
</section>

<section class="panel form-panel">
    <form method="POST" action="{{ route('admin.students.store') }}">
        @csrf
        @include('admin.students._form')
    </form>
</section>
@endsection