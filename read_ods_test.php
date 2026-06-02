<?php
$files = glob('E:\ods\*.ods');
$file = $files[0];
$zip = new ZipArchive;
if ($zip->open($file) === TRUE) {
    $content = $zip->getFromName('content.xml');
    $zip->close();
    $content = str_replace(['><', '</text:p>'], ['> <', ' </text:p>'], $content);
    $text = strip_tags($content);
    
    // regex to extract lines that look like table rows with coordinates
    // We want to see some context around Vértice
    if (preg_match('/V[eé]rtice(.{0,500})/is', $text, $matches)) {
        echo "Found Vertice context:\n";
        echo $matches[1] . "\n";
    }
} else {
    echo "Cannot open file\n";
}
