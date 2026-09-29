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
            return redirect()->to(site_url())->with('error', lang('Messages.flash_77'));
        }

        // Validate hash
        $expectedHash = hash_hmac('sha256', $email, env('encryption.key', 'apiempresas-secret-key'));
        
        if ($hash !== $expectedHash) {
            log_message('warning', "[Unsubscribe] Intento de baja con hash inválido para: {$email}");
            return redirect()->to(site_url())->with('error', lang('Messages.flash_78'));
        }

        return view('unsubscribe/confirm', [
            'email' => $email,
            'hash'  => $hash,
            'title' => 'Confirmar baja | APIEmpresas'
        ]);
    }

    /**
     * Baja de UN CLIC solo de las alertas del Registro Mercantil.
     *
     * Sin pantalla de confirmación a propósito: es la salida obligatoria de un correo
     * de alertas y cuanto menos fricción tenga, mejor. No toca `unsuscribe`, así que
     * quien quiera seguir recibiendo el resto de correos los sigue recibiendo.
     */
    public function alerts($hash)
    {
        $email = $this->request->getGet('email');

        if (!$email || !$hash) {
            return redirect()->to(site_url());
        }

        $expectedHash = hash_hmac('sha256', 'alerts:' . $email, env('encryption.key', 'apiempresas-secret-key'));

        if (!hash_equals($expectedHash, $hash)) {
            log_message('warning', "[Unsubscribe] Hash de alertas inválido para: {$email}");
            return redirect()->to(site_url())->with('error', lang('Messages.flash_78'));
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if ($user) {
            $userModel->update($user->id, ['alerts_borme' => 0]);
            log_message('info', "[Unsubscribe] {$email} ha desactivado las alertas del BORME.");
        }

        return view('unsubscribe/success', [
            'title'   => 'Alertas desactivadas | APIEmpresas',
            'message' => 'Has dejado de recibir alertas del Registro Mercantil. El resto de tus preferencias no ha cambiado.'
        ]);
    }

    /**
     * Baja de UN CLIC desde la cabecera List-Unsubscribe (RFC 8058).
     *
     * Gmail, Yahoo y Apple Mail muestran un botón "Cancelar suscripción" junto al
     * remitente y, al pulsarlo, hacen un POST a esta URL con el cuerpo
     * "List-Unsubscribe=One-Click". No hay pantalla intermedia: la baja es inmediata.
     * Está fuera del CSRF (Config\Filters) porque el POST no viene de nuestra web; lo
     * protege el hash HMAC del correo, igual que el enlace del pie.
     */
    public function oneClick($hash)
    {
        $email = (string) $this->request->getGet('email');
        $expectedHash = hash_hmac('sha256', $email, env('encryption.key', 'apiempresas-secret-key'));

        if ($email === '' || !$hash || !hash_equals($expectedHash, (string) $hash)) {
            log_message('warning', "[Unsubscribe] Baja de un clic con hash inválido para: {$email}");
            return $this->response->setStatusCode(400)->setBody('Invalid unsubscribe link');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();
        if ($user && (int) ($user->unsuscribe ?? 0) !== 1) {
            $userModel->update($user->id, ['unsuscribe' => 1]);
            log_message('info', "[Unsubscribe] {$email} se ha dado de baja con un clic (List-Unsubscribe).");
        }

        // El cliente de correo no enseña esta respuesta: basta con un 200
        return $this->response->setStatusCode(200)->setBody('OK');
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
            return redirect()->to(site_url())->with('error', lang('Messages.flash_79'));
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

        return redirect()->to(site_url())->with('error', lang('Messages.flash_80'));
    }
}
