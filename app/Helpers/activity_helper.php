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

        // Check if user is admin
        try {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($userId);
    
            if ($user && isset($user->is_admin) && $user->is_admin == 1) {
                return;
            }
        } catch (\Exception $e) {
            // Ignore user lookup errors, proceed to try logging or return? 
            // Better to proceed or return? If we can't find user, maybe we should log? 
            // But if user doesn't exist, logActivity might fail too (FK constraint).
            // Let's just log a warning and continue, effectively trying to log activity.
            // Actually, if find fails, it might be safer to just return or ignore.
            // Let's keep it simple.
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
