@extends('layouts.parent')

@section('title', 'Data Anak')
@section('page_title', 'Data Anak')

@section('content')
    <section class="page-heading">
        <div>
            <p class="eyebrow">MONITORING AKADEMIK</p>
            <h1>Data Anak</h1>
            <p>Daftar seluruh anak yang terhubung dengan akun orang tua Anda.</p>
        </div>
        <span class="today">{{ now()->translatedFormat('l, d F Y') }}</span>
    </section>

    <section class="panel">
        <div class="panel-heading" style="margin-bottom: 16px;">
            <div>
                <h2>Daftar Anak Terdaftar</h2>
                <p>Klik tombol detail untuk memantau ringkasan nilai, riwayat Try Out, dan absensi per anak.</p>
            </div>
        </div>

        @if ($children->isNotEmpty())
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Lengkap Anak</th>
                            <th>NIS</th>
                            <th>Kelas</th>
                            <th>Jenjang</th>
                            <th>Try Out Selesai</th>
                            <th>Kehadiran</th>
                            <th>Status Akun</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($children as $child)
                            <tr>
                                <td>
                                    <strong style="color: #111827; font-weight: 600;">{{ $child->user?->name }}</strong>
                                    <small style="display: block; color: var(--muted); font-size: 11px;">
                                        {{ $child->user?->email }}
                                    </small>
                                </td>
                                <td>{{ $child->nis ?? '-' }}</td>
                                <td>{{ $child->classRoom?->name ?? 'Belum ditentukan' }}</td>
                                <td>{{ $child->education_level }}</td>
                                <td>
                                    <strong>{{ $child->completed_tryouts_count ?? 0 }}</strong> try out
                                </td>
                                <td>
                                    <strong>{{ $child->present_attendances_count ?? 0 }}</strong> kali hadir
                                </td>
                                <td>
                                    <span class="badge {{ $child->user?->status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                                        {{ $child->user?->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        <a class="button button-primary" style="font-size: 11px; padding: 5px 12px;"
                                            href="{{ route('parent.children.show', $child) }}">
                                            Lihat Perkembangan
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <span class="empty-icon">👥</span>
                <strong>Belum ada anak yang terhubung</strong>
                <p>Akun Anda belum terhubung dengan data siswa manapun. Silakan hubungi admin bimbel untuk menghubungkan data anak Anda.</p>
            </div>
        @endif
    </section>
@endsection
