<?php

$appBasePath = $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__);
$runtimePath = '/tmp/laravel';
$bootstrapCachePath = $runtimePath.'/bootstrap/cache';

foreach ([
    $runtimePath,
    $runtimePath.'/logs',
    $runtimePath.'/framework/cache',
    $runtimePath.'/framework/sessions',
    $runtimePath.'/framework/views',
    $bootstrapCachePath,
] as $directory) {
    if (! is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}

$app = new Illuminate\Foundation\Application($appBasePath);

$app->useStoragePath($runtimePath);
$app->useBootstrapPath($runtimePath.'/bootstrap');

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
