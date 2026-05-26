<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Truncating 'bases' table...\n";
DB::table('bases')->truncate();
echo "Done truncating table.\n";

$storage = storage_path('app/public/bases_zip');
if (is_dir($storage)) {
    $files = glob($storage . '/*');
    foreach ($files as $f) {
        if (is_file($f)) {
            @unlink($f);
        }
    }
    echo "Removed files in: {$storage}\n";
} else {
    echo "Storage folder not found: {$storage}\n";
}

echo "Truncate script finished.\n";
