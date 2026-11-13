<div class="form-grid">
    <div class="form-group form-group-full">
        <label for="title">Judul Try Out</label>
        <input id="title" name="title" value="{{ old('title', $tryout->title ?? '') }}" required maxlength="255">
    </div>
    <div class="form-group form-group-full">
        <label for="description">Deskripsi</label>
        <textarea id="description" name="description" rows="4">{{ old('description', $tryout->description ?? '') }}</textarea>
    </div>
    <div class="form-group">
        <label for="subject_id">Mata Pelajaran</label>
        <select id="subject_id" name="subject_id" required>
            <option value="">Pilih mata pelajaran</option>
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" @selected((string) old('subject_id', $tryout->subject_id ?? '') === (string) $subject->id)>{{ $subject->code }} — {{ $subject->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="tutor_id">Tutor Pengelola</label>
        <select id="tutor_id" name="tutor_id">
            <option value="">Dikelola admin</option>
            @foreach ($tutors as $tutor)
                <option value="{{ $tutor->id }}" @selected((string) old('tutor_id', $tryout->tutor_id ?? '') === (string) $tutor->id)>{{ $tutor->user->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="start_at">Waktu Mulai</label>
        <input id="start_at" type="datetime-local" name="start_at" value="{{ old('start_at', isset($tryout) ? $tryout->start_at->format('Y-m-d\TH:i') : '') }}" required>
    </div>
    <div class="form-group">
        <label for="end_at">Waktu Selesai</label>
        <input id="end_at" type="datetime-local" name="end_at" value="{{ old('end_at', isset($tryout) ? $tryout->end_at->format('Y-m-d\TH:i') : '') }}" required>
    </div>
    <div class="form-group">
        <label for="duration_minutes">Durasi (menit)</label>
        <input id="duration_minutes" type="number" name="duration_minutes" min="1" value="{{ old('duration_minutes', $tryout->duration_minutes ?? '') }}" required>
    </div>
    <div class="form-group form-group-full">
        <label for="class_room_ids">Kelas Peserta</label>
        @php($selectedClassRooms = old('class_room_ids', isset($tryout) ? $tryout->classRooms->pluck('id')->all() : []))
        <select id="class_room_ids" name="class_room_ids[]" multiple required size="5">
            @foreach ($classRooms as $classRoom)
                <option value="{{ $classRoom->id }}" @selected(in_array($classRoom->id, array_map('intval', $selectedClassRooms), true))>{{ $classRoom->code }} — {{ $classRoom->name }}</option>
            @endforeach
        </select>
        <small>Pilih minimal satu kelas peserta.</small>
    </div>
</div>
