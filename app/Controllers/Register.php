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
        if ($this->request->getGet('intent')) {
            session()->set('signup_intent', trim((string)$this->request->getGet('intent')));
        }
        // CIF de la empresa que originó el registro: permite saber qué fichas
        // convierten y personalizar el correo de bienvenida.
        if ($this->request->getGet('cif')) {
            session()->set('signup_cif', trim((string)$this->request->getGet('cif')));
        }

        return view('auth/register', [
            'validation' => $validation,
            'redirectUrl' => $redirectUrl
        ]);
    }

    /**
     * Muestra el formulario de registro en ingles
     */
    public function english()
    {
        if (session('logged_in')) {
            $redirectUrl = $this->request->getGet('redirect') ?? 'dashboard';
            return redirect()->to(site_url(ltrim($redirectUrl, '/')));
        }
        $validation = session('validation') ?? \Config\Services::validation();
        $redirectUrl = $this->request->getGet('redirect') ?? '';
        if ($this->request->getGet('intent')) {
            session()->set('signup_intent', trim((string)$this->request->getGet('intent')));
        }

        return view('auth/register_en', [
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
                'rules' => 'required|valid_email|max_length[190]|not_disposable_email',
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

        $host = $this->request->getServer('HTTP_HOST') ?? '';
        $isEnglish = (strpos((string)$host, 'spaincompanyapi') !== false);

        $messages = $isEnglish ? [
            'name' => [
                'required' => 'Full name is required.',
                'min_length' => 'Name must be at least 3 characters.',
                'max_length' => 'Name cannot exceed 100 characters.',
            ],
            'company' => [
                'max_length' => 'Company name cannot exceed 150 characters.',
            ],
            'email' => [
                'required' => 'Email address is required.',
                'valid_email' => 'Please provide a valid email address.',
                'max_length' => 'Email cannot exceed 190 characters.',
                'is_unique' => 'An account already exists with this email.',
                'not_disposable_email' => 'Temporary or disposable email addresses are not allowed.',
            ],
            'password' => [
                'required' => 'Password is required.',
                'min_length' => 'Password must be at least 8 characters.',
                'max_length' => 'Password cannot exceed 255 characters.',
            ],
            'terms' => [
                'required' => 'You must accept the terms to continue.',
            ],
        ] : [
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
                'not_disposable_email' => 'No se permiten correos electrónicos temporales o desechables.',
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

        helper('turnstile');
        $turnstileResponse = $this->request->getPost('cf-turnstile-response');
        if (!verify_turnstile($turnstileResponse, $this->request->getIPAddress())) {
            $msg = $isEnglish ? 'Security verification failed (Turnstile). Please try again.' : 'Fallo en la verificación de seguridad (Turnstile). Por favor, inténtalo de nuevo.';
            return redirect()->back()->withInput()->with('error', $msg);
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));

        // Scoped Uniqueness Check: Check if email already exists for 'apiempresas'
        $existingUser = $this->userModel
            ->where('email', $email)
            ->where('source_app', 'apiempresas')
            ->first();

        if ($existingUser) {
            $msg = $isEnglish ? 'An account already exists with this email address.' : 'Ya existe una cuenta registrada con este correo en APIEmpresas.';
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $msg);
        }

        // Generar API key robusta (64 chars hex)
        $apiKey = bin2hex(random_bytes(32));

        $redirectUrl = $this->request->getGet('redirect') ?? $this->request->getPost('redirect');

        $intent = $this->request->getPost('intent') 
            ? trim((string) $this->request->getPost('intent')) 
            : (session()->get('signup_intent') ?: null);

        if (!$intent) {
            if (strpos((string)$redirectUrl, 'radar') !== false) {
                $intent = 'radar';
            } elseif (strpos((string)$redirectUrl, 'database') !== false || strpos((string)$redirectUrl, 'listado') !== false) {
                $intent = 'database';
            } elseif (strpos((string)$redirectUrl, 'risk') !== false || strpos((string)$redirectUrl, 'riesgo') !== false || strpos((string)$redirectUrl, 'score') !== false) {
                // 'riesgo' además de 'risk': el destino real que manda el teaser es
                // `empresa/123-slug?ver-riesgo=1`, que no contiene ninguna de las
                // dos palabras inglesas, así que caía en el `else` y se registraba
                // como usuario de la API.
                $intent = 'view_risk_profile';
            } else {
                $intent = 'api';
            }
        }
        session()->remove('signup_intent');

        // Aquí se calculaba un 'preferred_product' que no llegaba a ningún sitio:
        // la columna no existe en la base de datos y el campo tampoco estaba en
        // UserModel::$allowedFields, así que se descartaba dos veces. Quien decide
        // qué panel ve el usuario es 'signup_intent', que sí se guarda.

        $host = $this->request->getServer('HTTP_HOST') ?? '';
        $lang = (strpos((string)$host, 'spaincompanyapi') !== false) ? 'en' : 'es';

        $data = [
            'name' => trim((string) $this->request->getPost('name')),
            'company' => trim((string) $this->request->getPost('company')),
            'lang' => $lang,
            'email' => $email,
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'is_active' => 1,
            'api_access' => 1,
            'source_app' => 'apiempresas', // Default source
            'signup_intent' => $intent,
            'unsuscribe' => $this->request->getPost('no_marketing') ? 1 : 0,
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
                    ->with('error', lang('Messages.flash_55'));
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
                'user_id'       => $user_id,
                'name'          => $data['name'],
                'company'       => $data['company'],
                'email'         => $data['email'],
                'signup_intent' => $data['signup_intent'] ?? null,
            ];

            // En su propio try, por lo mismo que en quick_store: la cuenta ya existe.
            try {
                // Notificación al Admin (papelo.amh@gmail.com)
                $this->emailService->sendRegistrationAdminNotification($userData);

                // Correo de Bienvenida al usuario
                if (($data['signup_intent'] ?? '') === 'view_risk_profile') {
                    // El CIF sigue en sesión: logSignupOrigin() lo consume más abajo
                    $this->emailService->sendRiskWelcomeEmail(
                        $userData,
                        (string)($redirectUrl ?? ''),
                        (string)(session()->get('signup_cif') ?? '')
                    );
                } elseif (($data['signup_intent'] ?? '') === 'api') {
                    $this->emailService->sendWelcomeEmail($userData);
                }
            } catch (\Throwable $e) {
                log_message('error', 'Registro: fallo enviando correos al usuario ' . $user_id . ': ' . $e->getMessage());
            }

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

            $this->logSignupOrigin($user_id, $intent);

            // 7) Redirección directa al Dashboard o URL previa
            $targetUrl = !empty($redirectUrl) ? site_url(ltrim((string)$redirectUrl, '/')) : site_url('dashboard');

            return redirect()
                ->to($targetUrl)
                ->with('success', lang('Messages.flash_56'));
        } catch (\Throwable $e) {

            // Log del error real para depuración
            log_message('error', 'Register store exception: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('error', lang('Messages.flash_57'));
        }
    }

    /**
     * Deja constancia de la empresa que originó el registro (tracking_events).
     * Sin tocar user_events: ahí un evento 'view_risk_profile' consumiría cuota.
     */
    protected function logSignupOrigin(int $userId, ?string $intent = null): void
    {
        // La sesión es la fuente principal, pero el formulario rápido arrastra el CIF
        // en un hidden: si la sesión se perdió por el camino, seguimos teniéndolo.
        $cif = trim((string)(session()->get('signup_cif') ?? ''));
        if ($cif === '') {
            $cif = trim((string)($this->request->getPost('cif') ?? ''));
        }
        session()->remove('signup_cif');

        if ($userId <= 0 || $cif === '') {
            return;
        }

        try {
            (new \App\Models\TrackingEventModel())->insert([
                'event_name'   => 'risk_signup_origin',
                'page'         => 'register',
                'user_id'      => $userId,
                'session_id'   => substr((string)session_id(), 0, 100),
                'anonymous_id' => '',
                'element'      => substr($cif, 0, 255),
                'metadata'     => json_encode(['intent' => $intent, 'cif' => $cif]),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'logSignupOrigin: ' . $e->getMessage());
        }
    }

    public function quick()
    {
        if (session('logged_in')) {
            $redirect = $this->request->getGet('redirect') ?? 'billing/checkout';
            return redirect()->to(site_url(ltrim($redirect, '/')));
        }

        $intent = trim((string)($this->request->getGet('intent') ?? ''));
        if ($intent !== '') {
            session()->set('signup_intent', $intent);
        }

        $cif = trim((string)($this->request->getGet('cif') ?? ''));
        if ($cif !== '') {
            session()->set('signup_cif', $cif);
        }

        $db = \Config\Database::connect();
        $oppsCount = $db->table('companies')
            ->where('fecha_constitucion >=', date('Y-m-d'))
            ->countAllResults();

        // Nombre de la empresa de origen: permite personalizar el encabezado
        // ("Ver el perfil de riesgo de X") en vez de un genérico "último paso".
        $companyName = '';
        if ($cif !== '' && $intent === 'view_risk_profile') {
            helper('company');
            $row = (new \App\Models\CompanyModel())->where('cif', strtoupper($cif))->first();
            if ($row) {
                $companyName = company_short_name(company_display_name(
                    $row['company_name'] ?? ($row['name'] ?? ''),
                    ''
                ));
            }
        }

        return view('auth/quick_register', [
            'redirect'    => $this->request->getGet('redirect') ?? 'billing/checkout',
            'oppsCount'   => $oppsCount,
            'intent'      => $intent,
            'signupCif'   => $cif,
            'companyName' => $companyName,
        ]);
    }

    public function quick_store()
    {
        $email = strtolower(trim((string) $this->request->getPost('email')));
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', lang('Messages.flash_58'));
        }

        // Check for disposable email
        $validation = \Config\Services::validation();
        $validation->setRules([
            'email' => [
                'rules' => 'not_disposable_email',
                'errors' => [
                    'not_disposable_email' => 'No se permiten correos electrónicos temporales o desechables.'
                ]
            ]
        ]);
        if (!$validation->run(['email' => $email])) {
            return redirect()->back()->with('error', $validation->getError('email'))->withInput();
        }

        // Check if user exists
        $user = $this->userModel->where('email', $email)->where('source_app', 'apiempresas')->first();

        if ($user) {
            // Autologin para usuarios existentes (Zero Friction) excepto admins
            if (($user->is_admin ?? 0) == 1) {
                return redirect()->to(site_url('enter?redirect=billing/checkout'))
                    ->with('info', lang('Messages.flash_59'))
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
        $redirect = $this->request->getPost('redirect') ?: 'billing/checkout';

        $intent = $this->request->getPost('intent') 
            ? trim((string) $this->request->getPost('intent')) 
            : (session()->get('signup_intent') ?: null);

        if (!$intent) {
            if (strpos($redirect, 'radar') !== false) {
                $intent = 'radar';
            } elseif (strpos($redirect, 'database') !== false || strpos($redirect, 'listado') !== false) {
                $intent = 'database';
            } elseif (strpos($redirect, 'risk') !== false || strpos($redirect, 'riesgo') !== false || strpos($redirect, 'score') !== false) {
                // Ver la nota de Register::store: el destino del teaser es
                // `empresa/123-slug?ver-riesgo=1`, sin 'risk' ni 'score'.
                $intent = 'view_risk_profile';
            } else {
                $intent = 'api';
            }
        }
        session()->remove('signup_intent');

        // Ver la nota de Register::store: 'preferred_product' no existe en la BD.

        $host = $this->request->getServer('HTTP_HOST') ?? '';
        $lang = (strpos((string)$host, 'spaincompanyapi') !== false) ? 'en' : 'es';

        $data = [
            'name' => explode('@', $email)[0], // Use email part as name
            'email' => $email,
            'lang' => $lang,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'reset_token' => $token,
            'reset_expires' => $expires,
            'is_active' => 1,
            'api_access' => 1,
            'source_app' => 'apiempresas',
            'signup_intent' => $intent,
            'unsuscribe' => $this->request->getPost('no_marketing') ? 1 : 0,
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

            // Notificaciones. En su PROPIO try: llegados aquí la cuenta, la API key
            // y la suscripción ya existen, así que un fallo mandando un correo no
            // puede acabar en "Error al crear la cuenta" con el usuario sin sesión
            // y la fila ya creada — que es justo lo que pasaba.
            try {
                $this->emailService->sendRegistrationAdminNotification([
                    'user_id' => $user_id,
                    'name'    => $data['name'],
                    'email'   => $email,
                    'company' => 'N/A (Quick Register)'
                ]);

                $this->emailService->sendSetPasswordEmail($email, $token);

                // El registro rápido solo mandaba el correo de contraseña: quien viene
                // por el perfil de riesgo debe recibir además su bienvenida de Solvencia.
                if (($data['signup_intent'] ?? '') === 'view_risk_profile') {
                    $this->emailService->sendRiskWelcomeEmail([
                        'user_id'       => $user_id,
                        'name'          => $data['name'],
                        'email'         => $email,
                        'signup_intent' => $data['signup_intent'],
                    ], (string)$redirect, (string)(session()->get('signup_cif') ?? $this->request->getPost('cif') ?? ''));
                }
            } catch (\Throwable $e) {
                log_message('error', 'Quick Register: fallo enviando correos al usuario ' . $user_id . ': ' . $e->getMessage());
            }

            try {
                $this->logSignupOrigin((int)$user_id, $data['signup_intent'] ?? null);
            } catch (\Throwable $e) {
                log_message('error', 'Quick Register: no se pudo registrar el origen del alta ' . $user_id . ': ' . $e->getMessage());
            }

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
            return redirect()->back()->with('error', lang('Messages.flash_60'));
        }
    }

}
