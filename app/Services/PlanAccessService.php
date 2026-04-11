<?php

namespace App\Services;

class PlanAccessService
{
    /**
     * Permisos por plan (slug)
     */
    protected array $matrix = [
        'free' => [
            'company_score'   => 'basic',     // Enmascarado
            'company_signals' => false,
            'radar'          => 'limited',   // 10 resultados, enmascarado
            'insights'       => false,
            'contact_prep'    => false,
            'webhooks'       => false,
        ],
        'pro' => [
            'company_score'   => 'full',
            'company_signals' => true,
            'radar'          => 'pro',       // 100 resultados, sin enmascarar
            'insights'       => 'preview',   // Solo etiquetas de sector/perfil, sin pitch
            'contact_prep'    => false,
            'webhooks'       => false,
        ],
        'business' => [
            'company_score'   => 'full',
            'company_signals' => true,
            'radar'          => 'unlimited', // Sin límites, sin enmascarar
            'insights'       => 'full',      // Análisis IA completo
            'contact_prep'    => true,       // Mensajes IA sugeridos
            'webhooks'       => true,
        ],
    ];

    /**
     * Verifica si un plan tiene acceso a una característica.
     */
    public function canAccess(string $planSlug, string $feature): bool
    {
        $planSlug = strtolower($planSlug);
        if (!isset($this->matrix[$planSlug])) {
            $planSlug = 'free'; // Default to free if unknown
        }

        $access = $this->matrix[$planSlug][$feature] ?? false;
        
        return $access !== false && $access !== 'none';
    }

    /**
     * Obtiene el nivel de acceso (string) para una característica.
     */
    public function getAccessLevel(string $planSlug, string $feature): string
    {
        $planSlug = strtolower($planSlug);
        if (!isset($this->matrix[$planSlug])) {
            $planSlug = 'free';
        }

        $access = $this->matrix[$planSlug][$feature] ?? 'none';
        
        if (is_bool($access)) {
            return $access ? 'enabled' : 'none';
        }

        return (string) $access;
    }

    /**
     * Obtiene los límites de resultados para el radar.
     */
    public function getRadarLimit(string $planSlug): int
    {
        $level = $this->getAccessLevel($planSlug, 'radar');
        
        switch ($level) {
            case 'limited':   return 10;
            case 'pro':       return 100;
            case 'unlimited': return 1000; // Limitamos a 1000 por performance de API
            default:          return 0;
        }
    }
}
