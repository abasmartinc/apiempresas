<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class MicroBenchCmd extends BaseCommand {
    protected $group       = 'B2B Scoring';
    protected $name        = 'score:microbench';
    protected $description = 'Microbenchmark';

    public function run(array $params) {
        $db = Database::connect();

        // 1. Check Threads
        $threadsBefore = $db->query("SHOW STATUS LIKE 'Threads_connected'")->getRowArray()['Value'] . " / " . 
                         $db->query("SHOW STATUS LIKE 'Threads_running'")->getRowArray()['Value'];

        // 2. Explain queries
        $q1 = "SELECT id, cnae_label, objeto_social FROM companies WHERE id > 100000 AND objeto_social IS NOT NULL LIMIT 10";
        $explain1 = $db->query("EXPLAIN " . $q1)->getResultArray();

        $q2 = "SELECT company_id, embedding FROM company_embeddings WHERE company_id IN (100001,100002,100003)";
        $explain2 = $db->query("EXPLAIN " . $q2)->getResultArray();

        if ($explain1[0]['type'] === 'ALL' || $explain2[0]['type'] === 'ALL') {
            die(json_encode(['error' => 'FULL TABLE SCAN DETECTED', 'e1' => $explain1, 'e2' => $explain2]));
        }

        // 3. Extract 10 companies
        $t0 = microtime(true);
        $companies = $db->query($q1)->getResultArray();
        $ids = array_column($companies, 'id');

        // 4. Extract embeddings
        $idStr = implode(',', $ids);
        $embeds = [];
        if (!empty($ids)) {
            $embedRows = $db->query("SELECT company_id, embedding FROM company_embeddings WHERE company_id IN ($idStr)")->getResultArray();
            foreach ($embedRows as $r) {
                $embeds[$r['company_id']] = json_decode($r['embedding'], true);
            }
        }

        foreach ($companies as &$c) {
            $c['embedding'] = $embeds[$c['id']] ?? null;
        }
        unset($c);

        $t1 = microtime(true);
        $extractionTime = $t1 - $t0;

        // Save Snapshot
        $snapshotPath = 'C:/Users/papel/.gemini/antigravity/brain/c8b17c04-4d0a-454b-a66d-56ce3eb1b3f9/scratch/microbenchmark_snapshot.json';
        file_put_contents($snapshotPath, json_encode($companies));
        $snapshotSize = filesize($snapshotPath);

        // 5. Pre-load ProductProfiles
        $builder = new \App\Libraries\B2B\ProductProfileBuilder();
        $products = ["SEO para clínicas dentales", "mantenimiento de maquinaria CNC", "consultoría"];
        $profiles = [];
        foreach ($products as $p) {
            $profiles[$p] = $builder->getProfile($p);
        }

        // 6. SCORING LOOP (0 Queries expected)
        $startQueries = $db->query("SHOW SESSION STATUS LIKE 'Com_select'")->getRowArray()['Value'];
        $t2 = microtime(true);

        $scorer = new \App\Libraries\B2B\B2BOpportunityScorer();
        $v1 = new \App\Libraries\RadarAnalyzer();
        $results = [];

        foreach ($products as $p) {
            foreach ($companies as $c) {
                $res1 = $v1->calculateMatch($c, $p);
                $res2 = $scorer->calculate($c, $p);
                $results[] = [
                    'product' => $p,
                    'company_id' => $c['id'],
                    'v1' => $res1['match_score'] ?? 0,
                    'v2_opp' => $res2['opportunity_fit'] ?? 0,
                    'v2_trig' => $res2['trigger_score'] ?? 0,
                    'v2_conf' => $res2['confidence_score'] ?? 0
                ];
            }
        }

        $t3 = microtime(true);
        $scoringTime = $t3 - $t2;
        $endQueries = $db->query("SHOW SESSION STATUS LIKE 'Com_select'")->getRowArray()['Value'];

        $threadsAfter = $db->query("SHOW STATUS LIKE 'Threads_connected'")->getRowArray()['Value'] . " / " . 
                        $db->query("SHOW STATUS LIKE 'Threads_running'")->getRowArray()['Value'];

        echo json_encode([
            'threads_before' => $threadsBefore,
            'threads_after' => $threadsAfter,
            'explain1' => $explain1[0],
            'explain2' => $explain2[0],
            'extraction_time' => $extractionTime,
            'snapshot_size_bytes' => $snapshotSize,
            'scoring_time' => $scoringTime,
            'total_runtime' => $t3 - $t0,
            'peak_memory' => memory_get_peak_usage(true) / 1024 / 1024 . " MB",
            'queries_in_loop' => $endQueries - $startQueries - 1, // minus the endQueries call
            'results' => array_slice($results, 0, 3)
        ], JSON_PRETTY_PRINT);
    }
}