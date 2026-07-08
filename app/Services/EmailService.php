<?php

namespace App\Services;

use CodeIgniter\Config\Services;
use App\Models\EmailTemplateModel;

class EmailService
{
    /**
     * Send a notification email to the admin for a successful payment.
     */
    public function sendPaymentNotification(array $data)
    {
        $adminEmail = 'papelo.amh@gmail.com';
        $templateData = [
            'invoice_number' => $data['invoice_number'] ?? '',
            'customer'       => $data['customer_name'] ?? 'Cliente',
            'email'          => $data['customer_email'] ?? 'N/A',
            'plan'           => $data['plan_name'] ?? 'Plan API',
            'amount'         => $data['amount'] ?? '0.00',
            'currency'       => $data['currency'] ?? 'EUR',
            'invoice'        => $data['invoice'] ?? ''
        ];

        return $this->sendTemplateEmail('payment_notification', $templateData, $adminEmail);
    }

    /**
     * Send the invoice PDF to the user after a successful payment.
     */
    public function sendInvoiceToUser(array $data)
    {
        $userEmail = $data['customer_email'];
        $templateData = [
            'name'           => $data['customer_name'] ?? 'Cliente',
            'plan_name'      => $data['plan_name'] ?? 'Plan API',
            'amount'         => $data['amount'] ?? '0.00',
            'currency'       => $data['currency'] ?? 'EUR',
            'invoice_number' => $data['invoice_number'] ?? '',
        ];

        $attachments = [];
        if (!empty($data['pdf_path'])) {
            $relativePath = ltrim($data['pdf_path'], '/\\');
            $fullPath = ROOTPATH . $relativePath;
            if (file_exists($fullPath)) {
                $attachments[] = [
                    'path' => $fullPath,
                    'name' => 'factura_' . ($data['invoice_number'] ?? 'doc') . '.pdf'
                ];
            }
        }

        return $this->sendTemplateEmail('user_invoice', $templateData, $userEmail, ['papelo.amh@gmail.com'], $attachments);
    }

    /**
     * Send a notification email to the admin for a new user registration.
     */
    public function sendRegistrationAdminNotification(array $userData)
    {
        $adminEmail = 'papelo.amh@gmail.com';
        $templateData = [
            'name'    => $userData['name'] ?? 'N/A',
            'company' => $userData['company'] ?? 'No especificada',
            'email'   => $userData['email'] ?? 'N/A',
            'user_id' => $userData['user_id'] ?? '?'
        ];

        return $this->sendTemplateEmail('admin_registration', $templateData, $adminEmail, [], [], $userData['user_id'] ?? 0);
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
        $userEmail = $userData['email'];
        $templateData = ['name' => $userData['name'] ?? 'Usuario'];

        return $this->sendTemplateEmail('welcome_email', $templateData, $userEmail, ['papelo.amh@gmail.com'], [], $userData['user_id'] ?? 0);
    }

    /**
     * Send a password setup email for quick registrations.
     */
    public function sendSetPasswordEmail(string $userEmail, string $token)
    {
        $templateData = ['token' => $token];
        return $this->sendTemplateEmail('set_password', $templateData, $userEmail, ['papelo.amh@gmail.com']);
    }

