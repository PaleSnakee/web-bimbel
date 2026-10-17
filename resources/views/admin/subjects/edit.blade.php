@extends('layouts.admin')
@section('title', 'Edit Mata Pelajaran')
@section('content')
<section class="page-heading">
    <div>
        <p class="eyebrow">DATA MASTER / MATA PELAJARAN</p>
        <h1>Edit Mata Pelajaran</h1>
        <p>Perbarui informasi untuk mata pelajaran {{ $subject->code }}.</p>
    </div>
</section>

<section class="panel form-panel">
    <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
        @csrf
        @method('PUT')
        @include('admin.subjects._form')
    </form>
</section>
@endsection