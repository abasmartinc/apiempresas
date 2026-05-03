<?php

namespace App\Libraries;

/**
 * RadarScoringSystem
 * 
 * Centralized logic for calculating company commercial opportunity scores.
 * Refactored to prioritize BORME signals over contact data.
 */
class RadarScoringSystem
{
    /**
     * Calculates the full breakdown and final score for a company.
     * 
     * @param array $company Company data from DB (includes crs fields)
     * @param int $engagementScore Personal interaction score
     * @param int $groupScore Sector/Province global success rate
     * @param int $userPrefScore User's learned preference score
     * @return array Breakdown and final score
     */
    public static function calculate(array $company, int $engagementScore = 0, int $groupScore = 0, int $userPrefScore = 0): array
    {
        // MODELO HÍBRIDO: 
        // 1. Intentamos usar el score_total de la DB si es > 0 (asumimos que el script externo ya aplicó el 90% de la lógica)
        // 2. Si es 0, usamos el fallback de cálculo en tiempo real (mismo algoritmo que el script externo)
        
        $dbScore = (int)($company['score_total'] ?? 0);
        
        if ($dbScore > 0) {
            $baseScore = $dbScore;
            // Para el desglose en el debug, estimamos los bloques si no los tenemos
            $bormeScore = (int)($company['borme_score_static'] ?? $dbScore); // Campo hipotético si el script lo guarda
            $qualityScore = 100; // Placeholder
            $contactScore = 100; // Placeholder
        } else {
            $bormeScore = self::calculateBormeScore($company);
            $qualityScore = self::calculateQualityScore($company);
            $contactScore = self::calculateContactScore($company);
            
            // Cálculo manual del 90% estático
            $baseScore = (($bormeScore * 0.60) + ($qualityScore * 0.15) + ($contactScore * 0.15)) / 0.90;
        }

        $personalizationScore = self::calculatePersonalizationScore($engagementScore, $groupScore, $userPrefScore);

        // FÓRMULA HÍBRIDA: 90% Base Estática (DB o Fallback) + 10% Personalización IA
        $finalScore = ($baseScore * 0.90) + ($personalizationScore * 0.10);

        // Cap at 100 and floor at 0
        $finalScore = max(0, min(100, round($finalScore)));

        // Señales negativas críticas (siempre se verifican en tiempo real)
        $mainAct = $company['main_act_type'] ?? '';
        if ($mainAct === 'Extinción') {
            $finalScore = 0;
        } elseif (in_array($mainAct, ['Disolución', 'Situación concursal'])) {
            $finalScore = min($finalScore, 20); 
        }

        $visuals = self::getVisuals($finalScore, $mainAct);

        return [
            'final_score' => $finalScore,
            'borme_score' => $bormeScore,
            'quality_score' => $qualityScore,
            'contact_score' => $contactScore,
            'personalization_score' => $personalizationScore,
            'visuals' => $visuals,
            'explanation' => self::buildExplanation($bormeScore, $qualityScore, $contactScore, $personalizationScore, $mainAct)
        ];
    }


    /**
     * Block 1: BORME Opportunity Score (60% weight)
     */
    private static function calculateBormeScore(array $company): int
    {
        $actType = $company['main_act_type'] ?? '';
        $baseWeights = [
            'Constitución' => 90,
            'Ampliación de capital' => 80,
            'Cambio de objeto social' => 75,
            'Fusión' => 72,
            'Transformación' => 68,
            'Escisión' => 65,
            'Cambio de domicilio social' => 62,
            'Nombramientos' => 55,
            'Ceses/Dimisiones' => 48,
            'Revocaciones' => 45,
            'Declaración de unipersonalidad' => 42,
            'Reelecciones' => 35,
            'Reducción de capital' => 30,
            'Otros' => 25,
            'Disolución' => 15,
            'Situación concursal' => 10,
            'Extinción' => 0,
        ];

        $score = $baseWeights[$actType] ?? 25;

        // Freshness bonus
        if (!empty($company['last_borme_date'])) {
            $daysSince = (time() - strtotime($company['last_borme_date'])) / 86400;
            if ($daysSince <= 7) {
                $score += 10;
            } elseif ($daysSince <= 30) {
                $score += 5;
            }
        }

        return min(100, $score);
    }

