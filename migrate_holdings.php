<?php
$db = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
if($db->connect_error) die('Connection failed');

// 1. Alter table to add slug column
$db->query("ALTER TABLE holdings ADD COLUMN slug VARCHAR(255) NULL AFTER name");
echo $db->error . "\n";
$db->query("ALTER TABLE holdings ADD UNIQUE INDEX(slug)");
echo $db->error . "\n";

// Function to generate slug
function generate_slug($text) {
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // transliterate
    if (function_exists('iconv')) {
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    }
    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    // trim
    $text = trim($text, '-');
    // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    // lowercase
    $text = strtolower($text);
    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}

// 2. Backfill slugs
$res = $db->query("SELECT id, name FROM holdings WHERE slug IS NULL");
$count = 0;
while($row = $res->fetch_assoc()) {
    $slug = generate_slug($row['name']);
    // handle potential duplicates
    $original_slug = $slug;
    $suffix = 1;
    while(true) {
        $check = $db->query("SELECT id FROM holdings WHERE slug = '" . $db->real_escape_string($slug) . "' AND id != " . $row['id']);
        if($check->num_rows == 0) {
            break;
        }
        $slug = $original_slug . '-' . $suffix;
        $suffix++;
    }
    
    $update = $db->query("UPDATE holdings SET slug = '" . $db->real_escape_string($slug) . "' WHERE id = " . $row['id']);
    $count++;
}
echo "Backfilled $count slugs.\n";
