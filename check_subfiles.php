<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = App\Models\Pasta::where('nome', 'FAZENDA ARIZONA')->first();
if ($p) {
    $pastas = App\Models\Pasta::where('parent_id', $p->id)->pluck('id')->toArray();
    $files = App\Models\Arquivo::whereIn('pasta_id', $pastas)->get();
    foreach($files as $f) {
        echo $f->nome . "\n";
    }
}
