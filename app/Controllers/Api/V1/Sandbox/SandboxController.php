<?php

namespace App\Controllers\Api\V1\Sandbox;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class SandboxController extends ResourceController
{
    use ResponseTrait;

    protected $format = 'json';

    /**
     * Valida si el CIF es un CIF mágico permitido en el Sandbox.
     * Devuelve el CIF limpiado o false si no es mágico.
     */
    private function validateMagicCif($cif)
    {
        $cif = strtoupper(trim((string)$cif));
        $cif = preg_replace('/[^A-Z0-9]/', '', $cif);

        $allowedCifs = ['A15075062', 'B00000000', 'C11111111'];
        if (!in_array($cif, $allowedCifs)) {
            return false;
        }
        return $cif;
    }

    private function getForbiddenResponse()
    {
        return $this->respond([
            'success' => false,
            'error' => 'TEST_MODE_RESTRICTION',
            'message' => 'Estás usando la API Key en modo Sandbox. Para buscar datos reales, utiliza tu Live API Key en la URL de producción. Los CIFs permitidos en pruebas son A15075062 (éxito), B00000000 (no encontrado), C11111111 (encolado).'
        ], ResponseInterface::HTTP_FORBIDDEN);
    }

    private function getMockInditex()
    {
        return [
            'name' => 'INDUSTRIA DE DISENO TEXTIL SA',
            'cif' => 'A15075062',
            'cnae' => '4642',
            'cnae_label' => 'Comercio al por mayor de prendas de vestir y calzado',
            'cnae_2025' => '4642',
            'cnae_2025_label' => 'Comercio al por mayor de prendas de vestir y calzado',
            'corporate_purpose' => 'COMERCIO AL POR MAYOR Y MENOR DE TODA CLASE DE PRENDAS DE VESTIR.',
            'founded' => '1985-06-12',
            'province' => 'A CORUÑA',
            'address' => 'AVENIDA DE LA DIPUTACION (ED INDITEX), S/N',
            'municipality' => 'ARTEIXO',
            'lat' => '43.317',
            'lng' => '-8.508',
            'status' => 'ACTIVA',
            'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ];
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies
    // =========================================================================
    public function companies()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'El parámetro "cif" es obligatorio.'], 400);
        }

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif === 'B00000000') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Empresa de prueba no encontrada.'], 404);
        }

        if ($cif === 'C11111111') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Empresa no encontrada en BD principal. Ha sido encolada automáticamente y estará disponible en los próximos minutos.'], 404);
        }

        // A15075062 (Inditex) - Mock Data
        $data = $this->getMockInditex();

        // Mapeo opcional de administradores
        if (filter_var($this->request->getGet('admin'), FILTER_VALIDATE_BOOLEAN)) {
            $data['administrators'] = [
                ['name' => 'MARTA ORTEGA PEREZ', 'position' => 'Presidente'],
                ['name' => 'OSCAR GARCIA MACEIRAS', 'position' => 'Consejero Delegado']
            ];
        }

        return $this->respond(['success' => true, 'data' => $data]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/search
    // =========================================================================
    public function search()
    {
        $q = trim((string) $this->request->getGet('name'));
        if ($q === '') $q = trim((string) $this->request->getGet('q'));

        if ($q === '') {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'El parámetro "q" es obligatorio.'], 400);
        }

        $multiple = filter_var($this->request->getGet('multiple'), FILTER_VALIDATE_BOOLEAN);

        $mockInditex = $this->getMockInditex();

        if ($multiple) {
            $mockInditex2 = $mockInditex;
            $mockInditex2['id'] = 888888;
            $mockInditex2['cif'] = 'B00000001';
            $mockInditex2['name'] = 'INDITEX LOGISTICA SA';

            return $this->respond([
                'success' => true,
                'data' => [$mockInditex, $mockInditex2],
                'meta' => ['total' => 2, 'page' => 1, 'limit' => 20]
            ]);
        }

        return $this->respond(['success' => true, 'data' => $mockInditex]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/score
    // =========================================================================
    public function score()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'CIF es requerido'], 400);

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Score no disponible.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'cif' => $cif,
                'score' => 98,
                'fuerza_financiera' => 'Excelente',
                'riesgo_impago' => 'Muy Bajo',
                'trayectoria' => 'Sólida',
                'mensaje' => 'La empresa presenta una solidez financiera sobresaliente.'
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/signals
    // =========================================================================
    public function signals()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'CIF es requerido'], 400);

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Señales no disponibles.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'cif' => $cif,
                'signals' => [
                    ['date' => '2025-01-15', 'type' => 'Nombramiento', 'description' => 'Nombramiento de nuevo Consejero Delegado publicado en BORME.'],
                    ['date' => '2024-11-20', 'type' => 'Ampliación de Capital', 'description' => 'Ampliación de capital social registrada.']
                ]
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/batch
    // =========================================================================
    public function batch()
    {
        $input = $this->request->getJSON();
        if (!$input || empty($input->cifs) || !is_array($input->cifs)) {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'El cuerpo de la petición debe ser un JSON válido que contenga un array "cifs".'], 400);
        }

        $foundData = [];
        $cost = 0;
        foreach ($input->cifs as $rawCif) {
            $cif = $this->validateMagicCif($rawCif);
            if ($cif === 'A15075062') {
                $foundData[] = $this->getMockInditex();
                $cost++;
            }
        }

        return $this->respond([
            'success' => true,
            'data' => $foundData,
            'meta' => [
                'requested' => count($input->cifs),
                'found' => count($foundData),
                'cost' => $cost, // En el sandbox no se resta saldo real, es una simulación.
                'truncated' => false
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/insights
    // =========================================================================
    public function insights()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'CIF es requerido'], 400);

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Insights no disponibles.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'profile' => 'Retail / Fast Fashion',
                'summary' => 'Empresa líder mundial en la fabricación y distribución textil.',
                'needs' => ['Optimización logística', 'Sostenibilidad', 'Digitalización B2B'],
                'conversion_probability' => 'Alta',
                'estimated_ticket' => 'Muy Alto'
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/contact-prep
    // =========================================================================
    public function contactPrep()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'CIF es requerido'], 400);

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Datos no disponibles.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'sales_approach' => 'Enfoque altamente consultivo y corporativo.',
                'suggested_message' => 'Hola, conociendo el volumen de operaciones logísticas de Inditex, nuestra solución aporta...',
                'likely_objection' => 'Ya trabajamos con grandes consultoras internacionales.',
                'attack_angle' => 'Agilidad y especialización de nicho con menores costes de implantación.'
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/match
    // =========================================================================
    public function match()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'CIF es requerido'], 400);

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Match no disponible.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'match_score' => 85,
                'fit_level' => 'Alto',
                'pain_points_addressed' => ['Ineficiencia operativa', 'Gestión de cadena de suministro'],
                'sales_argument' => 'Nuestro software elimina el trabajo manual...',
                'recommendation' => 'Contactar de inmediato.'
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/network
    // =========================================================================
    public function network()
    {
        $cifRaw = $this->request->getGet('cif');
        if (!$cifRaw) return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => 'CIF es requerido'], 400);

        $cif = $this->validateMagicCif($cifRaw);
        if (!$cif) return $this->getForbiddenResponse();

        if ($cif !== 'A15075062') {
            return $this->respond(['success' => false, 'error' => 'COMPANY_NOT_FOUND', 'message' => 'Red no disponible.'], 404);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'nodes' => [
                    ['id' => 'C_123', 'type' => 'company', 'label' => 'INDUSTRIA DE DISENO TEXTIL SA', 'cif' => 'A15075062', 'root' => true],
                    ['id' => 'A_abc', 'type' => 'administrator', 'label' => 'MARTA ORTEGA PEREZ']
                ],
                'edges' => [
                    ['source' => 'A_abc', 'target' => 'C_123', 'label' => 'Presidente']
                ],
                'stats' => [
                    'total_administrators' => 1,
                    'total_linked_companies' => 1
                ]
            ]
        ]);
    }

    // =========================================================================
    // ENDPOINT: /api/sandbox/v1/companies/radar
    // =========================================================================
    public function radar()
    {
        return $this->respond([
            'success' => true,
            'meta' => [
                'plan' => 'business',
                'count' => 1,
                'limit' => 1000,
                'total_disponibles' => 1
            ],
            'data' => [
                [
                    'cif' => 'A15075062',
                    'company_name' => 'INDUSTRIA DE DISENO TEXTIL SA',
                    'registro_mercantil' => 'A CORUÑA',
                    'fecha_constitucion' => '1985-06-12'
                ]
            ]
        ]);
    }
}
