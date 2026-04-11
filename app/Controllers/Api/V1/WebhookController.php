<?php

namespace App\Controllers\Api\V1;

use CodeIgniter\RESTful\ResourceController;
use App\Services\PlanAccessService;
use App\Services\WebhookService;

class WebhookController extends ResourceController
{
    protected PlanAccessService $planAccess;
    protected WebhookService   $webhookService;

    public function __construct()
    {
        $this->planAccess     = new PlanAccessService();
        $this->webhookService = new WebhookService();
    }

    public function index()
    {
        $planSlug = $this->request->api_meta['plan_slug'] ?? 'free';
        if (!$this->planAccess->canAccess($planSlug, 'webhooks')) {
            return $this->failForbidden('Los webhooks requieren un plan Business.');
        }

        $userId = (int)$this->request->api_meta['user_id'];
        $list = $this->webhookService->list($userId);

        return $this->respond([
            'success' => true,
            'data' => $list
        ]);
    }

    public function create()
    {
        $planSlug = $this->request->api_meta['plan_slug'] ?? 'free';
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

        $userId = (int)$this->request->api_meta['user_id'];
        $id = $this->webhookService->create($userId, $this->request->getJSON(true));

        return $this->respondCreated([
            'success' => true,
            'message' => 'Webhook creado correctamente',
            'id' => $id
        ]);
    }

    public function delete($id = null)
    {
        $planSlug = $this->request->api_meta['plan_slug'] ?? 'free';
        if (!$this->planAccess->canAccess($planSlug, 'webhooks')) {
            return $this->failForbidden('Los webhooks requieren un plan Business.');
        }

        $userId = (int)$this->request->api_meta['user_id'];
        if ($this->webhookService->delete($userId, (int)$id)) {
            return $this->respondDeleted(['success' => true, 'message' => 'Webhook eliminado']);
        }

        return $this->failNotFound('Webhook no encontrado.');
    }
}
