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
     * TRIGGER: reached_20_requests
     */
    public function sendReached20Requests(array $userData)
    {
        $templateData = [
            'name'        => $userData['name'] ?? 'Usuario',
            'content'     => 'Has alcanzado las 20 consultas. Tu límite mensual de 30 está cerca.<br><br>Para evitar que tu integración se detenga por falta de cuota, te recomendamos activar el Plan Pro hoy mismo.<br><br><b>¿Qué obtendrás al activar Pro?</b><br>• Hasta 3.000 consultas mensuales.<br>• Datos enriquecidos sin enmascarar.<br>• Soporte técnico prioritario.',
            'button_text' => 'Evitar cortes de servicio',
            'button_url'  => base_url('billing')
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
            'content'     => 'Has agotado tu cuota de 30 consultas gratuitas de este mes.<br><br>Tu integración ha dejado de recibir datos oficiales hasta que se reinicie el ciclo mensual o actives un Plan Pro.<br><br><b>Activa Pro ahora para reanudar el servicio instantáneamente:</b>',
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
            return false;
        }

        $email = Services::email();
        $email->clear(true);

        $fromEmail = env('email.fromEmail', 'soporte@apiempresas.es');
        $fromName  = env('email.fromName', 'APIEmpresas.es');
        $email->setFrom($fromEmail, $fromName);

        $subject = $this->parsePlaceholders($template->subject, $data);
        $body    = $this->parsePlaceholders($template->body, $data);

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
            return true;
        } else {
            $error = $email->printDebugger(['headers']);
            log_message('error', "[EmailService] Error al enviar [{$slug}] a {$to}: " . $error);
            if ($userId > 0) {
                $this->logToDatabase($userId, $subject, $body, 'error', $error);
            }
            return false;
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
}
