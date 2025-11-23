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
        $data = [
            'message' => session('message'),
            'error'   => session('error'),
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

        if (! $this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Por favor revisa los datos introducidos.');
        }

        $email    = strtolower(trim($this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');

        // Buscar usuario por email
        $user = $this->userModel
            ->where('email', $email)
            ->first();

        if (! $user) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Credenciales incorrectas.');
        }

        // Comprobar si está activo
        if (isset($user->is_active) && (int) $user->is_active !== 1) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Tu cuenta está inactiva. Contacta con soporte.');
        }

        $hash = (string) ($user->password_hash ?? '');

        // Depuración de verificación
        $verifyOk = ($hash !== '' && password_verify($password, $hash));

        if (! $verifyOk) {
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
            'user_id'    => $user->id,
            'user_email' => $user->email,
            'user_name'  => $user->name ?? '',
            'logged_in'  => true,
        ]);

        return redirect()->to(site_url('dashboard'));
    }

    /**
     * Cierra la sesión de usuario
     */
    public function logout()
    {
        session()->destroy();

        return redirect()
            ->to(site_url('enter'))
            ->with('message', 'Has cerrado sesión correctamente.');
    }
}
