<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$updatedFolders = 0;
$updatedFiles = 0;

$pastas = App\Models\Pasta::all();
foreach($pastas as $pasta) {
    if (preg_match('/gnss|metrica|métrica/i', $pasta->nome)) {
        $pasta->oculto = 1;
        $pasta->save();
        $updatedFolders++;
        
        // Hide all subfolders and files inside this folder recursively
        $files = App\Models\Arquivo::where('pasta_id', $pasta->id)->get();
        foreach($files as $f) {
            $f->oculto = 1;
            $f->save();
            $updatedFiles++;
        }
    }
}

$arquivos = App\Models\Arquivo::all();
foreach($arquivos as $arq) {
    if (strtolower($arq->tipo) === 'topo' || preg_match('/topos\.zip$/i', $arq->nome)) {
        if (!$arq->oculto) {
            $arq->oculto = 1;
            $arq->save();
            $updatedFiles++;
        }
    }
}

echo "Hid $updatedFolders folders and $updatedFiles files.\n";
