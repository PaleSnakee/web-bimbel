<div class="form-grid">
    <div class="form-field">
        <label for="code">Kode Kelas <span>*</span></label>
        <input id="code" name="code" type="text" value="{{ old('code', $classRoom->code ?? '') }}" maxlength="50" required placeholder="Contoh: SD-5-A">
        @error('code')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="form-field">
        <label for="name">Nama Kelas <span>*</span></label>
        <input id="name" name="name" type="text" value="{{ old('name', $classRoom->name ?? '') }}" maxlength="150" required placeholder="Contoh: Kelas Reguler 5A">
        @error('name')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="form-field">
        <label for="education_level">Jenjang Pendidikan <span>*</span></label>
        <select id="education_level" name="education_level" required>
            <option value="">Pilih jenjang</option>
            @foreach ($educationLevels as $level)
                <option value="{{ $level }}" @selected(old('education_level', $classRoom->education_level ?? '') === $level)>{{ $level }}</option>
            @endforeach
        </select>
        @error('education_level')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="form-field">
        <label for="grade_level">Tingkat/Kelas <span>*</span></label>
        <select id="grade_level" name="grade_level" required data-selected="{{ old('grade_level', $classRoom->grade_level ?? '') }}">
            <option value="">Pilih jenjang terlebih dahulu</option>
        </select>
        @error('grade_level')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="form-field">
        <label for="capacity">Kapasitas Siswa <span>*</span></label>
        <input id="capacity" name="capacity" type="number" value="{{ old('capacity', $classRoom->capacity ?? '') }}" min="1" required placeholder="Contoh: 20">
        @error('capacity')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="form-field">
        <label for="status">Status <span>*</span></label>
        <select id="status" name="status" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $classRoom->status ?? 'active') === $status)>{{ $status === 'active' ? 'Aktif' : 'Nonaktif' }}</option>
            @endforeach
        </select>
        @error('status')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="form-field form-field-full">
        <label for="description">Deskripsi</label>
        <textarea id="description" name="description" rows="4" placeholder="Tambahkan keterangan kelas (opsional)">{{ old('description', $classRoom->description ?? '') }}</textarea>
        @error('description')<small class="field-error">{{ $message }}</small>@enderror
    </div>
</div>
<div class="form-actions">
    <a class="button button-secondary" href="{{ isset($classRoom) ? route('admin.classes.show', $classRoom) : route('admin.classes.index') }}">Batal</a>
    <button class="button button-primary" type="submit">{{ isset($classRoom) ? 'Simpan Perubahan' : 'Simpan Kelas' }}</button>
</div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const levels = @json($gradeOptions);
        const education = document.getElementById('education_level');
        const grade = document.getElementById('grade_level');
        const selected = grade.dataset.selected;
        function updateGrades() {
            const current = grade.value || selected;
            grade.innerHTML = '<option value="">Pilih tingkat</option>';
            (levels[education.value] || []).forEach(function (value) {
                const option = new Option(value, value, false, value === current);
                grade.add(option);
            });
        }
        education.addEventListener('change', function () { grade.dataset.selected = ''; updateGrades(); });
        updateGrades();
    });
</script>
@endpush
