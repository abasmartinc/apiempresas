<?php

namespace App\Services;

use App\Models\CompanyModel;
use App\Models\CompanyRadarScoreModel;

class CompanyScoringService
{
    protected CompanyRadarScoreModel $scoreModel;
    protected CompanyModel $companyModel;

    public function __construct()
    {
        $this->scoreModel = new CompanyRadarScoreModel();
        $this->companyModel = new CompanyModel();
    }

    /**
     * Obtiene los datos de score y senales para un CIF.
     */
    public function getScoreData(string $cif): ?array
    {
        $cif = strtoupper(trim($cif));
        if ($cif === '') {
            return null;
        }

        $data = $this->scoreModel->getByCif($cif);

        if ($data) {
            $score = (int) $data['score_total'];

            return [
                'score' => $score,
                'priority' => $this->priorityFromScore($score),
                'reasons' => $this->normalizeReasons($data['score_reasons'] ?? null),
                'last_signal' => [
                    'type' => $data['main_act_type'],
                    'date' => $data['last_borme_date'],
                ],
                'source' => 'radar',
            ];
        }

        $company = $this->companyModel->getByCif($cif);
        if (!$company) {
            return null;
        }

        return $this->buildRadarScoreV2($company);
    }

    /**
     * Formatea senales comerciales adicionales.
     */
    public function getSignals(string $cif): array
    {
        $data = $this->getScoreData($cif);

        if (!$data) {
            return [];
        }

        return [
            [
                'type' => 'borme_event',
                'label' => $data['last_signal']['type'],
                'date' => $data['last_signal']['date'],
                'probability' => $data['priority'],
            ],
        ];
    }

    private function buildRadarScoreV2(array $company): array
    {
        $signal = $this->getLatestBormeSignal((int) ($company['id'] ?? 0));
        $actType = $signal['act_types'] ?? 'Otros';
        $bormeDate = $signal['borme_date'] ?? null;

        $opportunityScore = $this->calculateOpportunityScore($actType, $bormeDate);
        if ($this->isActType($actType, ['extincion'])) {
            $opportunityScore = 0;
        }

        $qualityScore = $this->calculateQualityScore($company);
        $contactScore = $this->calculateContactScore($company);

        $score = (int) round(($opportunityScore * 0.60) + ($qualityScore * 0.15) + ($contactScore * 0.15));
        if ($this->isActType($actType, ['extincion'])) {
            $score = 0;
        }

        $score = max(0, min(90, $score));

        return [
            'score' => $score,
            'priority' => $this->priorityFromScore($score),
            'reasons' => [
                "OPP:{$opportunityScore}|QUAL:{$qualityScore}|CONT:{$contactScore} · {$actType}",
            ],
            'last_signal' => [
                'type' => $actType,
                'date' => $bormeDate,
            ],
            'source' => 'radar',
        ];
    }

    private function priorityFromScore(int $score): string
    {
        if ($score >= 75) return 'muy_alta';
        if ($score >= 60) return 'alta';
        if ($score >= 40) return 'media';
        if ($score >= 20) return 'baja';

        return 'muy_baja';
    }

