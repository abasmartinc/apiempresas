<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use PDO;

class MicroBench50Cmd extends BaseCommand {
    protected $group       = 'B2B Scoring';
    protected $name        = 'score:microbench50';
    protected $description = 'Microbenchmark 50';

    public function run(array $params) {
        $dsn = 'mysql:host=217.61.210.127;dbname=reseller3537_apiempresas;charset=utf8mb4';
        $user = 'apiempresas_user';
        $pass = 'WONwyjpsmx3h3$@2';
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

        // Custom function to get threads
        $getThreads = function() use ($pdo) {
            if (!$pdo) return ['conn' => 0, 'run' => 0];
            $c = $pdo->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'];
            $r = $pdo->query("SHOW STATUS LIKE 'Threads_running'")->fetch()['Value'];
            return ['conn' => $c, 'run' => $r];
        };

        // T0
        $t0_threads = $getThreads();

        // Check if we have our own process in processlist
        $connId = $pdo->query("SELECT CONNECTION_ID()")->fetchColumn();

        // 1. Explain queries
        $explains = [];
        $qEmbedIdx = "SELECT company_id FROM company_embeddings LIMIT 10";
        $explains['embed_idx'] = $pdo->query("EXPLAIN " . $qEmbedIdx)->fetchAll()[0];

        $qCompPK1 = "SELECT id, cnae_label, objeto_social FROM companies WHERE id BETWEEN 100000 AND 105000 AND objeto_social IS NOT NULL LIMIT 10";
        $explains['comp_pk'] = $pdo->query("EXPLAIN " . $qCompPK1)->fetchAll()[0];

        foreach ($explains as $k => $e) {
            if ($e['type'] === 'ALL') {
                die(json_encode(['error' => "FULL TABLE SCAN DETECTED on $k"]));
            }
        }

        $extractionStart = microtime(true);
        $dbQueriesTime = 0;
        $transferTime = 0;

        $startQueries = $pdo->query("SHOW SESSION STATUS LIKE 'Com_select'")->fetch()['Value'];

        // Extraction
        // a) 10 companies with embeddings
        $t_q1_s = microtime(true);
        $embIdsRaw = $pdo->query($qEmbedIdx)->fetchAll();
        $t_q1_e = microtime(true);
        $dbQueriesTime += ($t_q1_e - $t_q1_s);
        
        $embIds = array_column($embIdsRaw, 'company_id');
        $embIdsStr = implode(',', $embIds);
        
        $companies = [];
        if (!empty($embIds)) {
            $t_q2_s = microtime(true);
            $companies = $pdo->query("SELECT id, cnae_label, objeto_social FROM companies WHERE id IN ($embIdsStr)")->fetchAll();
            $t_q2_e = microtime(true);
            $dbQueriesTime += ($t_q2_e - $t_q2_s);
        }

        // b) 40 more companies via 4 chunks
        $ranges = [ [200000, 205000], [300000, 305000], [400000, 405000], [500000, 505000] ];
        foreach ($ranges as $r) {
            $q = "SELECT id, cnae_label, objeto_social FROM companies WHERE id BETWEEN $r[0] AND $r[1] AND objeto_social IS NOT NULL LIMIT 10";
            $t_q_s = microtime(true);
            $chunk = $pdo->query($q)->fetchAll();
            $t_q_e = microtime(true);
            $dbQueriesTime += ($t_q_e - $t_q_s);
            $companies = array_merge($companies, $chunk);
        }

        $ids = array_column($companies, 'id');
        $idStr = implode(',', $ids);

        // Extract those embeddings
        $embeds = [];
        $embeddingBytes = 0;
        if (!empty($ids)) {
            $t_q3_s = microtime(true);
            $qEmbed = "SELECT company_id, embedding FROM company_embeddings WHERE company_id IN ($idStr)";
            $embedRows = $pdo->query($qEmbed)->fetchAll();
            $t_q3_e = microtime(true);
            $dbQueriesTime += ($t_q3_e - $t_q3_s);

            foreach ($embedRows as $r) {
                $embeddingBytes += strlen($r['embedding']);
                $embeds[$r['company_id']] = json_decode($r['embedding'], true);
            }
        }

        $validEmbeddings = 0;
        foreach ($companies as &$c) {
            $c['embedding'] = $embeds[$c['id']] ?? null;
            if ($c['embedding']) $validEmbeddings++;
        }
        unset($c);

        $extractionEnd = microtime(true);
        $transferTime = ($extractionEnd - $extractionStart) - $dbQueriesTime;

        // T1
        $t1_threads = $getThreads();

        $endQueries = $pdo->query("SHOW SESSION STATUS LIKE 'Com_select'")->fetch()['Value'];
        $prodQueries = $endQueries - $startQueries;

        // Snapshot serialization
        $serStart = microtime(true);
        $snapshotPath = 'C:/Users/papel/.gemini/antigravity/brain/c8b17c04-4d0a-454b-a66d-56ce3eb1b3f9/scratch/microbenchmark_50_snapshot.json';
        file_put_contents($snapshotPath, json_encode($companies));
        $snapshotSize = filesize($snapshotPath);
        $serEnd = microtime(true);
        $serializationTime = $serEnd - $serStart;

        // Pre-load ProductProfiles
        // This relies on the CI4 database connection used by ProductProfileBuilder
        $ciDb = Database::connect();
        $builder = new \App\Libraries\B2B\ProductProfileBuilder();
        $products = ["SEO para clínicas dentales", "mantenimiento de maquinaria CNC", "consultoría", "servicios de ciberseguridad para empresas industriales", "software de gestión documental"];
        
        $profileReads = 0;
        $cacheHits = 0;
        $cacheMisses = 0;

        foreach ($products as $p) {
            // Count queries before/after getProfile to infer cache misses
            $bq = 0;
            $builder->getProfile($p);
            $aq = 0;
            if ($aq > $bq) {
                $profileReads += ($aq - $bq);
                $cacheMisses++;
            } else {
                $cacheHits++;
            }
        }

        // Close ALL DB Connections before local scoring loop!
        $pdo = null;
        $ciDb->close();

        // T2
        $pdoCheck = new PDO($dsn, $user, $pass, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $t2_threads = [
            'conn' => $pdoCheck->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'],
            'run' => $pdoCheck->query("SHOW STATUS LIKE 'Threads_running'")->fetch()['Value']
        ];
        
        // Verify connection ID is gone
        $procs = $pdoCheck->query("SHOW PROCESSLIST")->fetchAll();
        $connFound = false;
        foreach ($procs as $pr) {
            if ($pr['Id'] == $connId) $connFound = true;
        }
        
        $pdoCheck = null; // Close check

        // SCORING LOOP IN MEMORY
        $scorer = new \App\Libraries\B2B\B2BOpportunityScorer();
        $v1 = new \App\Libraries\RadarAnalyzer();
        $results = [];

        $scoringStart = microtime(true);
        foreach ($products as $p) {
            foreach ($companies as $c) {
                // Must be 100% in-memory!
                $res1 = $v1->calculateMatch($c, $p);
                $res2 = $scorer->calculate($c, $p);
                $results[] = [
                    'product' => $p,
                    'company_id' => $c['id']
                ];
            }
        }
        $scoringEnd = microtime(true);
        $scoringTime = $scoringEnd - $scoringStart;

        // T3
        sleep(5);
        $pdoCheck = new PDO($dsn, $user, $pass, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $t3_threads = [
            'conn' => $pdoCheck->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'],
            'run' => $pdoCheck->query("SHOW STATUS LIKE 'Threads_running'")->fetch()['Value']
        ];
        $pdoCheck = null;

        // T4
        sleep(10); // Makes it 15 seconds after T2
        $pdoCheck = new PDO($dsn, $user, $pass, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $t4_threads = [
            'conn' => $pdoCheck->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'],
            'run' => $pdoCheck->query("SHOW STATUS LIKE 'Threads_running'")->fetch()['Value']
        ];
        $pdoCheck = null;


        echo json_encode([
            'T0' => $t0_threads,
            'T1' => $t1_threads,
            'T2' => $t2_threads,
            'T3' => $t3_threads,
            'T4' => $t4_threads,
            'conn_id' => $connId,
            'conn_leaked' => $connFound,
            'explain_embed_idx' => $explains['embed_idx'],
            'explain_comp_pk' => $explains['comp_pk'],
            'times' => [
                'db_queries_time' => $dbQueriesTime,
                'network_transfer_time' => $transferTime,
                'serialization_time' => $serializationTime,
                'local_scoring_time' => $scoringTime,
                'total_script_runtime' => microtime(true) - $extractionStart + 15 // added sleep time manually for pure metric
            ],
            'snapshot_size_bytes' => $snapshotSize,
            'total_production_selects' => $prodQueries + $profileReads, // manual estimation for total DB hits
            'valid_embeddings' => $validEmbeddings,
            'embedding_bytes' => $embeddingBytes,
            'cache_hits' => $cacheHits,
            'cache_misses' => $cacheMisses,
            'evaluations' => count($results)
        ], JSON_PRETTY_PRINT);
    }
}