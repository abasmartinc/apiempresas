<?php
namespace App\Libraries\B2B;

use App\Libraries\B2B\Scorers\SectorFitScorer;
use App\Libraries\B2B\Scorers\ScaleFitScorer;
use App\Libraries\B2B\Scorers\TriggerScorer;
use App\Libraries\B2B\Scorers\CompanyRiskEvaluator;
use Config\Database;
use App\Services\OpenAiService;

class B2BOpportunityScorer {
    protected static array $productEmbeddingCache = [];

    public function calculate(array $company, string $rawProduct): array {
        $config = config('B2BScoring');
        $builder = new ProductProfileBuilder();
        $profile = $builder->getProfile($rawProduct);
        $normalizedProduct = ProductNormalizer::normalize($rawProduct);

        // 1. Get Product Embedding (request-local memory cache)
        $productEmbedding = $this->getProductEmbedding($normalizedProduct);

        // 2. Get Company Embedding (Lazy DB Cache / On-Demand Generation)
        $companyEmbedding = $this->getCompanyEmbeddingLazy($company);

        // 3. Compute raw Cosine if both embeddings are valid
        $realCosine = null;
        if ($companyEmbedding && $productEmbedding 
            && count($companyEmbedding) === $config->expected_embedding_dimensions 
            && count($productEmbedding) === $config->expected_embedding_dimensions) {
            $realCosine = $this->cosine($companyEmbedding, $productEmbedding);
        }

        // 4. Sector Fit
        $sector = SectorFitScorer::score($company, $profile, $realCosine);
        $scale  = $config->scale_enabled ? ScaleFitScorer::score($company, $profile) : ['active' => false, 'score' => 0, 'confidence' => 0.0];
        
        $trigger = TriggerScorer::score($company);
        $risk    = CompanyRiskEvaluator::evaluate($company);
        
        $activeWeight = 0;
        $fit = 0;
        $confidence = 0;
        
        if ($sector['active']) {
            $activeWeight += $config->weight_sector;
            $fit += $sector['score'] * $config->weight_sector;
            $confidence += $sector['confidence'] * $config->weight_sector;
        }
        
        if ($scale['active']) {
            $activeWeight += $config->weight_scale;
            $fit += $scale['score'] * $config->weight_scale;
            $confidence += $scale['confidence'] * $config->weight_scale;
        }
        
        if ($activeWeight > 0) {
            $fit = $fit / $activeWeight;
            $confidence = $confidence / $activeWeight;
        } else {
            $fit = null;
            $confidence = 0.0;
        }
        
        $disqualified = false;
        if ($fit !== null) {
            if ($risk['level'] === 'hard') {
                $disqualified = true;
                $fit = 0;
            } elseif ($risk['level'] === 'strong') {
                $fit = min($fit, $config->strong_risk_cap);
            } elseif ($risk['level'] === 'soft') {
                $fit = max(0, $fit - $config->soft_risk_penalty);
            }
        }
        
        return [
            'match_score' => $fit === null ? 0 : round($fit),
            'opportunity_fit' => $fit === null ? null : round($fit),
            'score_status' => $fit === null ? 'unavailable' : 'valid',
            'trigger_score' => round($trigger['score']),
            'confidence_score' => round($confidence, 3),
            'disqualified' => $disqualified,
            'risk' => $risk,
            'components' => [
                'sector_fit' => $sector,
                'scale_fit'  => $scale
            ],
            'model' => [
                'scoring_version' => $config->scoring_version,
                'product_profile_version' => $config->product_profile_version,
                'embedding_model' => $config->embedding_model,
                'score_status' => $fit === null ? 'unavailable' : 'valid',
                'company_embedding_source' => $companyEmbedding ? 'cached_or_generated' : 'unavailable',
                'product_embedding_source' => $productEmbedding ? 'cached_or_generated' : 'unavailable',
                'scale_threshold_version' => $scale['version'] ?? null,
                'scale_active' => $scale['active']
            ]
        ];
    }

    protected function getProductEmbedding(string $normalizedProduct): ?array {
        $hash = md5($normalizedProduct);
        if (isset(self::$productEmbeddingCache[$hash])) {
            return self::$productEmbeddingCache[$hash];
        }
        try {
            $openai = new OpenAiService();
            $emb = $openai->getEmbeddings($normalizedProduct);
            if (is_array($emb) && count($emb) === 1536) {
                self::$productEmbeddingCache[$hash] = $emb;
                return $emb;
            }
        } catch (\Exception $e) {
            log_message('error', 'Product embedding generation error: ' . $e->getMessage());
        }
        return null;
    }

    protected function getCompanyEmbeddingLazy(array $company): ?array {
        $config = config('B2BScoring');
        $companyId = $company['id'] ?? null;
        if (!$companyId) return null;

        $canonicalText = CompanySemanticProfileBuilder::build($company);
        $sourceHash    = CompanySemanticProfileBuilder::hash($canonicalText);

        $model   = $config->embedding_model ?? 'text-embedding-3-small';
        $version = $config->company_embedding_version ?? '1.0.0';

        // 1. Cache Lookup
        try {
            $db = Database::connect();
            $cached = $db->table('company_embeddings')
                ->where('company_id', $companyId)
                ->where('embedding_model', $model)
                ->where('embedding_version', $version)
                ->get()->getRowArray();

            if ($cached && !empty($cached['embedding'])) {
                if ($cached['source_hash'] === $sourceHash) {
                    $emb = json_decode($cached['embedding'], true);
                    if (is_array($emb) && count($emb) === $config->expected_embedding_dimensions) {
                        return $emb; // VALID -> REUSE
                    }
                }
            }
        } catch (\Exception $e) {
            log_message('error', 'company_embeddings cache lookup error: ' . $e->getMessage());
        }

        // 2. MISSING, STALE or INVALID -> Generate On-Demand
        try {
            $openai = new OpenAiService();
            $emb = $openai->getEmbeddings($canonicalText);

            if (!is_array($emb) || count($emb) !== $config->expected_embedding_dimensions) {
                return null; // Invalid response format -> fail-soft
            }
            foreach ($emb as $v) {
                if (!is_numeric($v) || is_infinite($v) || is_nan($v)) return null;
            }

            // 3. Persist to Cache (Fail-soft if write fails)
            try {
                $db = Database::connect();
                $db->query(
                    "INSERT INTO company_embeddings (company_id, embedding_model, embedding_version, source_hash, embedding, created_at, updated_at) 
                     VALUES (?, ?, ?, ?, ?, NOW(), NOW()) 
                     ON DUPLICATE KEY UPDATE source_hash = VALUES(source_hash), embedding = VALUES(embedding), updated_at = NOW()",
                    [$companyId, $model, $version, $sourceHash, json_encode($emb)]
                );
            } catch (\Exception $e) {
                log_message('error', 'company_embeddings persist error: ' . $e->getMessage());
            }

            return $emb; // Return generated embedding for immediate use
        } catch (\Exception $e) {
            log_message('error', 'OpenAI company embedding generation error: ' . $e->getMessage());
            return null; // Fail-soft: fallback to Taxonomy-only
        }
    }

    protected function cosine(array $a, array $b): float {
        $dot=0; $mA=0; $mB=0;
        for($i=0;$i<count($a);$i++){$dot+=$a[$i]*$b[$i];$mA+=$a[$i]*$a[$i];$mB+=$b[$i]*$b[$i];}
        return ($mA==0||$mB==0) ? 0.0 : ($dot/(sqrt($mA)*sqrt($mB)));
    }
}