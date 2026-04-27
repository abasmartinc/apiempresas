<?php

namespace App\Services;

use CodeIgniter\Config\Services;

class EmailService
{
    /**
     * Send a notification email to the admin for a successful payment.
     */
    public function sendPaymentNotification(array $data)
    {
        $email = Services::email();
        $email->clear(true); 

        // Forzar remitente desde configuración o valor seguro
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $adminEmail = 'papelo.amh@gmail.com';
        $subject = "💰 ¡Nuevo Pago Recibido! - " . ($data['invoice_number'] ?? '');
        
        $email->setTo($adminEmail);
        $email->setSubject($subject);

        $body = view('emails/payment_notification', [
            'invoice'   => $data['invoice'],
            'customer'  => $data['customer_name'] ?? 'Cliente',
            'email'     => $data['customer_email'] ?? 'N/A',
            'plan'      => $data['plan_name'] ?? 'Plan API',
            'amount'    => $data['amount'] ?? '0.00',
            'currency'  => $data['currency'] ?? 'EUR',
            'subject'   => $subject
        ]);

        $email->setMessage($body);

        if ($email->send()) {
            log_message('info', "[EmailService] Notificación de pago ENVIADA a {$adminEmail}. Remitente: {$fromEmail}");
            return true;
        } else {
            log_message('error', "[EmailService] Error al enviar notificación de pago a {$adminEmail}: " . $email->printDebugger(['headers']));
            return false;
        }
    }

    /**
     * Send the invoice PDF to the user after a successful payment.
     */
    public function sendInvoiceToUser(array $data)
    {
        $email = Services::email();
        $email->clear(true); 
        
        // Forzar remitente
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $userEmail = $data['customer_email'];
        $subject   = "🧾 Tu factura de APIEmpresas.es - " . ($data['invoice_number'] ?? '');

        $email->setTo($userEmail);
        $email->setSubject($subject);

        $body = view('emails/user_invoice', [
            'name'           => $data['customer_name'] ?? 'Cliente',
            'plan_name'      => $data['plan_name'] ?? 'Plan API',
            'amount'         => $data['amount'] ?? '0.00',
            'currency'       => $data['currency'] ?? 'EUR',
            'invoice_number' => $data['invoice_number'] ?? '',
        ]);

        $email->setMessage($body);

        // Adjuntar PDF si existe
        if (!empty($data['pdf_path'])) {
            // Intentar con ROOTPATH y verificar existencia real
            $relativePath = ltrim($data['pdf_path'], '/\\');
            $fullPath = ROOTPATH . $relativePath;
            
            if (file_exists($fullPath)) {
                $email->attach($fullPath, 'attachment', 'factura_' . $data['invoice_number'] . '.pdf');
                log_message('info', "[EmailService] PDF localizado y adjuntado: " . $fullPath);
            } else {
                // Fallback a FCPATH por si ROOTPATH no es lo que esperamos
                $fullPath = FCPATH . $relativePath;
                if (file_exists($fullPath)) {
                    $email->attach($fullPath, 'attachment', 'factura_' . $data['invoice_number'] . '.pdf');
                    log_message('info', "[EmailService] PDF localizado y adjuntado (vía FCPATH): " . $fullPath);
                } else {
                    log_message('error', "[EmailService] No se encontró el archivo PDF en ninguna ruta conocida para: " . $relativePath);
                }
            }
        }

        if ($email->send()) {
            log_message('info', "[EmailService] Factura ENVIADA a {$userEmail}. Remitente: {$fromEmail}");
            return true;
        } else {
            log_message('error', "[EmailService] Error al enviar factura a {$userEmail}: " . $email->printDebugger(['headers', 'subject']));
            return false;
        }
    }

