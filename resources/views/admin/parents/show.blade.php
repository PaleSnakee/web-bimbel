**Applying suggested code reformatting****Applying suggested code reformatting**@extends('layouts.admin')
@section('title', 'Detail Orang Tua')
@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">DATA MASTER / MANAJEMEN ORANG TUA</p>
            <h1>{{ $guardian->user->name }}</h1>
            <p>Detail akun, profil, dan siswa yang terhubung.</p>
        </div>
        <div class="heading-actions">
            <a class="button button-secondary" href="{{ route('admin.parents.index') }}">Kembali</a>
            <a class="button button-primary" href="{{ route('admin.parents.edit', $guardian) }}">Edit Orang Tua</a>
        </div>
    </section>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <section class="detail-grid">
        <article class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Informasi Orang Tua</h2>
                    <p>Data akun dan profil orang tua.</p>
                </div>
                <span
                    class="badge {{ $guardian->user->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $guardian->user->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span>
            </div>
            <dl class="detail-list">
                <div>
                    <dt>Nama Lengkap</dt>
                    <dd>{{ $guardian->user->name }}</dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd>{{ $guardian->user->email }}</dd>
                </div>
                <div>
                    <dt>Nomor Telepon</dt>
                    <dd>{{ $guardian->phone ?: '—' }}</dd>
                </div>
                <div>
                    <dt>Status Akun</dt>
                    <dd>{{ $guardian->user->status === 'active' ? 'Aktif' : 'Nonaktif' }}</dd>
                </div>
                <div class="detail-full">
                    <dt>Alamat</dt>
                    <dd>{{ $guardian->address ?: '—' }}</dd>
                </div>
            </dl>
        </article>

        <article class="panel">
            <div class="panel-heading">
                <div>
                    <h2>Anak Terhubung</h2>
                    <p>Daftar siswa yang tersambung ke akun ini.</p>
                </div><span class="panel-tag">{{ $guardian->students->count() }} siswa</span>
            </div>
            @if ($guardian->students->isNotEmpty())
                <div class="table-wrap" style="margin-top:18px">
                    <table class="data-table" style="min-width:0">
                        <thead>
                            <tr>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($guardian->students as $student)
                                <tr>
                                    <td>{{ $student->nis }}</td>
                                    <td><a
                                            href="{{ route('admin.students.show', $student) }}">{{ $student->user->name }}</a>
                                    </td>
                                    <td>{{ $student->classRoom?->name ?: 'Belum ditetapkan' }}</td>
                                    <td><span
                                            class="badge {{ $student->user->status === 'active' ? 'badge-active' : 'badge-inactive' }}">{{ $student->user->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state compact"><span class="empty-icon">⌁</span><strong>Belum ada siswa yang
                        terhubung</strong>
                    <p>Hubungkan orang tua ini melalui halaman edit siswa.</p>
                </div>
            @endif
        </article>
    </section>
@endsection
