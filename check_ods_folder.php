<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$arq = App\Models\Arquivo::where('nome', 'Faz_Arizona_Desmembramento.ODS')->first();
if ($arq) {
    echo "Pasta_id: {$arq->pasta_id}\n";
    $p = App\Models\Pasta::find($arq->pasta_id);
    echo "Folder: {$p->nome} (parent_id: {$p->parent_id})\n";
    if ($p->parent) {
        echo "Parent: {$p->parent->nome} (parent_id: {$p->parent->parent_id})\n";
    }
}
