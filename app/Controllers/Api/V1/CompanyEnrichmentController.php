<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Services\PlanAccessService;
use App\Services\CompanyScoringService;
use App\Services\CompanyAiService;
use OpenApi\Attributes as OA;

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
    #[OA\Get(
        path: "/api/v1/companies/score",
        summary: "Scoring Comercial",
        description: "Obtener la puntuación y desglose de scoring para una empresa. **Coste:** 1 llamada de tu cuota mensual (plan suscripción) o 3 créditos del monedero (bono prepago). Las respuestas con error (400, 404, 403, etc.) no consumen cuota ni créditos.",
        tags: ["2. Plan Professional"]
    )]
    #[OA\Parameter(
        name: "cif",
        in: "query",
        required: true,
        description: "CIF de la empresa",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Datos del scoring comercial",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
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
                    'fuerza_financiera' => '🔒 Actualiza a Pro',
                    'riesgo_impago' => '🔒 Actualiza a Pro',
                    'trayectoria' => '🔒 Actualiza a Pro',
                    'mensaje' => 'Actualiza a Pro para ver el desglose detallado y señales del BORME'
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
    #[OA\Get(
        path: "/api/v1/companies/signals",
        summary: "Señales Societarias",
        description: "Obtener eventos societarios relevantes (BORME) de una empresa. **Coste:** 1 llamada de tu cuota mensual (plan suscripción) o 3 créditos del monedero (bono prepago). Las respuestas con error (400, 404, 403, etc.) no consumen cuota ni créditos.",
        tags: ["2. Plan Professional"]
    )]
    #[OA\Parameter(
        name: "cif",
        in: "query",
        required: true,
        description: "CIF de la empresa",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Señales societarias de la empresa",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    public function signals()
    {
        $cif = $this->request->getGet('cif');
        if (!$cif) return $this->fail('CIF es requerido', 400);

        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        if (!$this->planAccess->canAccess($planSlug, 'company_signals')) {
            $signals = $this->scoringService->getSignals($cif);
            $count = is_array($signals) ? count($signals) : 0;
            return $this->respond([
                'success' => false,
                'message' => "Te estás perdiendo {$count} eventos societarios recientes (nombramientos, ampliaciones, etc.). Actualiza al plan Pro para acceder al historial completo y tomar mejores decisiones.",
                'upsell_opportunities' => [
                    'eventos_ocultos' => $count
                ]
            ], 403);
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
    #[OA\Get(
        path: "/api/v1/companies/insights",
        summary: "Análisis con Inteligencia Artificial",
        description: "Obtener un análisis profundo y perfilado de ventas mediante IA. **Coste:** 1 llamada de tu cuota mensual (plan suscripción) o 3 créditos del monedero (bono prepago). Las respuestas con error (400, 404, 403, etc.) no consumen cuota ni créditos.",
        tags: ["3. Plan Business"]
    )]
    #[OA\Parameter(
        name: "cif",
        in: "query",
        required: true,
        description: "CIF de la empresa",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Insights generados por IA",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    public function insights()
    {
        $cif = $this->request->getGet('cif');
        if (!$cif) return $this->fail('CIF es requerido', 400);

        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        $accessLevel = $this->planAccess->getAccessLevel($planSlug, 'insights');

        if ($accessLevel === 'none') {
            return $this->respond([
                'success' => false,
                'message' => 'El análisis IA requiere un plan Business.',
                'upsell_opportunities' => [
                    'pain_points' => '🔒 Desbloquea Business para ver los puntos de dolor',
                    'buyer_persona' => '🔒 Desbloquea Business para ver quién toma las decisiones',
                    'sales_arguments' => '🔒 Desbloquea Business para obtener argumentos de venta'
                ]
            ], 403);
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
                    'pain_points' => '🔒 Desbloquea Business para ver los puntos de dolor',
                    'buyer_persona' => '🔒 Desbloquea Business para ver quién toma las decisiones',
                    'sales_arguments' => '🔒 Desbloquea Business para obtener argumentos de venta',
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
    #[OA\Get(
        path: "/api/v1/companies/contact-prep",
        summary: "Preparación de Contacto",
        description: "Obtener tácticas recomendadas por la IA para contactar o vender a la empresa. **Coste:** 1 llamada de tu cuota mensual (plan suscripción) o 3 créditos del monedero (bono prepago). Las respuestas con error (400, 404, 403, etc.) no consumen cuota ni créditos.",
        tags: ["3. Plan Business"]
    )]
    #[OA\Parameter(
        name: "cif",
        in: "query",
        required: true,
        description: "CIF de la empresa",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Tácticas de preparación",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    public function contactPrep()
    {
        $cif = $this->request->getGet('cif');
        if (!$cif) return $this->fail('CIF es requerido', 400);

        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        if (!$this->planAccess->canAccess($planSlug, 'contact_prep')) {
            return $this->respond([
                'success' => false,
                'message' => 'La IA ha detectado que esta empresa es altamente receptiva. Pásate a Business para obtener los guiones de venta y preparación de contacto generados por IA.',
                'upsell_opportunities' => [
                    'tacticas' => '🔒 Exclusivo Business',
                    'guiones_email' => '🔒 Exclusivo Business',
                    'guiones_linkedin' => '🔒 Exclusivo Business'
                ]
            ], 403);
        }

        $prep = $this->aiService->getContactPrep($cif);
        if (!$prep) return $this->failNotFound('Sin datos de contacto para esta empresa.');

        return $this->respond([
            'success' => true,
            'data' => $prep
        ]);
    }
}
