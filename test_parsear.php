<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = App\Models\Pasta::where('nome', 'Fazenda Arizona')->first();
$controller = app(\App\Http\Controllers\SigefMapaController::class);
$response = $controller->parsear($p->id);
echo json_encode($response->getData());
