<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use App\Services\OpenAiService;
use App\Libraries\B2B\CompanySemanticProfileBuilder;

class B2BGenerateEmbeddings extends BaseCommand {
    protected $group       = 'B2B Scoring';
    protected $name        = 'embeddings:companies';
    protected $description = 'Generate embeddings for a 1000 company sample';

    public function run(array $params) {
        CLI::write("Generating embeddings for sample...", 'green');
        $db = Database::connect();
        $openai = new OpenAiService();
        $config = config('B2BScoring');
        
        // LIMIT 100
        $companies = $db->query("SELECT id, cnae_label, objeto_social FROM companies WHERE objeto_social IS NOT NULL LIMIT 100")->getResultArray();
        
        $processed = 0;
        $created = 0;
        $reused = 0;
        $failures = 0;
        
        foreach($companies as $c) {
            $processed++;
            $text = CompanySemanticProfileBuilder::buildSemanticText($c);
            $hash = hash('sha256', $text);
            
            $existing = $db->table('company_embeddings')->where('company_id', $c['id'])
                           ->where('embedding_model', $config->embedding_model)
                           ->where('embedding_version', $config->embedding_version)
                           ->get()->getRowArray();
                           
            if ($existing && $existing['source_hash'] === $hash) {
                $reused++;
                continue;
            }
            
            $emb = $openai->getEmbeddings($text);
            if (!$emb) {
                $failures++;
                continue;
            }
            
            $db->query("INSERT INTO company_embeddings (company_id, embedding, embedding_model, embedding_version, source_hash) VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE embedding = VALUES(embedding), source_hash = VALUES(source_hash), updated_at = NOW()",
                        [$c['id'], json_encode($emb), $config->embedding_model, $config->embedding_version, $hash]);
            $created++;
            
            if ($processed % 10 === 0) CLI::write("Processed: $processed");
        }
        
        CLI::write("Processed: $processed");
        CLI::write("Created/Regenerated: $created");
        CLI::write("Reused: $reused");
        CLI::write("Failures: $failures");
    }
}