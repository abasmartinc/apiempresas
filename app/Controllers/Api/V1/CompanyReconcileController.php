<?php

namespace App\Controllers\Api\V1;

use App\Filters\ApiKeyFilter;
use App\Services\ApiCompanyEnricher;
use App\Services\ApiReconcileService as R;
use CodeIgniter\HTTP\ResponseInterface;
use OpenApi\Attributes as OA;

/**
 * POST /api/v1/companies/reconcile (29-09-2026). Nombre → CIF, en lote.
 * Pro y Business (y Free con saldo). 1 consulta por cada "match"; ambiguous y no_match no se cobran.
 */
class CompanyReconcileController extends BaseApiController
{
    protected $format = 'json';

    #[OA\Post(
        path: "/api/v1/companies/reconcile",
        summary: "Nombre a CIF (reconciliación)",
        description: "Envía hasta 100 nombres de empresa (con provincia opcional para desempatar) y recibe, para cada uno, el CIF con una puntuación de 0 a 100 y un veredicto: match, ambiguous (con hasta 3 candidatos) o no_match. **Coste:** 1 consulta por cada match; ambiguous y no_match no se cobran. Si no te llega el cupo, los nombres que quedan se devuelven como skipped_quota.",
        tags: ["2. Plan Pro"]
    )]
    #[OA\RequestBody(required: true, content: new OA\JsonContent(example: ["items" => [["name" => "Mercadona", "province" => "Valencia"], ["name" => "Telefonica de España"]]]))]
    #[OA\Response(response: 200, description: "Resultado por nombre")]
    #[OA\Response(response: 400, description: "Entrada no válida")]
    #[OA\Response(response: 403, description: "Plan Free sin saldo")]
    public function index()
    {
        $meta = ApiKeyFilter::$apiMeta;
        $planSlug = strtolower((string) ($meta['plan_slug'] ?? 'free'));
        $access = strtolower((string) ($meta['access_slug'] ?? $planSlug));
        $planId = (int) ($meta['plan_id'] ?? 1);
        $userId = (int) ($meta['user_id'] ?? 0);

        if (!in_array($access, ['pro', 'business', 'enterprise'], true)) {
            return $this->respond([
                'success' => false,
                'error'   => 'PLAN_RESTRICTION',
                'message' => 'La reconciliación de nombres requiere un plan Pro o Business.',
                'upsell_opportunities' => [
                    'mensaje'     => 'Pasa una lista de nombres y recibe el CIF de cada empresa, con una puntuación de confianza. Solo pagas los que encuentran coincidencia.',
                    'upgrade_url' => site_url('billing?plan=pro&source=api_403_reconcile'),
                ],
            ], ResponseInterface::HTTP_FORBIDDEN);
        }

        [$items, $err] = R::parseInput($this->request->getJSON(true));
        if ($err !== null) {
            return $this->respond(['success' => false, 'error' => 'VALIDATION_ERROR', 'message' => $err], ResponseInterface::HTTP_BAD_REQUEST);
        }

        $esMonitor = $userId === ApiKeyFilter::MONITOR_USER_ID;
        $budget = $esMonitor ? PHP_INT_MAX : max(0, (int) ($meta['quota_remaining'] ?? 0)) + max(0, (int) ($meta['wallet_balance'] ?? 0));
        $quota  = max(0, (int) ($meta['quota_remaining'] ?? 0));
        if ($planId === 1) {
            // Free con saldo: paga del monedero, no de las 100 gratuitas (igual que ApiKeyFilter).
            $quota = 0;
            $budget = $esMonitor ? PHP_INT_MAX : max(0, (int) ($meta['wallet_balance'] ?? 0));
        }

        $out = [];
        $counts = ['match' => 0, 'ambiguous' => 0, 'no_match' => 0, 'invalid' => 0, 'skipped_quota' => 0];
        $charged = 0;

        foreach ($items as $it) {
            $row = ['input' => $it];
            if (mb_strlen($it['name']) < 3) {
                $row['status'] = 'invalid';
                $row['message'] = 'El nombre debe tener al menos 3 caracteres.';
                $counts['invalid']++;
                $out[] = $row;
                continue;
            }
            if ($charged >= $budget) {
                $row['status'] = 'skipped_quota';
                $counts['skipped_quota']++;
                $out[] = $row;
                continue;
            }
            try {
                $cands = R::score($it['name'], R::candidates($it['name']));
            } catch (\Throwable $e) {
                log_message('error', '[CompanyReconcileController] ' . $e->getMessage());
                $cands = [];
            }
            $d = R::decide($cands, $it['province']);
            $row['status'] = $d['status'];
            $counts[$d['status']]++;

            $mostrar = $d['best'] ? [$d['best']] : $d['candidates'];
            if ($mostrar) {
                $mostrar = ApiCompanyEnricher::enrich($mostrar, true);
            }
            if ($d['status'] === 'match') {
                $row['score'] = (int) $d['best']['score'];
                $row['company'] = R::publicCandidate($mostrar[0]);
                $charged++;
            } elseif ($d['status'] === 'ambiguous') {
                $row['candidates'] = array_map([R::class, 'publicCandidate'], $mostrar);
            }
            $out[] = $row;
        }

        $cost = $esMonitor ? 0 : $charged;
        $sub = min($cost, $quota);
        ApiKeyFilter::$apiSkipBilling = $esMonitor || $cost === 0;
        ApiKeyFilter::$apiMeta['sub_cost'] = $sub;
        ApiKeyFilter::$apiMeta['wallet_cost'] = $cost - $sub;

        $body = [
            'success' => true,
            'data'    => $out,
            'meta'    => [
                'requested' => count($items),
                'matched'   => $counts['match'],
                'ambiguous' => $counts['ambiguous'],
                'no_match'  => $counts['no_match'],
                'invalid'   => $counts['invalid'],
                'skipped_quota' => $counts['skipped_quota'],
                'cost'      => $cost,
                'thresholds' => ['match' => R::MATCH_MIN, 'ambiguous' => R::AMBIGUOUS_MIN],
            ],
        ];
        if ($counts['skipped_quota'] > 0) {
            $body['message'] = 'Te has quedado sin consultas: ' . $counts['skipped_quota'] . ' nombres no se han procesado.';
            $body += ApiKeyFilter::enlacesCompra($planId, 'api_429_reconcile');
        }
        return $this->respond($body);
    }
}
