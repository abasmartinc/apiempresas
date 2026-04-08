<?php
require_once dirname(__DIR__) . '/app/Config/Paths.php';
require_once FCPATH . 'index.php';

$db = \Config\Database::connect();
$forge = \Config\Database::forge();

$fields = [
    'email' => [
        'type'       => 'VARCHAR',
        'constraint' => 255,
        'null'       => true,
        'after'      => 'phone_mobile'
    ],
    'website_official' => [
        'type'       => 'VARCHAR',
        'constraint' => 255,
        'null'       => true,
        'after'      => 'email'
    ]
];

try {
    $forge->addColumn('companies', $fields);
    echo "Columns added successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
