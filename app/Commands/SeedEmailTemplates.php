<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\EmailTemplateModel;

class SeedEmailTemplates extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:seed_emails';
    protected $description = 'Seeds the email_templates table with initial data from views.';
    protected $usage       = 'db:seed_emails [slug]';

    public function run(array $params)
    {
        $model = new EmailTemplateModel();

        // Sin argumento reescribe TODAS las plantillas desde las vistas, lo que se lleva
        // por delante cualquier edición hecha en el panel de admin. Con un slug concreto
        // solo toca esa: es lo que conviene para añadir una plantilla nueva.
        $soloSlug = trim((string) ($params[0] ?? ''));
        if ($soloSlug !== '') {
            CLI::write("Sembrando únicamente la plantilla '{$soloSlug}'.", 'yellow');
        } else {
            CLI::write('ATENCIÓN: se van a reescribir TODAS las plantillas desde las vistas.', 'red');
            CLI::write('Las ediciones hechas desde el panel de admin se perderán.', 'red');
            CLI::write('Para sembrar solo una: php spark db:seed_emails <slug>', 'yellow');
            if (CLI::prompt('¿Continuar?', ['s', 'n']) !== 's') {
                CLI::write('Cancelado.', 'green');
                return;
            }
        }

        $templates = [
            [
                'slug'    => 'welcome_email',
                'name'    => 'Bienvenida (Registro)',
                'subject' => '🚀 [Configuración] Tu acceso a la API de Empresas España',
                'view'    => 'welcome',
                'vars'    => '{name}',
                'trigger' => 'Se envía inmediatamente después de que un usuario completa su registro.'
            ],
            [
                'slug'    => 'welcome_risk',
                'name'    => 'Bienvenida (Riesgo y Solvencia)',
                'subject' => '🛡️ Tu cuenta está lista: dispones de 3 informes de riesgo gratis este mes',
                'view'    => 'welcome_risk',
                'vars'    => '{name}, {button_url}, {origin_line}',
                'trigger' => 'Se envía inmediatamente a los usuarios registrados con intención de ver perfil de riesgo (view_risk_profile).'
            ],
            [
                'slug'    => 'risk_unused_credits_48h',
                'name'    => 'Recordatorio Créditos de Riesgo (24h-72h)',
                'subject' => '🛡️ Consultas de solvencia gratis que aún puedes usar este mes: {remaining_credits}',
                'view'    => 'risk_unused_credits_48h',
                'vars'    => '{name}, {remaining_credits}, {button_url}',
                'trigger' => 'Se envía a usuarios de riesgo tras 24h-72h del registro si aún les quedan créditos gratuitos sin consumir.'
            ],
            [
                'slug'    => 'risk_first_query_nudge',
                'name'    => 'Seguimiento 1ª Consulta de Riesgo (12h-48h)',
                'subject' => '🛡️ Tu análisis de {company_name}: te quedan 2 consultas de solvencia gratis este mes',
                'view'    => 'risk_first_query_nudge',
                'vars'    => '{name}, {company_name}, {button_url}',
                'trigger' => 'Se envía 12h-48h tras la primera consulta de riesgo para incentivar el uso de los créditos restantes.'
            ],
            [
                'slug'    => 'risk_paywall_abandoned',
                'name'    => 'Paywall de Riesgo Alcanzado (Límite 3/3)',
                'subject' => 'Has usado tus 3 consultas de este mes: cómo seguir revisando clientes',
                'view'    => 'risk_paywall_abandoned',
                'vars'    => '{name}, {company_name}, {button_url}, {pdf_url}',
                'trigger' => 'Se envía 2h tras agotar las 3 consultas gratuitas de riesgo al intentar auditar una empresa.'
            ],
            [
                'slug'    => 'risk_pack_welcome',
                'name'    => 'Bienvenida Pack Auditorías (Compra)',
                'subject' => '🛡️ Tu pack de {credits} auditorías de solvencia ya está activo',
                'view'    => 'risk_pack_welcome',
                'vars'    => '{name}, {credits}, {button_url}',
                'trigger' => 'Se envía inmediatamente tras la compra de un Pack de Auditorías de Solvencia (risk_pack_5).'
            ],
            [
                'slug'    => 'risk_pro_welcome',
                'name'    => 'Bienvenida Solvencia Pro (Suscripción)',
                'subject' => '⭐ Tu suscripción Solvencia Pro está activa: ya puedes vigilar tu cartera',
                'view'    => 'risk_pro_welcome',
                'vars'    => '{name}, {button_url}, {guarantee_block}',
                'trigger' => 'Se envía inmediatamente al suscribirse a Solvencia Pro (risk_pro).'
            ],
            [
                'slug'    => 'risk_credits_low_upsell',
                'name'    => 'Upsell Solvencia Pro (Créditos de Pack Bajos/Agotados)',
                'subject' => 'Saldo de auditorías ({remaining_credits_text}): Solvencia Pro vigila tu cartera por ti',
                'view'    => 'risk_credits_low_upsell',
                'vars'    => '{name}, {remaining_credits_text}, {credits_status_phrase}, {button_url}, {pack_url}',
                'trigger' => 'Se envía automáticamente a compradores de packs cuando les queda <= 1 crédito para ofrecer Solvencia Pro.'
            ],
            [
                'slug'    => 'payment_notification',
                'name'    => 'Notificación de Pago (Admin)',
                'subject' => '💰 ¡Nuevo Pago Recibido! - {invoice_number}',
                'view'    => 'payment_notification',
                'vars'    => '{invoice}, {customer}, {email}, {plan}, {amount}, {currency}',
                'trigger' => 'Se envía al administrador (papelo) cuando se confirma un pago exitoso en Stripe.'
            ],
            [
                'slug'    => 'user_invoice',
                'name'    => 'Factura de Usuario',
                'subject' => '🧾 Tu factura de APIEmpresas.es - {invoice_number}',
                'view'    => 'user_invoice',
                'vars'    => '{name}, {plan_name}, {amount}, {currency}, {invoice_number}',
                'trigger' => 'Se envía al cliente junto con el PDF de la factura tras un pago completado.'
            ],
            [
                'slug'    => 'admin_registration',
                'name'    => 'Aviso Nuevo Registro (Admin)',
                'subject' => '🆕 Nuevo registro de usuario: {name}',
                'view'    => 'admin_notification',
                'vars'    => '{name}, {company}, {email}, {user_id}',
                'trigger' => 'Notifica al administrador cada vez que alguien crea una cuenta nueva.'
            ],
            [
                'slug'    => 'set_password',
                'name'    => 'Establecer Contraseña',
                'subject' => 'Establece tu contraseña - APIEmpresas.es',
                'view'    => 'set_password_email',
                'vars'    => '{token}',
                'trigger' => 'Se envía en registros rápidos para que el usuario configure su contraseña por primera vez.'
            ],
            [
                'slug'    => 'login_link',
                'name'    => 'Enlace de acceso (un solo uso)',
                'subject' => 'Tu enlace para entrar en APIEmpresas',
                'view'    => 'login_link',
                'vars'    => '{login_url}, {minutos}',
                'trigger' => 'Se envía cuando alguien introduce en el registro rápido un email que ya tiene cuenta. El enlace caduca y solo sirve una vez.'
            ],
            [
                'slug'    => 'risk_generic',
                'name'    => 'Solvencia: plantilla común',
                // El asunto lo pone cada envío (EmailService::sendRiskGeneric).
                'subject' => '{asunto}',
                'view'    => 'risk_generic',
                'vars'    => '{asunto}, {preheader}, {name}, {content}, {button_text}, {button_url}',
                'trigger' => 'Correos de Solvencia sin diseño propio: lista de vigilancia llena, resumen mensual de la cartera, pago sin terminar, consultas renovadas y comparativa de precio.'
            ],
            [
                'slug'    => 'automation_generic',
                'name'    => 'Plantilla Genérica de Automatización',
                'subject' => 'Notificación APIEmpresas.es',
                'view'    => 'automation_generic',
                'vars'    => '{name}, {content}, {button_text}, {button_url}',
                'trigger' => 'Plantilla base usada para múltiples avisos (Límites de cuota, avisos de 15min, reporte mensual, etc).'
            ],
            [
                'slug'    => 'quick_start',
                'name'    => 'Prompt de Inicio Rápido',
                'subject' => 'Configura tu integración con APIEmpresas en 1 minuto 🚀',
                'view'    => 'quick_start',
                'vars'    => '{name}',
                'trigger' => 'Se dispara automáticamente 5 minutos después del registro si el usuario no ha hecho nada.'
            ],
            [
                'slug'    => 'inactivity_reminder',
                'name'    => 'Recordatorio de Inactividad',
                'subject' => '[Tech Report] Hoy hay {count} nuevas empresas (Tu API Key sigue inactiva) 📉',
                'view'    => 'inactivity_reminder',
                'vars'    => '{name}, {count}',
                'trigger' => 'Se envía tras 24 horas sin actividad para incentivar el uso de la API.'
            ],
            [
                'slug'    => 'first_request_success',
                'name'    => 'Hito: Primera Petición',
                'subject' => 'Ya estás usando la API ⚡',
                'view'    => 'first_request_success',
                'vars'    => '{name}',
                'trigger' => 'Se envía en el momento exacto en que el usuario realiza su primera llamada con éxito.'
            ],
            [
                'slug'    => 'excel_day1_new_companies',
                'name'    => 'Excel Secuencia: Día 1',
                'subject' => 'Nuevas empresas detectadas hoy ⚡',
                'view'    => 'excel_day1_new_companies',
                'vars'    => '{name}',
                'trigger' => 'Primer correo de la secuencia de nutrición para usuarios de Excel.'
            ],
            [
                'slug'    => 'excel_day2_case_study',
                'name'    => 'Excel Secuencia: Día 2',
                'subject' => 'Cómo otros están consiguiendo clientes 💎',
                'view'    => 'excel_day2_case_study',
                'vars'    => '{name}',
                'trigger' => 'Segundo correo de la secuencia Excel (Casos de éxito).'
            ],
            [
                'slug'    => 'excel_day3_urgency',
                'name'    => 'Excel Secuencia: Día 3',
                'subject' => 'Estás perdiendo oportunidades ⚠️',
                'view'    => 'excel_day3_urgency',
                'vars'    => '{name}',
                'trigger' => 'Tercer correo de la secuencia Excel (Urgencia/Venta).'
            ],
            [
                'slug'    => 'borme_alert',
                'name'    => 'Alerta de movimiento en el BORME',
                'subject' => '🔔 {company_name} — {resumen_actos}',
                'view'    => 'borme_alert',
                'vars'    => '{name}, {intro}, {companies_html}, {company_name}, {resumen_actos}, {preheader}, {total_empresas}, {total_actos}, {button_url}, {button_text}, {footer_note}',
                'trigger' => 'Lo envía el comando alerts:borme cuando aparecen actos nuevos en el BORME de empresas que el usuario tiene en vigilancia.'
            ]
        ];

        foreach ($templates as $t) {
            if ($soloSlug !== '' && $t['slug'] !== $soloSlug) {
                continue;
            }

            $viewPath = APPPATH . 'Views/emails/' . $t['view'] . '.php';
            if (file_exists($viewPath)) {
                $content = file_get_contents($viewPath);
                
                // --- REEMPLAZOS DINÁMICOS PARA NORMALIZAR CABECERAS ---
                
                // Patrón 1: Cabecera Estándar de Tabla (Negra con Logo)
                $p1 = '/<!-- Header -->\s*<tr>\s*<td style="background-color: #1a1a1a; padding: 30px; text-align: center;">\s*<img src="https:\/\/apiempresas\.es\/logo\.png" alt="APIEmpresas\.es" style="max-width: 200px;">\s*<\/td>\s*<\/tr>/is';
                
                // Patrón 2: Cabecera Div Verde (first_request_success)
                $p2 = '/<div class="header">\s*<h1>¡Enhorabuena, {name}!<\/h1>\s*<\/div>/is';
                
                // Patrón 3: Cabecera Div Gris (inactivity_reminder)
                $p3 = '/<div class="header">\s*<h1>APIEmpresas\.es<\/h1>\s*<\/div>/is';
                
                // Patrón 4: Cabecera Div Azul (payment_notification / admin_notification)
                $p4 = '/<div class="header">.*?<h1>.*?<\/h1>\s*<\/div>/is';

                // Definición del Nuevo Header (Diseño Premium)
                $newHeaderTable = '<!-- Header -->
                    <tr>
                        <td style="background: #2152ff; background: linear-gradient(135deg, #2152ff 0%, #10b981 100%); padding: 25px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 800; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; letter-spacing: -1px;">APIEmpresas.es</h1>
                            <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0; font-size: 14px; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;">Datos oficiales para desarrolladores</p>
                        </td>
                    </tr>';

                $newHeaderDiv = '<div style="background: #2152ff; background: linear-gradient(135deg, #2152ff 0%, #10b981 100%); padding: 25px 30px; text-align: center; border-radius: 12px 12px 0 0;">
                    <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 800; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; letter-spacing: -1px;">APIEmpresas.es</h1>
                    <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0; font-size: 14px; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;">Datos oficiales para desarrolladores</p>
                </div>';

                // 1. Limpiar variables complejas y formateadas primero
                $content = preg_replace('/<\?= number_format\(\$amount,.*?\?>/i', '{amount}', $content);
                $content = preg_replace('/<\?= esc\(\$invoice->invoice_number\)\s*\?>/i', '{invoice_number}', $content);
                $content = preg_replace('/<\?= \$invoice->invoice_number\s*\?>/i', '{invoice_number}', $content);
                
                // 2. Mapeo masivo de variables simples
                $replacements = [
                    'name'           => ['<?= esc($name) ?>', '<?= $name ?>'],
                    'customer'       => ['<?= esc($customer) ?>', '<?= $customer ?>'],
                    'email'          => ['<?= esc($email) ?>', '<?= $email ?>'],
                    'plan_name'      => ['<?= esc($plan) ?>', '<?= $plan ?>', '<?= esc($plan_name) ?>', '<?= $plan_name ?>'],
                    'amount'         => ['<?= esc($amount) ?>', '<?= $amount ?>'],
                    'currency'       => ['<?= esc($currency) ?>', '<?= $currency ?>', '<?= $currency ?>'],
                    'invoice_number' => ['<?= esc($invoice_number) ?>', '<?= $invoice_number ?>'],
                    'company'        => ['<?= esc($company) ?>', '<?= $company ?>'],
                    'user_id'        => ['<?= esc($user_id) ?>', '<?= $user_id ?>'],
                    'token'          => ['<?= $token ?>'],
                    'count'          => ['<?= $count ?>'],
                    'content'        => ['<?= $content ?>'],
                    'button_url'     => ['<?= $button_url ?>'],
                    'button_text'    => ['<?= esc($button_text) ?>'],
                    'subject'        => ['<?= esc($subject) ?>', '<?= $subject ?>'],
                ];

                foreach ($replacements as $placeholder => $tags) {
                    $content = str_replace($tags, '{' . $placeholder . '}', $content);
                }
                
                // 3. Aplicar nuevos diseños de cabecera (Normalización)
                $content = preg_replace($p1, $newHeaderTable, $content);
                $content = preg_replace($p2, $newHeaderDiv, $content);
                $content = preg_replace($p3, $newHeaderDiv, $content);
                $content = preg_replace($p4, $newHeaderDiv, $content);

                // 4. Limpieza de tags PHP residuales (por si acaso)
                $content = preg_replace('/<\?=.*? \?>/i', '', $content);

                $data = [
                    'slug'        => $t['slug'],
                    'name'        => $t['name'],
                    'subject'     => $t['subject'],
                    'body'        => $content,
                    'description' => $t['trigger'] . ' | Variables: ' . $t['vars']
                ];

                if ($model->where('slug', $t['slug'])->first()) {
                    $model->where('slug', $t['slug'])->set($data)->update();
                    CLI::write("Actualizada: " . $t['slug'], 'yellow');
                } else {
                    $model->insert($data);
                    CLI::write("Insertada: " . $t['slug'], 'green');
                }
            } else {
                CLI::error("Vista no encontrada: " . $viewPath);
            }
        }

        CLI::write("Proceso de semilla completado.", 'cyan');
    }
}
