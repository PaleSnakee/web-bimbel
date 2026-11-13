@extends('layouts.admin')

@section('title', 'Koreksi Absensi')

@section('content')
    @php($schedule = $learningSession->learningSchedule)

    <section class="page-heading">
        <div>
            <p class="eyebrow">ABSENSI</p>
            <h1>Koreksi Absensi</h1>
            <p>
                {{ $learningSession->session_date->format('d M Y') }} ·
                {{ $schedule->classRoom->name }} ·
                {{ $schedule->subject->name }}
            </p>
        </div>
    </section>

    @if ($errors->any())
        <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    <section class="panel">
        <form method="POST" action="{{ route('admin.student-attendances.update', $learningSession) }}">
            @csrf
            @method('PUT')

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $studentIndex => $student)
                            @php($attendance = $learningSession->attendances->firstWhere('student_id', $student->id))
                            <tr>
                                <td>
                                    {{ $student->nis }}
                                    <input type="hidden" name="attendances[{{ $studentIndex }}][student_id]"
                                        value="{{ $student->id }}">
                                </td>
                                <td>{{ $student->user->name }}</td>
                                <td>
                                    <select name="attendances[{{ $studentIndex }}][status]">
                                        @foreach (\App\Models\StudentAttendance::STATUSES as $status)
                                            <option value="{{ $status }}" @selected(old("attendances.{$studentIndex}.status", $attendance?->status ?? 'present') === $status)>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" maxlength="1000" name="attendances[{{ $studentIndex }}][notes]"
                                        value="{{ old("attendances.{$studentIndex}.notes", $attendance?->notes) }}"
                                        placeholder="Opsional">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-actions">
                <a class="button button-secondary"
                    href="{{ route('admin.student-attendances.show', $learningSession) }}">Batal</a>
                <button class="button button-primary" type="submit">Simpan Absensi</button>
            </div>
        </form>
    </section>
@endsection
