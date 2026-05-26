<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Base;
$b = Base::where('nome','base 239')->first();
if (!$b) {
    echo "Base 239 not found\n";
    exit(1);
}
echo "nome={$b->nome}\n";
echo "norte={$b->norte}\n";
echo "este={$b->este}\n";
echo "norte_original=" . ($b->norte_original ?? 'NULL') . "\n";
echo "este_original=" . ($b->este_original ?? 'NULL') . "\n";
echo "latitude={$b->latitude}\n";
echo "longitude={$b->longitude}\n";
