<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use PDO;
use App\Libraries\B2B\CompanySemanticProfileBuilder;
use App\Libraries\B2B\ProductProfileBuilder;
use App\Libraries\B2B\ProductNormalizer;
use App\Libraries\B2B\Scorers\SectorFitScorer;
use App\Libraries\B2B\ProductProfile;
use App\Services\OpenAiService;

class EmbeddingValidationCmd extends BaseCommand {
    protected $group       = 'B2B Scoring';
    protected $name        = 'score:embedding-validation';
    protected $description = 'Local semantic embedding validation - READ ONLY';

    private function cosine(array $a, array $b): float {
        $dot = 0;
        $magA = 0;
        $magB = 0;
        $n = count($a);
        for ($i = 0; $i < $n; $i++) {
            $dot += $a[$i] * $b[$i];
            $magA += $a[$i] * $a[$i];
            $magB += $b[$i] * $b[$i];
        }
        if ($magA == 0 || $magB == 0) return 0.0;
        return $dot / (sqrt($magA) * sqrt($magB));
    }

    private function stats(array $values): array {
        if (empty($values)) return [];
        sort($values);
        $n = count($values);
        $sum = array_sum($values);
        $mean = $sum / $n;
        $variance = 0;
        foreach ($values as $v) $variance += pow($v - $mean, 2);
        $stddev = sqrt($variance / $n);
        
        $pct = function(float $p) use ($values, $n): float {
            $idx = ($p / 100) * ($n - 1);
            $lo = (int)floor($idx);
            $hi = (int)ceil($idx);
            return $values[$lo] + ($idx - $lo) * ($values[$hi] - $values[$lo]);
        };

        return [
            'n' => $n, 'min' => $values[0], 'max' => $values[$n - 1],
            'mean' => round($mean, 4),
            'median' => $pct(50),
            'stddev' => round($stddev, 4),
            'p5' => $pct(5), 'p10' => $pct(10), 'p25' => $pct(25),
            'p50' => $pct(50), 'p75' => $pct(75), 'p90' => $pct(90), 'p95' => $pct(95),
        ];
    }

    private function spearman(array $rankA, array $rankB): float {
        $n = count($rankA);
        if ($n < 2) return 0.0;
        $dSqSum = 0;
        foreach ($rankA as $id => $rA) {
            $rB = $rankB[$id] ?? 0;
            $dSqSum += pow($rA - $rB, 2);
        }
        return 1 - (6 * $dSqSum) / ($n * ($n * $n - 1));
    }

