<div class="form-grid">
    <div class="form-field">
        <label for="code">Kode Mata Pelajaran <span>*</span></label>
        <input id="code" name="code" type="text" value="{{ old('code', $subject->code ?? '') }}" maxlength="50" required placeholder="Contoh: MTK-SD">
        @error('code')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="form-field">
        <label for="name">Nama Mata Pelajaran <span>*</span></label>
        <input id="name" name="name" type="text" value="{{ old('name', $subject->name ?? '') }}" maxlength="150" required placeholder="Contoh: Matematika">
        @error('name')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="form-field">
        <label for="education_level">Jenjang Pendidikan <span>*</span></label>
        <select id="education_level" name="education_level" required>
            <option value="">Pilih jenjang</option>
            @foreach ($educationLevels as $level)
                <option value="{{ $level }}" @selected(old('education_level', $subject->education_level ?? '') === $level)>{{ $level }}</option>
            @endforeach
        </select>
        @error('education_level')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="form-field">
        <label for="status">Status <span>*</span></label>
        <select id="status" name="status" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $subject->status ?? 'active') === $status)>{{ $status === 'active' ? 'Aktif' : 'Nonaktif' }}</option>
            @endforeach
        </select>
        @error('status')<small class="field-error">{{ $message }}</small>@enderror
    </div>
    <div class="form-field form-field-full">
        <label for="description">Deskripsi</label>
        <textarea id="description" name="description" rows="4" placeholder="Tambahkan keterangan mata pelajaran (opsional)">{{ old('description', $subject->description ?? '') }}</textarea>
        @error('description')<small class="field-error">{{ $message }}</small>@enderror
    </div>
</div>
<div class="form-actions">
    <a class="button button-secondary" href="{{ isset($subject) ? route('admin.subjects.show', $subject) : route('admin.subjects.index') }}">Batal</a>
    <button class="button button-primary" type="submit">{{ isset($subject) ? 'Simpan Perubahan' : 'Simpan Mata Pelajaran' }}</button>
</div>
