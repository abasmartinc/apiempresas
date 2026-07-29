<?php
$dir = new RecursiveDirectoryIterator(__DIR__ . '/app/Views');
$ite = new RecursiveIteratorIterator($dir);
$count = 0;
foreach($ite as $file) {
    if($file->getExtension() === 'php') {
        $c = file_get_contents($file->getPathname());
        if(substr($c, 0, 3) === "\xEF\xBB\xBF") {
            file_put_contents($file->getPathname(), substr($c, 3));
            echo "Removed BOM from: " . $file->getPathname() . "\n";
            $count++;
        }
    }
}
echo "Total files fixed: $count\n";
