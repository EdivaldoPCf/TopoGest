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
        
        $content = str_replace('><', '> <', $content);
        $xml = @simplexml_load_string($content);
        if (!$xml) continue;
        
        $xml->registerXPathNamespace('table', 'urn:oasis:names:tc:opendocument:xmlns:table:1.0');
        $xml->registerXPathNamespace('text', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0');

        $tables = $xml->xpath('//table:table');
        foreach ($tables as $table) {
            $name = (string)$table->attributes('table', true)->name;
            echo "  TAB: $name\n";
            $rows = $table->xpath('.//table:table-row');
            foreach ($rows as $rowIndex => $row) {
                if ($rowIndex > 15) break;
                $cells = $row->xpath('.//table:table-cell');
                $rowText = [];
                foreach ($cells as $cell) {
                    $text = trim((string)$cell);
                    if (empty($text)) {
                        $textNodes = $cell->xpath('.//text:p');
                        $t = '';
                        foreach ($textNodes as $node) {
                            $t .= (string)$node . ' ';
                        }
                        $text = trim($t);
                    }
                    if ($text !== '') $rowText[] = $text;
                }
                if (!empty($rowText)) {
                    echo "    ROW $rowIndex: " . implode(" | ", $rowText) . "\n";
                }
            }
        }
    }
}
