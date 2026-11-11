<div class="form-grid">
    <div class="form-field form-field-full">
        <label for="learning_schedule_id">Jadwal Pembelajaran <span>*</span></label>
        <select id="learning_schedule_id" name="learning_schedule_id" required>
            <option value="">Pilih jadwal</option>
            @foreach ($schedules as $schedule)
                <option value="{{ $schedule->id }}"
                    @selected((string) old('learning_schedule_id', $learningSession->learning_schedule_id ?? '') === (string) $schedule->id)>
                    {{ $schedule->classRoom->code }} — {{ $schedule->subject->name }} — {{ $schedule->tutor->user->name }}
                    ({{ \App\Models\LearningSchedule::dayLabel($schedule->day_of_week) }}, {{ $schedule->start_time->format('H:i') }}–{{ $schedule->end_time->format('H:i') }})
                    {{ $schedule->status === 'inactive' ? '[Nonaktif]' : '' }}
                </option>
            @endforeach
        </select>
        <small class="field-hint">Tanggal dan waktu sesi wajib sesuai hari, periode, dan rentang waktu jadwal.</small>
        @error('learning_schedule_id')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field">
        <label for="session_date">Tanggal Sesi <span>*</span></label>
        <input id="session_date" name="session_date" type="date"
            value="{{ old('session_date', isset($learningSession) ? $learningSession->session_date->format('Y-m-d') : '') }}" required>
        @error('session_date')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field">
        <label for="status">Status <span>*</span></label>
        <select id="status" name="status" required @disabled(isset($learningSession) && $learningSession->status === 'completed')>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $learningSession->status ?? 'scheduled') === $status)>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
        @if (isset($learningSession) && $learningSession->status === 'completed')
            <input type="hidden" name="status" value="completed">
        @endif
        @error('status')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field">
        <label for="start_time">Jam Mulai Aktual <span>*</span></label>
        <input id="start_time" name="start_time" type="time"
            value="{{ old('start_time', isset($learningSession) ? $learningSession->start_time->format('H:i') : '') }}" required>
        @error('start_time')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field">
        <label for="end_time">Jam Selesai Aktual <span>*</span></label>
        <input id="end_time" name="end_time" type="time"
            value="{{ old('end_time', isset($learningSession) ? $learningSession->end_time->format('H:i') : '') }}" required>
        @error('end_time')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field form-field-full">
        <label for="topic">Materi Pembelajaran</label>
        <input id="topic" name="topic" type="text" maxlength="255"
            value="{{ old('topic', $learningSession->topic ?? '') }}" placeholder="Contoh: Pecahan dan operasi hitung">
        @error('topic')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field form-field-full">
        <label for="notes">Catatan Sesi</label>
        <textarea id="notes" name="notes" rows="4" placeholder="Tambahkan catatan sesi (opsional)">{{ old('notes', $learningSession->notes ?? '') }}</textarea>
        @error('notes')<small class="field-error">{{ $message }}</small>@enderror
    </div>
</div>

<div class="form-actions">
    <a class="button button-secondary"
        href="{{ isset($learningSession) ? route('admin.learning-sessions.show', $learningSession) : route('admin.learning-sessions.index') }}">Batal</a>
    <button class="button button-primary" type="submit">{{ isset($learningSession) ? 'Simpan Perubahan' : 'Simpan Sesi' }}</button>
</div>