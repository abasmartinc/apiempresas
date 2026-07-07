<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profile extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->to('login');
        }

        $user = $this->userModel->find($userId);

        return $this->renderView('profile', [
            'user' => $user,
            'title' => 'Mi Perfil'
        ]);
    }

    public function update()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->to('login');
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[3]',
            'email' => "required|valid_email|is_unique[users.email,id,{$userId}]"
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('error', 'Por favor, revisa los campos. Es posible que el correo ya esté en uso.');
        }

        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $company = $this->request->getPost('company');

        $this->userModel->update($userId, [
            'name' => $name,
            'email' => $email,
            'company' => $company
        ]);

        // Actualizar datos en sesión
        session()->set([
            'user_name' => $name,
            'user_email' => $email
        ]);

        return redirect()->to('profile')->with('message', 'Perfil actualizado correctamente.');
    }

    public function password()
    {
        $userId = session('user_id');
        if (!$userId) {
            return redirect()->to('login');
        }

        $user = $this->userModel->find($userId);

        $currentPassword = (string)$this->request->getPost('current_password');
        $newPassword = (string)$this->request->getPost('new_password');
        $confirmPassword = (string)$this->request->getPost('confirm_password');

        if (!password_verify($currentPassword, $user->password_hash)) {
            return redirect()->back()->with('error_password', 'La contraseña actual no es correcta.');
        }

        if (strlen($newPassword) < 6) {
            return redirect()->back()->with('error_password', 'La nueva contraseña debe tener al menos 6 caracteres.');
        }

        if ($newPassword !== $confirmPassword) {
            return redirect()->back()->with('error_password', 'Las contraseñas no coinciden.');
        }

        $this->userModel->update($userId, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);

        return redirect()->to('profile')->with('message_password', 'Contraseña actualizada correctamente.');
    }
}
