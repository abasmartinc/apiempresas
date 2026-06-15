<?php

namespace App\Services;

use App\Models\UserActivityLogModel;

class ApiRequestLogger
{
    /**
     * Log an API request event.
     *
     * @param int|null $userId User ID
     * @param string $action Action / Endpoint name (e.g. 'network', 'match')
     * @param string $status Status (e.g. 'success', 'error')
     * @param string $message Detailed message
     * @return void
     */
    public static function log(?int $userId, string $action, string $status, string $message): void
    {
        // Skip logging completely for the internal Status Monitor (ID: 376)
        if ($userId === 376) {
            return;
        }

        // 1) Log to system log file
        $logLevel = ($status === 'error') ? 'error' : 'info';
        log_message($logLevel, "[API Request] User: " . ($userId ?? 'Guest') . " | Action: {$action} | Status: {$status} | Message: {$message}");

        // 2) Log to database if user is known
        if ($userId) {
            try {
                $model = new UserActivityLogModel();
                $model->logActivity($userId, "api_{$action}_{$status}", [
                    'message' => $message,
                    'endpoint' => "api/v1/companies/{$action}"
                ]);
            } catch (\Throwable $e) {
                log_message('error', '[ApiRequestLogger::log] Failed database log: ' . $e->getMessage());
            }
        }
    }
}
