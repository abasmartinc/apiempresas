<?php
namespace App\Libraries\B2B;

use App\Libraries\B2B\Scorers\SectorFitScorer;
use App\Libraries\B2B\Scorers\ScaleFitScorer;
use App\Libraries\B2B\Scorers\TriggerScorer;
use App\Libraries\B2B\Scorers\CompanyRiskEvaluator;

class B2BOpportunityScorer {
    public function calculate(array $company, string $rawProduct): array {
        $config = config('B2BScoring');
        $builder = new ProductProfileBuilder();
        $profile = $builder->getProfile($rawProduct);
        
        $sector = SectorFitScorer::score($company, $profile);
        $scale = $config->scale_enabled ? ScaleFitScorer::score($company, $profile) : ['active' => false, 'score' => 0, 'confidence' => 0.0];
        
        $trigger = TriggerScorer::score($company);
        $risk = CompanyRiskEvaluator::evaluate($company);
        
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
            // Both inactive/unavailable
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
            'trigger_score' => round($trigger['score']),
            'confidence_score' => round($confidence, 3),
            'disqualified' => $disqualified,
            'risk' => $risk,
            'components' => [
                'sector_fit' => $sector,
                'scale_fit' => $scale
            ],
            'model' => [
                'scoring_version' => $config->scoring_version,
                'product_profile_version' => $config->product_profile_version,
                'embedding_model' => $config->embedding_model,
                'scale_threshold_version' => $scale['version'] ?? null,
                'scale_active' => $scale['active']
            ]
        ];
    }
}