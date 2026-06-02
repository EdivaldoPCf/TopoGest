<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function extractCpfFromFiles($files, $adminCpfs = []) {
    foreach ($files as $file) {
        $filename = basename($file);
        $found = null;
        if (preg_match('/\b\d{11}\b/', $filename, $m)) {
            $found = $m[0];
        } elseif (preg_match('/\b\d{3}\.\d{3}\.\d{3}-\d{2}\b/', $filename, $m)) {
            $found = preg_replace('/[^0-9]/', '', $m[0]);
        } elseif (preg_match('/\b\d{14}\b/', $filename, $m)) {
            $found = $m[0];
        } elseif (preg_match('/\b\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}\b/', $filename, $m)) {
            $found = preg_replace('/[^0-9]/', '', $m[0]);
        }
        if ($found && !in_array($found, $adminCpfs)) {
            return $found;
        }
    }
    return null;
}

$adminCpfs = \App\Models\User::where('role', 'admin')->pluck('cpf')->toArray();

$pastas = App\Models\Pasta::whereNull('cliente_id')->whereNotNull('identificador_cliente')->get();
$updated = 0;
foreach($pastas as $pasta) {
    // Collect files
    $files = App\Models\Arquivo::where('pasta_id', $pasta->id)->get();
    $paths = [];
    foreach($files as $f) {
        $paths[] = storage_path('app/public/' . $f->path);
    }
    
    $cpf = extractCpfFromFiles($paths, $adminCpfs);
    if ($cpf) {
        $pasta->identificador_cliente = $cpf;
    } else {
        $pasta->identificador_cliente = null;
    }
    $pasta->save();
    $updated++;
}

echo "Updated $updated folders.\n";
