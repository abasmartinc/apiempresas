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
        $data = [
            'id' => 999999,
            'cif' => 'A15075062',
            'name' => 'INDUSTRIA DE DISENO TEXTIL SA',
            'commercial_name' => 'INDITEX',
            'address' => 'AVENIDA DE LA DIPUTACION (ED INDITEX), S/N',
            'city' => 'ARTEIXO',
            'province' => 'A CORUÑA',
            'zipcode' => '15143',
            'cnae' => '4642 - Comercio al por mayor de prendas de vestir y calzado',
            'cnae_code' => '4642',
            'legal_form' => 'Sociedad Anonima',
            'status' => 'Activa',
            'constitution_date' => '1985-06-12',
            'phone' => '981185400',
            'email' => 'info@inditex.com',
            'website' => 'www.inditex.com',
            'latest_revenue' => '20000000000',
            'latest_employees' => '160000'
        ];

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

        $mockInditex = [
            'id' => 999999,
            'cif' => 'A15075062',
            'name' => 'INDUSTRIA DE DISENO TEXTIL SA',
            'province' => 'A CORUÑA',
            'status' => 'Activa'
        ];

        if ($multiple) {
            return $this->respond([
                'success' => true,
                'data' => [$mockInditex, ['id' => 888888, 'cif' => 'B00000001', 'name' => 'INDITEX LOGISTICA SA', 'province' => 'A CORUÑA', 'status' => 'Activa']],
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
}
