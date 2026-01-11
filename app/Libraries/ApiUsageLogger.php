<?php


namespace App\Libraries;

use App\Models\ApiRequestsModel;
use App\Models\ApiUsageDailyModel;
use CodeIgniter\Database\BaseConnection;
use DateTime;

class ApiUsageLogger
{
    private ApiRequestsModel $requestsModel;
    private ApiUsageDailyModel $dailyModel;
    private BaseConnection $db;

    public function __construct()
    {
        $this->requestsModel = new ApiRequestsModel();
        $this->dailyModel = new ApiUsageDailyModel();
        $this->db = db_connect();
    }

    /**
     * Registra:
     *  - fila en api_requests
     *  - upsert + increment en api_usage_daily
     */
    public function log(array $ctx): void
    {
        // ctx esperado:
        // user_id, api_key_id, subscription_id, plan_id, endpoint, http_method, status_code,
        // request_id, ip_address, user_agent, duration_ms
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');

        // 1) Insert detallado
        $this->requestsModel->insert([
            'user_id' => (int)$ctx['user_id'],
            'api_key_id' => (int)$ctx['api_key_id'],
            'subscription_id' => $ctx['subscription_id'] !== null ? (int)$ctx['subscription_id'] : null,
            'endpoint' => (string)$ctx['endpoint'],
            'http_method' => (string)$ctx['http_method'],
            'status_code' => (int)$ctx['status_code'],
            'request_id' => $ctx['request_id'] ? (string)$ctx['request_id'] : null,
            'ip_address' => $ctx['ip_address'] ? (string)$ctx['ip_address'] : null,
            'user_agent' => $ctx['user_agent'] ? (string)$ctx['user_agent'] : null,
            'duration_ms' => $ctx['duration_ms'] !== null ? (int)$ctx['duration_ms'] : null,
            'created_at' => $now,
        ]);

        // 2) Upsert diario (MySQL ON DUPLICATE KEY)
        // Requiere unique (user_id, plan_id, date) que YA tienes.
        $sql = "
            INSERT INTO api_usage_daily (user_id, plan_id, date, requests_count, created_at, updated_at)
            VALUES (?, ?, ?, 1, ?, ?)
            ON DUPLICATE KEY UPDATE
              requests_count = requests_count + 1,
              updated_at = VALUES(updated_at)
        ";
        $this->db->query($sql, [
            (int)$ctx['user_id'],
            (int)$ctx['plan_id'],
            $today,
            $now,
            $now,
        ]);
    }
}
