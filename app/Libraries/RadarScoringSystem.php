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
        
        $triggerType = $company['trigger_type'] ?? '';

        // Empresa nueva que el cálculo nocturno aún no ha puntuado: su acto es la
        // constitución. Antes salía "Sin acto reciente" con 25 puntos.
        if (empty($company['main_act_type']) && $triggerType === 'nueva_empresa' && !empty($company['fecha_constitucion'])) {
            $company['main_act_type'] = 'Constitución';
            if (empty($company['last_borme_date'])) {
                $company['last_borme_date'] = $company['fecha_constitucion'];
            }
        }
        $isHighValueTrigger = in_array($triggerType, ['contrato', 'subvencion']);

        $dbScore = (int)($company['score_total'] ?? 0);
        
        if ($dbScore > 0) {
            // Una subvención o un contrato ya no fuerzan 95 puntos: copaban el listado por
            // encima de todas las constituciones (02-10-2026).
            $baseScore = $dbScore;
            $bormeScore = (int)($company['borme_score_static'] ?? $dbScore);
            $qualityScore = self::calculateQualityScore($company);
            $contactScore = self::calculateContactScore($company);
        } else {
            $bormeScore = $isHighValueTrigger ? 70 : self::calculateBormeScore($company);
            $qualityScore = self::calculateQualityScore($company);
            $contactScore = self::calculateContactScore($company);
            
            // Misma escala que la puntuación guardada (máximo 90), sin dividir entre 0,90.
            $baseScore = ($bormeScore * 0.60) + ($qualityScore * 0.15) + ($contactScore * 0.15);
        }

        $personalizationScore = self::calculatePersonalizationScore($engagementScore, $groupScore, $userPrefScore);

        // FÓRMULA HÍBRIDA: 90% Base Estática (DB o Fallback) + 10% Personalización IA
        // La puntuación guardada se muestra tal cual; la personalización suma hasta 10 puntos.
        // Antes se mostraba el 90 % de la guardada, y las etiquetas altas eran inalcanzables.
        $finalScore = $baseScore + ($personalizationScore * 0.10);

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
            'explanation' => self::buildExplanation($bormeScore, $qualityScore, $contactScore, $personalizationScore, $mainAct, $triggerType)
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
        if ($mainAct === 'Extinción') {
            return [
                'label' => 'No contactar',
                'icon' => '🚫',
                'color' => '#64748b',
                'bg' => 'rgba(100, 116, 139, 0.1)',
                'priority' => 'ninguna'
            ];
        }
        // Sin puntuación no es "No contactar": eso queda para las extinguidas.
        if ($score === 0) {
            return [
                'label' => 'Sin puntuar',
                'icon' => '⚪',
                'color' => '#94a3b8',
                'bg' => 'rgba(148, 163, 184, 0.1)',
                'priority' => 'ninguna'
            ];
        }
        // Tramos acordes con la escala real (la puntuación guardada no pasa de ~76).
        if ($score >= 70) {
            return [
                'label' => 'Prioridad alta',
                'icon' => '🔥',
                'color' => '#ef4444',
                'bg' => 'rgba(239, 68, 68, 0.1)',
                'priority' => 'muy_alta'
            ];
        }

        if ($score >= 60) {
            return [
                'label' => 'Prioridad media-alta',
                'icon' => '🟡',
                'color' => '#f59e0b',
                'bg' => 'rgba(245, 158, 11, 0.1)',
                'priority' => 'alta'
            ];
        }

        if ($score >= 45) {
            return [
                'label' => 'Prioridad media',
                'icon' => '🟢',
                'color' => '#10b981',
                'bg' => 'rgba(16, 185, 129, 0.1)',
                'priority' => 'media'
            ];
        }

        if ($score >= 30) {
            return [
                'label' => 'Prioridad baja',
                'icon' => '⚪',
                'color' => '#94a3b8',
                'bg' => 'rgba(148, 163, 184, 0.1)',
                'priority' => 'baja'
            ];
        }

        return [
            'label' => 'Prioridad muy baja',
            'icon' => '⚠️',
            'color' => '#fbbf24',
            'bg' => 'rgba(251, 191, 36, 0.1)',
            'priority' => 'muy_baja'
        ];
    }

    private static function buildExplanation(int $borme, int $quality, int $contact, int $personalization, string $act, string $triggerType = ''): string
    {
        $reasons = [];
        if ($triggerType === 'contrato') {
            $reasons[] = "Contrato público adjudicado recientemente";
        } elseif ($triggerType === 'subvencion') {
            $reasons[] = "Subvención concedida recientemente";
        } else {
            $reasons[] = trim($act) !== '' ? "Acto en el BORME: $act" : "Sin acto reciente en el BORME";
        }
        if ($quality > 50) $reasons[] = "Ficha con bastantes datos (sector, capital, objeto social)";
        if ($contact > 50) $reasons[] = "Tiene datos de contacto";
        if ($personalization > 50) $reasons[] = "Parecida a empresas que has guardado o contactado";
        
        return implode(". ", $reasons);
    }
}
