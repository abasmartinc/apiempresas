<?php

namespace App\Libraries;

/**
 * Lo que incluye cada plan de pago de la API, en un solo sitio.
 *
 * Lo usan el panel (tarjeta "Lo que incluye tu plan"), el correo de bienvenida al
 * pagar y el aviso antes de la renovación. Existe porque 18 de 29 clientes Pro no
 * pasan de 500 consultas al mes y casi todo lo que usan es /companies: las funciones
 * por las que pagan (administradores, verify, vigilancia, reconcile...) no las
 * descubren, y son los que pagan un mes y se van.
 *
 * Solo aparece lo que el plan da de verdad (PlanAccessService y los controladores).
 */
class PlanIncluye
{
    public const CIF_EJEMPLO = 'A15075062';

    /**
     * Funciones del plan. Cada una: clave, título, para qué sirve, ejemplo de llamada,
     * patrón del endpoint en api_requests (null = no se puede saber si lo ha usado) y
     * ancla de la documentación.
     *
     * @return array<int, array{clave:string,titulo:string,detalle:string,ejemplo:string,patron:?string,doc:string}>
     */
    public static function items(int $planId, string $cif = self::CIF_EJEMPLO): array
    {
        $cif = preg_match('/^[A-Z0-9]{8,10}$/i', $cif) ? strtoupper($cif) : self::CIF_EJEMPLO;

        $pro = [
            [
                'clave'   => 'admin',
                'titulo'  => 'Administradores vigentes',
                'detalle' => 'Quién manda hoy en la empresa, con su cargo y la fecha de nombramiento.',
                'ejemplo' => 'GET /api/v1/companies?cif=' . $cif . '&admin=true',
                'patron'  => null,
                'doc'     => 'endpoint-by-cif',
            ],
            [
                'clave'   => 'verify',
                'titulo'  => 'Verificación KYB en una llamada',
                'detalle' => 'Si existe y está activa, si el nombre coincide, si el firmante es administrador y si el NIF-IVA está en VIES.',
                'ejemplo' => 'GET /api/v1/companies/verify?cif=' . $cif . '&name=...&person=...',
                'patron'  => '/api/v1/companies/verify',
                'doc'     => 'endpoint-verify',
            ],
            [
                'clave'   => 'watchlist',
                'titulo'  => 'Vigilancia de hasta 100 empresas',
                'detalle' => 'Te avisa de actos en el BORME, cambios de estado y de riesgo. Vigilar no gasta consultas.',
                'ejemplo' => 'POST /api/v1/watchlist  {"cifs": ["' . $cif . '"]}',
                'patron'  => '/api/v1/watchlist',
                'doc'     => 'endpoint-watchlist',
            ],
            [
                'clave'   => 'reconcile',
                'titulo'  => 'Nombre a CIF en lote',
                'detalle' => 'Completa tu base de clientes: hasta 100 nombres por petición; solo pagas las coincidencias.',
                'ejemplo' => 'POST /api/v1/companies/reconcile  {"names": ["..."]}',
                'patron'  => '/api/v1/companies/reconcile',
                'doc'     => 'endpoint-reconcile',
            ],
            [
                'clave'   => 'batch',
                'titulo'  => 'Consultas por lotes',
                'detalle' => 'Hasta 100 CIF en una sola petición.',
                'ejemplo' => 'POST /api/v1/companies/batch  {"cifs": ["' . $cif . '", "..."]}',
                'patron'  => '/api/v1/companies/batch',
                'doc'     => 'endpoint-expanded',
            ],
        ];

        if ($planId !== 3) {
            return $pro;
        }

        return array_merge($pro, [
            [
                'clave'   => 'risk',
                'titulo'  => 'Perfil de riesgo',
                'detalle' => 'Nivel de riesgo de la empresa y las señales que lo explican.',
                'ejemplo' => 'GET /api/v1/companies/risk-profile?cif=' . $cif,
                'patron'  => '/api/v1/companies/risk-profile',
                'doc'     => 'endpoint-risk-profile',
            ],
            [
                'clave'   => 'contracts',
                'titulo'  => 'Contratos públicos',
                'detalle' => 'Adjudicaciones de la empresa: número, importe y organismos.',
                'ejemplo' => 'GET /api/v1/companies/contracts?cif=' . $cif,
                'patron'  => '/api/v1/companies/contracts',
                'doc'     => 'endpoint-contracts',
            ],
            [
                'clave'   => 'filter',
                'titulo'  => 'Segmentos',
                'detalle' => 'Descarga empresas por sector, zona y tamaño (el recuento es gratis).',
                'ejemplo' => 'GET /api/v1/companies/filter?cnae=62&province=Madrid&count_only=true',
                'patron'  => '/api/v1/companies/filter',
                'doc'     => 'endpoint-filter',
            ],
            [
                'clave'   => 'webhooks',
                'titulo'  => 'Webhooks de tu vigilancia',
                'detalle' => 'Recibe en tu servidor los cambios de las empresas que vigilas, sin preguntar.',
                'ejemplo' => 'POST /api/v1/webhooks  {"url": "https://...", "event": "watchlist.*"}',
                'patron'  => '/api/v1/webhooks',
                'doc'     => 'endpoint-webhooks',
            ],
        ]);
    }

    /**
     * Claves de las funciones que el usuario ya ha usado desde una fecha
     * (cualquier respuesta que no sea 5xx: un 400 también es haberlo probado).
     *
     * @return array<string, true>
     */
    public static function usadas(int $userId, int $planId, string $desde): array
    {
        $usadas = [];
        try {
            $db = \Config\Database::connect();
            foreach (self::items($planId) as $it) {
                if ($it['patron'] === null) {
                    continue;
                }
                $n = $db->table('api_requests')
                    ->where('user_id', $userId)
                    ->where('created_at >=', $desde)
                    ->where('status_code <', 500)
                    ->like('endpoint', $it['patron'], 'both')
                    ->countAllResults();
                if ($n > 0) {
                    $usadas[$it['clave']] = true;
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[PlanIncluye] usadas: ' . $e->getMessage());
        }

        return $usadas;
    }

    /** Último CIF que el usuario consultó con éxito, para que los ejemplos sean suyos. */
    public static function ultimoCif(int $userId): string
    {
        try {
            $fila = \Config\Database::connect()->table('api_requests')
                ->select('search_term')
                ->where('user_id', $userId)
                ->where('status_code', 200)
                ->like('endpoint', '/api/v1/companies', 'before')
                ->where('search_term IS NOT NULL', null, false)
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()->getRowArray();
            $cif = strtoupper(trim((string) ($fila['search_term'] ?? '')));
            if (preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/', $cif)) {
                return $cif;
            }
        } catch (\Throwable $e) {
            log_message('error', '[PlanIncluye] ultimoCif: ' . $e->getMessage());
        }

        return self::CIF_EJEMPLO;
    }
}
