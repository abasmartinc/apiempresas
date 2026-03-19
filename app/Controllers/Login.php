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
                ->with('error', 'Por favor revisa los datos introducidos.');
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
                ->with('error', 'Credenciales incorrectas.');
        }

        // Comprobar si está activo
        if (isset($user->is_active) && (int) $user->is_active !== 1) {
            log_message('error', "[Login DEBUG] User inactive: {$email}");
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Tu cuenta está inactiva. Contacta con soporte.');
        }

        $hash = (string) ($user->password_hash ?? '');

        // Depuración de verificación
        $verifyOk = ($hash !== '' && password_verify($password, $hash));

        if (!$verifyOk) {
            log_message('error', "[Login DEBUG] Password verification failed for user: {$email}");
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Credenciales incorrectas.');
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

        // Log successful login
        log_activity('login');

        // Redirección contextual o por defecto
        $redirectUrl = $this->request->getPost('redirect') ?: 'dashboard';
        
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
            ->with('message', 'Has cerrado sesión correctamente.');
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
            $emailService = \Config\Services::email();
            $emailService->setTo($email);
            $emailService->setSubject('Restablecer contraseña - APIEmpresas.es');
            $emailService->setMessage(view('emails/reset_email', ['token' => $token]));

            if (!$emailService->send()) {
                log_message('error', 'Error enviando email de reseteo: ' . $emailService->printDebugger(['headers']));
            }
        }

        // Siempre mostrar el mismo mensaje para evitar enumeración de usuarios
        return redirect()->back()->with('message', 'Si el correo existe en nuestro sistema, recibirás un enlace para restablecer tu contraseña en unos minutos.');
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
            return redirect()->to(site_url('enter'))->with('error', 'El enlace de restablecimiento es inválido o ha caducado.');
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
            return redirect()->back()->with('error', 'Las contraseñas no coinciden.');
        }

        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'La contraseña debe tener al menos 8 caracteres.');
        }

        $user = $this->userModel
            ->where('reset_token', $token)
            ->where('reset_expires >=', date('Y-m-d H:i:s'))
            ->first();

        if (!$user) {
            return redirect()->to(site_url('enter'))->with('error', 'El enlace de restablecimiento es inválido o ha caducado.');
        }

        $this->userModel->update($user->id, [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_expires' => null
        ]);

        return redirect()->to(site_url('enter'))->with('message', 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.');
    }
}
