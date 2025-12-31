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

        // Validación básica
        $rules = [
            'name'    => 'required|min_length[3]',
            'email'   => 'required|valid_email',
            'subject' => 'required|min_length[4]',
            'message' => 'required|min_length[10]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('contact_error', 'Por favor revisa los campos del formulario.');
        }

        // Datos del formulario
        $name    = $this->request->getPost('name');
        $email   = $this->request->getPost('email');
        $type    = $this->request->getPost('type');
        $company = $this->request->getPost('company');
        $subject = $this->request->getPost('subject');
        $message = $this->request->getPost('message');

        // Construir cuerpo del email
        $body = "
        <strong>Nuevo mensaje desde el formulario de contacto</strong><br><br>

        <strong>Nombre:</strong> {$name}<br>
        <strong>Email:</strong> {$email}<br>
        <strong>Empresa:</strong> " . ($company ?: '—') . "<br>
        <strong>Tipo de consulta:</strong> {$type}<br><br>

        <strong>Mensaje:</strong><br>
        <pre style='font-family:Arial,sans-serif; white-space:pre-wrap;'>{$message}</pre>
        ";

        $emailService = Services::email();

        $emailService->setFrom('no-reply@apiempresas.es', 'APIEmpresas');
        $emailService->setTo('soporte@apiempresas.es');
        $emailService->setReplyTo($email, $name);
        $emailService->setSubject('[Contacto Web] ' . $subject);
        $emailService->setMessage($body);
        $emailService->setMailType('html');

        if (! $emailService->send()) {
            log_message('error', 'Error enviando formulario de contacto: ' . $emailService->printDebugger(['headers']));
            return redirect()->back()
                ->withInput()
                ->with('contact_error', 'No se pudo enviar el mensaje. Inténtalo de nuevo más tarde.');
        }

        return redirect()->back()->with('contact_success', 'Tu mensaje se ha enviado correctamente. Te responderemos lo antes posible.');
    }
}

