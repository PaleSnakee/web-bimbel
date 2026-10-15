@extends('layouts.admin')
@section('title', 'Edit Kelas')
@section('content')
<section class="page-heading"><div><p class="eyebrow">DATA MASTER / MANAJEMEN KELAS</p><h1>Edit Kelas</h1><p>Perbarui informasi untuk kelas {{ $classRoom->code }}.</p></div></section>
<section class="panel form-panel"><form method="POST" action="{{ route('admin.classes.update', $classRoom) }}">@csrf @method('PUT') @include('admin.classes._form')</form></section>
@endsection
