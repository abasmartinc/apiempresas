<?php

namespace App\Libraries;

class RadarMetricsService
{
    /**
     * Calcula métricas agregadas basadas en un conjunto de oportunidades
     */
    public function getMetrics($totalCount)
    {
        // 1. Pipeline Económico
        // Valores por defecto alineados con la lógica de las filas
        $minTicket = 5000;
        $maxTicket = 12000;

        // Si tenemos muchas empresas, el pipeline es masivo
        $pipelineMin = $totalCount * $minTicket;
        $pipelineMax = $totalCount * $maxTicket;

        // 2. Clientes Potenciales Reales
        // Estimamos una tasa de cierre conservadora entre el 8% y el 15% para empresas nuevas
        $clientsMin = max(1, floor($totalCount * 0.08));
        $clientsMax = max(2, floor($totalCount * 0.15));

        return [
            'total_opps' => $totalCount,
            'pipeline_min' => $pipelineMin,
            'pipeline_max' => $pipelineMax,
            'pipeline_label' => $this->formatCurrency($pipelineMin) . ' – ' . $this->formatCurrency($pipelineMax),
            'clients_min' => $clientsMin,
            'clients_max' => $clientsMax,
            'clients_label' => $clientsMin . ' – ' . $clientsMax,
            'timing_refuerzo' => 'Empresas con menos de 7 días',
            'roi_message' => 'Con 1 solo cliente cubres el coste mensual'
        ];
    }

    /**
     * Formatea valores monetarios abreviados para impacto visual
     */
    public function formatCurrency($value)
    {
        if ($value >= 1000000) {
            return number_format($value / 1000000, 1, ',', '.') . 'M€';
        }
        if ($value >= 1000) {
            return number_format($value / 1000, 0, ',', '.') . '.000€';
        }
        return number_format($value, 0, ',', '.') . '€';
    }
}
