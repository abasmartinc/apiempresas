<?php

namespace App\Controllers;

use App\Models\EmailLogModel;
use CodeIgniter\Controller;

class EmailTracking extends Controller
{
    /**
     * Track email opening
     */
    public function open($code)
    {
        log_message('debug', 'Email open tracking request. Code: ' . $code);
        $model = new EmailLogModel();
        $log = $model->where('tracking_code', $code)->first();

        if ($log && is_null($log->opened_at)) {
            $model->update($log->id, [
                'opened_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Return a 1x1 transparent GIF
        $img = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        return $this->response
            ->setHeader('Content-Type', 'image/gif')
            ->setHeader('Content-Length', strlen($img))
            ->setBody($img);
    }

    /**
     * Track email link clicks
     */
    public function click($code)
    {
        log_message('debug', 'Email click tracking request. Code: ' . $code);
        $model = new EmailLogModel();
        $log = $model->where('tracking_code', $code)->first();

        if ($log) {
            log_message('debug', 'Log found for code: ' . $code);
            $updateData = [];
            if (is_null($log->opened_at)) {
                $updateData['opened_at'] = date('Y-m-d H:i:s');
            }
            if (is_null($log->clicked_at)) {
                $updateData['clicked_at'] = date('Y-m-d H:i:s');
            }
            
            if (!empty($updateData)) {
                $model->update($log->id, $updateData);
            }

            // Set session for login tracking
            session()->set('email_tracking_code', $code);
        }

        $url = $this->request->getGet('t') ?: site_url('enter');
        
        return redirect()->to($url);
    }
}
