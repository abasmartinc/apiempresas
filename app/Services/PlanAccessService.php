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

    /**
     * Valida el acceso de una API Key basándose en el plan requerido.
     *
     * @param string $apiKey API key del usuario
     * @param string $requirement Tipo de requerimiento ('pro_and_business' o 'business_only')
     * @return array Array asociativo con 'success', 'message' y 'user_id'
     */
    public function validateAccess(string $apiKey, string $requirement): array
    {
        $meta = \App\Filters\ApiKeyFilter::$apiMeta;
        
        $userId = null;
        $planSlug = 'free';
        
        if (!empty($meta) && isset($meta['plan_slug']) && isset($meta['user_id'])) {
            $userId = (int)$meta['user_id'];
            $planSlug = $meta['plan_slug'];
        } else {
            // Fallback: buscar directamente en BD si el filtro no se ha ejecutado
            try {
                $db = \Config\Database::connect('default');
                $row = $db->table('api_keys')
                    ->select('api_keys.user_id, api_keys.is_active, users.is_active as user_active')
                    ->join('users', 'users.id = api_keys.user_id', 'left')
                    ->where('api_keys.api_key', $apiKey)
                    ->get()
                    ->getRow();
                
                if (!$row) {
                    return [
                        'success' => false,
                        'message' => 'API key inválida',
                        'user_id' => null
                    ];
                }
                
                if ((int)$row->is_active !== 1 || (int)$row->user_active !== 1) {
                    return [
                        'success' => false,
                        'message' => 'API key inactiva o usuario inactivo',
                        'user_id' => (int)$row->user_id
                    ];
                }
                
                $userId = (int)$row->user_id;
                
                if ($db->tableExists('user_subscriptions')) {
                    $sub = $db->table('user_subscriptions')
                        ->select('api_plans.slug as plan_slug')
                        ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
                        ->where('user_subscriptions.user_id', $userId)
                        ->where('user_subscriptions.status', 'active')
                        ->groupStart()
                            ->where('api_plans.product_type', 'api')
                            ->orWhere('api_plans.product_type', 'bundle')
                        ->groupEnd()
                        ->orderBy('user_subscriptions.id', 'DESC')
                        ->get()
                        ->getRow();
                    
                    if ($sub) {
                        $planSlug = $sub->plan_slug;
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', '[PlanAccessService::validateAccess] ' . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Error interno al validar acceso',
                    'user_id' => null
                ];
            }
        }
        
        $planSlug = strtolower($planSlug);
        $allowed = false;
        
        if ($requirement === 'pro_and_business') {
            if (in_array($planSlug, ['pro', 'business', 'enterprise'])) {
                $allowed = true;
            }
        } elseif ($requirement === 'business_only') {
            if (in_array($planSlug, ['business', 'enterprise'])) {
                $allowed = true;
            }
        } else {
            if ($planSlug === 'enterprise' || $planSlug === strtolower($requirement)) {
                $allowed = true;
            }
        }
        
        if (!$allowed) {
            $msg = "Tu plan ({$planSlug}) no tiene acceso a esta función.";
            if ($requirement === 'pro_and_business') {
                $msg = "Esta característica requiere un plan Professional o Business.";
            } elseif ($requirement === 'business_only') {
                $msg = "Esta característica es exclusiva del plan Business.";
            }
            return [
                'success' => false,
                'message' => $msg,
                'user_id' => $userId
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Acceso concedido',
            'user_id' => $userId
        ];
    }

    /**
     * Registra el uso de una API Key para un usuario.
     * Nota: ApiKeyFilter ya registra el uso diario al final del ciclo de vida del request.
     *
     * @param int|null $userId ID del usuario
     * @return void
     */
    public function recordUsage(?int $userId): void
    {
        // No hacemos nada para evitar duplicar el conteo de la petición,
        // ya que ApiKeyFilter::after() se ejecuta automáticamente e incrementa
        // la tabla api_usage_daily para todas las peticiones con filtro 'apikey'.
    }
}
