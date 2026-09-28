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
        description: "Obtener todos los webhooks configurados para recibir notificaciones (Requiere Plan Business).",
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
            return $this->failForbidden('Los webhooks requieren un plan Business.');
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
        description: "Registra una nueva URL para recibir eventos asíncronos.",
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
            return $this->failForbidden('Los webhooks requieren un plan Business.');
        }

        $rules = [
            'url'   => 'required|valid_url',
            'event' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->fail($this->validator->getErrors());
        }

        // Solo HTTPS a hosts públicos: impide registrar URLs internas (localhost,
        // IPs privadas, metadatos del servidor) a las que luego llamaría nuestro
        // servidor. Mismo formato que el error de validación de arriba.
        $json = $this->request->getJSON(true);
        $url  = (string) ((is_array($json) ? ($json['url'] ?? null) : null) ?? $this->request->getVar('url') ?? '');
        if (!\App\Services\SafeUrlValidator::isSafeUrl($url)) {
            return $this->fail(['url' => 'La URL debe ser HTTPS (puerto 443) y apuntar a un servidor público.']);
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
        description: "Elimina un webhook previamente configurado.",
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
            return $this->failForbidden('Los webhooks requieren un plan Business.');
        }

        $userId = (int)\App\Filters\ApiKeyFilter::$apiMeta['user_id'];
        if ($this->webhookService->delete($userId, (int)$id)) {
            return $this->respondDeleted(['success' => true, 'message' => 'Webhook eliminado']);
        }

        return $this->failNotFound('Webhook no encontrado.');
    }

    /**
     * POST /api/v1/webhooks/{id}/test (28-09-2026): envía un test.ping a la URL del
     * webhook en el momento y devuelve lo que respondió.
     */
    public function test($id = null)
    {
        $planSlug = \App\Filters\ApiKeyFilter::$apiMeta['plan_slug'] ?? 'free';
        if (!$this->planAccess->canAccess($planSlug, 'webhooks')) {
            return $this->failForbidden('Los webhooks requieren un plan Business.');
        }
        $userId = (int)\App\Filters\ApiKeyFilter::$apiMeta['user_id'];
        $hook = \Config\Database::connect()->table('api_webhooks')
            ->where('user_id', $userId)->where('id', (int) $id)->get()->getRowArray();
        if (!$hook) {
            return $this->failNotFound('Webhook no encontrado.');
        }
        $r = \App\Services\WebhookDispatcher::testPing($hook);
        return $this->respond([
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
}
