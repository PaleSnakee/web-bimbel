@extends('layouts.admin')
@section('title', 'Edit Orang Tua')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">DATA MASTER / MANAJEMEN ORANG TUA</p>
            <h1>Edit Orang Tua</h1>
            <p>Perbarui akun dan profil {{ $guardian->user->name }}.</p>
        </div>
    </section>

    <section class="panel form-panel">
        <form method="POST" action="{{ route('admin.parents.update', $guardian) }}">
            @csrf
            @method('PUT')
            @include('admin.parents._form')
        </form>
    </section>
@endsection
