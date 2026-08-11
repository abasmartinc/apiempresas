<?php
$lines = file('d:/laragon/www/apiempresas/app/Config/Routes.php');
$lines[] = "\n\$routes->get('testpdf2', 'TestPdf2::index');\n";
file_put_contents('d:/laragon/www/apiempresas/app/Config/Routes.php', implode('', $lines));
