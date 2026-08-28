<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use PDO;
use App\Libraries\B2B\CompanySemanticProfileBuilder;
use App\Services\OpenAiService;
use App\Libraries\B2B\ProductProfileBuilder;
use App\Libraries\B2B\ProductNormalizer;
use App\Libraries\B2B\Scorers\SectorFitScorer;

class Backfill20Cmd extends BaseCommand {
    protected $group       = 'B2B Scoring';
    protected $name        = 'score:backfill-20';
    protected $description = 'Micro-backfill controlado de 20 empresas en produccion';

    public function run(array $params) {
        $config = config('B2BScoring');
        $model = $config->embedding_model ?? 'text-embedding-3-small';
        $version = $config->company_embedding_version ?? '1.0.0';

        CLI::write("=== MICRO-BACKFILL 20 COMPANIES ===", 'yellow');
        CLI::write("Model: $model, Version: $version", 'cyan');
        
        $dsn = 'mysql:host=217.61.210.127;dbname=reseller3537_apiempresas;charset=utf8mb4';
        $user = 'apiempresas_user';
        $pass = 'WONwyjpsmx3h3$@2';
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

        // Select exactly 20 IDs
        $artifactDir = 'C:/Users/papel/.gemini/antigravity/brain/c8b17c04-4d0a-454b-a66d-56ce3eb1b3f9/scratch/';
        $localComps = json_decode(file_get_contents($artifactDir . 'embedding_validation_companies.json'), true);
        $targetIds = array_slice(array_column($localComps, 'id'), 0, 20);
        $placeholders = implode(',', array_fill(0, count($targetIds), '?'));
        
        // Monitoring BEFORE
        $t0conn = $pdo->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'];
        $t0run  = $pdo->query("SHOW STATUS LIKE 'Threads_running'")->fetch()['Value'];
        CLI::write("Pre-flight: Threads_connected=$t0conn, Threads_running=$t0run");

        // Fetch 20 companies
        $stmt = $pdo->prepare("SELECT id, cnae_code, cnae_label, objeto_social FROM companies WHERE id IN ($placeholders)");
        $stmt->execute($targetIds);
        $companies = $stmt->fetchAll();
        CLI::write("Loaded " . count($companies) . " companies from DB.", 'green');
        
        if (count($companies) !== 20) {
            CLI::error("Expected 20, got " . count($companies));
            return;
        }

        $openai = new OpenAiService();

        $stats = [
            'selected' => 20,
            'new' => 0,
            'stale' => 0,
            'reused' => 0,
            'generated' => 0,
            'inserted' => 0,
            'updated' => 0,
            'failed' => 0,
            'invalid' => 0,
            'queries' => ['select' => 1, 'insert' => 0, 'update' => 0],
            'openai_calls' => 0,
            'latencies' => [],
            'write_latencies' => []
        ];

        // Process sequentially
        foreach ($companies as $c) {
            $companyId = $c['id'];
            
            // 1. Build profile exact canonical text
            $canonicalText = CompanySemanticProfileBuilder::build($c);
            $sourceHash = CompanySemanticProfileBuilder::hash($canonicalText);

            // 2. Pre-check DB
            $stmtCheck = $pdo->prepare("SELECT source_hash FROM company_embeddings WHERE company_id = ? AND embedding_model = ? AND embedding_version = ?");
            $stmtCheck->execute([$companyId, $model, $version]);
            $stats['queries']['select']++;
            $existing = $stmtCheck->fetch();

            $needsGeneration = false;
            $isUpdate = false;

            if ($existing) {
                if ($existing['source_hash'] === $sourceHash) {
                    $stats['reused']++;
                    continue; // Skip OpenAI
                } else {
                    $stats['stale']++;
                    $needsGeneration = true;
                    $isUpdate = true;
                    CLI::write("Company $companyId: STALE (hash mismatch, will update)");
                }
            } else {
                $stats['new']++;
                $needsGeneration = true;
                CLI::write("Company $companyId: NEW (will insert)");
            }

            if ($needsGeneration) {
                usleep(200 * 1000); // 200ms throttle

                // OpenAI Call
                $t0 = microtime(true);
                $embedding = $openai->getEmbeddings($canonicalText);
                $latency = microtime(true) - $t0;
                $stats['latencies'][] = $latency;
                $stats['openai_calls']++;

                // Validate
                $valid = true;
                if (!$embedding || !is_array($embedding) || count($embedding) !== 1536) $valid = false;
                if ($valid) {
                    foreach ($embedding as $v) {
                        if (!is_numeric($v) || is_infinite($v) || is_nan($v)) { $valid = false; break; }
                    }
                }

                if (!$valid) {
                    $stats['invalid']++;
                    $stats['failed']++;
                    CLI::error("Company $companyId: INVALID embedding array.");
                    continue;
                }
                
                $stats['generated']++;

                // Write to DB
                $tW = microtime(true);
                try {
                    $stmtWrite = $pdo->prepare("INSERT INTO company_embeddings (company_id, embedding_model, embedding_version, source_hash, embedding, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW()) ON DUPLICATE KEY UPDATE source_hash = VALUES(source_hash), embedding = VALUES(embedding), updated_at = NOW()");
                    $stmtWrite->execute([$companyId, $model, $version, $sourceHash, json_encode($embedding)]);
                    $wLat = microtime(true) - $tW;
                    $stats['write_latencies'][] = $wLat;
                    
                    if ($isUpdate) {
                        $stats['updated']++;
                        $stats['queries']['update']++;
                    } else {
                        $stats['inserted']++;
                        $stats['queries']['insert']++;
                    }
                } catch (\Exception $e) {
                    $stats['failed']++;
                    CLI::error("Company $companyId WRITE FAILED: " . $e->getMessage());
                }
            }
        }

        // Monitoring AFTER
        $t1conn = $pdo->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'];
        $t1run  = $pdo->query("SHOW STATUS LIKE 'Threads_running'")->fetch()['Value'];
        
        $stats['monitoring'] = [
            'Threads_connected_before' => (int)$t0conn,
            'Threads_running_before' => (int)$t0run,
            'Threads_connected_after' => (int)$t1conn,
            'Threads_running_after' => (int)$t1run,
        ];

        // Verification Read
        $stmtVerify = $pdo->prepare("SELECT company_id, source_hash, embedding_model, created_at, updated_at FROM company_embeddings WHERE company_id IN ($placeholders)");
        $stmtVerify->execute($targetIds);
        $verified = $stmtVerify->fetchAll();
        $stats['verified_count'] = count($verified);
        
        // Disconnect DB for local scoring step
        $pdo = null;

        // --- LOCAL SEMANTIC SCORING TEST ---
        $ciDb = Database::connect();
        $products = [
            "SEO para clínicas dentales",
            "software CRM para inmobiliarias",
            "software para despachos de abogados",
            "mantenimiento de maquinaria CNC"
        ];
        
        // Fetch embeddings again (just via CI DB to simulate real app flow)
        $persistedEmbeddings = $ciDb->table('company_embeddings')
            ->whereIn('company_id', $targetIds)
            ->where('embedding_model', $model)
            ->where('embedding_version', $version)
            ->get()->getResultArray();
            
        $vectorMap = [];
        foreach ($persistedEmbeddings as $row) {
            $vectorMap[$row['company_id']] = json_decode($row['embedding'], true);
        }

        $builder = new ProductProfileBuilder();
        $localTestResults = [];
        
        foreach ($products as $pName) {
            $normalized = ProductNormalizer::normalize($pName);
            $profile = $builder->getProfile($pName);
            // Need product embedding
            $prodEmb = $openai->getEmbeddings($normalized);
            
            // Score against the first 3 companies with valid embeddings
            $count = 0;
            foreach ($companies as $c) {
                if (!isset($vectorMap[$c['id']])) continue;
                $c['embedding'] = $vectorMap[$c['id']];
                
                // Calculate raw cosine locally to pass to Scorer
                $rawCosine = $this->cosine($c['embedding'], $prodEmb);
                
                // Score
                $res = SectorFitScorer::score($c, $profile, $rawCosine);
                
                $localTestResults[] = [
                    'product' => $pName,
                    'company_id' => $c['id'],
                    'cnae' => $c['cnae_code'],
                    'tax_active' => $res['active'],
                    'hybrid_score' => $res['score'],
                    'confidence' => $res['confidence']
                ];
                
                if (++$count >= 3) break; // just test a few per product
            }
        }
        
        $stats['local_test_samples'] = $localTestResults;

        // Read previous stats if idempotent test
        $statsFile = $artifactDir . 'backfill_20_stats.json';
        if (file_exists($statsFile)) {
            $oldStats = json_decode(file_get_contents($statsFile), true);
            $statsFile = $artifactDir . 'backfill_20_stats_run2.json';
        }
        
        // Save report
        file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        CLI::write("DONE. Stats saved to $statsFile", 'green');
    }
    
    private function cosine(array $a, array $b): float {
        $dot = 0; $magA = 0; $magB = 0;
        for ($i = 0; $i < count($a); $i++) {
            $dot += $a[$i] * $b[$i];
            $magA += $a[$i] * $a[$i];
            $magB += $b[$i] * $b[$i];
        }
        return ($magA == 0 || $magB == 0) ? 0.0 : ($dot / (sqrt($magA) * sqrt($magB)));
    }
}