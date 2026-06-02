<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pastas = App\Models\Pasta::whereNotNull('identificador_cliente')->get();
$cleared = 0;
foreach($pastas as $pasta) {
    if (!preg_match('/^\d{11}$/', $pasta->identificador_cliente) && !preg_match('/^\d{14}$/', $pasta->identificador_cliente)) {
        $pasta->identificador_cliente = null;
        $pasta->save();
        $cleared++;
    }
}
echo "Cleared identificador_cliente on $cleared folders.\n";