    /**
     * Send a notification email to the admin for a new user registration.
     */
    public function sendRegistrationAdminNotification(array $userData)
    {
        $email = Services::email();
        $email->clear(true);

        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $adminEmail = 'papelo.amh@gmail.com';
        $subject = "🆕 Nuevo registro de usuario: " . ($userData['name'] ?? 'Usuario');

        $email->setTo($adminEmail);
        $email->setSubject($subject);

        $body = view('emails/admin_notification', [
            'name'     => $userData['name'] ?? 'N/A',
            'company'  => $userData['company'] ?? 'No especificada',
            'email'    => $userData['email'] ?? 'N/A',
            'user_id'  => $userData['user_id'] ?? '?'
        ]);

        $email->setMessage($body);

        if ($email->send()) {
            log_message('info', "[EmailService] Notificación de registro ENVIADA a admin ({$adminEmail})");
            return true;
        } else {
            log_message('error', "[EmailService] Error al enviar notificación de registro a admin: " . $email->printDebugger(['headers']));
            return false;
        }
    }

    /**
     * Send a welcome email to the new user.
     */
    public function sendWelcomeEmail(array $userData)
    {
        $email = Services::email();
        $email->clear(true);

        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $userEmail = $userData['email'];
        $subject = "¡Bienvenido a APIEmpresas.es!";

        $email->setTo($userEmail);
        $email->setSubject($subject);

        $body = view('emails/welcome', ['name' => $userData['name'] ?? 'Usuario']);
        $email->setMessage($body);

        if ($email->send()) {
            log_message('info', "[EmailService] Bienvenido ENVIADO a {$userEmail}");
            return true;
        } else {
            log_message('error', "[EmailService] Error al enviar bienvenido a {$userEmail}: " . $email->printDebugger(['headers']));
            return false;
        }
    }

    /**
     * Send a password setup email for quick registrations.
     */
    public function sendSetPasswordEmail(string $userEmail, string $token)
    {
        $email = Services::email();
        $email->clear(true);

        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $subject = "Establece tu contraseña - APIEmpresas.es";

        $email->setTo($userEmail);
        $email->setSubject($subject);

        $body = view('emails/set_password_email', ['token' => $token]);
        $email->setMessage($body);

        if ($email->send()) {
            log_message('info', "[EmailService] Establecer contraseña ENVIADO a {$userEmail}");
            return true;
        } else {
            log_message('error', "[EmailService] Error al enviar establecer contraseña a {$userEmail}: " . $email->printDebugger(['headers']));
            return false;
        }
    }

    /**
     * Send a quick start prompt email (5 min after register).
     */
    public function sendQuickStartPrompt(array $userData)
    {
        $email = Services::email();
        $email->clear(true);
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $userEmail = $userData['email'];
        $subject = "Prueba la API en menos de 30 segundos ⚡";
        $email->setTo($userEmail);
        $email->setSubject($subject);

        $body = view('emails/quick_start', ['name' => $userData['name'] ?? 'Usuario']);
        $email->setMessage($body);
        return $email->send();
    }

    /**
     * Send an inactivity reminder email (24h without requests).
     */
    public function sendInactivityReminder(array $userData)
    {
        $email = Services::email();
        $email->clear(true);
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $userEmail = $userData['email'];
        $subject = "Aún no has probado la API - APIEmpresas.es";
        $email->setTo($userEmail);
        $email->setSubject($subject);

        $body = view('emails/inactivity_reminder', ['name' => $userData['name'] ?? 'Usuario']);
        $email->setMessage($body);
        return $email->send();
    }

    /**
     * Send a success email after the first successful request.
     */
    public function sendFirstRequestMilestone(array $userData)
    {
        $email = Services::email();
        $email->clear(true);
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $userEmail = $userData['email'];
        $subject = "Ya estás usando la API ⚡";
        $email->setTo($userEmail);
        $email->setSubject($subject);

        $body = view('emails/first_request_success', ['name' => $userData['name'] ?? 'Usuario']);
        $email->setMessage($body);
        return $email->send();
    }

    /**
     * Send Quota Warning (already exists above, just ensuring context)
     */
    public function sendQuotaWarning(array $userData, int $percent)
    {
        // ... (existing code)
    }

    /**
     * EXCEL SEQUENCE: Day 1 - New Companies Detected
     */
    public function sendExcelSequenceDay1(array $userData)
    {
        $email = Services::email();
        $email->clear(true);
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $email->setTo($userData['email']);
        $email->setSubject("Nuevas empresas detectadas hoy ⚡");

        $body = view('emails/excel_day1_new_companies', ['name' => $userData['name'] ?? 'Usuario']);
        $email->setMessage($body);
        return $email->send();
    }

