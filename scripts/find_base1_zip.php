<?php
$dir = 'E:\\BASES';
$files = glob($dir . '\\*.zip');
foreach ($files as $file) {
    $name = pathinfo($file, PATHINFO_FILENAME);
    if (strpos($name, 'base 1') !== false) {
        echo basename($file) . '\n';
    }
}
