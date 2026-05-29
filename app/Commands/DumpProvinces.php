<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DumpProvinces extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:provinces';
    protected $description = 'Benchmarks optimized CNAE query with fast label lookup.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        $invalidNames = [
            '', ' ', '  ', '-', '.', '..', '...', '8', 'N/A', 'NULL', 'UNDEFINED', 
            '00 DESCONOCIDA', 'desconocido', 'desconocida', 'no disponible', 'n/a', 'unknown', 'sin especificar'
        ];

        CLI::write("=== Benchmarking Fast CNAE & Label Lookup Query ===");
        $start = microtime(true);
        
        // 1. Get top 24 codes using covering index
        $query = $db->query("
            SELECT cnae_code as cnae, COUNT(*) as total 
            FROM companies 
            WHERE cnae_code IS NOT NULL
              AND cnae_code >= '0100'
            GROUP BY cnae_code 
            ORDER BY total DESC 
            LIMIT 24
        ");
        $cnaes = $query->getResultArray();
        
        // 2. Fetch clean label for each
        foreach ($cnaes as &$cnae) {
            $row = $db->table('companies')
                ->select('cnae_label')
                ->where('cnae_code', $cnae['cnae'])
                ->where('cnae_label >=', 'A')
                ->whereNotIn('cnae_label', $invalidNames)
                ->limit(1)
                ->get()
                ->getRowArray();
            $cnae['name'] = $row['cnae_label'] ?? "CNAE {$cnae['cnae']}";
        }
        
        $time = microtime(true) - $start;
        CLI::write(sprintf("Total Time taken: %.4f seconds. Found %d CNAEs.", $time, count($cnaes)));
        foreach ($cnaes as $c) {
            CLI::write(sprintf("- Code: %s | Label: %s | Count: %d", $c['cnae'], $c['name'], $c['total']));
        }
    }
}
