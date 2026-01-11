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
        $this->userModel            = new UserModel();
        $this->ApikeysModel         = new ApikeysModel();
        $this->UsersuscriptionsModel = new UsersuscriptionsModel();
    }

    /**
     * Muestra el formulario de registro
     */
    public function index()
    {
        $validation = session('validation') ?? \Config\Services::validation();

        return view('auth/register', [
            'validation' => $validation,
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
                'required'   => 'El nombre es obligatorio.',
                'min_length' => 'El nombre debe tener al menos 3 caracteres.',
                'max_length' => 'El nombre no puede superar los 100 caracteres.',
            ],
            'company' => [
                'max_length' => 'El nombre de empresa no puede superar los 150 caracteres.',
            ],
            'email' => [
                'required'    => 'El correo electrónico es obligatorio.',
                'valid_email' => 'Introduce un correo electrónico válido.',
                'max_length'  => 'El correo electrónico no puede superar los 190 caracteres.',
                'is_unique'   => 'Ya existe una cuenta registrada con este correo.',
            ],
            'password' => [
                'required'   => 'La contraseña es obligatoria.',
                'min_length' => 'La contraseña debe tener al menos 8 caracteres.',
                'max_length' => 'La contraseña no puede superar los 255 caracteres.',
            ],
            'terms' => [
                'required' => 'Debes aceptar los términos para continuar.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));

        // Generar API key robusta (64 chars hex)
        $apiKey = bin2hex(random_bytes(32));

        $data = [
            'name'          => trim((string) $this->request->getPost('name')),
            'company'       => trim((string) $this->request->getPost('company')),
            'email'         => $email,
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        try {
            // 1) Crear usuario
            $user_id = $this->userModel->insert($data);

            if (! $user_id) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('validation', $this->validator)
                    ->with('error', 'Ha ocurrido un error al crear tu cuenta. Inténtalo de nuevo.');
            }

            // 2) Crear API key
            $this->ApikeysModel->insert([
                'user_id'    => $user_id,
                'name'       => 'Default API Key',
                'api_key'    => $apiKey,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            // 3) Crear suscripción (plan gratuito)
            $this->UsersuscriptionsModel->insert([
                'user_id'               => $user_id,
                'plan_id'               => 1,
                'status'                => 'active',
                'current_period_start'  => date('Y-m-d H:i:s'),
                'current_period_end'    => date('Y-m-d H:i:s', strtotime('+1 month')),
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ]);

            // 4) Enviar correo (con FROM + BODY + DEBUG)
            $emailService = \Config\Services::email();

            // Forzar FROM válido (del mismo dominio del SMTP)
            $emailService->setFrom('admin@abasmart.net', 'ABASmart');

            // Destino (tu correo de notificación)
            $emailService->setTo('papelo.amh@gmail.com');

            // Opcional: responder al email del usuario registrado
            $emailService->setReplyTo($email, $data['name']);

            $emailService->setSubject('Nuevo registro de usuario');

            $emailService->setMessage("
                <h3>Nuevo registro</h3>
                <p><b>Nombre:</b> " . esc($data['name']) . "</p>
                <p><b>Empresa:</b> " . esc($data['company']) . "</p>
                <p><b>Email:</b> " . esc($data['email']) . "</p>
                <p><b>Fecha:</b> " . date('Y-m-d H:i:s') . "</p>
                <hr>
                <p><b>User ID:</b> " . (int) $user_id . "</p>
            ");

            $sent = $emailService->send();

            if (! $sent) {
                // Log detallado para diagnosticar SMTP/TLS/auth/headers
                log_message('error', 'Email send failed: ' . $emailService->printDebugger(['headers', 'subject', 'body']));
            } else {
                log_message('info', 'Email sent OK to papelo.amh@gmail.com');
            }

            return redirect()
                ->to(site_url('enter'))
                ->with('message', 'Cuenta creada correctamente. Ya puedes iniciar sesión.');
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
}