    /**
     * Send a quick start prompt email (5 min after register).
     */
    public function sendQuickStartPrompt(array $userData)
    {
        return $this->sendTemplateEmail('quick_start', ['name' => $userData['name'] ?? 'Usuario'], $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * Send an inactivity reminder email (24h without requests).
     */
    public function sendInactivityReminder(array $userData)
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');
        $newCompaniesCount = $db->table('companies')
                                ->where('fecha_constitucion >=', $today)
                                ->countAllResults();

        $templateData = [
            'name'  => $userData['name'] ?? 'Usuario',
            'count' => $newCompaniesCount
        ];
        return $this->sendTemplateEmail('inactivity_reminder', $templateData, $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * Send a success email after the first successful request.
     */
    public function sendFirstRequestMilestone(array $userData)
    {
        return $this->sendTemplateEmail('first_request_success', ['name' => $userData['name'] ?? 'Usuario'], $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * EXCEL SEQUENCE: Day 1 - New Companies Detected
     */
    public function sendExcelSequenceDay1(array $userData)
    {
        return $this->sendTemplateEmail('excel_day1_new_companies', ['name' => $userData['name'] ?? 'Usuario'], $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * EXCEL SEQUENCE: Day 2 - Case Study
     */
    public function sendExcelSequenceDay2(array $userData)
    {
        return $this->sendTemplateEmail('excel_day2_case_study', ['name' => $userData['name'] ?? 'Usuario'], $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * EXCEL SEQUENCE: Day 3 - Urgency
     */
    public function sendExcelSequenceDay3(array $userData)
    {
        return $this->sendTemplateEmail('excel_day3_urgency', ['name' => $userData['name'] ?? 'Usuario'], $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * TRIGGER: no_requests_15min
     */
    public function sendNoUsage15Min(array $userData)
    {
        $templateData = [
            'name'        => $userData['name'] ?? 'Usuario',
            'content'     => 'He visto que todavía no has lanzado tu primera validación técnica.<br><br>Para que no pierdas tiempo con la documentación, aquí tienes tu endpoint listo:<br><br><code style="background:#f1f5f9; padding:10px; display:block; border-radius:5px;">GET /api/v1/companies?cif=B12345678</code><br><br>No olvides incluir tu <b>X-API-KEY</b> en los headers. Si necesitas un ejemplo en un lenguaje específico, responde a este correo.',
            'button_text' => 'Ver mi API Key',
            'button_url'  => base_url('dashboard')
        ];
        return $this->sendTemplateEmail('automation_generic', $templateData, $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * TRIGGER: one_request_inactive_1h
     */
    public function sendOneUsageInactive1H(array $userData)
    {
        $templateData = [
            'name'        => $userData['name'] ?? 'Usuario',
            'content'     => 'Has realizado tu primera validación con éxito. ¡Buen comienzo!<br><br>Ahora que ya has probado la base, queremos enseñarte cómo llevar tu automatización al siguiente nivel. El <b>Plan Pro</b> desbloquea capas de datos inteligentes que no están disponibles en la versión Free:<br><br>• <b>Scoring de Propensión:</b> Identifica empresas con alta probabilidad de compra.<br>• <b>Señales de Crecimiento:</b> Detecta eventos del BORME en tiempo real.<br>• <b>Insights Tecnológicos:</b> Descubre el stack técnico de tus clientes.',
            'button_text' => 'Ver capacidades del Plan Pro',
            'button_url'  => base_url('billing')
        ];
        return $this->sendTemplateEmail('automation_generic', $templateData, $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * TRIGGER: reached_5_requests
     */
    public function sendReached5Requests(array $userData)
    {
        $templateData = [
            'name'        => $userData['name'] ?? 'Usuario',
            'content'     => 'Ya has validado tus primeras empresas. ¡Genial!<br><br>Como habrás notado, en el Plan Free enmascaramos campos clave como la <b>dirección completa, el objeto social detallado y los cargos societarios</b>.<br><br>Pásate a Pro para desbloquear el 100% del payload y automatizar tu flujo de datos sin "asteriscos".',
            'button_text' => 'Desbloquear datos Pro',
            'button_url'  => base_url('billing')
        ];
        return $this->sendTemplateEmail('automation_generic', $templateData, $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * TRIGGER: reached_80_requests
     */
    public function sendReached80Requests(array $userData)
    {
        $templateData = [
            'name'        => $userData['name'] ?? 'Usuario',
            'content'     => 'Has alcanzado las 80 consultas. Tu bono garantizado de 100 está cerca de agotarse.<br><br>Para evitar que tu integración se detenga por falta de cuota, te recomendamos activar el Plan Pro hoy mismo.<br><br><b>¿Qué obtendrás al activar Pro?</b><br>• Hasta 3.000 consultas mensuales.<br>• Datos enriquecidos sin enmascarar.<br>• Soporte técnico prioritario.',
            'button_text' => 'Evitar cortes de servicio',
            'button_url'  => base_url('billing')
        ];
        return $this->sendTemplateEmail('automation_generic', $templateData, $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * TRIGGER: bad_request_help
     * Sent when a user generates many 400 errors (bad CIF format).
     * Includes info about how many credits were restored.
     */
    public function sendBadRequestHelp(array $userData, int $errorCount): array
    {
        $templateData = [
            'name'        => $userData['name'] ?? 'Usuario',
            'content'     => "Nuestro sistema automatizado de monitoreo ha detectado una alta tasa de errores en tus peticiones de hoy (<b>{$errorCount} consultas rechazadas con código 400 - Bad Request</b>).<br><br>Este error ocurre cuando el parámetro <code>cif</code> no tiene el formato correcto de un identificador fiscal español. El problema más habitual es enviar texto adicional pegado al CIF al parsearlo desde un documento externo.<br><br><b>Ejemplos de peticiones incorrectas detectadas:</b><br><code style=\"background:#f1f5f9; padding:6px 10px; display:inline-block; border-radius:4px; margin:4px 0;\">❌ /api/v1/companies?cif=A08649477ELADJUDICATARIO</code><br><code style=\"background:#f1f5f9; padding:6px 10px; display:inline-block; border-radius:4px; margin:4px 0;\">❌ /api/v1/companies?cif=ADJUDICATARIO</code><br><br><b>El formato correcto es únicamente el identificador limpio:</b><br><code style=\"background:#dcfce7; padding:6px 10px; display:inline-block; border-radius:4px; margin:4px 0;\">✅ /api/v1/companies?cif=A08649477</code><br><br>Para que este error técnico no penalice tu prueba, <b>hemos devuelto automáticamente las {$errorCount} consultas rechazadas</b> a tu cuenta. Puedes verificarlo en tu dashboard.<br><br>Si tienes alguna duda sobre cómo extraer correctamente los identificadores de tus documentos, responde a este correo y te echamos un cable.",
            'button_text' => 'Ver mi dashboard',
            'button_url'  => base_url('dashboard')
        ];
        return $this->sendTemplateEmail('automation_generic', $templateData, $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * TRIGGER: reached_100_percent_quota
     */
    public function sendQuotaExceeded(array $userData)
    {
        $templateData = [
            'name'        => $userData['name'] ?? 'Usuario',
            'content'     => 'Has agotado tu bono de 100 consultas gratuitas.<br><br>Tu integración ha dejado de recibir datos oficiales hasta que actives un Plan Pro o Business.<br><br><b>Activa Pro ahora para reanudar el servicio instantáneamente:</b>',
            'button_text' => 'Reanudar servicio (Plan Pro)',
            'button_url'  => base_url('billing')
        ];
        return $this->sendTemplateEmail('automation_generic', $templateData, $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * TRIGGER: monthly_usage_report
     */
    public function sendMonthlyUsageReport(array $userData, int $totalRequests)
    {
        $hoursSaved = round(($totalRequests * 5) / 60, 1);
        $templateData = [
            'name'        => $userData['name'] ?? 'Usuario',
            'content'     => "Este mes has realizado <b>{$totalRequests} validaciones</b> con éxito.<br><br>Gracias a la automatización de la API, has ahorrado aproximadamente <b>{$hoursSaved} horas</b> de entrada manual de datos y búsqueda en registros oficiales.<br><br>Sigue optimizando tus procesos con nosotros.",
            'button_text' => 'Ver estadísticas detalladas',
            'button_url'  => base_url('dashboard')
        ];
        return $this->sendTemplateEmail('automation_generic', $templateData, $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * CORE: Send email using a database template
     */
    private function sendTemplateEmail(string $slug, array $data, string $to, array $bcc = [], array $attachments = [], int $userId = 0)
    {
        $templateModel = new EmailTemplateModel();
        $template = $templateModel->getBySlug($slug);

        if (!$template) {
            log_message('error', "[EmailService] Plantilla no encontrada: {$slug}");
            return ['success' => false, 'body' => ''];
        }

        // Define which templates are purely transactional (must send even if unsubscribed)
        $transactionalSlugs = ['payment_notification', 'user_invoice', 'admin_registration', 'set_password', 'welcome_email'];

        // Check if the recipient is unsubscribed
        if (!in_array($slug, $transactionalSlugs) && $this->isUnsubscribed($to)) {
            log_message('info', "[EmailService] Email comercial [{$slug}] saltado para {$to} por unsuscribe=1");
            return ['success' => true, 'body' => '']; // Return true as if handled
        }

        $email = Services::email();
        $email->clear(true);

        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $subject = $this->parsePlaceholders($template->subject, $data);
        $body    = $this->parsePlaceholders($template->body, $data);

        // Add unsubscribe link if not already present and only for commercial/marketing emails
        if (!in_array($slug, $transactionalSlugs) && strpos($body, 'unsubscribe') === false) {
            $unsubUrl = $this->generateUnsubscribeLink($to);
            $body .= "\n\n<p style='font-size:12px; color:#94a3b8; text-align:center; margin-top:30px;'>¿No quieres recibir correos con consejos u ofertas? <a href='{$unsubUrl}' style='color:#94a3b8; text-decoration:underline;'>Date de baja de la lista aquí</a>.</p>";
        }

        $email->setTo($to);
        if (!empty($bcc)) {
            $email->setBCC($bcc);
        }
        $email->setSubject($subject);
        $email->setMessage($body);

        foreach ($attachments as $att) {
            $email->attach($att['path'], 'attachment', $att['name'] ?? null);
        }

        if ($email->send()) {
            log_message('info', "[EmailService] Email [{$slug}] enviado a {$to}");
            if ($userId > 0) {
                $this->logToDatabase($userId, $subject, $body, 'success');
            }
            return ['success' => true, 'body' => $body];
        } else {
            $error = $email->printDebugger(['headers']);
            log_message('error', "[EmailService] Error al enviar [{$slug}] a {$to}: " . $error);
            if ($userId > 0) {
                $this->logToDatabase($userId, $subject, $body, 'error', $error);
            }
            return ['success' => false, 'body' => ''];
        }
    }

    /**
     * Helper to parse placeholders like {name} in a string
     */
    private function parsePlaceholders(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $content = str_replace('{' . $key . '}', (string) $value, $content);
            }
        }
        return $content;
    }

    /**
     * Check if an email address belongs to an unsubscribed user.
     */
    private function isUnsubscribed(string $email): bool
    {
        $db = \Config\Database::connect();
        $user = $db->table('users')
                   ->select('unsuscribe')
                   ->where('email', $email)
                   ->get()
                   ->getRow();
        
        return $user && (int)($user->unsuscribe ?? 0) === 1;
    }

    /**
     * Generate a secure unsubscribe link for an email address.
     */
    public function generateUnsubscribeLink(string $email): string
    {
        $hash = hash_hmac('sha256', $email, env('encryption.key', 'apiempresas-secret-key'));
        return site_url("unsubscribe/{$hash}?email=" . urlencode($email));
    }

    /**
     * Send an alert when an API Key is blocked due to a Geo Anomaly.
     */
    public function sendApiKeyBlockedAlert(string $userEmail, string $countryCode)
    {
        $emailService = \Config\Services::email();
        $emailService->clear();

        $fromEmail = env('email.fromEmail', 'no-reply@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas Seguridad');
        
        $emailService->setFrom($fromEmail, $fromName);
        $emailService->setTo($userEmail);
        $emailService->setSubject('⚠️ ALERTA DE SEGURIDAD: Tu API Key ha sido bloqueada');

        $body = view('emails/api_key_blocked', ['countryCode' => $countryCode]);
        $emailService->setMessage($body);

        if ($emailService->send()) {
            log_message('info', "[EmailService] Alerta Geo-Anomaly enviada a {$userEmail}");
            return true;
        } else {
            log_message('error', "[EmailService] Error al enviar alerta Geo-Anomaly a {$userEmail}: " . $emailService->printDebugger(['headers']));
            return false;
        }
    }

    /**
     * Send email with download link when massive export job completes.
     */
    public function sendMassiveExportReady(string $userEmail, string $downloadToken, string $exportType, int $totalRecords)
    {
        $emailService = \Config\Services::email();
        $emailService->clear();

        $fromEmail = env('email.fromEmail', 'no-reply@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        
        $emailService->setFrom($fromEmail, $fromName);
        $emailService->setTo($userEmail);
        $emailService->setSubject('✅ Tu Base de Datos masiva está lista para descargar');

        $downloadUrl = site_url("download/secure/{$downloadToken}");
        
        $typeLabel = strpos($exportType, 'subsidies') !== false ? 'Subvenciones' : 'Licitaciones Públicas';

        $body = "Hola,<br><br>";
        $body .= "Tu exportación masiva de <b>{$typeLabel}</b> (" . number_format($totalRecords, 0, ',', '.') . " registros) ha finalizado correctamente y está lista para descargar.<br><br>";
        $body .= "Hemos procesado y optimizado los datos para ti. Puedes descargar tu archivo de forma segura haciendo clic en el siguiente enlace (el archivo viene comprimido en ZIP):<br><br>";
        $body .= "<a href='{$downloadUrl}' style='display:inline-block; padding:12px 24px; background:#10b981; color:#fff; text-decoration:none; border-radius:8px; font-weight:bold;'>Descargar BBDD Completa</a><br><br>";
        $body .= "<i>Nota: Este enlace es seguro y privado. Por favor, no lo compartas.</i><br><br>";
        $body .= "Gracias por confiar en APIEmpresas.<br>";

        $emailService->setMessage($body);

        if ($emailService->send()) {
            log_message('info', "[EmailService] Export ready email sent to {$userEmail}");
            return true;
        } else {
            log_message('error', "[EmailService] Failed to send export ready email to {$userEmail}: " . $emailService->printDebugger(['headers']));
            return false;
        }
    }
}
