<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = App\Models\Pasta::where('nome', 'FAZENDA ARIZONA')->first();
if ($p) {
    $files = App\Models\Arquivo::where('pasta_id', $p->id)->get();
    foreach($files as $f) {
        echo $f->nome . "\n";
    }
}
