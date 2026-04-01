<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserActivityLogModel;

class ActivityLogs extends BaseController
{
    protected $activityModel;

    public function __construct()
    {
        $this->activityModel = new UserActivityLogModel();
    }

    /**
     * Display activity logs with filters
     */
    public function index()
    {
        // Check if user is admin
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        // Get filters from query string
        $filters = [
            'user_id'   => $this->request->getGet('user_id'),
            'action'    => $this->request->getGet('action'),
            'from_date' => $this->request->getGet('from_date'),
            'to_date'   => $this->request->getGet('to_date'),
        ];

        // Remove empty filters
        $filters = array_filter($filters);

        // Get limit from query or default to 100
        $limit = (int) ($this->request->getGet('limit') ?? 100);
        $limit = min(max($limit, 10), 500); // Between 10 and 500

        // Fetch activity logs
        $data['logs'] = $this->activityModel->getRecentActivity($limit, $filters);
        
        // DEBUG: Check what is actually retrieved
        // log_message('error', 'DEBUG_ACTIVITY_LOGS: ' . print_r($data['logs'], true));

        // Get unique actions for filter dropdown
        $data['actions'] = $this->activityModel->getUniqueActions();
        
        // Get all users for filter dropdown
        $userModel = new \App\Models\UserModel();
        $data['users'] = $userModel->select('id, name, email')->orderBy('name', 'ASC')->findAll();

        $data['filters'] = $filters;
        $data['limit'] = $limit;
        
        // Calculate Statistics for KPIs
        $data['stats'] = [
            'total_24h' => $this->activityModel->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))->countAllResults(),
            'logins_24h' => $this->activityModel->where('action', 'login')->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))->countAllResults(),
            'active_users' => $this->activityModel->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))->select('user_id')->groupBy('user_id')->findAll() ? count($this->activityModel->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))->select('user_id')->groupBy('user_id')->findAll()) : 0,
            'top_action' => $this->activityModel->getActivityStats(date('Y-m-d', strtotime('-24 hours')), date('Y-m-d'))[0]['action'] ?? 'None'
        ];

        return view('admin/activity_logs', $data);
    }

    /**
     * View activity for a specific user
     */
    public function user($userId)
    {
        // Check if user is admin
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $userId = (int) $userId;
        
        $data['logs'] = $this->activityModel->getUserActivity($userId, 200);
        $data['user_id'] = $userId;

        return view('admin/user_activity', $data);
    }

    /**
     * Export activity logs to CSV
     */
    public function export()
    {
        // Check if user is admin
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        // Get filters
        $filters = [
            'user_id'   => $this->request->getGet('user_id'),
            'action'    => $this->request->getGet('action'),
            'from_date' => $this->request->getGet('from_date'),
            'to_date'   => $this->request->getGet('to_date'),
        ];

        $filters = array_filter($filters);

        // Fetch logs (limit to 5000 for export)
        $logs = $this->activityModel->getRecentActivity(5000, $filters);

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="activity_logs_' . date('Y-m-d_His') . '.csv"');

        // Open output stream
        $output = fopen('php://output', 'w');

        // Write UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Write header row
        fputcsv($output, ['ID', 'User ID', 'User Name', 'User Email', 'Action', 'Details', 'IP Address', 'User Agent', 'Created At']);

        // Write data rows
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['user_id'],
                $log['user_name'] ?? '',
                $log['user_email'] ?? '',
                $log['action'],
                $log['details'] ?? '',
                $log['ip_address'] ?? '',
                $log['user_agent'] ?? '',
                $log['created_at'],
            ]);
        }

        fclose($output);
        exit;
    }
}
