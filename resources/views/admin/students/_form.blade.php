<div class="form-section">
    <h3 class="form-section-title">Informasi Siswa</h3>
    <div class="form-grid">
        <div class="form-field">
            <label for="nis">NIS <span>*</span></label>
            <input id="nis" name="nis" type="text" value="{{ old('nis', $student->nis ?? '') }}" maxlength="50"
                required placeholder="Nomor Induk Siswa">
            @error('nis')
                <small class="field-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-field">
            <label for="name">Nama Lengkap <span>*</span></label>
            <input id="name" name="name" type="text" value="{{ old('name', $student->user->name ?? '') }}"
                maxlength="255" required placeholder="Nama lengkap siswa">
            @error('name')
                <small class="field-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-field">
            <label for="education_level">Jenjang Pendidikan <span>*</span></label>
            <select id="education_level" name="education_level" required>
                <option value="">Pilih jenjang</option>
                @foreach ($educationLevels as $level)
                    <option value="{{ $level }}" @selected(old('education_level', $student->education_level ?? '') === $level)>{{ $level }}</option>
                @endforeach
            </select>
            @error('education_level')
                <small class="field-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-field">
            <label for="gender">Jenis Kelamin</label>
            <select id="gender" name="gender">
                <option value="">Pilih jenis kelamin</option>
                @foreach ($genders as $gender)
                    <option value="{{ $gender }}" @selected(old('gender', $student->gender ?? '') === $gender)>
                        {{ $gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</option>
                @endforeach
            </select>
            @error('gender')
                <small class="field-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-field">
            <label for="date_of_birth">Tanggal Lahir</label>
            <input id="date_of_birth" name="date_of_birth" type="date"
                value="{{ old('date_of_birth', isset($student) && $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '') }}">
            @error('date_of_birth')
                <small class="field-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-field">
            <label for="class_room_id">Kelas</label>
            <select id="class_room_id" name="class_room_id">
                <option value="">Belum ditetapkan</option>
                @foreach ($classRooms as $classRoom)
                    @php($full = $classRoom->students_count >= $classRoom->capacity)
                    <option value="{{ $classRoom->id }}" @selected(old('class_room_id', $student->class_room_id ?? '') == $classRoom->id)
                        @if ($full && (!isset($student) || $student->class_room_id != $classRoom->id)) disabled @endif>
                        {{ $classRoom->name }} ({{ $classRoom->code }}) — {{ $classRoom->education_level }} Kelas
                        {{ $classRoom->grade_level }} — {{ $classRoom->students_count }}/{{ $classRoom->capacity }}
                        siswa{{ $full ? ' — PENUH' : '' }}
                    </option>
                @endforeach
            </select>
            @error('class_room_id')
                <small class="field-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-field">
            <label for="status">Status Siswa <span>*</span></label>
            <select id="status" name="status" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(old('status', $student->user->status ?? 'active') === $status)>
                        {{ $status === 'active' ? 'Aktif' : 'Nonaktif' }}</option>
                @endforeach
            </select>
            @error('status')
                <small class="field-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-field form-field-full">
            <label for="address">Alamat</label>
            <textarea id="address" name="address" rows="3" placeholder="Alamat siswa (opsional)">{{ old('address', $student->address ?? '') }}</textarea>
            @error('address')
                <small class="field-error">{{ $message }}</small>
            @enderror
        </div>
    </div>
</div>

<div class="form-section">
    <h3 class="form-section-title">Informasi Akun</h3>
    <div class="form-grid">
        <div class="form-field">
            <label for="email">Email <span>*</span></label>
            <input id="email" name="email" type="email" value="{{ old('email', $student->user->email ?? '') }}"
                maxlength="255" required placeholder="email@contoh.com">
            @error('email')
                <small class="field-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-field">
            <label for="password">Password {!! isset($student) ? '' : '<span>*</span>' !!}</label>
            <input id="password" name="password" type="password" minlength="8"
                @unless (isset($student)) required @endunless
                placeholder="{{ isset($student) ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}">
            @error('password')
                <small class="field-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="form-field">
            <label for="password_confirmation">Konfirmasi Password {!! isset($student) ? '' : '<span>*</span>' !!}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" minlength="8"
                @unless (isset($student)) required @endunless placeholder="Ulangi password">
        </div>
    </div>
</div>

<div class="form-section">
    <h3 class="form-section-title">Orang Tua / Wali</h3>
    @if ($guardians->isEmpty())
        <div class="empty-state compact">
            <span class="empty-icon">⌁</span>
            <strong>Belum ada akun orang tua aktif</strong>
            <p>Tambahkan akun orang tua terlebih dahulu melalui menu <a
                    href="{{ route('admin.parents.create') }}">Manajemen Orang Tua</a> sebelum menghubungkannya dengan
                siswa.</p>
        </div>
    @else
        @php($selectedGuardianIds = old('guardian_ids', isset($student) ? $student->guardians->pluck('id')->all() : []))
        <div class="form-grid">
            <div class="form-field form-field-full">
                <label>Pilih Orang Tua / Wali</label>
                <select name="guardian_ids[]" multiple size="6">
                    @foreach ($guardians as $guardian)
                        <option value="{{ $guardian->id }}" @selected(in_array($guardian->id, $selectedGuardianIds))>{{ $guardian->user->name }} —
                            {{ $guardian->user->email }}{{ $guardian->phone ? ' — ' . $guardian->phone : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="field-hint">Gunakan Ctrl atau Cmd untuk memilih lebih dari satu orang tua.</small>
                @error('guardian_ids')
                    <small class="field-error">{{ $message }}</small>
                @enderror
                @error('guardian_ids.*')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </div>
        </div>
    @endif
</div>

<div class="form-actions">
    <a class="button button-secondary"
        href="{{ isset($student) ? route('admin.students.show', $student) : route('admin.students.index') }}">Batal</a>
    <button class="button button-primary"
        type="submit">{{ isset($student) ? 'Simpan Perubahan' : 'Simpan Siswa' }}</button>
</div>
