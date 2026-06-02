<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$files = App\Models\Arquivo::where('nome', 'like', '%topo%')->limit(10)->get();
foreach($files as $f) {
    echo "File: $f->nome\n";
}

$pastas = App\Models\Pasta::where('nome', 'like', '%metrica%')->limit(10)->get();
foreach($pastas as $p) {
    echo "Folder: $p->nome\n";
}

$pastas = App\Models\Pasta::where('nome', 'like', '%gnss%')->limit(10)->get();
foreach($pastas as $p) {
    echo "Folder: $p->nome\n";
}
