<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Siswa') — El Renaissance</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="student-header">
        <div class="student-container student-header-inner">
            <a class="student-brand" href="{{ route('student.dashboard') }}">El Renaissance</a>
            <nav class="student-nav" aria-label="Navigasi siswa">
                <a href="{{ route('student.dashboard') }}">Dashboard</a>
                <a href="{{ route('student.tryouts.index') }}">Try Out</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit">Keluar</button>
                </form>
            </nav>
        </div>
    </header>
    <main class="student-container student-main">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>