<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pastas = App\Models\Pasta::with('cliente')->whereNotNull('cliente_id')->get();
$dummyUsers = [];
foreach($pastas as $pasta) {
    if ($pasta->cliente && $pasta->cliente->created_at > '2026-06-02 12:00:00') {
        if (preg_match('/\d{2,3}@getectopografia\.com\.br$/', $pasta->cliente->email) || strpos($pasta->cliente->email, 'aguardando') !== false) {
            $dummyUsers[$pasta->cliente->id] = $pasta->cliente;
        }
    }
}

foreach($dummyUsers as $user) {
    echo "ID: $user->id, Nome: $user->name, Email: $user->email\n";
}