    /**
     * Block 2: Company Quality Score (15% weight)
     */
    private static function calculateQualityScore(array $company): int
    {
        $score = 0;
        
        // SL / CIF starts with B
        if (!empty($company['cif']) && (strtoupper($company['cif'][0]) === 'B')) {
            $score += 30;
        }

        // Social Object
        $objLen = strlen($company['objeto_social'] ?? '');
        if ($objLen > 250) {
            $score += 20;
        } elseif ($objLen > 100) {
            $score += 10;
        }

        // Capital Social
        if (!empty($company['capital_social_raw'])) {
            $score += 20;
        }

        // Sector/CNAE identified
        if (!empty($company['cnae_label'])) {
            $score += 15;
        }

        // Province identified
        if (!empty($company['registro_mercantil'])) {
            $score += 15;
        }

        return min(100, $score);
    }

    /**
     * Block 3: Contactability Score (15% weight)
     */
    private static function calculateContactScore(array $company): int
    {
        $score = 0;

        if (!empty($company['phone'])) $score += 40;
        if (!empty($company['url'])) $score += 25; // Assuming url or company_url_id indicates web
        if (!empty($company['address'])) $score += 20;
        if (!empty($company['municipality'])) $score += 15;

        return min(100, $score);
    }

    /**
     * Block 4: Personalization Score (10% weight)
     */
    private static function calculatePersonalizationScore(int $engagement, int $group, int $userPref): int
    {
        // Internal weighting for this block
        $score = ($engagement * 0.4) + ($group * 0.3) + ($userPref * 0.3);
        return min(100, (int)$score);
    }

    /**
     * Projections for visual UI
     */
    private static function getVisuals(int $score, string $mainAct): array
    {
        if ($mainAct === 'Extinción' || $score === 0) {
            return [
                'label' => 'No contactar',
                'icon' => '🚫',
                'color' => '#64748b',
                'bg' => 'rgba(100, 116, 139, 0.1)',
                'priority' => 'ninguna'
            ];
        }

        if ($score >= 85) {
            return [
                'label' => 'Lead prioritario',
                'icon' => '🔥',
                'color' => '#ef4444',
                'bg' => 'rgba(239, 68, 68, 0.1)',
                'priority' => 'muy_alta'
            ];
        }

        if ($score >= 70) {
            return [
                'label' => 'Oportunidad alta',
                'icon' => '🟡',
                'color' => '#f59e0b',
                'bg' => 'rgba(245, 158, 11, 0.1)',
                'priority' => 'alta'
            ];
        }

        if ($score >= 50) {
            return [
                'label' => 'Oportunidad media',
                'icon' => '🟢',
                'color' => '#10b981',
                'bg' => 'rgba(16, 185, 129, 0.1)',
                'priority' => 'media'
            ];
        }

        if ($score >= 30) {
            return [
                'label' => 'Potencial bajo',
                'icon' => '⚪',
                'color' => '#94a3b8',
                'bg' => 'rgba(148, 163, 184, 0.1)',
                'priority' => 'baja'
            ];
        }

        return [
            'label' => 'Baja prioridad',
            'icon' => '⚠️',
            'color' => '#fbbf24',
            'bg' => 'rgba(251, 191, 36, 0.1)',
            'priority' => 'muy_baja'
        ];
    }

    private static function buildExplanation(int $borme, int $quality, int $contact, int $personalization, string $act): string
    {
        $reasons = [];
        $reasons[] = "Señal BORME ($act): $borme/100";
        if ($quality > 50) $reasons[] = "Perfil de empresa de alta calidad";
        if ($contact > 50) $reasons[] = "Múltiples vías de contacto detectadas";
        if ($personalization > 50) $reasons[] = "Alta afinidad con tus intereses detectada por IA";
        
        return implode(". ", $reasons);
    }
}
