<?php

/**
 * Log user activity
 * 
 * @param string $action Action name (e.g., 'login', 'plan_upgrade')
 * @param array $details Additional details about the action
 * @param int|null $userId User ID (defaults to current session user)
 * @return void
 */
if (!function_exists('log_activity')) {
    function log_activity(string $action, array $details = [], ?int $userId = null): void
    {
        // Get user ID from session if not provided
        if ($userId === null) {
            $userId = session('user_id');
        }
        
        // Don't log if no user ID
        if (!$userId) {
            return;
        }
        
        try {
            $model = new \App\Models\UserActivityLogModel();
            $model->logActivity($userId, $action, $details);
        } catch (\Exception $e) {
            // Silently fail - don't break the app if logging fails
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
    }
}
