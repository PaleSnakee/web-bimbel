
<?php

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../public/index.php';

foreach ([
    '/tmp/laravel-cache',
    '/tmp/laravel-bootstrap-cache',
    '/tmp/framework/sessions',
    '/tmp/framework/views',
] as $directory) {
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(
    Illuminate\Contracts\Http\Kernel::class
);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
