<aside class="admin-sidebar" aria-label="Navigasi siswa">
    <div class="brand">
        <span class="brand-mark">E</span>
        <span>El Renaissance</span>
    </div>
    <nav class="sidebar-nav">
        {{-- Dashboard --}}
        <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'is-active' : '' }}"
            href="{{ route('student.dashboard') }}">
            <span>▦</span>
            <span>Dashboard</span>
        </a>

        {{-- Akademik (hanya jika fitur dan route siswa memang tersedia) --}}
        @if (Route::has('student.learning-schedules.index') || Route::has('student.schedules.index') || Route::has('student.student-attendances.index') || Route::has('student.attendances.index'))
            <div class="nav-group">
                <button
                    class="nav-link nav-toggle {{ request()->routeIs('student.learning-schedules.*') || request()->routeIs('student.schedules.*') || request()->routeIs('student.student-attendances.*') || request()->routeIs('student.attendances.*') ? 'is-active' : '' }}"
                    type="button" data-menu-toggle
                    aria-expanded="{{ request()->routeIs('student.learning-schedules.*') || request()->routeIs('student.schedules.*') || request()->routeIs('student.student-attendances.*') || request()->routeIs('student.attendances.*') ? 'true' : 'false' }}">
                    <span>▤</span>
                    <span>Akademik</span>
                    <span class="chevron">⌄</span>
                </button>
                <div class="submenu" @unless (request()->routeIs('student.learning-schedules.*') || request()->routeIs('student.schedules.*') || request()->routeIs('student.student-attendances.*') || request()->routeIs('student.attendances.*')) hidden @endunless>
                    @if (Route::has('student.learning-schedules.index'))
                        <a class="submenu-item {{ request()->routeIs('student.learning-schedules.*') ? 'is-active' : '' }}"
                            href="{{ route('student.learning-schedules.index') }}">Jadwal Pembelajaran</a>
                    @elseif (Route::has('student.schedules.index'))
                        <a class="submenu-item {{ request()->routeIs('student.schedules.*') ? 'is-active' : '' }}"
                            href="{{ route('student.schedules.index') }}">Jadwal Pembelajaran</a>
                    @endif

                    @if (Route::has('student.student-attendances.index'))
                        <a class="submenu-item {{ request()->routeIs('student.student-attendances.*') ? 'is-active' : '' }}"
                            href="{{ route('student.student-attendances.index') }}">Riwayat Absensi</a>
                    @elseif (Route::has('student.attendances.index'))
                        <a class="submenu-item {{ request()->routeIs('student.attendances.*') ? 'is-active' : '' }}"
                            href="{{ route('student.attendances.index') }}">Riwayat Absensi</a>
                    @endif
                </div>
            </div>
        @endif

        {{-- Try Out --}}
        <div class="nav-group">
            <button
                class="nav-link nav-toggle {{ request()->routeIs('student.tryouts.*') ? 'is-active' : '' }}"
                type="button" data-menu-toggle
                aria-expanded="{{ request()->routeIs('student.tryouts.*') ? 'true' : 'false' }}">
                <span>✏</span>
                <span>Try Out</span>
                <span class="chevron">⌄</span>
            </button>
            <div class="submenu" @unless (request()->routeIs('student.tryouts.*')) hidden @endunless>
                <a class="submenu-item {{ request()->routeIs('student.tryouts.index') ? 'is-active' : '' }}"
                    href="{{ route('student.tryouts.index') }}">Daftar Try Out</a>
                <a class="submenu-item"
                    href="{{ route('student.dashboard') }}#riwayat-pengerjaan">Riwayat Pengerjaan</a>
                <a class="submenu-item"
                    href="{{ route('student.dashboard') }}#informasi-hasil">Hasil Try Out</a>
            </div>
        </div>

        {{-- Akun --}}
        <div class="nav-group">
            <button
                class="nav-link nav-toggle {{ request()->routeIs('student.profile*') || request()->routeIs('profile*') ? 'is-active' : '' }}"
                type="button" data-menu-toggle
                aria-expanded="{{ request()->routeIs('student.profile*') || request()->routeIs('profile*') ? 'true' : 'false' }}">
                <span>👤</span>
                <span>Akun</span>
                <span class="chevron">⌄</span>
            </button>
            <div class="submenu" @unless (request()->routeIs('student.profile*') || request()->routeIs('profile*')) hidden @endunless>
                @if (Route::has('student.profile'))
                    <a class="submenu-item {{ request()->routeIs('student.profile') ? 'is-active' : '' }}"
                        href="{{ route('student.profile') }}">Profil Saya</a>
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
        <span class="status-dot"></span> Sistem aktif <span>v1.0</span>
    </div>
</aside>
