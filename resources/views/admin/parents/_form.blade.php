<div class="form-grid">
    <div class="form-field">
        <label for="name">Nama Lengkap <span>*</span></label>
        <input id="name" name="name" type="text" value="{{ old('name', $guardian->user->name ?? '') }}" maxlength="255" required placeholder="Nama lengkap orang tua">
        @error('name')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field">
        <label for="email">Email <span>*</span></label>
        <input id="email" name="email" type="email" value="{{ old('email', $guardian->user->email ?? '') }}" maxlength="255" required placeholder="nama@contoh.com">
        @error('email')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field">
        <label for="password">Password {!! isset($guardian) ? '' : '<span>*</span>' !!}</label>
        <input id="password" name="password" type="password" minlength="8" @unless (isset($guardian)) required @endunless placeholder="{{ isset($guardian) ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}">
        @error('password')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field">
        <label for="password_confirmation">Konfirmasi Password {!! isset($guardian) ? '' : '<span>*</span>' !!}</label>
        <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" @unless (isset($guardian)) required @endunless placeholder="Ulangi password">
    </div>

    <div class="form-field">
        <label for="status">Status Akun <span>*</span></label>
        <select id="status" name="status" required>
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $guardian->user->status ?? 'active') === $status)>{{ $status === 'active' ? 'Aktif' : 'Nonaktif' }}</option>
            @endforeach
        </select>
        @error('status')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field">
        <label for="phone">Nomor Telepon</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $guardian->phone ?? '') }}" maxlength="30" placeholder="Contoh: 081234567890">
        @error('phone')<small class="field-error">{{ $message }}</small>@enderror
    </div>

    <div class="form-field form-field-full">
        <label for="address">Alamat</label>
        <textarea id="address" name="address" rows="4" placeholder="Alamat orang tua (opsional)">{{ old('address', $guardian->address ?? '') }}</textarea>
        @error('address')<small class="field-error">{{ $message }}</small>@enderror
    </div>
</div>

<div class="form-actions">
    <a class="button button-secondary" href="{{ isset($guardian) ? route('admin.parents.show', $guardian) : route('admin.parents.index') }}">Batal</a>
    <button class="button button-primary" type="submit">{{ isset($guardian) ? 'Simpan Perubahan' : 'Simpan Orang Tua' }}</button>
</div>