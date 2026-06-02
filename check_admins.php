<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$adminCpfs = App\Models\User::where('role', 'admin')->pluck('cpf')->toArray();
print_r($adminCpfs);
