<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\ApikeysModel;
use App\Models\UsersuscriptionsModel;

class Register extends BaseController
{
    /** @var UserModel */
    protected $userModel;

    /** @var ApikeysModel */
    protected $ApikeysModel;

    /** @var UsersuscriptionsModel */
    protected $UsersuscriptionsModel;

    /** @var \App\Services\EmailService */
    protected $emailService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->ApikeysModel = new ApikeysModel();
        $this->UsersuscriptionsModel = new UsersuscriptionsModel();
        $this->emailService = new \App\Services\EmailService();
    }

    /**
     * Muestra el formulario de registro
     */
    public function index()
    {
        if (session('logged_in')) {
            $redirectUrl = $this->request->getGet('redirect') ?? 'dashboard';
            return redirect()->to(site_url(ltrim($redirectUrl, '/')));
        }
        $validation = session('validation') ?? \Config\Services::validation();
        $redirectUrl = $this->request->getGet('redirect') ?? '';

        return view('auth/register', [
            'validation' => $validation,
            'redirectUrl' => $redirectUrl
        ]);
    }

    /**
     * Procesa el registro
     */
    public function store()
    {
        $rules = [
            'name' => [
                'label' => 'Nombre',
                'rules' => 'required|min_length[3]|max_length[100]',
            ],
            'company' => [
                'label' => 'Empresa',
                'rules' => 'permit_empty|max_length[150]',
            ],
            'email' => [
                'label' => 'Correo electrónico',
                'rules' => 'required|valid_email|max_length[190]|is_unique[users.email]',
            ],
            'password' => [
                'label' => 'Contraseña',
                'rules' => 'required|min_length[8]|max_length[255]',
            ],
            'terms' => [
                'label' => 'Términos',
                'rules' => 'required',
            ],
        ];

        $messages = [
            'name' => [
                'required' => 'El nombre es obligatorio.',
                'min_length' => 'El nombre debe tener al menos 3 caracteres.',
                'max_length' => 'El nombre no puede superar los 100 caracteres.',
            ],
            'company' => [
                'max_length' => 'El nombre de empresa no puede superar los 150 caracteres.',
            ],
            'email' => [
                'required' => 'El correo electrónico es obligatorio.',
                'valid_email' => 'Introduce un correo electrónico válido.',
                'max_length' => 'El correo electrónico no puede superar los 190 caracteres.',
                'is_unique' => 'Ya existe una cuenta registrada con este correo.',
            ],
            'password' => [
                'required' => 'La contraseña es obligatoria.',
                'min_length' => 'La contraseña debe tener al menos 8 caracteres.',
                'max_length' => 'La contraseña no puede superar los 255 caracteres.',
            ],
            'terms' => [
                'required' => 'Debes aceptar los términos para continuar.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));

        // Generar API key robusta (64 chars hex)
        $apiKey = bin2hex(random_bytes(32));

        $redirectUrl = $this->request->getGet('redirect') ?? $this->request->getPost('redirect');
        $prefProduct = (strpos((string)$redirectUrl, 'radar') !== false) ? 'radar' : 'api';

        $data = [
            'name' => trim((string) $this->request->getPost('name')),
            'company' => trim((string) $this->request->getPost('company')),
            'email' => $email,
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'is_active' => 1,
            'api_access' => 1,
            'source_app' => 'apiempresas', // Default source
            'preferred_product' => $prefProduct,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        try {
            // 1) Crear usuario
            $user_id = $this->userModel->insert($data);

            if (!$user_id) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('validation', $this->validator)
                    ->with('error', 'Ha ocurrido un error al crear tu cuenta. Inténtalo de nuevo.');
            }

            // 2) Crear API key
            $this->ApikeysModel->insert([
                'user_id' => $user_id,
                'name' => 'Default API Key',
                'api_key' => $apiKey,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // 3) Crear suscripción (plan gratuito)
            $this->UsersuscriptionsModel->insert([
                'user_id' => $user_id,
                'plan_id' => 1, // Plan gratuito (API por defecto)
                'status' => 'active',
                'current_period_start' => date('Y-m-d H:i:s'),
                'current_period_end' => date('Y-m-d H:i:s', strtotime('+1 month')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // 4) Enviar notificaciones
            $userData = [
                'user_id' => $user_id,
                'name'    => $data['name'],
                'company' => $data['company'],
                'email'   => $data['email']
            ];

            // Notificación al Admin (papelo.amh@gmail.com)
            $this->emailService->sendRegistrationAdminNotification($userData);

            // Correo de Bienvenida al usuario
            $this->emailService->sendWelcomeEmail($userData);

            // 6) Auto-Login al usuario (RE-HABILITADO para mejorar conversión)
            $this->userModel->update($user_id, [
                'last_login_at' => date('Y-m-d H:i:s'),
            ]);

            session()->regenerate();
            session()->set([
                'user_id'    => $user_id,
                'user_email' => $email,
                'user_name'  => $data['name'],
                'is_admin'   => 0,
                'logged_in'  => true,
            ]);

            // Track login from email if tracking code exists
            if ($tc = session('email_tracking_code')) {
                $emailLogModel = new \App\Models\EmailLogModel();
                $log = $emailLogModel->where('tracking_code', $tc)->first();
                if ($log && is_null($log->logged_in_at)) {
                    $emailLogModel->update($log->id, ['logged_in_at' => date('Y-m-d H:i:s')]);
                }
                session()->remove('email_tracking_code');
            }

            // Log successful registration
            log_activity('register', ['email' => $email], $user_id);

            // 7) Redirección directa al Dashboard o URL previa
            $targetUrl = !empty($redirectUrl) ? site_url(ltrim((string)$redirectUrl, '/')) : site_url('dashboard');

            return redirect()
                ->to($targetUrl)
                ->with('success', '¡Bienvenido! Tu cuenta ha sido creada y ya estás dentro.');
        } catch (\Throwable $e) {

            // Log del error real para depuración
            log_message('error', 'Register store exception: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('error', 'Ha ocurrido un error al crear tu cuenta. Inténtalo de nuevo.');
        }
    }

    public function quick()
    {
        if (session('logged_in')) {
            $redirect = $this->request->getGet('redirect') ?? 'billing/checkout';
            return redirect()->to(site_url(ltrim($redirect, '/')));
        }
        return view('auth/quick_register', [
            'redirect' => $this->request->getGet('redirect') ?? 'billing/checkout'
        ]);
    }

    public function quick_store()
    {
        $email = strtolower(trim((string) $this->request->getPost('email')));
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Por favor, introduce un email válido.');
        }

        // Check if user exists
        $user = $this->userModel->where('email', $email)->first();

        if ($user) {
            // Autologin para usuarios existentes (Zero Friction) excepto admins
            if (($user->is_admin ?? 0) == 1) {
                return redirect()->to(site_url('enter?redirect=billing/checkout'))
                    ->with('info', 'Por seguridad, identifícate con tu cuenta de administrador.')
                    ->with('prefill_email', $email);
            }

            // Auto-Login
            session()->regenerate();
            session()->set([
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'logged_in' => true,
            ]);

            $this->userModel->update($user->id, ['last_login_at' => date('Y-m-d H:i:s')]);

            $redirect = $this->request->getPost('redirect') ?: 'billing/checkout';
            return redirect()->to(site_url(ltrim($redirect, '/')));
        }

        // Create new user (Quick)
        $password = bin2hex(random_bytes(8)); // Temporary password
        $token = bin2hex(random_bytes(32)); // Reset token for setting password
        $expires = date('Y-m-d H:i:s', strtotime('+48 hours'));

        $data = [
            'name' => explode('@', $email)[0], // Use email part as name
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'reset_token' => $token,
            'reset_expires' => $expires,
            'is_active' => 1,
            'api_access' => 1,
            'source_app' => 'apiempresas',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        try {
            $user_id = $this->userModel->insert($data);
            
            // API key and Subscription (Free)
            $this->ApikeysModel->insert([
                'user_id' => $user_id,
                'name' => 'Default API Key',
                'api_key' => bin2hex(random_bytes(32)),
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->UsersuscriptionsModel->insert([
                'user_id' => $user_id,
                'plan_id' => 1,
                'status' => 'active',
                'current_period_start' => date('Y-m-d H:i:s'),
                'current_period_end' => date('Y-m-d H:i:s', strtotime('+1 month')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // Enviar Notificaciones (Admin + Usuario)
            $this->emailService->sendRegistrationAdminNotification([
                'user_id' => $user_id,
                'name'    => $data['name'],
                'email'   => $email,
                'company' => 'N/A (Quick Register)'
            ]);

            $this->emailService->sendSetPasswordEmail($email, $token);

            // Auto-Login
            session()->regenerate();
            session()->set([
                'user_id' => $user_id,
                'user_email' => $email,
                'user_name' => $data['name'],
                'logged_in' => true,
            ]);

            // Redirect back to intended target or billing/checkout
            $redirect = $this->request->getPost('redirect') ?: 'billing/checkout';
            return redirect()->to(site_url(ltrim($redirect, '/')));

        } catch (\Throwable $e) {
            log_message('error', 'Quick Register failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al crear la cuenta. Inténtalo de nuevo.');
        }
    }
}
