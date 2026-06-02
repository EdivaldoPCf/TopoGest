<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

do {
    $duplicates = App\Models\Pasta::select('nome', 'parent_id', \DB::raw('MIN(id) as min_id'), \DB::raw('count(*) as c'))
        ->groupBy('nome', 'parent_id')
        ->having('c', '>', 1)
        ->get();

    $merged = 0;
    foreach($duplicates as $dup) {
        $all = App\Models\Pasta::where('nome', $dup->nome)->where('parent_id', $dup->parent_id)->orderBy('id', 'asc')->get();
        $keep = $all->first();
        
        foreach($all as $pasta) {
            if ($pasta->id === $keep->id) continue;
            
            // Move child folders to the main one
            App\Models\Pasta::where('parent_id', $pasta->id)->update(['parent_id' => $keep->id]);
            
            // Move arquivos to the main one
            App\Models\Arquivo::where('pasta_id', $pasta->id)->update(['pasta_id' => $keep->id]);
            
            // Delete the duplicate
            $pasta->delete();
            $merged++;
        }
    }
} while($merged > 0);

echo "Duplicates merged successfully!\n";
