@extends('layouts.admin')
@section('title', 'Detail Absensi')
@section('content')
@php($schedule = $learningSession->learningSchedule)
<section class="page-heading"><div><p class="eyebrow">ABSENSI</p><h1>Detail Absensi Sesi</h1><p>{{ $learningSession->session_date->format('d M Y') }} · {{ $schedule->classRoom->code }} · {{ $schedule->subject->name }}</p></div><a class="button button-secondary" href="{{ route('admin.student-attendances.index') }}">Kembali</a></section>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<section class="panel"><p><strong>Tutor:</strong> {{ $schedule->tutor->user->name }} &nbsp; <strong>Waktu:</strong> {{ $learningSession->start_time->format('H:i') }}–{{ $learningSession->end_time->format('H:i') }} &nbsp; <strong>Status sesi:</strong> {{ ucfirst($learningSession->status) }}</p>
<div class="filter-bar"><span>Hadir: {{ $summary['present'] ?? 0 }}</span><span>Sakit: {{ $summary['sick'] ?? 0 }}</span><span>Izin: {{ $summary['permission'] ?? 0 }}</span><span>Alpa: {{ $summary['absent'] ?? 0 }}</span>@if($learningSession->status !== 'cancelled')<a class="button button-primary" href="{{ route('admin.student-attendances.edit', $learningSession) }}">Koreksi Absensi</a>@endif</div>
@if($learningSession->attendances->count())<div class="table-wrap"><table class="data-table"><thead><tr><th>NIS</th><th>Nama</th><th>Status</th><th>Catatan</th><th>Pencatat</th></tr></thead><tbody>@foreach($learningSession->attendances as $attendance)<tr><td>{{ $attendance->student->nis }}</td><td>{{ $attendance->student->user->name }}</td><td>{{ ucfirst($attendance->status) }}</td><td>{{ $attendance->notes ?: '-' }}</td><td>{{ $attendance->recorder?->name ?? '-' }}</td></tr>@endforeach</tbody></table></div>@else<div class="empty-state"><strong>Absensi belum diisi</strong></div>@endif
</section>
@endsection
