<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Services\PlanAccessService;
use App\Services\CompanyScoringService;
use App\Services\CompanyAiService;

class CompanyEnrichmentController extends ResourceController
{
    protected PlanAccessService    $planAccess;
    protected CompanyScoringService $scoringService;
    protected CompanyAiService     $aiService;

    public function __construct()
    {
        $this->planAccess     = new PlanAccessService();
        $this->scoringService = new CompanyScoringService();
        $this->aiService      = new CompanyAiService();
    }

    /**
     * GET /api/v1/companies/score?cif=B12345678
     */
    public function score()
    {
        $cif = $this->request->getGet('cif');
        if (!$cif) {
            return $this->fail('CIF es requerido', 400);
        }

        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';

        if (!$this->planAccess->canAccess($planSlug, 'company_score')) {
            return $this->failForbidden('Tu plan no tiene acceso al scoring comercial.');
        }

        $data = $this->scoringService->getScoreData($cif);
        if (!$data) {
            return $this->failNotFound('Empresa no encontrada o sin score calculado.');
        }

        // Si es Free, devolvemos un score "base" o enmascaramos detalles
        if ($this->planAccess->getAccessLevel($planSlug, 'company_score') === 'basic') {
            return $this->respond([
                'success' => true,
                'data' => [
                    'cif' => 'B********',
                    'score' => $data['score'],
                    'message' => 'Actualiza a Pro para ver el desglose y señales del BORME'
                ]
            ]);
        }

        return $this->respond([
            'success' => true,
            'data' => array_merge(['cif' => $cif], $data)
        ]);
    }

    /**
     * GET /api/v1/companies/signals?cif=B12345678
     */
    public function signals()
    {
        $cif = $this->request->getGet('cif');
        if (!$cif) return $this->fail('CIF es requerido', 400);

        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        if (!$this->planAccess->canAccess($planSlug, 'company_signals')) {
            return $this->failForbidden('Tu plan Pro o Business es requerido para ver señales societarias.');
        }

        $signals = $this->scoringService->getSignals($cif);
        
        return $this->respond([
            'success' => true,
            'data' => [
                'cif' => $cif,
                'signals' => $signals
            ]
        ]);
    }

    /**
     * GET /api/v1/companies/insights?cif=B12345678
     */
    public function insights()
    {
        $cif = $this->request->getGet('cif');
        if (!$cif) return $this->fail('CIF es requerido', 400);

        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        $accessLevel = $this->planAccess->getAccessLevel($planSlug, 'insights');

        if ($accessLevel === 'none') {
            return $this->failForbidden('El análisis IA requiere un plan Business.');
        }

        $insights = $this->aiService->getInsights($cif);
        if (!$insights) return $this->failNotFound('Sin análisis disponible para esta empresa.');

        if ($accessLevel === 'preview') {
            // Solo mostramos el perfil básico y probabilidad
            return $this->respond([
                'success' => true,
                'data' => [
                    'profile' => $insights['profile'],
                    'conversion_probability' => $insights['conversion_probability'],
                    'message' => 'Actualiza a Business para ver el análisis comercial completo y necesidades detectadas.'
                ]
            ]);
        }

        return $this->respond([
            'success' => true,
            'data' => $insights
        ]);
    }

    /**
     * GET /api/v1/companies/contact-prep?cif=B12345678
     */
    public function contactPrep()
    {
        $cif = $this->request->getGet('cif');
        if (!$cif) return $this->fail('CIF es requerido', 400);

        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        if (!$this->planAccess->canAccess($planSlug, 'contact_prep')) {
            return $this->failForbidden('La preparación de contacto con IA requiere un plan Business.');
        }

        $prep = $this->aiService->getContactPrep($cif);
        if (!$prep) return $this->failNotFound('Sin datos de contacto para esta empresa.');

        return $this->respond([
            'success' => true,
            'data' => $prep
        ]);
    }
}
