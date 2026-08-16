<?php

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArgvInput(['artisan', 'cache:clear']),
    new Symfony\Component\Console\Output\BufferedOutput
);
$kernel->handle(
    $input = new Symfony\Component\Console\Input\ArgvInput(['artisan', 'view:clear']),
    new Symfony\Component\Console\Output\BufferedOutput
);
$kernel->handle(
    $input = new Symfony\Component\Console\Input\ArgvInput(['artisan', 'config:clear']),
    new Symfony\Component\Console\Output\BufferedOutput
);
$kernel->handle(
    $input = new Symfony\Component\Console\Input\ArgvInput(['artisan', 'route:clear']),
    new Symfony\Component\Console\Output\BufferedOutput
);

echo "Laravel caches cleared successfully!";
