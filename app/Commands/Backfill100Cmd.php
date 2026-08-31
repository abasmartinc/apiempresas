<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use PDO;
use App\Libraries\B2B\CompanySemanticProfileBuilder;
use App\Services\OpenAiService;

class Backfill100Cmd extends BaseCommand {
    protected $group       = 'B2B Scoring';
    protected $name        = 'score:backfill-100';
    protected $description = 'Micro-backfill controlado de 100 nuevas empresas en produccion con rate limit handling';

    public function run(array $params) {
        $config = config('B2BScoring');
        $model = $config->embedding_model ?? 'text-embedding-3-small';
        $version = $config->company_embedding_version ?? '1.0.0';
        $targetNewCount = 100;

        CLI::write("=== MICRO-BACKFILL 100 COMPANIES ===", 'yellow');
        CLI::write("Model: $model, Version: $version", 'cyan');
        
        $dsn = 'mysql:host=217.61.210.127;dbname=reseller3537_apiempresas;charset=utf8mb4';
        $user = 'apiempresas_user';
        $pass = 'WONwyjpsmx3h3$@2';
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

        $openai = new OpenAiService();

        $stats = [
            'target_new' => $targetNewCount,
            'processed_candidates' => 0,
            'already_existed' => 0,
            'generated' => 0,
            'failed' => 0,
            'rate_limits' => 0,
            'openai_calls' => 0,
            'latencies' => [],
            'write_latencies' => []
        ];

        $lastId = 0;
        
        while ($stats['generated'] < $targetNewCount) {
            $stmt = $pdo->prepare("SELECT id, cnae_code, cnae_label, objeto_social FROM companies WHERE id > ? AND objeto_social IS NOT NULL AND cnae_label IS NOT NULL ORDER BY id ASC LIMIT 500");
            $stmt->execute([$lastId]);
            $candidates = $stmt->fetchAll();
            
            if (empty($candidates)) {
                CLI::write("No more companies found.", 'red');
                break;
            }
            
            foreach ($candidates as $c) {
                if ($stats['generated'] >= $targetNewCount) break;
                
                $companyId = $c['id'];
                
                // Keep moving the pointer forward, but we'll retry if rate limited
                $lastId = $companyId;
                
                $stats['processed_candidates']++;
                $canonicalText = CompanySemanticProfileBuilder::build($c);
                $sourceHash = CompanySemanticProfileBuilder::hash($canonicalText);

                $stmtCheck = $pdo->prepare("SELECT id FROM company_embeddings WHERE company_id = ? AND embedding_model = ? AND embedding_version = ?");
                $stmtCheck->execute([$companyId, $model, $version]);
                
                if ($stmtCheck->fetch()) {
                    $stats['already_existed']++;
                    continue;
                }

                $retries = 3;
                $success = false;
                $embedding = null;
                
                while ($retries > 0 && !$success) {
                    usleep(1000 * 1000); // 1s baseline throttle to be gentler

                    $t0 = microtime(true);
                    $embedding = $openai->getEmbeddings($canonicalText);
                    $stats['openai_calls']++;

                    $valid = true;
                    if (!$embedding || !is_array($embedding) || count($embedding) !== 1536) $valid = false;
                    
                    if (!$valid) {
                        $stats['rate_limits']++;
                        CLI::write("Rate limit or invalid result for Company $companyId. Sleeping 20s... (Retries left: ".($retries-1).")", 'yellow');
                        sleep(20);
                        $retries--;
                    } else {
                        $success = true;
                        $stats['latencies'][] = microtime(true) - $t0;
                    }
                }

                if (!$success) {
                    $stats['failed']++;
                    CLI::error("Company $companyId: FAILED after retries.");
                    continue;
                }

                $tW = microtime(true);
                try {
                    $stmtWrite = $pdo->prepare("INSERT INTO company_embeddings (company_id, embedding_model, embedding_version, source_hash, embedding, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE source_hash = VALUES(source_hash), embedding = VALUES(embedding), updated_at = NOW()");
                    $stmtWrite->execute([$companyId, $model, $version, $sourceHash, json_encode($embedding)]);
                    $stats['write_latencies'][] = microtime(true) - $tW;
                    $stats['generated']++;
                    
                    CLI::write("Generated {$stats['generated']}/$targetNewCount...");
                } catch (\Exception $e) {
                    $stats['failed']++;
                    CLI::error("Company $companyId WRITE FAILED: " . $e->getMessage());
                }
            }
        }

        $artifactDir = 'C:/Users/papel/.gemini/antigravity/brain/c8b17c04-4d0a-454b-a66d-56ce3eb1b3f9/scratch/';
        $statsFile = $artifactDir . 'backfill_100_stats.json';
        file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        CLI::write("DONE. Stats saved to $statsFile", 'green');
    }
}