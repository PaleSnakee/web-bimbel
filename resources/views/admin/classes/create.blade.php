@extends('layouts.admin')
@section('title', 'Tambah Kelas')
@section('content')
<section class="page-heading"><div><p class="eyebrow">DATA MASTER / MANAJEMEN KELAS</p><h1>Tambah Kelas</h1><p>Lengkapi informasi kelas bimbingan belajar.</p></div></section>
<section class="panel form-panel"><form method="POST" action="{{ route('admin.classes.store') }}">@csrf @include('admin.classes._form')</form></section>
@endsection
