<?php
$files = glob('E:/ods/*.ods');
$count = 0;
foreach ($files as $file) {
    if ($count++ > 3) break;
    echo "FILE: $file\n";
    $zip = new ZipArchive;
    if ($zip->open($file) === TRUE) {
        $content = $zip->getFromName('content.xml');
        $zip->close();
        if (!$content) continue;
        
        // Remove text:p to just make extraction easier
        $content = str_replace(['<text:p>', '</text:p>'], ['', ' '], $content);
        
        // Find tables
        preg_match_all('/<table:table[^>]*table:name="([^"]+)"[^>]*>(.*?)<\/table:table>/is', $content, $tables);
        
        foreach ($tables[1] as $index => $tableName) {
            echo "  TAB: $tableName\n";
            if (stripos($tableName, 'perimetro') !== false || stripos($tableName, 'perímetro') !== false || stripos($tableName, 'Lote 18') !== false || stripos($tableName, 'Vertices') !== false) {
                $tableHtml = $tables[2][$index];
                
                // Find rows
                preg_match_all('/<table:table-row[^>]*>(.*?)<\/table:table-row>/is', $tableHtml, $rows);
                
                $started = false;
                $rowCount = 0;
                foreach ($rows[1] as $rowHtml) {
                    // Find cells
                    preg_match_all('/<table:table-cell[^>]*>(.*?)<\/table:table-cell>/is', $rowHtml, $cells);
                    
                    $rowText = [];
                    foreach ($cells[1] as $cellHtml) {
                        $text = trim(strip_tags($cellHtml));
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
