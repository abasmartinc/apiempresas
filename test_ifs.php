<?php
$content = file_get_contents('d:/laragon/www/apiempresas/app/Views/tickets/show.php');
$lines = explode("\n", $content);
$open = [];
foreach ($lines as $i => $line) {
    if (preg_match('/<\?php\s*if\s*\(/i', $line)) {
        if (strpos($line, 'endif;') === false && strpos($line, '}') === false) {
             if (preg_match('/:\s*\?>/', $line)) {
                 $open[] = $i + 1;
             }
        }
    }
    if (preg_match('/<\?php\s*endif/i', $line)) {
        array_pop($open);
    }
}
echo "Unclosed IFs at lines: " . implode(', ', $open) . "\n";
