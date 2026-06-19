<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CountEnrichment extends BaseCommand
{
    protected $group = 'App';
    protected $name = 'app:count';
    protected $description = 'Counts records.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $count = $db->table('company_enrichment')
            ->where('ai_seo_text IS NOT NULL')
            ->where('ai_pitch IS NULL')
            ->countAllResults();
        CLI::write("Total records to enrich: " . $count, 'yellow');
    }
}
