<?php
namespace Config;
use CodeIgniter\Config\BaseConfig;

class B2BScoring extends BaseConfig
{
    public string $mode = 'v1'; // v1, shadow, v2

    public function __construct() {
        parent::__construct();
        if (env('B2B_SCORING_MODE')) {
            $this->mode = env('B2B_SCORING_MODE');
        }
    }

    // Global Switches
    public bool $scale_enabled = false;

    // Feature Weights
    public string $scoring_version = '2.3.0';
    public string $product_profile_version = '1.2.0';
    public string $embedding_model = 'text-embedding-3-small';
    public string $embedding_version = '1.0.0';
    public int $expected_embedding_dimensions = 1536;

    // Heuristics V2 Calibration
    public float $weight_sector = 0.65;
    public float $weight_scale = 0.35;
    
    public float $cosine_lower_bound = 0.25;
    public float $cosine_upper_bound = 0.50;
    
    public float $sector_cnae_weight = 0.80;
    public float $sector_semantic_weight = 0.20;

    // Triggers [Base Strength, Half-Life Days]
    public array $triggers = [
        'Constitución' => [100, 30],
        'Contrato' => [90, 90],
        'Subvención' => [90, 90],
        'Ampliación' => [80, 180],
        'Nombramiento' => [70, 60]
    ];
    public float $trigger_max_support_boost = 10.0;
    public float $trigger_support_scale = 50.0;

    // Risks & Conflicts
    public int $soft_risk_penalty = 15;
    public int $strong_risk_cap = 40;
    public float $conflict_confidence_penalty = 0.30;
    public float $missing_semantic_confidence_penalty = 0.20;
    public float $exact_taxonomy_missing_semantic_penalty = 0.05;
    public float $semantic_only_confidence_cap = 0.65;
}