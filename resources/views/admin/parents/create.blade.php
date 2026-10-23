@extends('layouts.admin')
@section('title', 'Tambah Orang Tua')
@section('content')
<section class="page-heading">
    <div>
        <p class="eyebrow">DATA MASTER / MANAJEMEN ORANG TUA</p>
        <h1>Tambah Orang Tua</h1>
        <p>Buat akun dan lengkapi profil orang tua siswa.</p>
    </div>
</section>

<section class="panel form-panel">
    <form method="POST" action="{{ route('admin.parents.store') }}">
        @csrf
        @include('admin.parents._form')
    </form>
</section>
@endsection