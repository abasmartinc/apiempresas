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
            'plan_name'      => $data['plan_name'] ?? 'Plan API',
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
        // Solo enviar email técnico de la API si el registro es para la API
        if (($userData['signup_intent'] ?? 'api') !== 'api') {
            return false;
        }

        $userEmail = $userData['email'];
        $templateData = ['name' => $userData['name'] ?? 'Usuario'];

        return $this->sendTemplateEmail('welcome_email', $templateData, $userEmail, ['papelo.amh@gmail.com'], [], $userData['user_id'] ?? 0);
    }

    /**
     * Send welcome email specifically for risk profile users.
     */
    public function sendRiskWelcomeEmail(array $userData, string $redirectUrl = '')
    {
        $userEmail = $userData['email'];
        $buttonUrl = !empty($redirectUrl) ? site_url(ltrim($redirectUrl, '/')) : site_url('dashboard');

        $templateData = [
            'name'       => $userData['name'] ?? 'Usuario',
            'button_url' => $buttonUrl
        ];

        return $this->sendTemplateEmail('welcome_risk', $templateData, $userEmail, ['papelo.amh@gmail.com'], [], $userData['user_id'] ?? 0);
    }

    /**
     * Send a password setup email for quick registrations.
     */
    public function sendSetPasswordEmail(string $userEmail, string $token)
    {
        $templateData = ['token' => $token, 'reset_url' => site_url("reset-password/{$token}")];
        return $this->sendTemplateEmail('set_password', $templateData, $userEmail, ['papelo.amh@gmail.com']);
    }

    /**
     * Send a password reset email (forgot password).
     */
    public function sendPasswordResetEmail(string $userEmail, string $token)
    {
        $templateData = ['token' => $token, 'reset_url' => site_url("reset-password/{$token}")];
        return $this->sendTemplateEmail('reset_password', $templateData, $userEmail);
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
     * TRIGGER: monthly_report
     */
    public function sendMonthlyUsageReport(array $userData, int $usage): array
    {
        $templateData = [
            'name'        => $userData['name'] ?? 'Usuario',
            'content'     => "Aquí tienes el resumen de actividad de tu cuenta en los últimos 30 días:<br><br>• <b>Consultas a la API realizadas:</b> {$usage}<br><br>Si tu consumo sigue aumentando y necesitas asegurar disponibilidad, mayor tasa de peticiones y datos mercantiles completos sin restricciones, te recomendamos revisar nuestros planes:",
            'button_text' => 'Ver Planes y Facturación',
            'button_url'  => site_url('billing')
        ];
        return $this->sendTemplateEmail('automation_generic', $templateData, $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * TRIGGER: risk_first_query_nudge_24h
     * Sent 12-24h after the user executes their first free audit (72.7% drop-off recovery).
     */
    public function sendRiskFirstQueryNudge(array $userData, array $companyData = []): array
    {
        $compName = !empty($companyData['name']) ? $companyData['name'] : (!empty($companyData['cif']) ? $companyData['cif'] : 'tu cliente');

        $templateData = [
            'name'         => $userData['name'] ?? 'Usuario',
            'company_name' => $compName,
            'button_url'   => site_url('dashboard')
        ];
        return $this->sendTemplateEmail('risk_first_query_nudge', $templateData, $userData['email'], ['papelo.amh@gmail.com'], [], $userData['user_id'] ?? 0);
    }

    /**
     * TRIGGER: risk_paywall_abandoned_2h
     */
    public function sendRiskPaywallAbandoned(array $userData, array $companyData = []): array
    {
        $compName = !empty($companyData['name']) ? $companyData['name'] : (!empty($companyData['cif']) ? $companyData['cif'] : 'tu última consulta');
        $cif = $companyData['cif'] ?? '';

        $templateData = [
            'name'         => $userData['name'] ?? 'Usuario',
            'company_name' => $compName,
            'button_url'   => site_url('billing?plan=risk_pro'),
            'pdf_url'      => !empty($cif) ? site_url('dashboard?cif=' . urlencode((string)$cif)) : site_url('dashboard')
        ];
        return $this->sendTemplateEmail('risk_paywall_abandoned', $templateData, $userData['email'], ['papelo.amh@gmail.com'], [], $userData['user_id'] ?? 0);
    }

    /**
     * TRIGGER: risk_educational_savings_48h
     */
    public function sendRiskEducationalSavings(array $userData): array
    {
        $templateData = [
            'name'        => $userData['name'] ?? 'Usuario',
            'content'     => "La mayoría de empresas pagan entre 25 € y 35 € por cada informe mercantil en proveedores tradicionales, además de cuotas fijas o permanencias anuales.<br><br>En <b>APIEmpresas</b> hemos cambiado las reglas del sector:<br><br>✅ <b>Solvencia Pro por 29 € / mes:</b> Tarifa plana para auditar todas las empresas que quieras en España sin límites.<br>✅ <b>Sin ataduras:</b> Activa tu suscripción cuando tengas auditorías y cancélala en 1 clic cuando termines.<br>✅ <b>Datos oficiales y en tiempo real:</b> Semáforo de riesgo, scoring IES, incidencias BORME y contratación pública.<br><br>Protege tu negocio de impagos y toma mejores decisiones hoy mismo:",
            'button_text' => 'Ver Ventajas de Solvencia Pro',
            'button_url'  => site_url('billing?plan=risk_pro')
        ];
        return $this->sendTemplateEmail('automation_generic', $templateData, $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * TRIGGER: risk_monthly_renewal
     */
    public function sendRiskMonthlyRenewal(array $userData): array
    {
        $templateData = [
            'name'        => $userData['name'] ?? 'Usuario',
            'content'     => "Te recordamos que se renuevan tus <b>3 consultas de solvencia y riesgo gratuitas</b> en tu cuenta de APIEmpresas.<br><br>Ya puedes volver a buscar cualquier empresa en España para evaluar su estabilidad societaria, semáforo de riesgo y actos mercantiles del BORME.<br><br>Entra a tu panel y revisa tus próximos clientes o proveedores:",
            'button_text' => 'Auditar Empresas en mi Panel',
            'button_url'  => site_url('dashboard')
        ];
        return $this->sendTemplateEmail('automation_generic', $templateData, $userData['email'], ['papelo.amh@gmail.com']);
    }

    /**
     * TRIGGER: risk_unused_credits_48h
     */
    public function sendRiskUnusedCreditsReminder(array $userData, int $remainingCredits): array
    {
        $credText = ($remainingCredits === 1) ? '1 consulta' : "{$remainingCredits} consultas";

        $templateData = [
            'name'              => $userData['name'] ?? 'Usuario',
            'remaining_credits' => $credText,
            'button_url'        => site_url('dashboard')
        ];
        return $this->sendTemplateEmail('risk_unused_credits_48h', $templateData, $userData['email'], ['papelo.amh@gmail.com'], [], $userData['user_id'] ?? 0);
    }

    /**
     * ONBOARDING: Send welcome email when user purchases a Risk Pack (e.g. 5 audits).
     */
    public function sendRiskPackWelcome(array $userData, int $credits = 5, string $targetCif = ''): array
    {
        $buttonUrl = !empty($targetCif) ? site_url('dashboard?cif=' . urlencode($targetCif)) : site_url('dashboard');

        $templateData = [
            'name'       => $userData['name'] ?? 'Usuario',
            'credits'    => (string)$credits,
            'button_url' => $buttonUrl
        ];

        return $this->sendTemplateEmail('risk_pack_welcome', $templateData, $userData['email'], ['papelo.amh@gmail.com'], [], $userData['user_id'] ?? 0);
    }

    /**
     * ONBOARDING: Send welcome email when user subscribes to Solvencia Pro.
     */
    public function sendRiskProWelcome(array $userData): array
    {
        $templateData = [
            'name'       => $userData['name'] ?? 'Usuario',
            'button_url' => site_url('dashboard')
        ];

        return $this->sendTemplateEmail('risk_pro_welcome', $templateData, $userData['email'], ['papelo.amh@gmail.com'], [], $userData['user_id'] ?? 0);
    }

    /**
     * UPSELL: Sent when a pack buyer has low (<=1) or zero credits remaining, offering Solvencia Pro.
     */
    public function sendRiskCreditsLowUpsell(array $userData, int $remainingCredits = 0): array
    {
        if ($remainingCredits <= 0) {
            $credText = '0 créditos';
            $phrase   = 'has consumido todas las auditorías';
        } elseif ($remainingCredits === 1) {
            $credText = 'solo 1 crédito restante';
            $phrase   = 'te queda únicamente 1 crédito';
        } else {
            $credText = "{$remainingCredits} créditos";
            $phrase   = "te quedan {$remainingCredits} créditos";
        }

        $templateData = [
            'name'                   => $userData['name'] ?? 'Usuario',
            'remaining_credits_text' => $credText,
            'credits_status_phrase'  => $phrase,
            'button_url'             => site_url('billing?plan=risk_pro'),
            'pack_url'               => site_url('billing?plan=risk_pack_5')
        ];

        return $this->sendTemplateEmail('risk_credits_low_upsell', $templateData, $userData['email'], ['papelo.amh@gmail.com'], [], $userData['user_id'] ?? 0);
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
        $transactionalSlugs = [
            'payment_notification',
            'user_invoice',
            'admin_registration',
            'set_password',
            'welcome_email',
            'welcome_risk',
            'risk_pack_welcome',
            'risk_pro_welcome'
        ];

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

        // Fetch User Language Preference
        $userLang = 'es'; // default
        if ($userId > 0) {
            $db = \Config\Database::connect();
            $user = $db->table('users')->select('lang')->where('id', $userId)->get()->getRow();
            if ($user && !empty($user->lang)) {
                $userLang = $user->lang;
            }
        } else {
            // Try to find by email
            $db = \Config\Database::connect();
            $user = $db->table('users')->select('lang')->where('email', $to)->get()->getRow();
            if ($user && !empty($user->lang)) {
                $userLang = $user->lang;
            }
        }

        // Determine correct subject and body based on language
        $subjectTemplate = ($userLang === 'en' && !empty($template->subject_en)) ? $template->subject_en : $template->subject;
        $bodyTemplate = ($userLang === 'en' && !empty($template->body_en)) ? $template->body_en : $template->body;

        $subject = $this->parsePlaceholders($subjectTemplate, $data);
        $body    = $this->parsePlaceholders($bodyTemplate, $data);

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
        return $this->sendTemplateEmail('api_key_blocked', ['countryCode' => $countryCode], $userEmail);
    }

    /**
     * Send email with download link when massive export job completes.
     */
    public function sendMassiveExportReady(string $userEmail, string $downloadToken, string $exportType, int $totalRecords)
    {
        $downloadUrl = site_url("download/secure/{$downloadToken}");
        $typeLabel = strpos($exportType, 'subsidies') !== false ? 'Subvenciones' : 'Licitaciones Públicas';

        $templateData = [
            'typeLabel' => $typeLabel,
            'totalRecords' => number_format($totalRecords, 0, ',', '.'),
            'downloadUrl' => $downloadUrl
        ];

        return $this->sendTemplateEmail('massive_export_ready', $templateData, $userEmail);
    }
}