    private function getLatestBormeSignal(int $companyId): ?array
    {
        if ($companyId <= 0) {
            return null;
        }

        try {
            return \Config\Database::connect()
                ->table('borme_posts')
                ->select('act_types, borme_date')
                ->where('company_id', $companyId)
                ->orderBy('borme_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray() ?: null;
        } catch (\Throwable $e) {
            log_message('error', '[CompanyScoringService::getLatestBormeSignal] ' . $e->getMessage());
            return null;
        }
    }

    private function calculateOpportunityScore(?string $actType, ?string $bormeDate): int
    {
        $actType = trim((string) $actType);

        if ($this->isActType($actType, ['constitucion'])) {
            $score = 90;
        } elseif ($this->isActType($actType, ['ampliacion de capital', 'ampliacion'])) {
            $score = 80;
        } elseif ($this->isActType($actType, ['cambio de objeto social', 'objeto social'])) {
            $score = 75;
        } elseif ($this->isActType($actType, ['fusion'])) {
            $score = 72;
        } elseif ($this->isActType($actType, ['transformacion'])) {
            $score = 68;
        } elseif ($this->isActType($actType, ['escision'])) {
            $score = 65;
        } elseif ($this->isActType($actType, ['cambio de domicilio', 'domicilio'])) {
            $score = 62;
        } elseif ($this->isActType($actType, ['nombramientos'])) {
            $score = 55;
        } elseif ($this->isActType($actType, ['ceses', 'dimisiones'])) {
            $score = 48;
        } elseif ($this->isActType($actType, ['revocaciones'])) {
            $score = 45;
        } elseif ($this->isActType($actType, ['disolucion'])) {
            $score = 15;
        } elseif ($this->isActType($actType, ['concurso', 'liquidacion', 'situacion concursal'])) {
            $score = 10;
        } elseif ($this->isActType($actType, ['extincion'])) {
            return 0;
        } else {
            $score = 25;
        }

        if ($bormeDate) {
            $timestamp = strtotime($bormeDate);
            if ($timestamp !== false) {
                $daysSince = (time() - $timestamp) / 86400;
                if ($daysSince <= 7) {
                    $score += 10;
                } elseif ($daysSince <= 30) {
                    $score += 5;
                }
            }
        }

        return min(100, $score);
    }

    private function calculateQualityScore(array $company): int
    {
        $score = 0;
        $cif = strtoupper((string) ($company['cif'] ?? ''));

        if ($cif !== '' && $cif[0] === 'B') {
            $score += 20;
        }

        if (!empty($company['cnae']) || !empty($company['cnae_label'])) {
            $score += 20;
        }

        if (!empty($company['province']) || !empty($company['municipality'])) {
            $score += 15;
        }

        if (mb_strlen((string) ($company['corporate_purpose'] ?? ''), 'UTF-8') > 50) {
            $score += 20;
        }

        $capital = $this->parseMoney((string) ($company['capital_social_raw'] ?? ''));
        if ($capital >= 50000) {
            $score += 25;
        } elseif ($capital >= 10000) {
            $score += 15;
        } elseif ($capital >= 3000) {
            $score += 10;
        }

        return min(100, $score);
    }

    private function calculateContactScore(array $company): int
    {
        $score = 0;

        if (!empty($company['phone']) || !empty($company['phone_mobile']) || !empty($company['phone_enriched']) || !empty($company['phone_mobile_enriched'])) {
            $score += 50;
        }

        if (!empty($company['email'])) {
            $score += 20;
        }

        if (!empty($company['website_official'])) {
            $score += 20;
        }

        if (!empty($company['address'])) {
            $score += 10;
        }

        return min(100, $score);
    }

    private function parseMoney(string $value): float
    {
        $value = trim($value);
        if ($value === '' || $value === '-') {
            return 0.0;
        }

        $value = preg_replace('/[^\d,.]/', '', $value) ?? '';
        if ($value === '') {
            return 0.0;
        }

        if (strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $parts = explode('.', $value);
            if (count($parts) > 2) {
                $value = str_replace('.', '', $value);
            }
        }

        return (float) $value;
    }

    private function isActType(?string $actType, array $needles): bool
    {
        $normalized = $this->normalizeText((string) $actType);
        foreach ($needles as $needle) {
            if (strpos($normalized, $this->normalizeText($needle)) !== false) {
                return true;
            }
        }

        return false;
    }

    private function normalizeText(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($ascii !== false) {
            $value = $ascii;
        }

        $value = preg_replace('/[^a-z0-9\s\/-]/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }

    private function normalizeReasons($reasons): array
    {
        if (is_array($reasons)) {
            return array_values(array_filter($reasons));
        }

        $reasons = trim((string) $reasons);
        if ($reasons === '') {
            return [];
        }

        $decoded = json_decode($reasons, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded));
        }

        return [$reasons];
    }
}
