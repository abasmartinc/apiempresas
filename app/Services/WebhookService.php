<?php

namespace App\Services;

class WebhookService
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function list(int $userId): array
    {
        return $this->db->table('api_webhooks')
                        ->where('user_id', $userId)
                        ->get()
                        ->getResultArray();
    }

    public function create(int $userId, array $data): int
    {
        $payload = [
            'user_id'    => $userId,
            'url'        => $data['url'],
            'event'      => $data['event'] ?? 'new_company',
            'secret'     => $data['secret'] ?? bin2hex(random_bytes(16)),
            'filters'    => json_encode($data['filters'] ?? []),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('api_webhooks')->insert($payload);
        return $this->db->insertID();
    }

    public function delete(int $userId, int $id): bool
    {
        return $this->db->table('api_webhooks')
                        ->where('user_id', $userId)
                        ->where('id', $id)
                        ->delete();
    }
}
