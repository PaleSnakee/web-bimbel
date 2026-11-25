<header class="admin-navbar">
    <div class="nav-title">
        <button class="menu-button" type="button" data-sidebar-toggle aria-label="Buka navigasi">
            <span></span><span></span><span></span>
        </button>
        <span class="breadcrumb">El Renaissance <b>/</b> @yield('page_title', View::getSection('title', 'Dashboard'))</span>
    </div>
    <div class="nav-actions">
        <div class="profile-menu">
            <button class="profile-button" type="button" data-profile-toggle aria-expanded="false">
                <span class="avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <span class="profile-name">{{ auth()->user()->name }}</span>
                <span>⌄</span>
            </button>
            <div class="profile-dropdown" data-profile-menu hidden>
                <strong>{{ auth()->user()->name }}</strong>
                <span>{{ auth()->user()->email }}</span>
                @if (auth()->user()->student?->classRoom)
                    <span style="display: block; font-size: 11px; color: var(--muted); margin-top: 2px;">Kelas {{ auth()->user()->student->classRoom->name }}</span>
                @endif
                <hr style="border: 0; border-top: 1px solid var(--line); margin: 8px 0;">
                @if (Route::has('student.profile'))
                    <a href="{{ route('student.profile') }}" style="display: block; padding: 6px 0; color: var(--ink); text-decoration: none; font-size: 12px;">Profil Saya</a>
                @elseif (Route::has('profile'))
                    <a href="{{ route('profile') }}" style="display: block; padding: 6px 0; color: var(--ink); text-decoration: none; font-size: 12px;">Profil Saya</a>
                @endif
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">Keluar</button>
                </form>
            </div>
        </div>
    </div>
</header>
