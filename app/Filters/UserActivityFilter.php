<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;

class UserActivityFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userId = session()->get('user_id');

        if ($userId) {
            $lastUpdate = session()->get('last_activity_update');
            $currentTime = time();

            // Only update DB every 60 seconds to save resources
            if (!$lastUpdate || ($currentTime - $lastUpdate) > 60) {
                $userModel = new UserModel();
                $userModel->update($userId, [
                    'last_active_at' => date('Y-m-d H:i:s')
                ]);
                session()->set('last_activity_update', $currentTime);
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
