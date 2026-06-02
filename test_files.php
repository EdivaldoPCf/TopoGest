<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$arquivos = \App\Models\Arquivo::latest()->take(15)->get();
foreach($arquivos as $a) {
    echo $a->id . " - " . $a->nome . " - " . $a->created_at . "\n";
}
