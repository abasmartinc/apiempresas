<?php

namespace App\Controllers;


use CodeIgniter\Config\Services;

class Contact extends BaseController
{
    public function index()
    {
        return view('contact');
    }

    public function send()
    {
        helper(['form', 'url']);

        // Validación flexible
        $rules = [
            'name'    => 'required|min_length[3]',
            'email'   => 'required|valid_email',
            'message' => 'required', // Quitamos min_length para dar libertad total
        ];

        if (! $this->validate($rules)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Por favor revisa los campos del formulario.'
                ]);
            }
            return redirect()->back()
                ->withInput()
                ->with('contact_error', 'Por favor revisa los campos del formulario.');
        }

        // Validación Cloudflare Turnstile
        $turnstileResponse = $this->request->getPost('cf-turnstile-response');
        $turnstileSecret = env('TURNSTILE_SECRET_KEY');

        if (!empty($turnstileSecret) && !empty(env('TURNSTILE_SITE_KEY'))) {
            if (empty($turnstileResponse)) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Por favor, completa la verificación de seguridad (Captcha).']);
                }
                return redirect()->back()->withInput()->with('contact_error', 'Por favor, completa la verificación de seguridad (Captcha).');
            }

            // Llamar a la API de validación
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'secret'   => $turnstileSecret,
                'response' => $turnstileResponse,
                'remoteip' => $this->request->getIPAddress()
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $verifyResponse = curl_exec($ch);
            curl_close($ch);

            $verifyData = json_decode($verifyResponse, true);

            if (!$verifyData || !isset($verifyData['success']) || !$verifyData['success']) {
                log_message('error', 'Turnstile validation failed: ' . print_r($verifyData, true));
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Verificación de seguridad fallida. Eres un bot?']);
                }
                return redirect()->back()->withInput()->with('contact_error', 'Verificación de seguridad fallida. Inténtalo de nuevo.');
            }
        }

        // Datos del formulario
        $name    = $this->request->getPost('name');
        $email   = $this->request->getPost('email');
        $phone   = $this->request->getPost('phone');
        $type    = $this->request->getPost('type') ?? 'Consulta General';
        $company = $this->request->getPost('company');
        $subject = $this->request->getPost('subject') ?? 'Nueva consulta desde Ayuda Dashboard';
        $message = $this->request->getPost('message');

        // Construir cuerpo del email
        $body = "
        <strong>Nuevo mensaje desde el Centro de Ayuda</strong><br><br>

        <strong>Nombre:</strong> {$name}<br>
        <strong>Email:</strong> {$email}<br>
        <strong>Teléfono:</strong> " . ($phone ?: '—') . "<br>
        <strong>Empresa:</strong> " . ($company ?: '—') . "<br>
        <strong>Tipo:</strong> {$type}<br><br>

        <strong>Mensaje:</strong><br>
        <pre style='font-family:Arial,sans-serif; white-space:pre-wrap;'>{$message}</pre>
        ";

        $emailService = Services::email();

        $emailService->setFrom('no-reply@apiempresas.es', 'APIEmpresas Support');
        $emailService->setTo('soporte@apiempresas.es');
        $emailService->setReplyTo($email, $name);
        $emailService->setSubject('[AYUDA DASHBOARD] ' . $subject);
        $emailService->setMessage($body);
        $emailService->setMailType('html');

        if (! $emailService->send()) {
            log_message('error', 'Error enviando formulario de contacto: ' . $emailService->printDebugger(['headers']));
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se pudo enviar el mensaje. Inténtalo de nuevo más tarde.'
                ]);
            }

            return redirect()->back()
                ->withInput()
                ->with('contact_error', 'No se pudo enviar el mensaje. Inténtalo de nuevo más tarde.');
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tu mensaje se ha enviado correctamente. Te responderemos lo antes posible.'
            ]);
        }

        return redirect()->back()->with('contact_success', 'Tu mensaje se ha enviado correctamente. Te responderemos lo antes posible.');
    }
}

