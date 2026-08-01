<?php
$logos = [
    'wordpress' => '21759b',
    'googlesheets' => '34a853',
    'zapier' => 'ff4a00',
    'shopify' => '95bf47'
];

foreach ($logos as $name => $color) {
    $url = 'https://unpkg.com/simple-icons@v13/icons/' . $name . '.svg';
    $svg = @file_get_contents($url);
    if ($svg) {
        $svg = str_replace('<svg ', '<svg fill="#'.$color.'" ', $svg);
        file_put_contents('public/img/logos/'.$name.'.svg', $svg);
        echo "Downloaded $name.svg\n";
    } else {
        echo "Failed to download $name.svg\n";
    }
}
