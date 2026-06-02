<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pastas = App\Models\Pasta::whereNull('cliente_id')->limit(5)->get();
foreach($pastas as $p) {
    echo "ID: $p->id, Nome: $p->nome, Identificador: $p->identificador_cliente\n";
}
