<?php

namespace App\Services;

use App\Models\CompanyRadarScoreModel;

class CompanyScoringService
{
    protected CompanyRadarScoreModel $scoreModel;

    public function __construct()
    {
        $this->scoreModel = new CompanyRadarScoreModel();
    }

    /**
     * Obtiene los datos de score y señales para un CIF
     */
    public function getScoreData(string $cif): ?array
    {
        $data = $this->scoreModel->getByCif($cif);
        
        if (!$data) {
            return null;
        }

        return [
            'score' => (int) $data['score_total'],
            'priority' => $data['priority_level'],
            'reasons' => $data['score_reasons'],
            'last_signal' => [
                'type' => $data['main_act_type'],
                'date' => $data['last_borme_date'],
            ]
        ];
    }

    /**
     * Formatea señales comerciales adicionales
     */
    public function getSignals(string $cif): array
    {
        $data = $this->getScoreData($cif);
        
        if (!$data) return [];

        // Por ahora devolvemos la señal principal del BORME
        return [
            [
                'type' => 'borme_event',
                'label' => $data['last_signal']['type'],
                'date' => $data['last_signal']['date'],
                'probability' => $data['priority'],
            ]
        ];
    }
}
