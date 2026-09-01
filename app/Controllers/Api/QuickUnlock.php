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
            // Existing user: Do NOT create/adopt session and do NOT expose or generate API Key
            $redirectUrl = site_url('enter?redirect=dashboard&email=' . urlencode($email));
            $message = (($user->is_admin ?? 0) == 1)
                ? 'Inicia sesión como administrador para gestionar tus llaves.'
                : 'Ya existe una cuenta con este correo. Inicia sesión para acceder a tu API Key.';

            return $this->response->setJSON([
                'status'   => 'exists',
                'message'  => $message,
                'redirect' => $redirectUrl,
            ]);
        }

        // New user registration flow
        $password = bin2hex(random_bytes(8));
        $token = bin2hex(random_bytes(32));
        
        $userId = $userModel->insert([
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
            'user_id' => $userId,
            'plan_id' => 1,
            'status' => 'active',
            'current_period_start' => date('Y-m-d H:i:s'),
            'current_period_end' => date('Y-m-d H:i:s', strtotime('+1 month')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        session()->regenerate();
        session()->set([
            'user_id' => $userId,
            'user_email' => $email,
            'user_name' => explode('@', $email)[0],
            'logged_in' => true,
        ]);

        $emailService->sendRegistrationAdminNotification([
            'user_id' => $userId,
            'name'    => explode('@', $email)[0],
            'email'   => $email,
            'company' => 'N/A (Quick Unlock)'
        ]);
        
        $emailService->sendSetPasswordEmail($email, $token);

        // Generate API Key for the new user
        $keyValue = bin2hex(random_bytes(32));
        $apiKeyModel->insert([
            'user_id' => $userId,
            'name' => 'Default API Key',
            'api_key' => $keyValue,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'api_key' => $keyValue,
            'redirect' => site_url('documentation?key=' . $keyValue)
        ]);
    }
}
