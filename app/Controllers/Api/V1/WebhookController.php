<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Services\PlanAccessService;
use App\Services\WebhookService;
use OpenApi\Attributes as OA;

class WebhookController extends BaseApiController
{
    protected PlanAccessService $planAccess;
    protected WebhookService   $webhookService;

    public function __construct()
    {
        $this->planAccess     = new PlanAccessService();
        $this->webhookService = new WebhookService();
    }

    #[OA\Get(
        path: "/api/v1/webhooks",
        summary: "Listar Webhooks",
        description: "Obtener todos los webhooks configurados para recibir notificaciones (Requiere Plan Business). **Coste:** 0.",
        tags: ["3. Plan Business"]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de webhooks",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "data", type: "array", items: new OA\Items(type: "object"))
            ]
        )
    )]
    public function index()
    {
        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        if (!$this->planAccess->canAccess($planSlug, 'webhooks')) {
            return $this->fallo(403, 'PLAN_RESTRICTION', 'Los webhooks requieren un plan Business.', ['upsell_opportunities' => ['checkout_url' => self::checkoutUrl('business', 'api_403_webhooks')]]);
        }

        $userId = (int)\App\Filters\ApiKeyFilter::$apiMeta['user_id'];
        $list = $this->webhookService->list($userId);

        return $this->respond([
            'success' => true,
            'data' => $list
        ]);
    }

    #[OA\Post(
        path: "/api/v1/webhooks",
        summary: "Crear Webhook",
        description: "Registra una nueva URL para recibir eventos asíncronos. **Coste:** 0.",
        tags: ["3. Plan Business"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "url", type: "string", format: "uri"),
                new OA\Property(property: "event", type: "string")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Webhook creado",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string"),
                new OA\Property(property: "id", type: "integer")
            ]
        )
    )]
    public function create()
    {
        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        if (!$this->planAccess->canAccess($planSlug, 'webhooks')) {
            return $this->fallo(403, 'PLAN_RESTRICTION', 'Los webhooks requieren un plan Business.', ['upsell_opportunities' => ['checkout_url' => self::checkoutUrl('business', 'api_403_webhooks')]]);
        }

        $rules = [
            'url'   => 'required|valid_url',
            'event' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->fallo(400, 'VALIDATION_ERROR', $this->validator->getErrors());
        }

        // Solo HTTPS a hosts públicos: impide registrar URLs internas (localhost,
        // IPs privadas, metadatos del servidor) a las que luego llamaría nuestro
        // servidor. Mismo formato que el error de validación de arriba.
        $json = $this->request->getJSON(true);
        $url  = (string) ((is_array($json) ? ($json['url'] ?? null) : null) ?? $this->request->getVar('url') ?? '');
        if (!\App\Services\SafeUrlValidator::isSafeUrl($url)) {
            return $this->fallo(400, 'VALIDATION_ERROR', ['url' => 'La URL debe ser HTTPS (puerto 443) y apuntar a un servidor público.']);
        }

        // Evento: solo los que existen (28-09-2026). Antes se aceptaba cualquier texto y
        // el webhook no recibía nunca nada.
        $event = (string) ((is_array($json) ? ($json['event'] ?? null) : null) ?? $this->request->getVar('event') ?? '');
        if (!\App\Services\WebhookEvents::isValid($event)) {
            return $this->respond([
                'success' => false,
                'error'   => 'INVALID_EVENT',
                'message' => 'Evento no válido. Usa uno de: ' . implode(', ', \App\Services\WebhookEvents::all()) . '.',
            ], 400);
        }
        $data = is_array($json) ? $json : [];
        $data['url'] = $url;
        $data['event'] = \App\Services\WebhookEvents::normalize($event);
        if (empty($data['secret']) || !is_string($data['secret'])) {
            $data['secret'] = bin2hex(random_bytes(16));
        }

        $userId = (int)\App\Filters\ApiKeyFilter::$apiMeta['user_id'];
        $id = $this->webhookService->create($userId, $data);

        return $this->respondCreated([
            'success' => true,
            'message' => 'Webhook creado correctamente',
            'id' => $id,
            // Campos añadidos: con el secreto se comprueba la firma X-ApiEmpresas-Signature.
            'event'  => $data['event'],
            'secret' => $data['secret'],
        ]);
    }

    #[OA\Delete(
        path: "/api/v1/webhooks/{id}",
        summary: "Eliminar Webhook",
        description: "Elimina un webhook previamente configurado. **Coste:** 0.",
        tags: ["3. Plan Business"]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID del webhook a eliminar",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Webhook eliminado",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "success", type: "boolean", example: true),
                new OA\Property(property: "message", type: "string")
            ]
        )
    )]
    public function delete($id = null)
    {
        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        if (!$this->planAccess->canAccess($planSlug, 'webhooks')) {
            return $this->fallo(403, 'PLAN_RESTRICTION', 'Los webhooks requieren un plan Business.', ['upsell_opportunities' => ['checkout_url' => self::checkoutUrl('business', 'api_403_webhooks')]]);
        }

        $userId = (int)\App\Filters\ApiKeyFilter::$apiMeta['user_id'];
        if ($this->webhookService->delete($userId, (int)$id)) {
            return $this->respondDeleted(['success' => true, 'message' => 'Webhook eliminado']);
        }

        return $this->fallo(404, 'WEBHOOK_NOT_FOUND', 'Webhook no encontrado.');
    }

    /**
     * POST /api/v1/webhooks/{id}/test (28-09-2026): envía un test.ping a la URL del
     * webhook en el momento y devuelve lo que respondió.
     */
    public function test($id = null)
    {
        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        if (!$this->planAccess->canAccess($planSlug, 'webhooks')) {
            return $this->fallo(403, 'PLAN_RESTRICTION', 'Los webhooks requieren un plan Business.', ['upsell_opportunities' => ['checkout_url' => self::checkoutUrl('business', 'api_403_webhooks')]]);
        }
        $userId = (int)\App\Filters\ApiKeyFilter::$apiMeta['user_id'];
        $hook = \Config\Database::connect()->table('api_webhooks')
            ->where('user_id', $userId)->where('id', (int) $id)->get()->getRowArray();
        if (!$hook) {
            return $this->fallo(404, 'WEBHOOK_NOT_FOUND', 'Webhook no encontrado.');
        }
        $r = \App\Services\WebhookDispatcher::testPing($hook);
        // Si falla, code propio (antes salía UNKNOWN_ERROR): el fallo es del servidor del
        // cliente, no de la API (13-10-2026)
        $fallo = $r['ok'] ? [] : [
            'error'   => 'WEBHOOK_DELIVERY_FAILED',
            'message' => 'Tu servidor no ha aceptado el envío de prueba'
                . ($r['status'] ? ' (respondió ' . (int) $r['status'] . ')' : '')
                . ($r['error'] ? ': ' . $r['error'] : '.'),
        ];
        return $this->respond($fallo + [
            'success' => $r['ok'],
            'data'    => [
                'delivered'   => $r['ok'],
                'http_status' => $r['status'],
                'duration_ms' => $r['ms'],
                'error'       => $r['error'],
                'delivery_id' => $r['delivery_id'],
            ],
        ], $r['ok'] ? 200 : 502);
    }

    /**
     * Historial de entregas de un webhook (13-10-2026). Ruta nueva: las demás no cambian.
     */
    #[OA\Get(
        path: "/api/v1/webhooks/{id}/deliveries",
        summary: "Historial de entregas de un webhook",
        description: "Últimos envíos del webhook, del más reciente al más antiguo: estado, intentos, código HTTP de tu servidor, duración y error. Requiere Plan Business. **Coste:** 0.",
        tags: ["3. Plan Business"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, description: "ID del webhook", schema: new OA\Schema(type: "integer"))]
    #[OA\Parameter(name: "status", in: "query", required: false, description: "Filtra por estado: pending, processing, retry, delivered o dead.", schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "page", in: "query", required: false, description: "Página (>= 1).", schema: new OA\Schema(type: "integer", default: 1))]
    #[OA\Parameter(name: "limit", in: "query", required: false, description: "Entregas por página (1-100). Por defecto, 50.", schema: new OA\Schema(type: "integer", default: 50))]
    #[OA\Response(response: 200, description: "Entregas del webhook")]
    #[OA\Response(response: 404, description: "Webhook no encontrado")]
    public function deliveries($id = null)
    {
        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        if (!$this->planAccess->canAccess($planSlug, 'webhooks')) {
            return $this->fallo(403, 'PLAN_RESTRICTION', 'Los webhooks requieren un plan Business.', ['upsell_opportunities' => ['checkout_url' => self::checkoutUrl('business', 'api_403_webhooks')]]);
        }
        $userId = (int) \App\Filters\ApiKeyFilter::$apiMeta['user_id'];
        $db = \Config\Database::connect();
        $hook = $db->table('api_webhooks')->select('id')->where('user_id', $userId)->where('id', (int) $id)->get()->getRowArray();
        if (!$hook) {
            return $this->fallo(404, 'WEBHOOK_NOT_FOUND', 'Webhook no encontrado.');
        }

        $estados = ['pending', 'processing', 'retry', 'delivered', 'dead'];
        $status = strtolower(trim((string) $this->request->getGet('status')));
        if ($status !== '' && !in_array($status, $estados, true)) {
            return $this->fallo(400, 'VALIDATION_ERROR', 'status debe ser uno de: ' . implode(', ', $estados) . '.');
        }
        $page  = max(1, (int) ($this->request->getGet('page') ?? 1));
        $limit = (int) ($this->request->getGet('limit') ?? 50);
        $limit = ($limit < 1) ? 50 : min($limit, 100);

        $q = $db->table('webhook_deliveries')->where('webhook_id', (int) $hook['id']);
        if ($status !== '') {
            $q->where('status', $status);
        }
        $total = (clone $q)->countAllResults();
        $rows = $q->select('delivery_uuid, event, payload, status, attempts, http_status, duration_ms, error_message, created_at, last_attempt_at, next_attempt_at, completed_at')
            ->orderBy('id', 'DESC')
            ->limit($limit, ($page - 1) * $limit)
            ->get()->getResultArray();

        $data = [];
        foreach ($rows as $r) {
            $payload = json_decode((string) ($r['payload'] ?? ''), true);
            $pendiente = in_array($r['status'], ['pending', 'retry'], true);
            $data[] = [
                'delivery_id'     => $r['delivery_uuid'],
                'event'           => $r['event'],
                // id del evento de la vigilancia (el mismo de /watchlist/events)
                'event_id'        => is_array($payload) ? ($payload['data']['id'] ?? null) : null,
                'status'          => $r['status'],
                'attempts'        => (int) $r['attempts'],
                'http_status'     => $r['http_status'] !== null ? (int) $r['http_status'] : null,
                'duration_ms'     => $r['duration_ms'] !== null ? (int) $r['duration_ms'] : null,
                'error'           => $r['error_message'],
                'created_at'      => $r['created_at'],
                'last_attempt_at' => $r['last_attempt_at'],
                'next_attempt_at' => $pendiente ? $r['next_attempt_at'] : null,
                'completed_at'    => $r['completed_at'],
            ];
        }

        return $this->respond([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'webhook_id' => (int) $hook['id'],
                'total'      => $total,
                'page'       => $page,
                'limit'      => $limit,
                'has_more'   => $page * $limit < $total,
            ],
        ]);
    }
}
