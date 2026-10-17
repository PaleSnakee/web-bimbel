<div class="form-grid">
    <div class="form-field">
        <label for="name">Nama Lengkap <span>*</span></label>
        <input id="name" name="name" type="text" value="{{ old('name', $tutor->user->name ?? '') }}" maxlength="255" required placeholder="Nama lengkap tutor">
        @error('name')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field">
        <label for="email">Email <span>*</span></label>
        <input id="email" name="email" type="email" value="{{ old('email', $tutor->user->email ?? '') }}" maxlength="255" required placeholder="nama@contoh.com">
        @error('email')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field">
        <label for="password">Password {!! isset($tutor) ? '' : '<span>*</span>' !!}</label>
        <input id="password" name="password" type="password" minlength="8" @unless(isset($tutor)) required @endunless placeholder="{{ isset($tutor) ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}">
        @error('password')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field">
        <label for="password_confirmation">Konfirmasi Password {!! isset($tutor) ? '' : '<span>*</span>' !!}</label>
        <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" @unless(isset($tutor)) required @endunless placeholder="Ulangi password">
    </div>

    <div class="form-field">
        <label for="status">Status Akun <span>*</span></label>
        <select id="status" name="status" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $tutor->user->status ?? 'active') === $status)>{{ $status === 'active' ? 'Aktif' : 'Nonaktif' }}</option>
            @endforeach
        </select>
        @error('status')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field">
        <label for="phone">Nomor Telepon</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $tutor->phone ?? '') }}" maxlength="30" placeholder="Contoh: 081234567890">
        @error('phone')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field">
        <label for="gender">Jenis Kelamin</label>
        <select id="gender" name="gender">
            <option value="">Pilih jenis kelamin</option>
            @foreach ($genders as $gender)
                <option value="{{ $gender }}" @selected(old('gender', $tutor->gender ?? '') === $gender)>{{ $gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</option>
            @endforeach
        </select>
        @error('gender')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field">
        <label for="date_of_birth">Tanggal Lahir</label>
        <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', isset($tutor) && $tutor->date_of_birth ? $tutor->date_of_birth->format('Y-m-d') : '') }}">
        @error('date_of_birth')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field form-field-full">
        <label for="address">Alamat</label>
        <textarea id="address" name="address" rows="3" placeholder="Alamat tutor (opsional)">{{ old('address', $tutor->address ?? '') }}</textarea>
        @error('address')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field form-field-full">
        <label for="subject_ids">Mata Pelajaran yang Dikuasai</label>
        @php($selectedSubjectIds = old('subject_ids', isset($tutor) ? $tutor->subjects->pluck('id')->all() : []))
        <select id="subject_ids" name="subject_ids[]" multiple size="6">
            @foreach ($subjects as $subject)
                <option value="{{ $subject->id }}" @selected(in_array($subject->id, $selectedSubjectIds))>{{ $subject->name }} — {{ $subject->education_level }} ({{ $subject->code }})</option>
            @endforeach
        </select>
        <small class="field-hint">Gunakan Ctrl atau Cmd untuk memilih lebih dari satu mata pelajaran.</small>
        @error('subject_ids')<small class="field-error">{{ $message }}</small>@enderror
        @error('subject_ids.*')<small class="field-error">{{ $message }}</small>@enderror
    </div>
</div>

<div class="form-actions">
    <a class="button button-secondary" href="{{ isset($tutor) ? route('admin.tutors.show', $tutor) : route('admin.tutors.index') }}">Batal</a>
    <button class="button button-primary" type="submit">{{ isset($tutor) ? 'Simpan Perubahan' : 'Simpan Tutor' }}</button>
</div>