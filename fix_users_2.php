<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$aguardando = App\Models\User::where('name', 'Aguardando Cliente')->first();
if ($aguardando) {
    App\Models\Pasta::where('cliente_id', $aguardando->id)->update(['cliente_id' => null]);
    echo "Set cliente_id to null for Aguardando Cliente folders.\n";
} else {
    echo "Aguardando Cliente user not found.\n";
}
