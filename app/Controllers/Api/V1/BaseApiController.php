<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;

class BaseApiController extends ResourceController
{
    /**
     * Sobrescribimos el método respond de ResourceController para inyectar
     * propiedades del estándar RFC 7807 en caso de errores (success = false),
     * manteniendo los campos legacy (success, error, message) intactos.
     */
    public function respond($data = null, int $statusCode = null, string $message = '')
    {
        if (is_array($data)) {
            // Detectar si la respuesta es un error
            $isError = false;
            if (isset($data['success']) && $data['success'] === false) {
                $isError = true;
            } elseif (isset($data['error']) && !isset($data['success'])) {
                $isError = true;
            }

            if ($isError) {
                // Determinar el código HTTP final
                $finalStatusCode = $statusCode ?? $this->response->getStatusCode();
                if ($finalStatusCode === 200) {
                    $finalStatusCode = 400; // Fail-safe si olvidan el código de error HTTP
                }

                $errorCodeStr = (is_string($data['error'] ?? null)) ? $data['error'] : 'UNKNOWN_ERROR';
                $errorMessage = (is_string($data['message'] ?? null)) ? $data['message'] : 'Se ha producido un error no identificado.';

                // Inyectar campos RFC 7807 solo si no existen previamente para evitar sobrescrituras accidentales
                if (!isset($data['type'])) {
                    $data['type'] = 'https://apiempresas.es/docs/errors/' . strtolower($errorCodeStr);
                }
                if (!isset($data['title'])) {
                    $data['title'] = $errorCodeStr;
                }
                if (!isset($data['status'])) {
                    $data['status'] = $finalStatusCode;
                }
                if (!isset($data['detail'])) {
                    $data['detail'] = $errorMessage;
                }
                // "code": el mismo identificador en todos los errores (también en los
                // de autenticación y cupo). Campo nuevo: no cambia ninguno existente.
                if (!isset($data['code'])) {
                    $data['code'] = $errorCodeStr;
                }
                if (!isset($data['instance'])) {
                    $reqId = \App\Filters\ApiKeyFilter::$apiRequestId ?? 'req_' . bin2hex(random_bytes(4));
                    if ($reqId === '') $reqId = 'req_' . bin2hex(random_bytes(4));
                    $data['instance'] = $reqId;
                }

                // 403 de plan: lo mismo en todos los endpoints (solo se añade). Unos
                // traían checkout_url y otros upgrade_url, y ninguno decía de forma
                // legible por código qué plan hace falta.
                if ($finalStatusCode === 403 && isset($data['upsell_opportunities']) && is_array($data['upsell_opportunities'])) {
                    $uo  = $data['upsell_opportunities'];
                    $url = null;
                    foreach (['checkout_url', 'upgrade_url'] as $k) {
                        if (!empty($uo[$k]) && is_string($uo[$k]) && preg_match('/[?&]plan=(pro|business)\b/', $uo[$k])) {
                            $url = $uo[$k];
                            break;
                        }
                    }
                    if ($url !== null) {
                        if (empty($uo['checkout_url'])) {
                            $data['upsell_opportunities']['checkout_url'] = $url;
                        }
                        if (empty($uo['upgrade_url'])) {
                            $data['upsell_opportunities']['upgrade_url'] = $url;
                        }
                        if (!isset($data['required_plan'])) {
                            preg_match('/[?&]plan=(pro|business)\b/', $url, $mPlan);
                            $data['required_plan'] = $mPlan[1];
                        }
                    }
                    if (!isset($data['current_plan'])) {
                        $data['current_plan'] = (string) (\App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free');
                    }
                }
            } elseif (($data['success'] ?? null) === true && !isset($data['notice'])) {
                // Aviso de cupo desde el 80 % en todas las respuestas que se cobran del
                // plan (antes solo en /companies). Campo nuevo de primer nivel.
                try {
                    $aviso = self::avisoCupo();
                    if ($aviso !== null) {
                        $data['notice'] = $aviso;
                    }
                } catch (\Throwable $e) {
                    // El aviso nunca debe romper una respuesta correcta
                }
            }
        }

        return parent::respond($data, $statusCode, $message);
    }

    /**
     * Enlace de compra con plan y origen (campo nuevo `checkout_url` de los ganchos).
     * El origen llega al checkout y a checkout_completed: así se sabe qué gancho vende.
     */
    protected static function checkoutUrl(string $plan, string $source): string
    {
        return \App\Filters\ApiKeyFilter::urlGancho($plan, $source);
    }

    /**
     * Respuesta cuando se agota el tope diario de vistas previas que no se cobran
     * (ApiCompanyEnricher::TEASERS_POR_DIA, compartido con los ganchos de los 403).
     * Sin tope, /score en Free o /insights en Pro servían para sacar gratis ese dato
     * de miles de empresas.
     */
    protected function topeVistasPrevias(string $plan, string $source)
    {
        $msg = 'Has alcanzado el límite diario de vistas previas gratuitas de este endpoint. Vuelve mañana o pasa al plan '
            . ucfirst($plan) . ' para usarlo sin este límite.';
        $this->response->setHeader('Retry-After', (string) max(60, strtotime('tomorrow') - time()));
        return $this->respond([
            'success' => false,
            'error'   => 'PREVIEW_LIMIT_EXCEEDED',
            'message' => $msg,
            'required_plan' => $plan,
            'upsell_opportunities' => [
                'checkout_url' => self::checkoutUrl($plan, $source),
            ],
        ], 429);
    }

    /** Plan al que subir desde el actual: Free → pro, Pro → business, resto → null. */
    protected static function planSiguiente(): ?string
    {
        $planId = (int) (\App\Filters\ApiKeyFilter::$apiMeta['plan_id'] ?? 1);
        return $planId === 1 ? 'pro' : ($planId === 2 ? 'business' : null);
    }

    /**
     * Cupo que queda DESPUÉS de esta petición (si se cobra del plan) y el total.
     * null si la petición no cuenta para el cupo (sandbox, monitor, endpoints gratis)
     * o si paga el monedero.
     *
     * @return array{restantes:int, total:int}|null
     */
    protected static function cupoTrasPeticion(): ?array
    {
        $meta = \App\Filters\ApiKeyFilter::$apiMeta;
        if (empty($meta) || \App\Filters\ApiKeyFilter::$apiSkipBilling || (int) ($meta['sub_cost'] ?? 0) <= 0) {
            return null;
        }
        $total = (int) ($meta['quota_limit'] ?? 0);
        if ($total <= 0) {
            return null;
        }
        $restantes = max(0, (int) ($meta['quota_remaining'] ?? 0) - (int) $meta['sub_cost']);

        return ['restantes' => $restantes, 'total' => $total];
    }

    /**
     * Aviso de cupo dentro de la respuesta (campo nuevo `notice`, de primer nivel),
     * desde el 80 % gastado. Antes solo iba en cabeceras: quien integra por código se
     * enteraba con el 429, con su integración ya parada. null por debajo del 80 %.
     */
    protected static function avisoCupo(): ?array
    {
        $cupo = self::cupoTrasPeticion();
        if ($cupo === null || $cupo['restantes'] > 0.2 * $cupo['total']) {
            return null;
        }
        $planId = (int) (\App\Filters\ApiKeyFilter::$apiMeta['plan_id'] ?? 1);
        $n      = static fn (int $x) => number_format($x, 0, ',', '.');
        $free   = $planId === 1;
        $reset  = $free ? null : strtotime('first day of next month 00:00:00');
        $source = $free ? 'api_notice_free_80' : 'api_notice_paid_80';

        $mensaje = $free
            ? 'Te quedan ' . $n($cupo['restantes']) . ' de tus ' . $n($cupo['total']) . ' consultas gratuitas, y no se renuevan. Con Pro tienes 3.000 al mes y los datos completos.'
            : 'Te quedan ' . $n($cupo['restantes']) . ' de las ' . $n($cupo['total']) . ' consultas de este mes. Se renuevan el ' . date('d/m/Y', $reset) . '.';

        return [
            'type'            => 'quota_warning',
            'message'         => $mensaje,
            'quota_remaining' => $cupo['restantes'],
            'quota_limit'     => $cupo['total'],
            'quota_resets_at' => $reset !== null ? date('c', $reset) : null,
        ] + \App\Filters\ApiKeyFilter::enlacesCompra($planId, $source);
    }

    /**
     * Lo que Business añadiría sobre ESTA empresa, para un cliente Pro (campo nuevo
     * `business_preview`). Un Pro no veía en ninguna respuesta que Business existe, y
     * los endpoints de Business casi no se usan. Solo recuentos (nada del contenido de
     * pago), con el mismo tope diario que los ganchos de los 403 porque no se cobran
     * aparte. null si no es Pro, si se ha llegado al tope o si no hay nada que enseñar.
     */
    protected static function businessPreview(string $cif, string $source): ?array
    {
        $meta = \App\Filters\ApiKeyFilter::$apiMeta;
        if ((int) ($meta['plan_id'] ?? 0) !== 2 || $cif === '') {
            return null;
        }
        $uid = (int) ($meta['user_id'] ?? 0);
        if ($uid === \App\Filters\ApiKeyFilter::MONITOR_USER_ID
            || !\App\Services\ApiCompanyEnricher::teaserAllowed($uid)) {
            return null;
        }

        $clave = 'api_bizprev_' . md5(strtoupper($cif));
        $datos = cache()->get($clave);
        if (!is_array($datos)) {
            $contratos = \App\Services\ApiCompanyEnricher::contractsSummary($cif);
            $datos = [
                'contratos'   => (int) ($contratos['total_contracts'] ?? 0),
                'importe'     => (float) ($contratos['total_amount'] ?? 0),
                'con_riesgo'  => \App\Services\ApiCompanyEnricher::riskLevel($cif) !== null,
            ];
            cache()->save($clave, $datos, 43200);
        }
        if ($datos['contratos'] === 0 && !$datos['con_riesgo']) {
            return null;
        }

        $partes = [];
        if ($datos['contratos'] > 0) {
            $partes[] = $datos['contratos'] . ' ' . ($datos['contratos'] === 1 ? 'contrato público' : 'contratos públicos')
                . ' por ' . number_format($datos['importe'], 0, ',', '.') . ' €';
        }
        if ($datos['con_riesgo']) {
            $partes[] = 'su perfil de riesgo';
        }

        return [
            'contratos_publicos'       => $datos['contratos'],
            'importe_contratos'        => round($datos['importe'], 2),
            'perfil_riesgo_disponible' => $datos['con_riesgo'],
            'mensaje'                  => 'Con Business verías ' . implode(' y ', $partes) . ' de esta empresa.',
            'checkout_url'             => self::checkoutUrl('business', $source),
        ];
    }

    /**
     * API Key de la petición: cabecera X-API-KEY o, si no viene, Authorization: Bearer.
     * Es lo mismo que acepta ApiKeyFilter; algunos controladores solo miraban X-API-KEY.
     */
    protected function apiKeyFromRequest(): string
    {
        $apiKey = trim((string) $this->request->getHeaderLine('X-API-KEY'));
        if ($apiKey === '') {
            $auth = trim((string) $this->request->getHeaderLine('Authorization'));
            if (stripos($auth, 'Bearer ') === 0) {
                $apiKey = trim(substr($auth, 7));
            }
        }

        return $apiKey;
    }
}
