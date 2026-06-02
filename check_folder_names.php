<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pastas = App\Models\Pasta::where('parent_id', 1)->get(); // 1 = 2026 or Category? Let's just list all pastas
$pastas = App\Models\Pasta::where('tipo_servico', 'pendente')->limit(15)->get();
foreach($pastas as $p) {
    echo $p->nome . "\n";
}
