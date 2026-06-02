<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::statement('SET FOREIGN_KEY_CHECKS=0;');
DB::table('pasta_user')->truncate();
DB::table('arquivos')->truncate();
DB::table('pendencias')->truncate();
DB::table('pastas')->truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');
echo 'OK';
