<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pastas = App\Models\Pasta::select('nome', 'parent_id', \DB::raw('count(*) as c'))->groupBy('nome', 'parent_id')->having('c', '>', 1)->get();
foreach($pastas as $p) {
    echo $p->parent_id . ' -> ' . $p->nome . ' (' . $p->c . ")\n";
}
