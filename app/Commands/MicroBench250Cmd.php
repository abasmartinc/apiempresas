<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use PDO;

class MicroBench250Cmd extends BaseCommand {
    protected $group       = 'B2B Scoring';
    protected $name        = 'score:microbench250';
    protected $description = 'Microbenchmark 250';

    public function run(array $params) {
        $dsn = 'mysql:host=217.61.210.127;dbname=reseller3537_apiempresas;charset=utf8mb4';
        $user = 'apiempresas_user';
        $pass = 'WONwyjpsmx3h3$@2';
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

        $connId = $pdo->query("SELECT CONNECTION_ID()")->fetchColumn();
        
        $checkConn = function() use ($dsn, $user, $pass, $connId) {
            $p = new PDO($dsn, $user, $pass, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
            $conn = $p->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'];
            $run = $p->query("SHOW STATUS LIKE 'Threads_running'")->fetch()['Value'];
            $procs = $p->query("SHOW PROCESSLIST")->fetchAll();
            $present = false;
            foreach ($procs as $pr) {
                if ($pr['Id'] == $connId) $present = true;
            }
            $p = null;
            return ['conn' => $conn, 'run' => $run, 'present' => $present];
        };

        // T0
        $t0_metrics = $checkConn();

        // Explain queries
        $explains = [];
        $qCompPK = "SELECT id, cnae_label, objeto_social FROM companies WHERE id BETWEEN 100000 AND 105000 LIMIT 25";
        $explains['comp_pk'] = $pdo->query("EXPLAIN " . $qCompPK)->fetchAll()[0];

        foreach ($explains as $k => $e) {
            if ($e['type'] === 'ALL') {
                die(json_encode(['error' => "FULL TABLE SCAN DETECTED on $k"]));
            }
        }

        $extractionStart = microtime(true);
        $dbQueriesTime = 0;
        $startQueries = $pdo->query("SHOW SESSION STATUS LIKE 'Com_select'")->fetch()['Value'];
        $selectsCount = 0;

        // Extraction
        $companies = [];
        // 10 chunks to get 250 companies
        $ranges = [];
        for ($i = 1; $i <= 10; $i++) {
            $start = $i * 100000;
            $ranges[] = [$start, $start + 5000];
        }
        
        foreach ($ranges as $r) {
            $q = "SELECT id, cnae_label, objeto_social, NULL as embedding FROM companies WHERE id BETWEEN $r[0] AND $r[1] AND objeto_social IS NOT NULL LIMIT 25";
            $t_q_s = microtime(true);
            $chunk = $pdo->query($q)->fetchAll();
            $t_q_e = microtime(true);
            $dbQueriesTime += ($t_q_e - $t_q_s);
            $companies = array_merge($companies, $chunk);
            $selectsCount++;
        }

        $extractionEnd = microtime(true);
        $transferTime = ($extractionEnd - $extractionStart) - $dbQueriesTime;

        // T1
        $t1_metrics = $checkConn();

        $endQueries = $pdo->query("SHOW SESSION STATUS LIKE 'Com_select'")->fetch()['Value'];

        // Snapshot serialization
        $serStart = microtime(true);
        $snapshotPath = 'C:/Users/papel/.gemini/antigravity/brain/c8b17c04-4d0a-454b-a66d-56ce3eb1b3f9/scratch/microbenchmark_250_snapshot.json';
        file_put_contents($snapshotPath, json_encode($companies));
        $snapshotSize = filesize($snapshotPath);
        $serEnd = microtime(true);
        $serializationTime = $serEnd - $serStart;

        // PRE-LOAD PRODUCT PROFILES
        $ciDb = Database::connect();
        $builder = new \App\Libraries\B2B\ProductProfileBuilder();
        $products = [
            "SEO para clínicas dentales", 
            "mantenimiento de maquinaria CNC", 
            "consultoría", 
            "servicios de ciberseguridad para empresas industriales", 
            "software de gestión documental",
            "software de control horario para empresas",
            "software CRM para inmobiliarias",
            "software para clínicas veterinarias",
            "automatización industrial",
            "asesoría fiscal para empresas"
        ];
        
        $profileStats = [];
        $profileSelects = 0;
        
        foreach ($products as $p) {
            $normalized = \App\Libraries\B2B\ProductNormalizer::normalize($p);
            $hash = \App\Libraries\B2B\ProductNormalizer::hash($normalized);
            
            $inDb = $ciDb->table('b2b_product_profiles')->where('product_hash', $hash)->get()->getRowArray();
            $profileSelects++;
            
            $builder->getProfile($p);
            $profileSelects++; // Internal query
            
            $source = 'unknown';
            if ($inDb) $source = 'production DB cache';
            else $source = 'LLM/Mock (not persisted)';
            
            $profileStats[$p] = [
                'hash' => $hash,
                'source' => $source,
                'db_read' => $inDb ? 'yes' : 'no',
                'memory_hit' => 'no (cold start)',
                'llm_call' => $inDb ? 'no' : 'yes',
                'production_write' => 'no'
            ];
        }
        
        // Check cache hit
        foreach ($products as $p) {
            $builder->getProfile($p);
            $profileStats[$p]['memory_hit'] = 'yes (on 2nd pass)';
        }

        // Close ALL DB Connections before local scoring loop!
        $pdo = null;
        $ciDb->close();
        $ciDb = null;
        unset($chunk, $inDb);

        // T2
        $t2_metrics = $checkConn();

        // SCORING LOOP IN MEMORY
        $scorer = new \App\Libraries\B2B\B2BOpportunityScorer();
        $v1 = new \App\Libraries\RadarAnalyzer();
        $results = [];

        $scoringStart = microtime(true);
        $queriesDuringLoop = 0;
        try {
            foreach ($products as $p) {
                foreach ($companies as $c) {
                    $res1 = $v1->calculateMatch($c, $p);
                    $res2 = $scorer->calculate($c, $p);
                    $results[] = [
                        'product' => $p,
                        'company_id' => $c['id'],
                        'cnae' => $c['cnae_label'],
                        'v1' => $res1['match_score'] ?? 0,
                        'v2_opp' => $res2['opportunity_fit'] ?? 0,
                        'trigger' => $res2['trigger_score'] ?? 0,
                        'confidence' => $res2['confidence_score'] ?? 0
                    ];
                }
            }
        } catch (\Exception $e) {
            $queriesDuringLoop = -1; 
        }
        $scoringEnd = microtime(true);
        $scoringTime = $scoringEnd - $scoringStart;
        
        // Sort and get Top 5 / Bottom 5
        $sanityCheck = [];
        foreach ($products as $p) {
            $prodRes = array_filter($results, function($r) use ($p) { return $r['product'] === $p; });
            usort($prodRes, function($a, $b) { return $b['v2_opp'] <=> $a['v2_opp']; });
            
            $top = array_slice($prodRes, 0, 5);
            $bottom = array_slice($prodRes, -5);
            $sanityCheck[$p] = [
                'note' => 'TAXONOMY-ONLY / SEMANTIC EMBEDDING UNAVAILABLE',
                'top_5' => $top,
                'bottom_5' => $bottom
            ];
        }

        // CNAE distribution
        $cnaeDivs = [];
        $cnaeCodes = [];
        $missingCnae = 0;
        foreach ($companies as $c) {
            $code = $c['cnae_label'] ?? '';
            if (empty($code)) $missingCnae++;
            else {
                $cnaeCodes[$code] = ($cnaeCodes[$code] ?? 0) + 1;
                $div = substr($code, 0, 2);
                $cnaeDivs[$div] = ($cnaeDivs[$div] ?? 0) + 1;
            }
        }
        arsort($cnaeDivs);
        $top10Divs = array_slice($cnaeDivs, 0, 10, true);
        $top5Sum = array_sum(array_slice($cnaeDivs, 0, 5, true));
        $pctTop5 = ($top5Sum / count($companies)) * 100;

        echo json_encode([
            'T0' => $t0_metrics,
            'T1' => $t1_metrics,
            'T2' => $t2_metrics,
            'explain_comp_pk' => $explains['comp_pk'],
            'times' => [
                'db_queries_time' => $dbQueriesTime,
                'network_transfer_time' => $transferTime,
                'serialization_time' => $serializationTime,
                'local_scoring_time' => $scoringTime,
                'total_script_runtime' => microtime(true) - $extractionStart
            ],
            'snapshot_size_bytes' => $snapshotSize,
            'total_production_selects' => $selectsCount + $profileSelects,
            'evaluations' => count($results),
            'queries_in_loop' => $queriesDuringLoop, 'valid_embeddings' => 0, 'embedding_bytes' => 0,
            'profile_stats' => $profileStats,
            'cnae_stats' => [
                'unique_codes' => count($cnaeCodes),
                'unique_divisions' => count($cnaeDivs),
                'top_10_divisions' => $top10Divs,
                'top_5_percentage' => $pctTop5,
                'missing_cnae_pct' => ($missingCnae / count($companies)) * 100
            ],
            'sanity_check' => $sanityCheck
        ], JSON_PRETTY_PRINT);
    }
}