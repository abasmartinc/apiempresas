<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ApikeysModel;
use App\Models\UsersuscriptionsModel;
use App\Services\EmailService;

class QuickUnlock extends BaseController
{
    public function index()
    {
        $email = strtolower(trim((string) $this->request->getPost('email')));
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Por favor, introduce un email válido.']);
        }

        $userModel = new UserModel();
        $apiKeyModel = new ApikeysModel();
        $subModel = new UsersuscriptionsModel();
        $emailService = new EmailService();

        $user = $userModel->where('email', $email)->first();

        if ($user) {
            // Existing user
            if (($user->is_admin ?? 0) == 1) {
                return $this->response->setJSON([
                    'status' => 'exists',
                    'message' => 'Inicia sesión como administrador para gestionar tus llaves.',
                    'redirect' => site_url('enter?redirect=dashboard')
                ]);
            }

            session()->regenerate();
            session()->set([
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'logged_in' => true,
            ]);
        } else {
            // New user
            $password = bin2hex(random_bytes(8));
            $token = bin2hex(random_bytes(32));
            
            $user_id = $userModel->insert([
                'name' => explode('@', $email)[0],
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'reset_token' => $token,
                'reset_expires' => date('Y-m-d H:i:s', strtotime('+48 hours')),
                'is_active' => 1,
                'api_access' => 1,
                'source_app' => 'apiempresas',
                'preferred_product' => 'api',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Default Subscription (Free)
            $subModel->insert([
                'user_id' => $user_id,
                'plan_id' => 1,
                'status' => 'active',
                'current_period_start' => date('Y-m-d H:i:s'),
                'current_period_end' => date('Y-m-d H:i:s', strtotime('+1 month')),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            session()->regenerate();
            session()->set([
                'user_id' => $user_id,
                'user_email' => $email,
                'user_name' => explode('@', $email)[0],
                'logged_in' => true,
            ]);

            $emailService->sendSetPasswordEmail($email, $token);
        }

        $userId = session('user_id');
        
        // Generate API Key if not exists
        $apiKey = $apiKeyModel->where(['user_id' => $userId, 'is_active' => 1])->first();
        if (!$apiKey) {
            $keyValue = bin2hex(random_bytes(32));
            $apiKeyModel->insert([
                'user_id' => $userId,
                'name' => 'Default API Key',
                'api_key' => $keyValue,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $apiKey = $keyValue;
        } else {
            $apiKey = $apiKey->api_key;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'api_key' => $apiKey,
            'redirect' => site_url('documentation?key=' . $apiKey)
        ]);
    }
}
