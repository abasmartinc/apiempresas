<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Unsubscribe extends Controller
{
    /**
     * Show the unsubscribe confirmation page.
     * 
     * @param string $hash Secure hash of the email
     */
    public function index($hash)
    {
        $email = $this->request->getGet('email');
        
        if (!$email || !$hash) {
            return redirect()->to(site_url())->with('error', 'Enlace de baja inválido.');
        }

        // Validate hash
        $expectedHash = hash_hmac('sha256', $email, env('encryption.key', 'apiempresas-secret-key'));
        
        if ($hash !== $expectedHash) {
            log_message('warning', "[Unsubscribe] Intento de baja con hash inválido para: {$email}");
            return redirect()->to(site_url())->with('error', 'Enlace de baja inválido o expirado.');
        }

        return view('unsubscribe/confirm', [
            'email' => $email,
            'hash'  => $hash,
            'title' => 'Confirmar baja | APIEmpresas'
        ]);
    }

    /**
     * Process the unsubscribe confirmation.
     */
    public function confirm()
    {
        $email = $this->request->getPost('email');
        $hash  = $this->request->getPost('hash');

        if (!$email || !$hash) {
            return redirect()->to(site_url());
        }

        // Validate hash again for security
        $expectedHash = hash_hmac('sha256', $email, env('encryption.key', 'apiempresas-secret-key'));
        
        if ($hash !== $expectedHash) {
            return redirect()->to(site_url())->with('error', 'Error de seguridad al procesar la baja.');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if ($user) {
            $userModel->update($user->id, ['unsuscribe' => 1]);
            log_message('info', "[Unsubscribe] Usuario {$email} se ha dado de baja correctamente.");
            
            return view('unsubscribe/success', [
                'title' => 'Baja confirmada | APIEmpresas'
            ]);
        }

        return redirect()->to(site_url())->with('error', 'Usuario no encontrado.');
    }
}
