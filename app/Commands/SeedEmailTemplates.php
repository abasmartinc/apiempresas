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

    public function run(array $params)
    {
        $model = new EmailTemplateModel();

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
            ]
        ];

        foreach ($templates as $t) {
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
