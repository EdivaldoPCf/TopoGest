<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pastas = App\Models\Pasta::whereNull('parent_id')->get();
foreach($pastas as $p) {
    echo $p->id . ' - ' . $p->nome . "\n";
}
