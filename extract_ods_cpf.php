<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$parserService = app(\App\Services\SigefOdsParserService::class);
$pastasL3 = App\Models\Pasta::whereNotNull('parent_id')
    ->whereHas('parent', function($q) {
        $q->whereNotNull('parent_id');
    })
    ->get(); // This will get all pastas. We can filter further.

$updated = 0;

foreach ($pastasL3 as $pasta) {
    // Only process Level 3 (has parent, parent has parent, but parent->parent does not have parent)
    $p = $pasta->parent;
    if ($p && $p->parent_id && !$p->parent->parent_id) {
        // Find ODS file recursively
        $odsFiles = [];
        $searchOds = function($folder) use (&$searchOds, &$odsFiles) {
            foreach($folder->arquivos as $arq) {
                if (strtolower($arq->tipo) === 'ods' || pathinfo($arq->path, PATHINFO_EXTENSION) === 'ods') {
                    $odsFiles[] = $arq;
                }
            }
            foreach($folder->subpastas as $sub) {
                $searchOds($sub);
            }
        };
        $searchOds($pasta);

        if (!empty($odsFiles) && empty($pasta->identificador_cliente)) {
            foreach($odsFiles as $ods) {
                $caminho = Storage::disk('public')->path($ods->path);
                if (file_exists($caminho)) {
                    try {
                        $dados = $parserService->parseOdsFile($caminho);
                        if (!empty($dados['identificacao']['cpf_cnpj'])) {
                            $cpfRaw = preg_replace('/[^0-9]/', '', $dados['identificacao']['cpf_cnpj']);
                            if ($cpfRaw) {
                                $pasta->identificador_cliente = $cpfRaw;
                                $pasta->save();
                                $updated++;
                                echo "Extracted CPF $cpfRaw from ODS for folder {$pasta->nome}\n";
                                break; // Found one, no need to parse other ODS in the same folder
                            }
                        }
                    } catch (\Exception $e) {
                        // ignore
                    }
                }
            }
        }
    }
}

echo "Updated $updated folders with CPF from ODS.\n";
