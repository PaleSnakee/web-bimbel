
<?php

$appBasePath = $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__);

// Gunakan direktori sementara yang writable di Vercel.
$cachePath = '/tmp/laravel-bootstrap-cache';

if (!is_dir($cachePath)) {
    mkdir($cachePath, 0755, true);
}

$app = new Illuminate\Foundation\Application($appBasePath);

// Arahkan lokasi cache Laravel ke direktori sementara.
$app->useBootstrapPath($cachePath);

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

return $app;
