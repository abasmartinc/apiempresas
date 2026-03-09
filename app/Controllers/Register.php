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

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->ApikeysModel = new ApikeysModel();
        $this->UsersuscriptionsModel = new UsersuscriptionsModel();
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

            // 4) Enviar correo (con FROM + BODY + DEBUG)
            $emailService = \Config\Services::email();

            // Forzar FROM válido (del mismo dominio del SMTP)
            $emailService->setFrom('soporte@apiempresas.es', 'APIEmpresas.es');

            // Destino (tu correo de notificación)
            $emailService->setTo('papelo.amh@gmail.com');

            // Opcional: responder al email del usuario registrado
            $emailService->setReplyTo($email, $data['name']);

            $emailService->setSubject('Nuevo registro de usuario');

            $emailBody = view('emails/admin_notification', [
                'name' => $data['name'],
                'company' => $data['company'],
                'email' => $data['email'],
                'user_id' => $user_id
            ]);

            $emailService->setMessage($emailBody);

            $sent = $emailService->send();

            if (!$sent) {
                // Log detallado para diagnosticar SMTP/TLS/auth/headers
                log_message('error', 'Email send failed: ' . $emailService->printDebugger(['headers', 'subject', 'body']));
            } else {
                log_message('info', 'Email sent OK to papelo.amh@gmail.com');
            }

            // 5) Enviar correo de BIENVENIDA al usuario
            $welcomeEmail = \Config\Services::email();
            $welcomeEmail->setFrom('soporte@apiempresas.es', 'APIEmpresas.es');
            $welcomeEmail->setTo($email);
            $welcomeEmail->setSubject('¡Bienvenido a APIEmpresas.es!');

            $emailBody = view('emails/welcome', ['name' => $data['name']]);
            $welcomeEmail->setMessage($emailBody);

            if (!$welcomeEmail->send()) {
                log_message('error', 'Welcome Email failed for ' . $email . ': ' . $welcomeEmail->printDebugger(['headers']));
            } else {
                log_message('info', 'Welcome Email sent OK to ' . $email);
            }

            // 6) Auto-Login al usuario
            $this->userModel->update($user_id, [
                'last_login_at' => date('Y-m-d H:i:s'),
            ]);

            session()->regenerate();
            session()->set([
                'user_id' => $user_id,
                'user_email' => $email,
                'user_name' => $data['name'],
                'is_admin' => 0,
                'logged_in' => true,
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

            // 7) Redirección Contextual
            if ($redirectUrl) {
                // Seteamos la intención en sesión para el primer dashboard tras registro
                session()->set('intended_product', $prefProduct);

                // Ensure it's a relative path or safe local URL
                $redirectUrl = filter_var($redirectUrl, FILTER_SANITIZE_URL);
                return redirect()->to(site_url(ltrim($redirectUrl, '/')));
            }

            return redirect()
                ->to(site_url('dashboard'))
                ->with('message', '¡Cuenta creada con éxito! Bienvenid@ a APIEmpresas.');
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
            return redirect()->to(site_url('billing/checkout'));
        }
        return view('auth/quick_register');
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
            // Safety: Admins must ALWAYS use their password
            if (($user->is_admin ?? 0) == 1) {
                return redirect()->to(site_url('enter?redirect=billing/checkout'))->with('info', 'Por seguridad, identifícate con tu cuenta de administrador para continuar.')->with('prefill_email', $email);
            }

            // Auto-Login for regular users to allow "launching to Stripe" immediately
            session()->regenerate();
            session()->set([
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name ?? '',
                'is_admin' => 0,
                'logged_in' => true,
            ]);

            return redirect()->to(site_url('billing/checkout'));
        }

        // Create new user (Quick)
        $password = bin2hex(random_bytes(8)); // Temporary password
        $data = [
            'name' => explode('@', $email)[0], // Use email part as name
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
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

            // Auto-Login
            session()->regenerate();
            session()->set([
                'user_id' => $user_id,
                'user_email' => $email,
                'user_name' => $data['name'],
                'logged_in' => true,
            ]);

            // Redirect back to billing/checkout with original POST data preserved in session
            return redirect()->to(site_url('billing/checkout'));

        } catch (\Throwable $e) {
            log_message('error', 'Quick Register failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al crear la cuenta. Inténtalo de nuevo.');
        }
    }
}
