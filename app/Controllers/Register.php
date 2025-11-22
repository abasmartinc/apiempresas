<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Register extends BaseController
{
    /** @var UserModel */
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Muestra el formulario de registro
     */
    public function index()
    {
        // Si viene de un redirect con errores, usamos el de sesión
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
        // Reglas de validación
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

        if (! $this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $email = strtolower(trim($this->request->getPost('email')));

        // Generar API key robusta (64 chars hex)
        $apiKey = bin2hex(random_bytes(32));

        $data = [
            'name'          => trim($this->request->getPost('name')),
            'company'       => trim((string) $this->request->getPost('company')),
            'email'         => $email,
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'api_key'       => $apiKey,
            'is_active'     => 1,
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        try {
            $this->userModel->insert($data);

            return redirect()
                ->to(site_url('enter'))
                ->with('message', 'Cuenta creada correctamente. Ya puedes iniciar sesión.');
        } catch (\Throwable $e) {
            log_message('error', '[REGISTER] Error al crear usuario: {0}', [$e->getMessage()]);

            return redirect()
                ->back()
                ->withInput()
                ->with('validation', $this->validator)
                ->with('error', 'Ha ocurrido un error al crear tu cuenta. Inténtalo de nuevo.');
        }
    }
}
