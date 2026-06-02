<?php
$files = glob('E:/ods/*.ods');
$count = 0;
foreach ($files as $file) {
    if ($count++ > 5) break;
    echo "FILE: $file\n";
    $zip = new ZipArchive;
    if ($zip->open($file) === TRUE) {
        $content = $zip->getFromName('content.xml');
        $zip->close();
        if (!$content) continue;
        
        $dom = new DOMDocument();
        @$dom->loadXML($content);
        
        $tables = $dom->getElementsByTagNameNS('*', 'table');
        foreach ($tables as $table) {
            $name = $table->getAttribute('table:name');
            if (stripos($name, 'perimetro') !== false) {
                echo "  TAB: $name\n";
                $rows = $table->getElementsByTagNameNS('*', 'table-row');
                $started = false;
                $rowCount = 0;
                foreach ($rows as $row) {
                    $cells = $row->getElementsByTagNameNS('*', 'table-cell');
                    $rowText = [];
                    foreach ($cells as $cell) {
                        $text = trim($cell->textContent);
                        if ($text !== '') $rowText[] = $text;
                    }
                    if (empty($rowText)) continue;
                    
                    if (stripos($rowText[0], 'Vértice') !== false || stripos($rowText[0], 'Vertice') !== false) {
                        $started = true;
                        echo "    HEAD: " . implode(" | ", $rowText) . "\n";
                        continue;
                    }
                    if ($started) {
                        echo "    ROW: " . implode(" | ", $rowText) . "\n";
                        $rowCount++;
                        if ($rowCount > 5) break;
                    }
                }
            }
        }
    }
}
