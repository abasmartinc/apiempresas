<?php
$segment = 'B65410045-melwood-europe-sl';
$regex = '/^([a-zA-Z][0-9]{7}[a-zA-Z0-9].*)$/';
if (preg_match($regex, $segment, $matches)) {
    echo "Match found!\n";
    print_r($matches);
} else {
    echo "No match for: $segment\n";
}
