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

    /**
     * POST /api/usuario/activar-avisos
     *
     * Activa las alertas del BORME sin salir de donde esté el usuario.
     * Existe porque el aviso "tienes los avisos desactivados" mandaba al perfil,
     * y obligar a navegar, buscar una casilla y guardar para arreglar algo que
     * acabas de decirle que está mal es perder por el camino a la mayoría.
     *
     * Pone `alerts_borme = 1` explícito: eso manda sobre `unsuscribe`, que es
     * justo para lo que existen los tres estados (sí a las alertas aunque no
     * quiera marketing). Es una acción deliberada del usuario, así que el
     * consentimiento es limpio.
     */
    public function activarAvisos()
    {
        $response = $this->response->setHeader('Cache-Control', 'no-store');
        $userId = (int) (session('user_id') ?? 0);

        if ($userId <= 0) {
            return $response->setStatusCode(401)->setJSON([
                'ok'      => false,
                'message' => 'Debes iniciar sesión.',
            ]);
        }

        try {
            $this->userModel->update($userId, ['alerts_borme' => 1]);
        } catch (\Throwable $e) {
            log_message('error', 'activarAvisos(' . $userId . '): ' . $e->getMessage());
            return $response->setStatusCode(500)->setJSON(['ok' => false]);
        }

        return $response->setJSON(['ok' => true, 'alerts_on' => true]);
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
            return redirect()->back()->withInput()->with('error', lang('Messages.flash_49'));
        }

        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $company = $this->request->getPost('company');

        // Preferencia de alertas: casilla marcada = 1, desmarcada = 0. Nunca vuelve a
        // NULL, porque a partir de aquí el usuario ya se ha pronunciado.
        $datos = [
            'name'         => $name,
            'email'        => $email,
            'company'      => $company,
            'alerts_borme' => $this->request->getPost('alerts_borme') ? 1 : 0,
        ];

        $this->userModel->update($userId, $datos);

        // Actualizar datos en sesión
        session()->set([
            'user_name' => $name,
            'user_email' => $email
        ]);

        return redirect()->to('profile')->with('message', lang('Messages.flash_50'));
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
