<?php
$files = [
    'app/Views/company.php',
    'app/Views/contact.php',
    'app/Views/dashboard_construction.php',
    'app/Views/documentation.php',
    'app/Views/purchase_success.php',
    'app/Views/auth/forgot_password.php',
    'app/Views/auth/login.php',
    'app/Views/auth/register.php',
    'app/Views/auth/register_success.php',
    'app/Views/auth/reset_password.php',
    'app/Views/map/companies_map.php',
    'app/Views/partials/footer.php',
    'app/Views/partials/header.php',
    'app/Views/partials/header_inner.php',
];

$bom = chr(0xEF) . chr(0xBB) . chr(0xBF);

foreach ($files as $f) {
    $path = __DIR__ . '/' . $f;
    if (!file_exists($path)) {
        echo "Not found: $f\n";
        continue;
    }
    $content = file_get_contents($path);
    if (substr($content, 0, 3) === $bom) {
        file_put_contents($path, substr($content, 3));
        echo "Fixed BOM: $f\n";
    } else {
        echo "OK (no BOM): " . basename($f) . "\n";
    }
}
echo "Done.\n";
