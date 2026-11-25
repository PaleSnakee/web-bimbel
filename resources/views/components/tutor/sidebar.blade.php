<aside class="admin-sidebar" aria-label="Navigasi tutor">
    <div class="brand">
        <span class="brand-mark">E</span>
        <span>El Renaissance</span>
    </div>
    <nav class="sidebar-nav">
        {{-- Dashboard --}}
        <a class="nav-link {{ request()->routeIs('tutor.dashboard') ? 'is-active' : '' }}"
            href="{{ route('tutor.dashboard') }}">
            <span>▦</span>
            <span>Dashboard</span>
        </a>

        {{-- Akademik --}}
        <div class="nav-group">
            <button
                class="nav-link nav-toggle {{ request()->routeIs('tutor.attendances.*') ? 'is-active' : '' }}"
                type="button" data-menu-toggle
                aria-expanded="{{ request()->routeIs('tutor.attendances.*') ? 'true' : 'false' }}">
                <span>▤</span>
                <span>Akademik</span>
                <span class="chevron">⌄</span>
            </button>
            <div class="submenu" @unless (request()->routeIs('tutor.attendances.*')) hidden @endunless>
                <a class="submenu-item" href="{{ route('tutor.dashboard') }}#jadwal-mengajar">Jadwal Mengajar</a>
                <a class="submenu-item" href="{{ route('tutor.dashboard') }}#sesi-pembelajaran">Sesi Pembelajaran</a>
                <a class="submenu-item {{ request()->routeIs('tutor.attendances.*') ? 'is-active' : '' }}"
                    href="{{ route('tutor.attendances.index') }}">Kelola Absensi</a>
            </div>
        </div>

        {{-- Try Out --}}
        <div class="nav-group">
            <button
                class="nav-link nav-toggle {{ request()->routeIs('tutor.tryouts.*') || request()->routeIs('tutor.tryout-results.*') ? 'is-active' : '' }}"
                type="button" data-menu-toggle
                aria-expanded="{{ request()->routeIs('tutor.tryouts.*') || request()->routeIs('tutor.tryout-results.*') ? 'true' : 'false' }}">
                <span>✏</span>
                <span>Try Out</span>
                <span class="chevron">⌄</span>
            </button>
            <div class="submenu" @unless (request()->routeIs('tutor.tryouts.*') || request()->routeIs('tutor.tryout-results.*')) hidden @endunless>
                <a class="submenu-item {{ request()->routeIs('tutor.tryouts.index') ? 'is-active' : '' }}"
                    href="{{ route('tutor.tryouts.index') }}">Daftar Try Out</a>
                <a class="submenu-item {{ request()->routeIs('tutor.tryout-results.*') ? 'is-active' : '' }}"
                    href="{{ route('tutor.tryout-results.index') }}">Hasil Try Out</a>
            </div>
        </div>

        {{-- Akun --}}
        <div class="nav-group">
            <button
                class="nav-link nav-toggle {{ request()->routeIs('tutor.profile*') || request()->routeIs('profile*') ? 'is-active' : '' }}"
                type="button" data-menu-toggle
                aria-expanded="{{ request()->routeIs('tutor.profile*') || request()->routeIs('profile*') ? 'true' : 'false' }}">
                <span>👤</span>
                <span>Akun</span>
                <span class="chevron">⌄</span>
            </button>
            <div class="submenu" @unless (request()->routeIs('tutor.profile*') || request()->routeIs('profile*')) hidden @endunless>
                @if (Route::has('tutor.profile'))
                    <a class="submenu-item {{ request()->routeIs('tutor.profile') ? 'is-active' : '' }}"
                        href="{{ route('tutor.profile') }}">Profil Saya</a>
                @elseif (Route::has('profile'))
                    <a class="submenu-item {{ request()->routeIs('profile') ? 'is-active' : '' }}"
                        href="{{ route('profile') }}">Profil Saya</a>
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="submenu-item" style="width: 100%; text-align: left; background: none; border: 0; cursor: pointer;">
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>
    <div class="sidebar-footer">
        <span class="status-dot"></span> Tutor Aktif <span>v1.0</span>
    </div>
</aside>
