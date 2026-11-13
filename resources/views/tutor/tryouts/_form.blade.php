<div class="row g-3">
    <div class="col-12">
        <label class="form-label" for="title">Judul Try Out</label>
        <input class="form-control" id="title" name="title" value="{{ old('title', $tryout->title ?? '') }}" maxlength="255" required>
    </div>
    <div class="col-12">
        <label class="form-label" for="description">Deskripsi</label>
        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $tryout->description ?? '') }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="subject_id">Mata Pelajaran</label>
        <select class="form-select" id="subject_id" name="subject_id" required>
            <option value="">Pilih mata pelajaran</option>
            @foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected((string) old('subject_id', $tryout->subject_id ?? '') === (string) $subject->id)>{{ $subject->code }} — {{ $subject->name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-3"><label class="form-label" for="start_at">Waktu mulai</label><input class="form-control" id="start_at" type="datetime-local" name="start_at" value="{{ old('start_at', isset($tryout) ? $tryout->start_at->format('Y-m-d\TH:i') : '') }}" required></div>
    <div class="col-md-3"><label class="form-label" for="end_at">Waktu selesai</label><input class="form-control" id="end_at" type="datetime-local" name="end_at" value="{{ old('end_at', isset($tryout) ? $tryout->end_at->format('Y-m-d\TH:i') : '') }}" required></div>
    <div class="col-md-4"><label class="form-label" for="duration_minutes">Durasi (menit)</label><input class="form-control" id="duration_minutes" type="number" min="1" name="duration_minutes" value="{{ old('duration_minutes', $tryout->duration_minutes ?? '') }}" required></div>
    <div class="col-12">
        <label class="form-label" for="class_room_ids">Kelas peserta</label>
        @php($selectedClassRooms = old('class_room_ids', isset($tryout) ? $tryout->classRooms->pluck('id')->all() : []))
        <select class="form-select" id="class_room_ids" name="class_room_ids[]" multiple size="5" required>
            @foreach($classRooms as $classRoom)<option value="{{ $classRoom->id }}" @selected(in_array($classRoom->id, array_map('intval', $selectedClassRooms), true))>{{ $classRoom->code }} — {{ $classRoom->name }}</option>@endforeach
        </select>
        <div class="form-text">Pilih minimal satu kelas yang memiliki jadwal aktif Anda.</div>
    </div>
</div>
