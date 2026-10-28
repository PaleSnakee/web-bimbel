<header class="admin-navbar">
    <div class="nav-title"><button class="menu-button" type="button" data-sidebar-toggle
            aria-label="Buka navigasi"><span></span><span></span><span></span></button><span class="breadcrumb">Admin
            <b>/</b> Dashboard</span></div>
    <div class="nav-actions">
        <div class="profile-menu"><button class="profile-button" type="button" data-profile-toggle
                aria-expanded="false"><span
                    class="avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><span
                    class="profile-name">{{ auth()->user()->name }}</span><span>⌄</span></button>
            <div class="profile-dropdown" data-profile-menu hidden>
                <strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span>
                <form action="{{ route('logout') }}" method="POST">@csrf <button type="submit">Keluar</button></form>
            </div>
        </div>
    </div>
</header>
