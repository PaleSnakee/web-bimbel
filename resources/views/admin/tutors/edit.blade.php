@extends('layouts.admin')
@section('title', 'Edit Tutor')
@section('content')
<section class="page-heading">
    <div>
        <p class="eyebrow">DATA MASTER / MANAJEMEN TUTOR</p>
        <h1>Edit Tutor</h1>
        <p>Perbarui akun dan profil {{ $tutor->user->name }}.</p>
    </div>
</section>

<section class="panel form-panel">
    <form method="POST" action="{{ route('admin.tutors.update', $tutor) }}">
        @csrf
        @method('PUT')
        @include('admin.tutors._form')
    </form>
</section>
@endsection