    /**
     * EXCEL SEQUENCE: Day 2 - Case Study
     */
    public function sendExcelSequenceDay2(array $userData)
    {
        $email = Services::email();
        $email->clear(true);
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $email->setTo($userData['email']);
        $email->setSubject("Cómo otros están consiguiendo clientes 💎");

        $body = view('emails/excel_day2_case_study', ['name' => $userData['name'] ?? 'Usuario']);
        $email->setMessage($body);
        return $email->send();
    }

    /**
     * EXCEL SEQUENCE: Day 3 - Urgency
     */
    public function sendExcelSequenceDay3(array $userData)
    {
        $email = Services::email();
        $email->clear(true);
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $email->setTo($userData['email']);
        $email->setSubject("Estás perdiendo oportunidades ⚠️");

        $body = view('emails/excel_day3_urgency', ['name' => $userData['name'] ?? 'Usuario']);
        $email->setMessage($body);
    }

    /**
     * TRIGGER: no_requests_15min
     */
    public function sendNoUsage15Min(array $userData)
    {
        $email = Services::email();
        $email->clear(true);
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $email->setTo($userData['email']);
        $email->setSubject("¿Probaste ya tu primera validación?");

        $body = view('emails/automation_generic', [
            'name' => $userData['name'] ?? 'Usuario',
            'content' => 'Puedes validar una empresa desde tu panel sin integrar nada.<br><br>Introduce un CIF o nombre y verás qué datos puedes automatizar con la API.',
            'button_text' => 'Probar ahora',
            'button_url' => base_url('dashboard')
        ]);
        $email->setMessage($body);
        return ['success' => $email->send(), 'body' => $body];
    }

    /**
     * TRIGGER: one_request_inactive_1h
     */
    public function sendOneUsageInactive1H(array $userData)
    {
        $email = Services::email();
        $email->clear(true);
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $email->setTo($userData['email']);
        $email->setSubject("Haz 2–3 validaciones más antes de decidir");

        $body = view('emails/automation_generic', [
            'name' => $userData['name'] ?? 'Usuario',
            'content' => 'Ya has probado APIEmpresas.<br><br>Te recomendamos validar 2–3 empresas más para comprobar la calidad de los datos y ver si encaja con tu caso.',
            'button_text' => 'Seguir probando',
            'button_url' => base_url('dashboard')
        ]);
        $email->setMessage($body);
        return ['success' => $email->send(), 'body' => $body];
    }

    /**
     * TRIGGER: reached_5_requests
     */
    public function sendReached5Requests(array $userData)
    {
        $email = Services::email();
        $email->clear(true);
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $email->setTo($userData['email']);
        $email->setSubject("Ya estás viendo el valor real de la API");

        $body = view('emails/automation_generic', [
            'name' => $userData['name'] ?? 'Usuario',
            'content' => 'Has empezado a usar APIEmpresas en condiciones reales.<br><br>El siguiente paso es activar Pro para validar empresas sin restricciones y evitar interrupciones cuando lo integres en tu sistema.',
            'button_text' => 'Activar Pro',
            'button_url' => base_url('billing')
        ]);
        $email->setMessage($body);
        return ['success' => $email->send(), 'body' => $body];
    }

    /**
     * TRIGGER: reached_12_requests
     */
    public function sendReached12Requests(array $userData)
    {
        $email = Services::email();
        $email->clear(true);
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $email->setTo($userData['email']);
        $email->setSubject("Estás cerca del límite gratuito");

        $body = view('emails/automation_generic', [
            'name' => $userData['name'] ?? 'Usuario',
            'content' => 'Te quedan pocas consultas gratuitas.<br><br>Activa Pro antes de quedarte sin acceso y sigue validando empresas sin restricciones.',
            'button_text' => 'Activar Pro ahora',
            'button_url' => base_url('billing')
        ]);
        $email->setMessage($body);
        return ['success' => $email->send(), 'body' => $body];
    }
}
