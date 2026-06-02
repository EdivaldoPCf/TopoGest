<?php
$file = $argv[1];
$c = file_get_contents($file);
$div_open = substr_count($c, '<div');
$div_close = substr_count($c, '</div');
$section_open = substr_count($c, '<section');
$section_close = substr_count($c, '</section');
echo "$file -> divs: +$div_open -$div_close (" . ($div_open - $div_close) . ")\n";
echo "$file -> sections: +$section_open -$section_close (" . ($section_open - $section_close) . ")\n";
