<aside class="admin-sidebar" aria-label="Navigasi utama">
    <div class="brand"><span class="brand-mark">E</span><span>El Renaissance</span></div>
    <nav class="sidebar-nav">
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
            href="{{ route('admin.dashboard') }}"><span>▦</span><span>Dashboard</span></a>
        <div class="nav-group">
            <button
                class="nav-link nav-toggle {{ request()->routeIs('admin.classes.*') || request()->routeIs('admin.subjects.*') || request()->routeIs('admin.tutors.*') ? 'is-active' : '' }}"
                type="button" data-menu-toggle
                aria-expanded="{{ request()->routeIs('admin.classes.*') || request()->routeIs('admin.subjects.*') || request()->routeIs('admin.tutors.*') ? 'true' : 'false' }}"><span>▤</span><span>Data
                    Master</span><span class="chevron">⌄</span></button>
            <div class="submenu" @unless (request()->routeIs('admin.classes.*') ||
                    request()->routeIs('admin.subjects.*') ||
                    request()->routeIs('admin.tutors.*')) hidden @endunless>
                <a class="submenu-item {{ request()->routeIs('admin.classes.*') ? 'is-active' : '' }}"
                    href="{{ route('admin.classes.index') }}">Manajemen Kelas</a>
                <a class="submenu-item {{ request()->routeIs('admin.subjects.*') ? 'is-active' : '' }}"
                    href="{{ route('admin.subjects.index') }}">Mata Pelajaran</a>
                <a class="submenu-item {{ request()->routeIs('admin.tutors.*') ? 'is-active' : '' }}"
                    href="{{ route('admin.tutors.index') }}">Manajemen Tutor</a>
                @foreach (['Manajemen Orang Tua', 'Manajemen Siswa'] as $item)
                    <span class="submenu-item">{{ $item }} <small>segera hadir</small></span>
                @endforeach
            </div>
        </div>
        <div class="nav-group"><button class="nav-link nav-toggle" type="button" data-menu-toggle
                aria-expanded="false"><span>▤</span><span>Akademik</span><span class="chevron">⌄</span></button>
            <div class="submenu" hidden>
                @foreach (['Absensi', 'Try Out Online', 'Hasil dan Nilai'] as $item)
                    <span class="submenu-item">{{ $item }} <small>segera hadir</small></span>
                @endforeach
            </div>
        </div>
        <span class="nav-link is-unavailable"><span>▧</span><span>Laporan</span><small>segera hadir</small></span>
        <span class="nav-link is-unavailable"><span>⚙</span><span>Pengaturan</span><small>segera hadir</small></span>
    </nav>
    <div class="sidebar-footer"><span class="status-dot"></span> Sistem aktif <span>v1.0</span></div>
</aside>
