<?php
namespace App\Libraries\B2B\Scorers;

use App\Libraries\B2B\CnaeConceptResolver;

class SectorFitScorer {
    public static function score(array $company, \App\Libraries\B2B\ProductProfile $profile, ?float $realCosine = null): array {
        $config = config('B2BScoring');

        // ---------------------------------------------------------------
        // LAYER 1: TAXONOMY (CNAE-based, deterministic)
        // ---------------------------------------------------------------
        $taxActive = false;
        $taxScore  = 0;
        $taxConf   = 0.0;
        $taxMatchLevel = null;
        $taxMatchedCnae = null;

        $tiSource = $profile->targetIndustries['source'] ?? 'unknown';
        $tiValues = $profile->targetIndustries['value'] ?? [];
        $tiConf   = $profile->targetIndustries['confidence'] ?? 0.0;

        if (($tiSource === 'explicit' || $tiSource === 'inferred_high_confidence') && !empty($tiValues)) {
            $companyCnaeCode = trim($company['cnae_code'] ?? '');

            if (!empty($companyCnaeCode)) {
                $taxActive = true;

                // Resolve LLM concepts to CNAE hierarchy entries
                $resolved = CnaeConceptResolver::resolve($tiValues);

                if (!empty($resolved)) {
                    // Score company's CNAE against resolved entries
                    $result = CnaeConceptResolver::scoreCompanyAgainstCnaes($companyCnaeCode, $resolved);
                    $taxScore = $result['score'] ?? 20;
                    $taxMatchLevel = $result['match_level'];
                    $taxMatchedCnae = $result['matched_cnae'];
                } else {
                    // Concepts exist but none resolved to CNAE — fallback to label string match
                    $cnaeLabel = strtolower($company['cnae_label'] ?? '');
                    $taxScore = 20; // default unrelated
                    foreach ($tiValues as $ind) {
                        $keyword = CnaeConceptResolver::normalize($ind);
                        if (!empty($keyword) && str_contains($cnaeLabel, $keyword)) {
                            $taxScore = 90;
                            $taxMatchLevel = 'label_match';
                            break;
                        }
                    }
                }

                $taxConf = $tiConf;

                // Apply exclusions
                $eiSource = $profile->excludedIndustries['source'] ?? 'unknown';
                $eiValues = $profile->excludedIndustries['value'] ?? [];
                if (!empty($eiValues)) {
                    $resolvedEx = CnaeConceptResolver::resolve($eiValues);
                    $exResult = CnaeConceptResolver::scoreCompanyAgainstCnaes($companyCnaeCode, $resolvedEx);
                    if (($exResult['score'] ?? 0) >= 70) {
                        if ($eiSource === 'explicit') {
                            $taxScore = 0;
                            $taxConf  = $profile->excludedIndustries['confidence'] ?? 1.0;
                        } elseif ($eiSource === 'inferred_high_confidence') {
                            $taxScore = min($taxScore, 15);
                            $taxConf  = max($taxConf, 0.5);
                        }
                    }
                }
            }
        }

        // ---------------------------------------------------------------
        // LAYER 2: SEMANTIC (cosine-based)
        // ---------------------------------------------------------------
        $semanticActive = false;
        $semScore = 0.0;
        $semConf  = 0.0;

        // Accept pre-computed cosine (from EmbeddingValidationCmd or future scorer)
        // OR embedded vector (legacy path: checks embedding array length)
        $cosineInput = $realCosine;
        if ($cosineInput === null) {
            $embedding = $company['embedding'] ?? null;
            if (is_array($embedding) && count($embedding) === $config->expected_embedding_dimensions) {
                // This path is only reached if caller passes raw embedding vector.
                // Cosine is computed externally; here we would need product embedding too.
                // For now, skip semantic if no pre-computed cosine is provided and embedding is not
                // from a format where we can compute it here.
                // NOTE: The old mock cosine = 0.6 is REMOVED.
                $cosineInput = null;
            }
        }

        if ($cosineInput !== null && is_finite($cosineInput)) {
            $semanticActive = true;
            $normalized = ($cosineInput - $config->cosine_lower_bound) / ($config->cosine_upper_bound - $config->cosine_lower_bound);
            $semScore = max(0.0, min(100.0, $normalized * 100));
            // Confidence reflects quality of semantic evidence
            // High cosine → high confidence; ambiguous cosine (near boundary) → lower
            $boundary_distance = min(
                abs($cosineInput - $config->cosine_lower_bound),
                abs($cosineInput - $config->cosine_upper_bound)
            );
            $boundary_ratio = min(1.0, $boundary_distance / (($config->cosine_upper_bound - $config->cosine_lower_bound) / 2));
            $semConf = 0.5 + ($boundary_ratio * 0.4); // range [0.5, 0.9]
        }

        // ---------------------------------------------------------------
        // COMBINE
        // ---------------------------------------------------------------
        if (!$taxActive && !$semanticActive) {
            return [
                'active'         => false,
                'score'          => null,
                'confidence'     => 0.0,
                'conflict'       => false,
                'tax_match_level'=> null,
                'tax_matched_cnae' => null,
            ];
        }

        $conflict = false;
        $finalScore = 0.0;
        $finalConf  = 0.0;

        if ($taxActive && $semanticActive) {
            if (abs($taxScore - $semScore) > 50) {
                $conflict = true;
            }
            $finalScore = ($taxScore * $config->sector_cnae_weight) + ($semScore * $config->sector_semantic_weight);
            $finalConf  = ($taxConf  * $config->sector_cnae_weight) + ($semConf  * $config->sector_semantic_weight);
            if ($conflict) $finalConf -= $config->conflict_confidence_penalty;

        } elseif ($taxActive) {
            $finalScore = $taxScore;
            $finalConf  = $taxConf - $config->missing_semantic_confidence_penalty;

        } elseif ($semanticActive) {
            $finalScore = $semScore;
            $finalConf  = $semConf;
        }

        return [
            'active'           => true,
            'score'            => $finalScore,
            'confidence'       => max(0.0, $finalConf),
            'conflict'         => $conflict,
            'tax_match_level'  => $taxMatchLevel,
            'tax_matched_cnae' => $taxMatchedCnae,
        ];
    }
}