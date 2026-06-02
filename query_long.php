<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$marcos = App\Models\Marco::whereRaw('LENGTH(numero) > 4')->limit(10)->get();
foreach($marcos as $m) {
    echo $m->credencial . '-' . $m->tipo . '-' . $m->numero . "\n";
}
