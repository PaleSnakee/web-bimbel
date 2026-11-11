<div class="form-grid">
    <div class="form-field">
        <label for="class_room_id">Kelas <span>*</span></label>
        <select id="class_room_id" name="class_room_id" required>
            <option value="">Pilih kelas</option>
            @foreach ($classRooms as $classRoom)
                <option value="{{ $classRoom->id }}" @selected((string) old('class_room_id', $learningSchedule->class_room_id ?? '') === (string) $classRoom->id)>
                    {{ $classRoom->code }} — {{ $classRoom->name }}
                </option>
            @endforeach
        </select>
        @error('class_room_id')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-field">
        <label for="tutor_id">Tutor <span>*</span></label>
        <select id="tutor_id" name="tutor_id" required>
            <option value="">Pilih tutor</option>
            @foreach ($tutors as $tutor)
                <option value="{{ $tutor->id }}" @selected((string) old('tutor_id', $learningSchedule->tutor_id ?? '') === (string) $tutor->id)>
                    {{ $tutor->user->name }}
                </option>
            @endforeach
        </select>
        @error('tutor_id')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-field">
        <label for="subject_id">Mata Pelajaran <span>*</span></label>
        <select id="subject_id" name="subject_id" required>
            <option value="">Pilih mata pelajaran</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" @selected((string) old('subject_id', $learningSchedule->subject_id ?? '') === (string) $subject->id)>
                    {{ $subject->code }} — {{ $subject->name }}
                </option>
            @endforeach
        </select>
        <small class="field-hint">Tutor harus memiliki assignment untuk mata pelajaran yang dipilih.</small>
        @error('subject_id')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-field">
        <label for="day_of_week">Hari Pembelajaran <span>*</span></label>
        <select id="day_of_week" name="day_of_week" required>
            <option value="">Pilih hari</option>
            @foreach ($daysOfWeek as $day)
                <option value="{{ $day }}" @selected(old('day_of_week', $learningSchedule->day_of_week ?? '') === $day)>
                    {{ \App\Models\LearningSchedule::dayLabel($day) }}
                </option>
            @endforeach
        </select>
        @error('day_of_week')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-field">
        <label for="start_time">Jam Mulai <span>*</span></label>
        <input id="start_time" name="start_time" type="time"
            value="{{ old('start_time', isset($learningSchedule) ? $learningSchedule->start_time->format('H:i') : '') }}"
            required>
        @error('start_time')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-field">
        <label for="end_time">Jam Selesai <span>*</span></label>
        <input id="end_time" name="end_time" type="time"
            value="{{ old('end_time', isset($learningSchedule) ? $learningSchedule->end_time->format('H:i') : '') }}"
            required>
        @error('end_time')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-field">
        <label for="effective_from">Tanggal Mulai Berlaku <span>*</span></label>
        <input id="effective_from" name="effective_from" type="date"
            value="{{ old('effective_from', isset($learningSchedule) ? $learningSchedule->effective_from->format('Y-m-d') : '') }}"
            required>
        @error('effective_from')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-field">
        <label for="effective_until">Tanggal Berakhir</label>
        <input id="effective_until" name="effective_until" type="date"
            value="{{ old('effective_until', isset($learningSchedule) && $learningSchedule->effective_until ? $learningSchedule->effective_until->format('Y-m-d') : '') }}">
        <small class="field-hint">Kosongkan jika jadwal berlaku tanpa batas waktu.</small>
        @error('effective_until')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-field">
        <label for="status">Status <span>*</span></label>
        <select id="status" name="status" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $learningSchedule->status ?? 'active') === $status)>
                    {{ $status === 'active' ? 'Aktif' : 'Nonaktif' }}
                </option>
            @endforeach
        </select>
        @error('status')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="form-field form-field-full">
        <label for="notes">Keterangan</label>
        <textarea id="notes" name="notes" rows="4" placeholder="Tambahkan keterangan jadwal (opsional)">{{ old('notes', $learningSchedule->notes ?? '') }}</textarea>
        @error('notes')
            <small class="field-error">{{ $message }}</small>
        @enderror
    </div>
</div>

<div class="form-actions">
    <a class="button button-secondary"
        href="{{ isset($learningSchedule) ? route('admin.learning-schedules.show', $learningSchedule) : route('admin.learning-schedules.index') }}">Batal</a>
    <button class="button button-primary"
        type="submit">{{ isset($learningSchedule) ? 'Simpan Perubahan' : 'Simpan Jadwal' }}</button>
</div>
