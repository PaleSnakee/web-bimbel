<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Informasi Manajemen Bimbingan Belajar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="container">
            <h1 class="mb-0 fw-bold">Dashboard Admin</h1>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <h2 class="card-title mb-3">Selamat Datang, {{ Auth::user()->name }}</h2>
                        <p class="card-text lead">Anda masuk sebagai <strong>Admin</strong></p>
                        <hr>
                        <p class="text-muted">Dashboard Admin sedang dalam pengembangan...</p>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
