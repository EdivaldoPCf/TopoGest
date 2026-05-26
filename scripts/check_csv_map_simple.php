<?php
$csv = 'E:\\BASES\\importacao_bases.csv';
if (!file_exists($csv)) {
    echo "CSV not found\n";
    exit(1);
}
$handle = fopen($csv, 'r');
$headers = fgetcsv($handle);
if ($headers === false) {
    echo "No headers\n";
    exit(1);
}
$headers = array_map('strtolower', $headers);
while (($row = fgetcsv($handle)) !== false) {
    if (count($row) !== count($headers)) {
        echo "count mismatch: " . count($row) . " vs " . count($headers) . "\n";
        var_export($row);
        echo "\n";
        continue;
    }
    $entry = array_combine($headers, $row);
    if (trim($entry['nome']) === 'base 1') {
        echo "entry=" . json_encode($entry) . "\n";
        break;
    }
}
fclose($handle);
