<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$marcos = App\Models\Marco::where('numero', 'like', '10055%')->get();
foreach($marcos as $m) {
    echo $m->credencial . '-' . $m->tipo . '-' . $m->numero . "\n";
}