    public function run(array $params) {
        $artifactDir = 'C:/Users/papel/.gemini/antigravity/brain/c8b17c04-4d0a-454b-a66d-56ce3eb1b3f9/scratch/';
        $companiesFile = $artifactDir . 'embedding_validation_companies.json';
        $vectorsFile   = $artifactDir . 'embedding_validation_vectors.json';
        $productsFile  = $artifactDir . 'embedding_validation_products.json';
        $resultsFile   = $artifactDir . 'embedding_validation_results.json';
        $summaryFile   = $artifactDir . 'embedding_validation_summary.json';

        // PHASE 1: Load or extract from production
        if (file_exists($companiesFile) && file_exists($vectorsFile) && file_exists($productsFile)) {
            CLI::write('[OFFLINE MODE] Loading from local snapshot files. 0 DB queries, 0 OpenAI calls.', 'green');
            $companies = json_decode(file_get_contents($companiesFile), true);
            $vectorMap = json_decode(file_get_contents($vectorsFile), true);
            $productData = json_decode(file_get_contents($productsFile), true);
            $productionSelects = 0;
            $dbExtractionTime = 0;
        } else {
            CLI::write('[ONLINE MODE] Extracting from production DB...', 'yellow');
            
            $dsn = 'mysql:host=217.61.210.127;dbname=reseller3537_apiempresas;charset=utf8mb4';
            $user = 'apiempresas_user';
            $pass = 'WONwyjpsmx3h3$@2';
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);

            $connId = $pdo->query("SELECT CONNECTION_ID()")->fetchColumn();
            $t0conn = $pdo->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'];
            $t0run  = $pdo->query("SHOW STATUS LIKE 'Threads_running'")->fetch()['Value'];

            // EXPLAIN first (no LIMIT param in EXPLAIN, use literal)
            $explainRow = $pdo->query("EXPLAIN SELECT id, cnae_code, cnae_label, objeto_social FROM companies WHERE id BETWEEN 100000 AND 120000 AND objeto_social IS NOT NULL AND cnae_label IS NOT NULL LIMIT 5")->fetch();
            if ($explainRow['type'] === 'ALL') die(json_encode(['error' => 'FULL TABLE SCAN']));

            $dbStart = microtime(true);
            $productionSelects = 0;
            $companies = [];
            
            // 10 PK chunks, 5 per chunk = 50 total (spread across DB)
            $chunks = [
                [100000, 120000],
                [200000, 220000],
                [300000, 320000],
                [400000, 420000],
                [500000, 520000],
                [600000, 620000],
                [700000, 720000],
                [800000, 820000],
                [900000, 920000],
                [1000000, 1020000]
            ];

            foreach ($chunks as [$start, $end]) {
                $stmt = $pdo->prepare("SELECT id, cnae_code, cnae_label, objeto_social FROM companies WHERE id BETWEEN ? AND ? AND objeto_social IS NOT NULL AND cnae_label IS NOT NULL LIMIT 5");
                $stmt->execute([$start, $end]);
                $rows = $stmt->fetchAll();
                $companies = array_merge($companies, $rows);
                $productionSelects++;
            }

            $dbEnd = microtime(true);
            $dbExtractionTime = $dbEnd - $dbStart;

            CLI::write("Extracted " . count($companies) . " companies in " . round($dbExtractionTime, 3) . "s", 'green');
            CLI::write("T1 threads - conn: " . $pdo->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'], 'green');

            // Add semantic profile text
            foreach ($companies as &$c) {
                $c['semantic_text'] = CompanySemanticProfileBuilder::build($c);
                $c['semantic_hash'] = CompanySemanticProfileBuilder::hash($c['semantic_text']);
            }
            unset($c);

            // Save snapshot BEFORE closing DB
            file_put_contents($companiesFile, json_encode($companies, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            CLI::write("Snapshot saved: " . $companiesFile);

            // CLOSE DB BEFORE OPENAI
            $pdo = null;
            unset($stmt, $explainStmt, $rows);

            // Verify disconnect
            $pdoCheck = new PDO($dsn, $user, $pass, [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
            $procs = $pdoCheck->query("SHOW PROCESSLIST")->fetchAll();
            $leaked = false;
            foreach ($procs as $p) {
                if ($p['Id'] == $connId) { $leaked = true; break; }
            }
            $t2conn = $pdoCheck->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'];
            $pdoCheck = null;
            CLI::write("DB closed. Leak=" . ($leaked ? 'YES-PROBLEM' : 'NO') . " conn=" . $t2conn, $leaked ? 'red' : 'green');

            // PHASE 2: Generate company embeddings via OpenAI
            $openai = new OpenAiService();
            $vectorMap = [];
            $embeddingStats = ['requested' => 0, 'success' => 0, 'failed' => 0, 'latencies' => []];

            CLI::write("Generating " . count($companies) . " company embeddings...", 'yellow');
            foreach ($companies as $c) {
                $embeddingStats['requested']++;
                $tEmb = microtime(true);
                $embedding = $openai->getEmbeddings($c['semantic_text']);
                $latency = microtime(true) - $tEmb;
                $embeddingStats['latencies'][] = $latency;

                if ($embedding && is_array($embedding) && count($embedding) === 1536) {
                    // Validate
                    $valid = true;
                    foreach ($embedding as $v) {
                        if (!is_numeric($v) || is_infinite($v) || is_nan($v)) { $valid = false; break; }
                    }
                    if ($valid) {
                        $vectorMap[$c['id']] = $embedding;
                        $embeddingStats['success']++;
                    } else {
                        $embeddingStats['failed']++;
                    }
                } else {
                    $embeddingStats['failed']++;
                }
            }
            CLI::write("Embeddings: success=" . $embeddingStats['success'] . " failed=" . $embeddingStats['failed']);

            // PHASE 3: Generate product embeddings
            $products = [
                "SEO para clínicas dentales",
                "mantenimiento de maquinaria CNC",
                "servicios de ciberseguridad para empresas industriales",
                "software de gestión documental",
                "software CRM para inmobiliarias",
                "software para clínicas veterinarias",
                "automatización industrial",
                "asesoría fiscal para empresas",
                "marketing digital para restaurantes",
                "software para despachos de abogados"
            ];

            $productData = [];
            $ciDb = Database::connect();
            $builder = new ProductProfileBuilder();

            foreach ($products as $p) {
                $normalized = ProductNormalizer::normalize($p);
                $hash = ProductNormalizer::hash($normalized);
                $profile = $builder->getProfile($p);

                $tEmb = microtime(true);
                $prodEmbedding = $openai->getEmbeddings($normalized);
                $latency = microtime(true) - $tEmb;
                $embeddingStats['latencies'][] = $latency;
                $embeddingStats['requested']++;

                if ($prodEmbedding && is_array($prodEmbedding)) {
                    $embeddingStats['success']++;
                }

                $productData[$p] = [
                    'hash' => $hash,
                    'normalized' => $normalized,
                    'embedding' => $prodEmbedding,
                    'profile_json' => json_decode(json_encode($profile), true)
                ];
            }

            $ciDb->close();

            file_put_contents($vectorsFile, json_encode($vectorMap, JSON_PRETTY_PRINT));
            file_put_contents($productsFile, json_encode($productData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            file_put_contents($artifactDir . 'embedding_generation_stats.json', json_encode($embeddingStats, JSON_PRETTY_PRINT));

            CLI::write("All files saved. Second run will be 100% offline.", 'green');
        }

        // PHASE 4: 100% LOCAL ANALYSIS (0 DB, 0 OpenAI)
        CLI::write("Running 500 cosine comparisons locally...", 'cyan');
        $config = config('B2BScoring');
        $products = array_keys($productData);

        $results = [];
        foreach ($productData as $productName => $pd) {
            $productEmbedding = $pd['embedding'];
            $profileData = $pd['profile_json'] ?? [];
            
            // Rebuild ProductProfile from saved data
            $profile = new ProductProfile($profileData);

            $cosines = [];
            foreach ($companies as $c) {
                $companyEmbedding = $vectorMap[$c['id']] ?? null;

                // --- MODE A: Taxonomy-Only ---
                $companyNoEmbed = $c;
                $companyNoEmbed['embedding'] = null;
                $sectorA = SectorFitScorer::score($companyNoEmbed, $profile);

                // --- MODE B: Taxonomy + Real Semantic ---
                $rawCosine = null;
                $semScore = null;
                if ($companyEmbedding && $productEmbedding) {
                    $rawCosine = $this->cosine($companyEmbedding, $productEmbedding);
                    $cosines[] = $rawCosine;

                    $normalized = ($rawCosine - $config->cosine_lower_bound) / ($config->cosine_upper_bound - $config->cosine_lower_bound);
                    $semScoreVal = max(0, min(100, $normalized * 100));

                    // Inject real embedding for MODE B
                    $companyWithEmbed = $c;
                    $companyWithEmbed['embedding'] = $companyEmbedding;
                    
                    // Override cosine in SectorFitScorer by building manually
                    $taxScore = $sectorA['score'] ?? 0;
                    $taxConf  = $sectorA['confidence'] ?? 0;
                    $taxActive = $sectorA['active'];

                    if ($taxActive) {
                        $conflict = abs($taxScore - $semScoreVal) > 50;
                        $finalScore = ($taxScore * $config->sector_cnae_weight) + ($semScoreVal * $config->sector_semantic_weight);
                        $finalConf  = ($taxConf  * $config->sector_cnae_weight) + (0.8 * $config->sector_semantic_weight);
                        if ($conflict) $finalConf -= $config->conflict_confidence_penalty;
                        $sectorB = ['active' => true, 'score' => $finalScore, 'confidence' => max(0, $finalConf), 'conflict' => $conflict];
                    } else {
                        // Semantic-only
                        $sectorB = ['active' => true, 'score' => $semScoreVal, 'confidence' => 0.8, 'conflict' => false];
                    }
                    $semScore = $semScoreVal;
                } else {
                    $sectorB = $sectorA; // fallback = same as A
                }

                $results[] = [
                    'product' => $productName,
                    'company_id' => $c['id'],
                    'cnae_code' => $c['cnae_code'],
                    'cnae_label' => $c['cnae_label'],
                    'objeto_social_short' => mb_substr($c['objeto_social'] ?? '', 0, 120),
                    'raw_cosine' => $rawCosine,
                    'semantic_score' => $semScore,
                    'tax_score' => $sectorA['score'] ?? null,
                    'tax_conf' => $sectorA['confidence'] ?? 0,
                    'tax_active' => $sectorA['active'],
                    'hybrid_score' => $sectorB['score'] ?? null,
                    'hybrid_conf' => $sectorB['confidence'] ?? 0,
                    'hybrid_conflict' => $sectorB['conflict'] ?? false,
                ];
            }
        }

        // Compute per-product cosine stats, top/middle/bottom, Spearman
        $summary = [];
        foreach ($products as $prod) {
            $rows = array_values(array_filter($results, fn($r) => $r['product'] === $prod));
            $cosines = array_filter(array_column($rows, 'raw_cosine'), fn($v) => $v !== null);

            // Sort by taxonomy and hybrid for Spearman
            $taxRanked = $rows;
            usort($taxRanked, fn($a, $b) => ($b['tax_score'] ?? 0) <=> ($a['tax_score'] ?? 0));
            $hybridRanked = $rows;
            usort($hybridRanked, fn($a, $b) => ($b['hybrid_score'] ?? 0) <=> ($a['hybrid_score'] ?? 0));

            $taxRank = [];
            foreach ($taxRanked as $i => $r) $taxRank[$r['company_id']] = $i + 1;
            $hybridRank = [];
            foreach ($hybridRanked as $i => $r) $hybridRank[$r['company_id']] = $i + 1;

            $spearman = $this->spearman($taxRank, $hybridRank);

            // Sorted by cosine for top/mid/bottom
            $cosineRows = array_values(array_filter($rows, fn($r) => $r['raw_cosine'] !== null));
            usort($cosineRows, fn($a, $b) => $b['raw_cosine'] <=> $a['raw_cosine']);

            $n = count($cosineRows);
            $top5    = array_slice($cosineRows, 0, 5);
            $mid3    = $n > 10 ? array_slice($cosineRows, (int)($n/2)-1, 3) : [];
            $bottom5 = array_slice($cosineRows, max(0, $n-5), 5);

            // Large rank changes
            $rankChanges = [];
            foreach ($rows as $r) {
                $tR = $taxRank[$r['company_id']] ?? null;
                $hR = $hybridRank[$r['company_id']] ?? null;
                if ($tR && $hR) {
                    $delta = $tR - $hR; // positive = moved up
                    if (abs($delta) >= 5) {
                        $r['tax_rank'] = $tR;
                        $r['hybrid_rank'] = $hR;
                        $r['rank_delta'] = $delta;
                        $rankChanges[] = $r;
                    }
                }
            }
            usort($rankChanges, fn($a, $b) => abs($b['rank_delta']) <=> abs($a['rank_delta']));

            // False positives: high cosine but low taxonomy
            $fps = array_filter($cosineRows, fn($r) => ($r['raw_cosine'] ?? 0) > 0.55 && ($r['tax_score'] ?? 100) < 30);
            // False negatives: low cosine but high taxonomy
            $fns = array_filter($rows, fn($r) => ($r['raw_cosine'] !== null) && $r['raw_cosine'] < 0.40 && ($r['tax_score'] ?? 0) > 70);

            // Conflicts
            $conflicts = array_filter($rows, fn($r) => $r['hybrid_conflict'] === true);

            // Confidence comparison
            $confTaxOnly = array_filter($rows, fn($r) => $r['tax_active'] && $r['raw_cosine'] === null);
            $confHybridConsistent = array_filter($rows, fn($r) => $r['hybrid_conflict'] === false && $r['raw_cosine'] !== null);
            $confHybridConflict = array_filter($rows, fn($r) => $r['hybrid_conflict'] === true);

            $avgConf = fn(array $rs, string $key): float => count($rs) ? array_sum(array_column($rs, $key)) / count($rs) : 0;

            $summary[$prod] = [
                'cosine_stats' => $this->stats(array_values($cosines)),
                'spearman' => round($spearman, 4),
                'coverage' => count($cosineRows) . '/' . count($rows),
                'top5' => $top5,
                'mid3' => $mid3,
                'bottom5' => $bottom5,
                'rank_changes' => array_slice($rankChanges, 0, 10),
                'false_positives' => array_values($fps),
                'false_negatives' => array_values($fns),
                'conflicts' => array_values($conflicts),
                'confidence' => [
                    'taxonomy_only_avg' => round($avgConf($confTaxOnly, 'tax_conf'), 3),
                    'hybrid_consistent_avg' => round($avgConf($confHybridConsistent, 'hybrid_conf'), 3),
                    'hybrid_conflict_avg' => round($avgConf($confHybridConflict, 'hybrid_conf'), 3),
                ]
            ];
        }

        // CNAE diversity
        $cnaeDiv = [];
        foreach ($companies as $c) {
            $div = substr($c['cnae_code'] ?? '00', 0, 2);
            $cnaeDiv[$div] = ($cnaeDiv[$div] ?? 0) + 1;
        }
        arsort($cnaeDiv);

        file_put_contents($resultsFile, json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents($summaryFile, json_encode([
            'companies' => count($companies),
            'products' => count($products),
            'total_comparisons' => count($results),
            'cnae_diversity' => ['unique_divisions' => count($cnaeDiv), 'top_10' => array_slice($cnaeDiv, 0, 10, true)],
            'summary' => $summary
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        CLI::write("Done. Summary saved: " . $summaryFile, 'green');
        CLI::write("Results saved: " . $resultsFile, 'green');
    }
}
