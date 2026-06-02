<?php
require 'vendor/autoload.php';

$path = "\\\\Getec-pc\\f\\Trabalhos Getec V2\\2026\\PARTICULAR\\Lote 90 - PAD Pedro Peixoto (Maria José)";
$pdfPath = $path . "\\Declaração Tabular.pdf";

echo "Parsing: $pdfPath\n";
$parser = new \Smalot\PdfParser\Parser();
try {
    $pdf = $parser->parseFile($pdfPath);
    $text = $pdf->getText();
    
    // Find CPF
    preg_match_all('/\d{3}\.\d{3}\.\d{3}\-\d{2}/', $text, $matches);
    echo "Found CPFs in PDF: " . implode(", ", array_unique($matches[0])) . "\n";
    
    // Find Vertices BCA-M, BCA-P, BCA-V
    preg_match_all('/BCA-[MPV][\w\d\-]+/', $text, $vertices);
    echo "Found Vertices in PDF: " . implode(", ", array_unique($vertices[0])) . "\n";
} catch (\Exception $e) {
    echo "Error parsing PDF: " . $e->getMessage() . "\n";
}

// Find ODS file in the subdirectories
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
foreach ($iterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'ods') {
        echo "Found ODS: " . $file->getPathname() . "\n";
    }
}
