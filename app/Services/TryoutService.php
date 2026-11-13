<?php

namespace App\Services;

use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Tryout;
use App\Models\Tutor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TryoutService
{
    public function validateAndSave(array $data, ?Tryout $tryout = null, ?Tutor $owner = null): Tryout
    {
        $subject = Subject::findOrFail($data['subject_id']);

        if ($subject->status !== 'active') {
            throw ValidationException::withMessages(['subject_id' => 'Mata pelajaran yang dipilih tidak aktif.']);
        }

        $classRooms = ClassRoom::query()
            ->whereIn('id', $data['class_room_ids'])
            ->get()
            ->keyBy('id');

        if ($classRooms->count() !== count(array_unique($data['class_room_ids']))) {
            throw ValidationException::withMessages(['class_room_ids' => 'Kelas yang dipilih tidak ditemukan.']);
        }

        if ($classRooms->contains(fn (ClassRoom $classRoom) => $classRoom->status !== 'active')) {
            throw ValidationException::withMessages(['class_room_ids' => 'Semua kelas peserta harus aktif.']);
        }

        if (Carbon::parse($data['start_at'])->gte(Carbon::parse($data['end_at']))) {
            throw ValidationException::withMessages(['end_at' => 'Waktu selesai harus setelah waktu mulai.']);
        }

        $availableMinutes = Carbon::parse($data['start_at'])->diffInMinutes(Carbon::parse($data['end_at']));

        if ((int) $data['duration_minutes'] > $availableMinutes) {
            throw ValidationException::withMessages(['duration_minutes' => 'Durasi tidak boleh melebihi rentang waktu pelaksanaan.']);
        }

        if ($owner) {
            if (! $owner->subjects()->whereKey($subject->id)->exists()) {
                throw ValidationException::withMessages(['subject_id' => 'Anda tidak mengampu mata pelajaran ini.']);
            }

            $allowedClassIds = $owner->learningSchedules()
                ->where('subject_id', $subject->id)
                ->where('status', 'active')
                ->pluck('class_room_id')
                ->all();

            if (array_diff($data['class_room_ids'], $allowedClassIds)) {
                throw ValidationException::withMessages(['class_room_ids' => 'Kelas harus memiliki jadwal aktif dengan Anda untuk mata pelajaran ini.']);
            }

            $data['tutor_id'] = $owner->id;
        }

        return DB::transaction(function () use ($data, $tryout) {
            $classRoomIds = $data['class_room_ids'];
            unset($data['class_room_ids']);

            $tryout = $tryout ? tap($tryout)->update($data) : Tryout::create($data);
            $tryout->classRooms()->sync($classRoomIds);

            return $tryout->fresh(['subject', 'tutor.user', 'classRooms']);
        });
    }
}