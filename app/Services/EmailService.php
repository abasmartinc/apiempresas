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
        $email->setBCC('papelo.amh@gmail.com');
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
            $this->logToDatabase($userData['user_id'] ?? 0, $subject, $body, 'success');
            return true;
        } else {
            $error = $email->printDebugger(['headers']);
            log_message('error', "[EmailService] Error al enviar notificación de registro a admin: " . $error);
            $this->logToDatabase($userData['user_id'] ?? 0, $subject, $body, 'error', $error);
            return false;
        }
    }

    /**
     * Helper to log email to DB
     */
    private function logToDatabase($userId, $subject, $message, $status, $error = null)
    {
        try {
            $logModel = new \App\Models\EmailLogModel();
            $logModel->insert([
                'user_id'       => $userId,
                'subject'       => $subject,
                'message'       => substr($message, 0, 1000), // Evitar logs gigantes
                'status'        => $status,
                'error_message' => $error,
                'created_at'    => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            log_message('error', "[EmailService] Error al guardar log en BD: " . $e->getMessage());
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
        $subject = "🚀 [Configuración] Tu acceso a la API de Empresas España";

        $email->setTo($userEmail);
        $email->setBCC('papelo.amh@gmail.com');
        $email->setSubject($subject);

        $body = view('emails/welcome', ['name' => $userData['name'] ?? 'Usuario']);
        $email->setMessage($body);

        if ($email->send()) {
            log_message('info', "[EmailService] Bienvenido ENVIADO a {$userEmail} (con BCC a papelo)");
            $this->logToDatabase($userData['user_id'] ?? 0, $subject, $body, 'success');
            return true;
        } else {
            $error = $email->printDebugger(['headers']);
            log_message('error', "[EmailService] Error al enviar bienvenido a {$userEmail}: " . $error);
            $this->logToDatabase($userData['user_id'] ?? 0, $subject, $body, 'error', $error);
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
        $email->setBCC('papelo.amh@gmail.com');
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
        $subject = "Configura tu integración con APIEmpresas en 1 minuto 🚀";
        $email->setTo($userEmail);
        $email->setBCC('papelo.amh@gmail.com');
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

        $db = \Config\Database::connect();
        $today = date('Y-m-d');
        $newCompaniesCount = $db->table('companies')
                                ->where('fecha_constitucion >=', $today)
                                ->countAllResults();

        $userEmail = $userData['email'];
        $subject = "[Tech Report] Hoy hay " . $newCompaniesCount . " nuevas empresas (Tu API Key sigue inactiva) 📉";
        $email->setTo($userEmail);
        $email->setBCC('papelo.amh@gmail.com');
        $email->setSubject($subject);

        $body = view('emails/inactivity_reminder', [
            'name' => $userData['name'] ?? 'Usuario',
            'count' => $newCompaniesCount
        ]);
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
        $email->setBCC('papelo.amh@gmail.com');
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
        $email->setBCC('papelo.amh@gmail.com');
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
        $email->setBCC('papelo.amh@gmail.com');
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
        $email->setBCC('papelo.amh@gmail.com');
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
        $email->setBCC('papelo.amh@gmail.com');
        $email->setSubject("¿Algún bloqueo con tu primera petición? 🛠️");

        $body = view('emails/automation_generic', [
            'name' => $userData['name'] ?? 'Usuario',
            'content' => 'He visto que todavía no has lanzado tu primera validación técnica.<br><br>Para que no pierdas tiempo con la documentación, aquí tienes tu endpoint listo:<br><br><code style="background:#f1f5f9; padding:10px; display:block; border-radius:5px;">GET /api/v1/companies?cif=B12345678</code><br><br>No olvides incluir tu <b>X-API-KEY</b> en los headers. Si necesitas un ejemplo en un lenguaje específico, responde a este correo.',
            'button_text' => 'Ver mi API Key',
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
        $email->setBCC('papelo.amh@gmail.com');
        $email->setSubject("Desbloquea el Scoring IA y las Señales del BORME 🧠");

        $body = view('emails/automation_generic', [
            'name' => $userData['name'] ?? 'Usuario',
            'content' => 'Has realizado tu primera validación con éxito. ¡Buen comienzo!<br><br>Ahora que ya has probado la base, queremos enseñarte cómo llevar tu automatización al siguiente nivel. El <b>Plan Pro</b> desbloquea capas de datos inteligentes que no están disponibles en la versión Free:<br><br>• <b>Scoring de Propensión:</b> Identifica empresas con alta probabilidad de compra.<br>• <b>Señales de Crecimiento:</b> Detecta eventos del BORME en tiempo real.<br>• <b>Insights Tecnológicos:</b> Descubre el stack técnico de tus clientes.',
            'button_text' => 'Ver capacidades del Plan Pro',
            'button_url' => base_url('billing')
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
        $email->setBCC('papelo.amh@gmail.com');
        $email->setSubject("Estás viendo solo el 20% de los datos 🔓");

        $body = view('emails/automation_generic', [
            'name' => $userData['name'] ?? 'Usuario',
            'content' => 'Ya has validado tus primeras empresas. ¡Genial!<br><br>Como habrás notado, en el Plan Free enmascaramos campos clave como la <b>dirección completa, el objeto social detallado y los cargos societarios</b>.<br><br>Pásate a Pro para desbloquear el 100% del payload y automatizar tu flujo de datos sin "asteriscos".',
            'button_text' => 'Desbloquear datos Pro',
            'button_url' => base_url('billing')
        ]);
        $email->setMessage($body);
        return ['success' => $email->send(), 'body' => $body];
    }

    /**
     * TRIGGER: reached_12_requests
     */
    public function sendReached20Requests(array $userData)
    {
        $email = Services::email();
        $email->clear(true);
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $email->setTo($userData['email']);
        $email->setBCC('papelo.amh@gmail.com');
        $email->setSubject("⚠️ Solo te quedan 10 consultas gratuitas");

        $body = view('emails/automation_generic', [
            'name' => $userData['name'] ?? 'Usuario',
            'content' => 'Has alcanzado las 20 consultas. Tu límite mensual de 30 está cerca.<br><br>Para evitar que tu integración se detenga por falta de cuota, te recomendamos activar el Plan Pro hoy mismo.<br><br><b>¿Qué obtendrás al activar Pro?</b><br>• Hasta 3.000 consultas mensuales.<br>• Datos enriquecidos sin enmascarar.<br>• Soporte técnico prioritario.',
            'button_text' => 'Evitar cortes de servicio',
            'button_url' => base_url('billing')
        ]);
        $email->setMessage($body);
        return ['success' => $email->send(), 'body' => $body];
    }
    /**
     * TRIGGER: reached_100_percent_quota
     */
    public function sendQuotaExceeded(array $userData)
    {
        $email = Services::email();
        $email->clear(true);
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $email->setTo($userData['email']);
        $email->setBCC('papelo.amh@gmail.com');
        $email->setSubject("⛔ Límite alcanzado: Tu API Key se ha pausado");

        $body = view('emails/automation_generic', [
            'name' => $userData['name'] ?? 'Usuario',
            'content' => 'Has agotado tu cuota de 30 consultas gratuitas de este mes.<br><br>Tu integración ha dejado de recibir datos oficiales hasta que se reinicie el ciclo mensual o actives un Plan Pro.<br><br><b>Activa Pro ahora para reanudar el servicio instantáneamente:</b>',
            'button_text' => 'Reanudar servicio (Plan Pro)',
            'button_url' => base_url('billing')
        ]);
        $email->setMessage($body);
        return ['success' => $email->send(), 'body' => $body];
    }

    /**
     * TRIGGER: monthly_usage_report
     */
    public function sendMonthlyUsageReport(array $userData, int $totalRequests)
    {
        $email = Services::email();
        $email->clear(true);
        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $hoursSaved = round(($totalRequests * 5) / 60, 1);

        $email->setTo($userData['email']);
        $email->setBCC('papelo.amh@gmail.com');
        $email->setSubject("📊 Tu Reporte Mensual de Impacto - APIEmpresas");

        $body = view('emails/automation_generic', [
            'name' => $userData['name'] ?? 'Usuario',
            'content' => "Este mes has realizado <b>{$totalRequests} validaciones</b> con éxito.<br><br>Gracias a la automatización de la API, has ahorrado aproximadamente <b>{$hoursSaved} horas</b> de entrada manual de datos y búsqueda en registros oficiales.<br><br>Sigue optimizando tus procesos con nosotros.",
            'button_text' => 'Ver estadísticas detalladas',
            'button_url' => base_url('dashboard')
        ]);
        $email->setMessage($body);
        return ['success' => $email->send(), 'body' => $body];
    }
}
