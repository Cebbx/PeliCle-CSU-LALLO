<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (\App\Models\Vehicle::all() as $v) {
    echo "ID: {$v->id} | Brand: '{$v->brand}' | Model: '{$v->model}' | Plate: '{$v->plate_number}'" . PHP_EOL;
}
