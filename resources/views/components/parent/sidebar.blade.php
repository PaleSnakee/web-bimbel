<aside class="admin-sidebar" aria-label="Navigasi orang tua">
    <div class="brand">
        <span class="brand-mark">E</span>
        <span>El Renaissance</span>
    </div>
    <nav class="sidebar-nav">
        {{-- Dashboard --}}
        <a class="nav-link {{ request()->routeIs('parent.dashboard') ? 'is-active' : '' }}"
            href="{{ route('parent.dashboard') }}">
            <span>▦</span>
            <span>Dashboard</span>
        </a>

        {{-- Data Anak --}}
        <a class="nav-link {{ request()->routeIs('parent.children.*') ? 'is-active' : '' }}"
            href="{{ route('parent.children.index') }}">
            <span>👥</span>
            <span>Data Anak</span>
        </a>

        {{-- Hasil Try Out --}}
        <a class="nav-link {{ request()->routeIs('parent.tryouts.*') ? 'is-active' : '' }}"
            href="{{ route('parent.tryouts.index') }}">
            <span>✏</span>
            <span>Hasil Try Out</span>
        </a>

        {{-- Riwayat Absensi (hanya jika fitur dan route tersedia) --}}
        @if (Route::has('parent.attendances.index'))
            <a class="nav-link {{ request()->routeIs('parent.attendances.*') ? 'is-active' : '' }}"
                href="{{ route('parent.attendances.index') }}">
                <span>📋</span>
                <span>Riwayat Absensi</span>
            </a>
        @endif

        {{-- Akun --}}
        <div class="nav-group">
            <button
                class="nav-link nav-toggle {{ request()->routeIs('parent.profile*') || request()->routeIs('profile*') ? 'is-active' : '' }}"
                type="button" data-menu-toggle
                aria-expanded="{{ request()->routeIs('parent.profile*') || request()->routeIs('profile*') ? 'true' : 'false' }}">
                <span>👤</span>
                <span>Akun</span>
                <span class="chevron">⌄</span>
            </button>
            <div class="submenu" @unless (request()->routeIs('parent.profile*') || request()->routeIs('profile*')) hidden @endunless>
                @if (Route::has('parent.profile'))
                    <a class="submenu-item {{ request()->routeIs('parent.profile') ? 'is-active' : '' }}"
                        href="{{ route('parent.profile') }}">Profil Saya</a>
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
        <span class="status-dot"></span> Orang Tua Aktif <span>v1.0</span>
    </div>
</aside>
