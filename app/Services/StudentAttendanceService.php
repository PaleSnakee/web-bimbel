<?php

namespace App\Services;

use App\Models\LearningSession;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentAttendanceService
{
    public function save(LearningSession $session, array $attendances, User $recorder): void
    {
        $session->loadMissing('learningSchedule.classRoom');

        if (! $session->learningSchedule || ! $session->learningSchedule->classRoom) {
            throw ValidationException::withMessages(['learning_session_id' => 'Sesi tidak memiliki jadwal atau kelas yang valid.']);
        }

        if ($session->status === 'cancelled') {
            throw ValidationException::withMessages(['learning_session_id' => 'Absensi tidak dapat dicatat untuk sesi yang dibatalkan.']);
        }

        $studentIds = collect($attendances)->pluck('student_id')->map(fn ($id) => (int) $id);

        if ($studentIds->count() !== $studentIds->unique()->count()) {
            throw ValidationException::withMessages(['attendances' => 'Setiap siswa hanya boleh dicatat satu kali dalam satu sesi.']);
        }

        $students = Student::query()
            ->with('user:id,status')
            ->whereIn('id', $studentIds)
            ->get()
            ->keyBy('id');

        if ($students->count() !== $studentIds->count()) {
            throw ValidationException::withMessages(['attendances' => 'Siswa yang dikirim tidak ditemukan.']);
        }

        foreach ($attendances as $attendance) {
            $student = $students->get((int) $attendance['student_id']);

            if ((int) $student->class_room_id !== (int) $session->learningSchedule->class_room_id) {
                throw ValidationException::withMessages(['attendances' => 'Siswa harus terdaftar pada kelas yang sama dengan sesi.']);
            }

            if ($student->user?->status !== 'active') {
                throw ValidationException::withMessages(['attendances' => 'Absensi hanya dapat dicatat untuk siswa aktif.']);
            }
        }

        DB::transaction(function () use ($session, $attendances, $recorder) {
            foreach ($attendances as $attendance) {
                StudentAttendance::updateOrCreate(
                    [
                        'learning_session_id' => $session->id,
                        'student_id' => $attendance['student_id'],
                    ],
                    [
                        'status' => $attendance['status'],
                        'notes' => $attendance['notes'] ?? null,
                        'recorded_by' => $recorder->id,
                    ],
                );
            }
        });
    }
}
