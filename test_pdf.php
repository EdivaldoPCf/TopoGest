<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$arquivos = \App\Models\Arquivo::where('nome', 'like', '%.pdf')->latest()->get();
$arquivo = null;
foreach ($arquivos as $arq) {
    if (file_exists(storage_path('app/public/' . $arq->path))) {
        $arquivo = $arq;
        break;
    }
}
if (!$arquivo) {
    echo "Nenhum PDF com arquivo fisico encontrado.\n";
    exit;
}

if (!$arquivo) {
    echo "Nenhum PDF encontrado no disco.\n";
    exit;
}

$path = storage_path('app/public/' . $arquivo->path);
echo "Lendo arquivo: " . $path . "\n";

$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseFile($path);
$text = $pdf->getText();

echo "\n--- INICIO DO TEXTO EXTRAIDO ---\n";
echo substr($text, 0, 1000);
echo "\n--- FIM DO TEXTO EXTRAIDO ---\n";

if (preg_match_all('/\d{3}\.\d{3}\.\d{3}\-\d{2}/', $text, $matches)) {
    echo "CPF encontrados:\n";
    print_r($matches[0]);
} else {
    echo "Nenhum CPF no formato 000.000.000-00 encontrado.\n";
}

// Teste CNPJ
if (preg_match_all('/\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}/', $text, $matches)) {
    echo "CNPJ encontrados:\n";
    print_r($matches[0]);
}

// Outro formato (apenas números?)
if (preg_match_all('/\b\d{11}\b/', $text, $matches)) {
    echo "Possíveis CPFs sem pontuação:\n";
    print_r($matches[0]);
}
