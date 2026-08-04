<?php

namespace App\Controllers;

use App\Models\UserModel;

class Login extends BaseController
{
    /** @var UserModel */
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Muestra el formulario de login
     */
    public function index()
    {
        $currentUri = (string) $this->request->getUri();
        @file_put_contents(WRITEPATH . 'debug_redirect.txt', date('Y-m-d H:i:s') . " | LOGIN_INDEX | URI: {$currentUri} | Referer: " . ($this->request->getServer('HTTP_REFERER') ?? 'N/A') . "\n", FILE_APPEND);

        if (session('logged_in')) {
            $redirectUrl = $this->request->getGet('redirect') ?: 'dashboard';
            return redirect()->to(site_url(ltrim($redirectUrl, '/')));
        }
        $data = [
            'message' => session('message'),
            'error' => session('error'),
            'info' => session('info'),
            'prefill_email' => session('prefill_email'),
        ];

        return view('auth/login', $data);
    }

    /**
     * Muestra el formulario de login en ingles
     */
    public function english()
    {
        if (session('logged_in')) {
            $redirectUrl = $this->request->getGet('redirect') ?: 'dashboard';
            return redirect()->to(site_url(ltrim($redirectUrl, '/')));
        }
        $data = [
            'message' => session('message'),
            'error' => session('error'),
            'info' => session('info'),
            'prefill_email' => session('prefill_email'),
        ];

        return view('auth/login_en', $data);
    }

    /**
     * Procesa el intento de login (POST /login)
     */
    public function authenticate()
    {
        $rules = [
            'email' => [
                'label' => 'Correo electrónico',
                'rules' => 'required|valid_email',
            ],
            'password' => [
                'label' => 'Contraseña',
                'rules' => 'required|min_length[8]',
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', lang('Messages.flash_35'));
        }

        helper('turnstile');
        $turnstileResponse = $this->request->getPost('cf-turnstile-response');
        if (!verify_turnstile($turnstileResponse, $this->request->getIPAddress())) {
            return redirect()->back()->withInput()->with('error', lang('Messages.flash_36'));
        }

        $email = strtolower(trim($this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');

        // Buscar usuario por email y filtrar por source_app
        $user = $this->userModel
            ->where('email', $email)
            ->where('source_app', 'apiempresas')
            ->first();

        if (!$user) {
            log_message('error', "[Login DEBUG] User not found for email: {$email} and source_app: apiempresas");
            return redirect()
                ->back()
                ->withInput()
                ->with('error', lang('Messages.flash_37'));
        }

        // Comprobar si está activo
        if (isset($user->is_active) && (int) $user->is_active !== 1) {
            log_message('error', "[Login DEBUG] User inactive: {$email}");
            return redirect()
                ->back()
                ->withInput()
                ->with('error', lang('Messages.flash_38'));
        }

        $hash = (string) ($user->password_hash ?? '');

        // Depuración de verificación
        $verifyOk = ($hash !== '' && password_verify($password, $hash));

        if (!$verifyOk) {
            log_message('error', "[Login DEBUG] Password verification failed for user: {$email}");
            return redirect()
                ->back()
                ->withInput()
                ->with('error', lang('Messages.flash_39'));
        }

        // Actualizar último login
        $this->userModel->update($user->id, [
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);

        // Regenerar ID de sesión
        session()->regenerate();

        session()->set([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name ?? '',
            'is_admin' => $user->is_admin ?? 0,
            'preferred_product' => $user->preferred_product ?? 'api',
            'lang'              => $user->lang ?? 'es',
            'logged_in'         => true,
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

        // Log successful login
        log_activity('login');

        // Redirección contextual o por defecto
        $redirectUrl = $this->request->getPost('redirect');
        
        if (empty($redirectUrl)) {
            // Si no hay redirección específica, comprobamos el plan del usuario
            $subscriptionModel = new \App\Models\UsersuscriptionsModel();
            $activePlan = $subscriptionModel->getActivePlanByUserId($user->id);
            
            if ($activePlan && $activePlan->plan_slug === 'copiloto_ventas') {
                $redirectUrl = '/'; // Redirigir a la home si solo tiene copiloto_ventas
            } elseif ($subscriptionModel->hasActiveSubscriptionFor($user->id, 'radar')) {
                $redirectUrl = 'radar';
            } else {
                $redirectUrl = 'dashboard';
            }
        }

        // Si el destino es el radar, marcamos la intención en sesión
        if (strpos($redirectUrl, 'radar') !== false) {
            session()->set('intended_product', 'radar');
        } else {
            session()->set('intended_product', 'api');
        }

        return redirect()->to(site_url(ltrim($redirectUrl, '/')));
    }


    /**
     * Cierra la sesión de usuario
     */
    public function logout()
    {
        // Log logout before destroying session
        log_activity('logout');

        session()->destroy();

        return redirect()
            ->to(site_url('enter'))
            ->with('message', lang('Messages.flash_40'));
    }

    /**
     * Muestra el formulario de solicitud de reseteo
     */
    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    /**
     * Envía el enlace de reseteo por email
     */
    public function sendResetLink()
    {
        $email = strtolower(trim($this->request->getPost('email')));
        
        $user = $this->userModel
            ->where('email', $email)
            ->where('source_app', 'apiempresas')
            ->first();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $this->userModel->update($user->id, [
                'reset_token' => $token,
                'reset_expires' => $expires
            ]);

            // Enviar email
            $emailService = new \App\Services\EmailService();
            $result = $emailService->sendPasswordResetEmail($email, $token);
            
            if (!$result || (is_array($result) && !$result['success'])) {
                log_message('error', 'Error enviando email de reseteo para: ' . $email);
            }
        }

        // Siempre mostrar el mismo mensaje para evitar enumeración de usuarios
        return redirect()->back()->with('message', lang('Messages.flash_41'));
    }

    /**
     * Muestra el formulario para establecer la nueva contraseña
     */
    public function resetPassword($token)
    {
        $user = $this->userModel
            ->where('reset_token', $token)
            ->where('reset_expires >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$user) {
            return redirect()->to(site_url('enter'))->with('error', lang('Messages.flash_42'));
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    /**
     * Procesa la actualización de la contraseña
     */
    public function updatePassword()
    {
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $passwordConfirm = $this->request->getPost('password_confirm');

        if ($password !== $passwordConfirm) {
            return redirect()->back()->with('error', lang('Messages.flash_43'));
        }

        if (strlen($password) < 8) {
            return redirect()->back()->with('error', lang('Messages.flash_44'));
        }

        $user = $this->userModel
            ->where('reset_token', $token)
            ->where('reset_expires >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$user) {
            return redirect()->to(site_url('enter'))->with('error', lang('Messages.flash_45'));
        }

        $this->userModel->update($user->id, [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_expires' => null
        ]);

        return redirect()->to(site_url('enter'))->with('message', lang('Messages.flash_46'));
    }
}
