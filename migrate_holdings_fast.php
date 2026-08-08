<?php
$db = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
if($db->connect_error) die('Connection failed');

function generate_slug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    if (function_exists('iconv')) $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text)) return 'n-a';
    return $text;
}

$batchSize = 5000;
$totalUpdated = 0;

while (true) {
    // Fetch a batch
    $res = $db->query("SELECT id, name FROM holdings WHERE slug IS NULL OR slug = '' LIMIT $batchSize");
    if ($res->num_rows == 0) {
        break; // All done
    }

    $db->begin_transaction();
    
    // Track slugs in this batch to prevent duplicates inside the batch
    $batchSlugs = [];

    while($row = $res->fetch_assoc()) {
        $slug = generate_slug($row['name']);
        
        // Very basic unique handling for speed (append ID if duplicate exists in DB)
        // Since doing a SELECT for every row is slow, we will just try to update
        // If it fails on UNIQUE constraint, we catch it and update with ID.
        // Wait, it's faster to just proactively append the ID to the slug if we know it's highly likely to duplicate, 
        // OR we can just rely on the ID being unique.
        
        $safe_slug = $db->real_escape_string($slug);
        
        try {
            $db->query("UPDATE holdings SET slug = '{$safe_slug}' WHERE id = {$row['id']}");
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $fallback_slug = $safe_slug . "-" . $row['id'];
                $db->query("UPDATE holdings SET slug = '{$fallback_slug}' WHERE id = {$row['id']}");
            } else {
                throw $e;
            }
        }
        
        $totalUpdated++;
    }
    
    $db->commit();
    echo "Updated $totalUpdated holdings...\n";
}

echo "Done! Total updated: $totalUpdated\n";
