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

        $userId = (int)\App\Filters\ApiKeyFilter::$apiMeta['user_id'];
        $id = $this->webhookService->create($userId, $this->request->getJSON(true));

        return $this->respondCreated([
            'success' => true,
            'message' => 'Webhook creado correctamente',
            'id' => $id
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
}
