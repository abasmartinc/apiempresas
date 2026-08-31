<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use PDO;

class MicroBench25Cmd extends BaseCommand {
    protected $group       = 'B2B Scoring';
    protected $name        = 'score:microbench25';
    protected $description = 'Microbenchmark 25';

    public function run(array $params) {
        // We'll use a direct PDO connection to have perfect control over lifecycle
        $dsn = 'mysql:host=217.61.210.127;dbname=reseller3537_apiempresas;charset=utf8mb4';
        $user = 'apiempresas_user';
        $pass = 'WONwyjpsmx3h3$@2';
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

        // 1. Threads before
        $tConnBefore = $pdo->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'];
        $tRunBefore = $pdo->query("SHOW STATUS LIKE 'Threads_running'")->fetch()['Value'];
        $startTotalQueries = $pdo->query("SHOW SESSION STATUS LIKE 'Com_select'")->fetch()['Value'];

        // 2. Explain queries
        $q1 = "SELECT id, cnae_label, objeto_social FROM companies WHERE id > 100000 AND objeto_social IS NOT NULL LIMIT 25";
        $explain1 = $pdo->query("EXPLAIN " . $q1)->fetchAll();

        $q2 = "SELECT company_id, embedding FROM company_embeddings WHERE company_id IN (100001,100002,100003)";
        $explain2 = $pdo->query("EXPLAIN " . $q2)->fetchAll();

        if ($explain1[0]['type'] === 'ALL' || $explain2[0]['type'] === 'ALL') {
            die(json_encode(['error' => 'FULL TABLE SCAN DETECTED']));
        }

        // 3. Extract 25 companies
        $t0 = microtime(true);
        $companies = $pdo->query($q1)->fetchAll();
        $ids = array_column($companies, 'id');

        // 4. Extract embeddings
        $idStr = implode(',', $ids);
        $embeds = [];
        if (!empty($ids)) {
            $qEmbed = "SELECT company_id, embedding FROM company_embeddings WHERE company_id IN ($idStr)";
            $embedRows = $pdo->query($qEmbed)->fetchAll();
            foreach ($embedRows as $r) {
                $embeds[$r['company_id']] = json_decode($r['embedding'], true);
            }
        }

        $validEmbeddings = 0;
        foreach ($companies as &$c) {
            $c['embedding'] = $embeds[$c['id']] ?? null;
            if ($c['embedding']) $validEmbeddings++;
        }
        unset($c);
        $missingEmbeddings = count($companies) - $validEmbeddings;

        // Threads after extraction
        $tConnAfterExt = $pdo->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'];
        $tRunAfterExt = $pdo->query("SHOW STATUS LIKE 'Threads_running'")->fetch()['Value'];
        
        $t1 = microtime(true);
        $extractionTime = $t1 - $t0;

        // Save Snapshot
        $snapshotPath = 'C:/Users/papel/.gemini/antigravity/brain/c8b17c04-4d0a-454b-a66d-56ce3eb1b3f9/scratch/microbenchmark_25_snapshot.json';
        file_put_contents($snapshotPath, json_encode($companies));
        $snapshotSize = filesize($snapshotPath);

        // 5. Pre-load ProductProfiles (Using CI4 Database for this one since Builder uses it)
        $builder = new \App\Libraries\B2B\ProductProfileBuilder();
        $products = ["SEO para clínicas dentales", "mantenimiento de maquinaria CNC", "consultoría"];
        $profiles = [];
        foreach ($products as $p) {
            $profiles[$p] = $builder->getProfile($p); // this uses Builder's memory cache
        }
        // Second call to check cache hits
        foreach ($products as $p) {
            $builder->getProfile($p); // Should be cache hit
        }

        $endTotalQueries = $pdo->query("SHOW SESSION STATUS LIKE 'Com_select'")->fetch()['Value'];
        $prodQueries = $endTotalQueries - $startTotalQueries - 1; 

        // 6. SCORING LOOP (0 Queries expected)
        // Switch to the CI4 DB instance to monitor Com_select for the loop (since Builder uses CI4 DB)
        $ciDb = Database::connect();
        $ciDbPdo = $ciDb->connID; // CI4 is MySQLi actually!
        // We will just measure total server-wide queries for this session via PDO to be sure
        $startQueries = $pdo->query("SHOW SESSION STATUS LIKE 'Com_select'")->fetch()['Value'];
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
                    'company_id' => $c['id']
                ];
            }
        }

        $t3 = microtime(true);
        $scoringTime = $t3 - $t2;
        $endQueries = $pdo->query("SHOW SESSION STATUS LIKE 'Com_select'")->fetch()['Value'];
        $queriesInLoop = $endQueries - $startQueries - 1;

        // Disconnect PDO to test cleanup
        $pdo = null;
        $ciDb->close();
        sleep(1);

        // Connect again to check final threads
        $pdoCheck = new PDO($dsn, $user, $pass, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $tConnCleanup = $pdoCheck->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'];
        $tRunCleanup = $pdoCheck->query("SHOW STATUS LIKE 'Threads_running'")->fetch()['Value'];

        echo json_encode([
            'threads_conn_before' => $tConnBefore,
            'threads_run_before' => $tRunBefore,
            'threads_conn_ext' => $tConnAfterExt,
            'threads_run_ext' => $tRunAfterExt,
            'threads_conn_cleanup' => $tConnCleanup,
            'threads_run_cleanup' => $tRunCleanup,
            'explain1' => $explain1[0],
            'explain2' => $explain2[0],
            'extraction_time' => $extractionTime,
            'snapshot_size_bytes' => $snapshotSize,
            'scoring_time' => $scoringTime,
            'total_runtime' => $t3 - $t0,
            'peak_memory' => memory_get_peak_usage(true) / 1024 / 1024 . " MB",
            'queries_in_loop' => $queriesInLoop,
            'total_production_selects' => $prodQueries,
            'valid_embeddings' => $validEmbeddings,
            'missing_embeddings' => $missingEmbeddings
        ], JSON_PRETTY_PRINT);
    }
}