<?php
echo "--------- CNAE MAIN --------- \n";
preg_match('/<main>(.*?)<\/main>/s', file_get_contents('app/Views/seo/cnae.php'), $m);
echo substr($m[1], 0, 500) . "\n...\n";

echo "--------- NEW PROVINCE MAIN --------- \n";
preg_match('/<main>(.*?)<\/main>/s', file_get_contents('app/Views/seo/new_province.php'), $m2);
echo substr($m2[1], 0, 500) . "\n...\n";
