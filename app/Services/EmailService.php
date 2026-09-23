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
    public function sendRiskWelcomeEmail(array $userData, string $redirectUrl = '', string $originCif = '')
    {
        $userEmail = $userData['email'];
        $buttonUrl = !empty($redirectUrl) ? site_url(ltrim($redirectUrl, '/')) : site_url('dashboard');

        // Empresa que motivó el registro. Es el primer correo que recibe y llega en el
        // momento de máxima atención: nombrar la empresa que venía buscando convierte
        // mucho mejor que un "bienvenido" genérico.
        $originLine = '';
        $originCif  = trim($originCif);

        if ($originCif !== '') {
            // Envuelto a propósito: esto es un adorno del correo. Si falla la
            // consulta, el usuario recibe su bienvenida sin la línea de la
            // empresa — nunca un registro roto. (La primera versión pedía una
            // columna `name` que en `companies` se llama `company_name`, y la
            // excepción tumbaba el alta entera.)
            try {
                helper('company');
                $fila = \Config\Database::connect()->table('companies')
                    ->select('company_name, cif')
                    ->where('cif', strtoupper($originCif))
                    ->get()->getRow();

                if ($fila) {
                    $nombre = company_short_name(company_display_name(
                        $fila->company_name ?? '', ''
                    ));

                    if ($nombre !== '') {
                        $originLine = '<div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:14px 16px; margin:0 0 20px;">'
                            . '<p style="margin:0; color:#1e3a8a; font-size:14.5px; line-height:1.5;">'
                            . 'Tu dictamen de <strong>' . esc($nombre) . '</strong> te está esperando: '
                            . 'ábrelo cuando quieras con el botón de abajo.</p></div>';
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', '[EmailService] No se pudo resolver la empresa de origen (' . $originCif . '): ' . $e->getMessage());
            }
        }

        $templateData = [
            'name'        => $userData['name'] ?? 'Usuario',
            'button_url'  => $buttonUrl,
            'origin_line' => $originLine,
        ];

        return $this->sendTemplateEmail('welcome_risk', $templateData, $userEmail, ['papelo.amh@gmail.com'], [], $userData['user_id'] ?? 0);
    }

    /**
     * Alerta de movimiento en el BORME para las empresas que el usuario vigila.
     *
     * El gating es la clave del producto: el gratuito ve QUÉ empresa se ha movido y
     * cuántos actos hay, pero no cuáles. El detalle (tipo de acto y descripción) es
     * lo que paga Solvencia Pro, y es también el motivo para volver a la ficha.
     *
     * @param array $empresas Salida de BormeAlertsCommand: nombre, cif, company_id, actos[]
     */
    public function sendBormeAlert(array $userData, array $empresas, bool $isSubscriber = false)
    {
        helper('company');

        $totalEmpresas = count($empresas);
        $totalActos    = array_sum(array_map(static fn ($e) => count($e['actos']), $empresas));
        $primera       = $empresas[0] ?? null;

        $filas = '';
        /** @var array{label:string,grave:bool}|null Hecho más grave de todo el aviso: gobierna el asunto. */
        $avisoGlobal = null;

        foreach (array_slice($empresas, 0, 8) as $e) {
            $nombre  = company_display_name($e['nombre'] ?? '', $e['cif'] ?? 'Empresa');
            // Enlazamos por CIF a secas y dejamos que la redirección canónica resuelva
            // el slug: fabricarlo aquí daría "georgia-s-l" donde la ficha es "georgia-sl".
            $urlFicha = site_url(($e['cif'] ?? '') . '?ver-riesgo=1');
            $nActos  = count($e['actos']);

            // Fecha del movimiento más reciente. Al gratuito no le decimos QUÉ pasó
            // (eso es lo que paga Pro), pero sí CUÁNDO: sin fecha el aviso es abstracto
            // y no invita a pulsar.
            $fechas = array_filter(array_map(static fn ($a) => trim((string) ($a['borme_date'] ?? '')), $e['actos']));
            $ultima = $fechas ? date('d/m/Y', strtotime(max($fechas))) : '';

            // Hecho destacado de la empresa: si entre los actos hay uno grave manda ese,
            // aunque venga el último de la lista. Un concurso no puede quedar sepultado
            // debajo de tres nombramientos solo porque se publicara antes.
            $destacado = null;
            foreach ($e['actos'] as $acto) {
                $d = self::actoDestacado(
                    trim((string) ($acto['act_types'] ?? '') . ' ' . (string) ($acto['description'] ?? ''))
                );
                if ($d === null) {
                    continue;
                }
                if ($destacado === null || ($d['grave'] && !$destacado['grave'])) {
                    $destacado = $d;
                }
            }
            if ($destacado !== null && ($avisoGlobal === null || ($destacado['grave'] && !$avisoGlobal['grave']))) {
                $avisoGlobal = $destacado;
            }

            $aviso = '';
            if ($destacado !== null) {
                $c = $destacado['grave']
                    ? ['#fef2f2', '#fecaca', '#b91c1c']
                    : ['#f0fdf4', '#bbf7d0', '#15803d'];
                $aviso = '<div style="margin-top:10px; background:' . $c[0] . '; border:1px solid ' . $c[1]
                    . '; color:' . $c[2] . '; font-size:13px; font-weight:700; padding:8px 10px; border-radius:8px;">'
                    . ($destacado['grave'] ? '&#9888; ' : '') . esc($destacado['label']) . '</div>';
            }

            $detalle = '';
            if ($isSubscriber) {
                foreach (array_slice($e['actos'], 0, 4) as $acto) {
                    $tipo = trim((string) ($acto['act_types'] ?? ''));
                    $desc = trim((string) ($acto['description'] ?? ''));
                    $texto = $tipo !== '' ? $tipo : $desc;
                    if ($texto === '') {
                        continue;
                    }
                    // La fecha, en formato español. La rama del gratuito ya la formatea
                    // con date('d/m/Y'); esta la imprimía cruda —"2014-07-16"—, así que el
                    // correo que recibe quien PAGA era el que enseñaba la fecha de la base
                    // de datos en bruto.
                    $fechaActo = trim((string) ($acto['borme_date'] ?? ''));
                    $fechaActo = $fechaActo !== '' ? date('d/m/Y', strtotime($fechaActo)) : '';

                    $detalle .= '<div style="font-size:13px; color:#475569; margin-top:6px; padding-left:10px; border-left:2px solid #cbd5e1;">'
                        . esc(company_sentence_case(mb_substr($texto, 0, 160)))
                        . ($fechaActo !== '' ? ' <span style="color:#94a3b8;">· ' . esc($fechaActo) . '</span>' : '')
                        . '</div>';
                }
            } else {
                // Al gratuito se le dice QUÉ TIPO de acto ha entrado y CUÁNDO; lo que paga
                // Pro es el detalle (quién entra, quién sale, cuánto capital, qué cargo).
                // Decir solo "26 actos nuevos" no permite triar el aviso: un cambio de
                // domicilio y una declaración de concurso llegan con el mismo aspecto, y
                // un correo que no se puede triar se deja de abrir a la tercera semana.
                $tipos = [];
                foreach ($e['actos'] as $acto) {
                    foreach (preg_split('/[.;,]/u', (string) ($acto['act_types'] ?? '')) as $trozo) {
                        $trozo = trim($trozo);
                        if ($trozo === '' || mb_strlen($trozo) < 3) {
                            continue;
                        }
                        $trozo = company_sentence_case(mb_substr($trozo, 0, 60));
                        if (!in_array($trozo, $tipos, true)) {
                            $tipos[] = $trozo;
                        }
                    }
                }

                $lineaTipos = '';
                if ($tipos !== []) {
                    $lineaTipos = '<div style="font-size:13px; color:#334155; margin-top:6px; padding-left:10px; border-left:2px solid #e2e8f0;">'
                        . esc(implode(' · ', array_slice($tipos, 0, 3)))
                        . (count($tipos) > 3 ? ' <span style="color:#94a3b8;">· y ' . (count($tipos) - 3) . ' tipo(s) más</span>' : '')
                        . '</div>';
                }

                $detalle = $lineaTipos
                    . '<div style="font-size:12px; color:#94a3b8; margin-top:6px; padding-left:10px;">'
                    . ($ultima !== '' ? 'Último movimiento: <strong style="color:#475569;">' . esc($ultima) . '</strong>' : '')
                    . '</div>';
            }

            $filas .= '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;">'
                . '<tr><td style="padding:14px 16px;">'
                . '<a href="' . $urlFicha . '" style="font-size:15px; font-weight:800; color:#0f172a; text-decoration:none;">' . esc($nombre) . '</a>'
                . ' <span style="display:inline-block; background:#eff6ff; color:#1d4ed8; font-size:11px; font-weight:800; padding:2px 8px; border-radius:999px; margin-left:6px;">'
                . $nActos . ' ' . ($nActos === 1 ? 'acto nuevo' : 'actos nuevos') . '</span>'
                . $aviso
                . $detalle
                . '</td></tr></table>';
        }

        if ($totalEmpresas > 8) {
            $filas .= '<p style="font-size:13px; color:#64748b; margin:4px 0 0;">y ' . ($totalEmpresas - 8)
                . ' empresa(s) más con movimientos.</p>';
        }

        // El asunto va encabezado por el nombre de la empresa: es lo único que el
        // destinatario reconoce de un vistazo, y si va detrás de una frase genérica
        // el cliente de correo lo corta justo antes de llegar.
        $nombrePrimera = company_short_name(
            company_display_name($primera['nombre'] ?? '', $primera['cif'] ?? 'Empresa')
        );

        // La frase concuerda con el número de ACTOS, no de empresas: decir "un movimiento
        // nuevo" encima de una etiqueta que pone "3 actos nuevos" queda descuidado.
        if ($totalEmpresas === 1) {
            $subjectCompany = $nombrePrimera;
            $intro = $totalActos === 1
                ? 'Ha aparecido un movimiento nuevo en el Registro Mercantil a nombre de una empresa que sigues.'
                : 'Han aparecido ' . $totalActos . ' movimientos nuevos en el Registro Mercantil a nombre de una empresa que sigues.';
        } else {
            $subjectCompany = $nombrePrimera . ' y ' . ($totalEmpresas - 1) . ' más';
            $intro = 'Han aparecido movimientos nuevos en el Registro Mercantil en ' . $totalEmpresas
                . ' de las empresas que sigues.';
        }

        // El asunto es lo único que decide si el correo se abre hoy o el viernes. Cuando
        // hay un hecho grave, ese hecho ES la noticia: "Concurso de acreedores" se abre,
        // "26 actos nuevos en el BORME" se deja para luego. El recuento pasa a la vista
        // previa, que es donde no estorba.
        $resumenActos = $avisoGlobal !== null && $avisoGlobal['grave']
            ? $avisoGlobal['label']
            : $totalActos . ' ' . ($totalActos === 1 ? 'acto nuevo' : 'actos nuevos') . ' en el BORME';

        // Fecha más reciente de todo el aviso, para el texto de vista previa
        $todasFechas = [];
        foreach ($empresas as $e) {
            foreach ($e['actos'] as $a) {
                $f = trim((string) ($a['borme_date'] ?? ''));
                if ($f !== '') {
                    $todasFechas[] = $f;
                }
            }
        }
        $ultimaGlobal = $todasFechas ? date('d/m/Y', strtotime(max($todasFechas))) : '';

        // La vista previa lleva SIEMPRE el recuento, aunque el asunto se lo haya cedido
        // al hecho grave: si no, el asunto dice "Concurso de acreedores" y debajo no hay
        // ni una cifra que sitúe el aviso.
        $preheader = $totalActos . ' ' . ($totalActos === 1 ? 'acto nuevo' : 'actos nuevos') . ' en el BORME'
            . ($ultimaGlobal !== '' ? ', el último del ' . $ultimaGlobal : '')
            . '. Entra para ver qué ha cambiado.';

        $templateData = [
            'name'            => $userData['name'] ?? 'Hola',
            'intro'           => $intro,
            'companies_html'  => $filas,
            'company_name'    => $subjectCompany,
            'resumen_actos'   => $resumenActos,
            'preheader'       => $preheader,
            'total_empresas'  => $totalEmpresas,
            'total_actos'     => $totalActos,
            'button_url'      => $isSubscriber
                ? site_url('dashboard?view=risk')
                : site_url(($primera['cif'] ?? '') . '?ver-riesgo=1'),
            'button_text'     => $isSubscriber ? 'Ver los movimientos' : 'Ver qué ha cambiado',
            'footer_note'     => $isSubscriber
                ? 'Vigilancia de tu cartera incluida en el plan Solvencia Pro.'
                : 'Con Solvencia Pro recibes el detalle de cada acto directamente en este correo.',
        ];

        return $this->sendTemplateEmail('borme_alert', $templateData, $userData['email'], [], [], $userData['user_id'] ?? 0);
    }

    /**
     * Reconoce en el texto crudo de un acto del BORME si hay un hecho que merece
     * destacarse, y si ese hecho es malo.
     *
     * Aquí no se puede consultar la taxonomía normalizada del motor: `borme_posts`
     * guarda `act_types` como texto libre tal cual lo publica el boletín, así que la
     * detección es por patrón, sin tildes de por medio. Devuelve null si el acto es
     * rutinario (nombramientos, cambios de domicilio, ampliaciones...).
     *
     * DECISIÓN DE PRODUCTO: esto se le enseña TAMBIÉN al usuario gratuito. Un concurso
     * de acreedores de un cliente suyo es justo el aviso por el que alguien recomienda
     * el producto en su despacho; esconderlo detrás del muro ahorraría alguna
     * suscripción y costaría la razón por la que se vuelve a abrir el correo. Lo que
     * paga Pro sigue siendo el detalle: quién entra, quién sale, cuánto capital.
     *
     * Es pública y estática a propósito: el comando de preparación de pruebas necesita
     * la MISMA clasificación para elegir una empresa que dispare el aviso rojo. Si
     * cada lado tuviera su lista, la prueba dejaría de probar el correo real.
     *
     * @return array{label:string,grave:bool}|null
     */
    public static function actoDestacado(string $texto): ?array
    {
        $t = mb_strtolower($texto, 'UTF-8');
        // Sin tildes: el boletín no es consistente y "disolucion" aparece de las dos formas.
        $t = strtr($t, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u']);

        // El orden importa: "conclusion del concurso" contiene "concurso", y anunciarle a
        // alguien un concurso cuando lo que ha pasado es que ha terminado es un error caro.
        $reglas = [
            ['agujas' => ['conclusion del concurso', 'concurso concluido', 'fin del concurso', 'reapertura de la hoja'],
             'label'  => 'Fin del concurso de acreedores', 'grave' => false],
            ['agujas' => ['concurso', 'suspension de pagos'],
             'label'  => 'Concurso de acreedores',          'grave' => true],
            ['agujas' => ['extincion'],
             'label'  => 'Extinción de la sociedad',        'grave' => true],
            ['agujas' => ['liquidacion'],
             'label'  => 'Apertura de la liquidación',      'grave' => true],
            ['agujas' => ['disolucion'],
             'label'  => 'Disolución de la sociedad',       'grave' => true],
            ['agujas' => ['revocacion del nif', 'revocacion nif'],
             'label'  => 'Revocación del NIF',              'grave' => true],
            ['agujas' => ['indice de entidades', 'baja provisional'],
             'label'  => 'Baja en el Índice de Entidades',  'grave' => true],
            ['agujas' => ['cierre provisional', 'cierre de hoja', 'cierre de la hoja', 'hoja registral'],
             'label'  => 'Cierre de la hoja registral',     'grave' => true],
        ];

        foreach ($reglas as $regla) {
            foreach ($regla['agujas'] as $aguja) {
                if (mb_strpos($t, $aguja) !== false) {
                    return ['label' => $regla['label'], 'grave' => $regla['grave']];
                }
            }
        }

        return null;
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
     * Enlace de acceso de un solo uso (ver App\Services\LoginLinkService).
     *
     * Transaccional: se envía aunque el usuario haya rechazado el marketing, porque
     * sin él no puede entrar en su propia cuenta.
     */
    public function sendLoginLinkEmail(string $userEmail, string $loginUrl, int $minutos, int $userId = 0)
    {
        return $this->sendTemplateEmail('login_link', [
            'login_url' => $loginUrl,
            'minutos'   => $minutos,
        ], $userEmail, [], [], $userId);
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
            'name'            => $userData['name'] ?? 'Usuario',
            'button_url'      => site_url('dashboard'),
            'guarantee_block' => $this->guaranteeBlock(),
        ];

        return $this->sendTemplateEmail('risk_pro_welcome', $templateData, $userData['email'], ['papelo.amh@gmail.com'], [], $userData['user_id'] ?? 0);
    }

    /**
     * Recuadro de la garantía para los correos. Devuelve cadena vacía si la
     * garantía está desactivada en Config\Solvencia, para que quitarla sea un
     * cambio de configuración y no una edición de plantillas.
     */
    private function guaranteeBlock(): string
    {
        helper('company');

        if (!solvencia('garantiaActiva', true)) {
            return '';
        }

        $dias = (int) solvencia('garantiaDias', 30);
        $url  = site_url('garantia');

        return '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin: 6px 0 4px;">'
            . '<tr><td style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 16px;'
            . 'color:#15803d;font-size:13px;line-height:1.55;text-align:center;">'
            . '🛡️ <strong>' . $dias . ' días de garantía.</strong> Si Solvencia Pro no te ha servido, '
            . 'te devolvemos el importe íntegro del periodo. Sin preguntas: '
            . '<a href="' . $url . '" style="color:#15803d;font-weight:700;">pídelo aquí</a>.'
            . '</td></tr></table>';
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
            return [
                'success' => false,
                'body'    => '',
                'error'   => "la plantilla '{$slug}' no existe en email_templates (¿falta 'php spark db:seed_emails'?)",
            ];
        }

        // Define which templates are purely transactional (must send even if unsubscribed)
        $transactionalSlugs = [
            'payment_notification',
            'user_invoice',
            'admin_registration',
            'set_password',
            'login_link',
            'welcome_email',
            'welcome_risk',
            'risk_pack_welcome',
            'risk_pro_welcome'
        ];

        // Las alertas del BORME tienen consentimiento propio: quien las ha activado las
        // recibe aunque haya rechazado el marketing, y quien las ha desactivado no las
        // recibe aunque lo acepte.
        if ($slug === 'borme_alert') {
            if ($this->isAlertsUnsubscribed($to)) {
                log_message('info', "[EmailService] Alerta BORME saltada para {$to} por preferencia del usuario");
                return ['success' => true, 'body' => '', 'skipped' => true];
            }
        } elseif (!in_array($slug, $transactionalSlugs) && $this->isUnsubscribed($to)) {
            log_message('info', "[EmailService] Email comercial [{$slug}] saltado para {$to} por unsuscribe=1");
            return ['success' => true, 'body' => '', 'skipped' => true]; // Return true as if handled
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

        // Baja de un clic específica para alertas: el enlace genérico daría de baja del
        // marketing, que no es lo mismo. Sin una salida propia no se pueden enviar.
        if ($slug === 'borme_alert') {
            $optOutUrl = $this->generateAlertsOptOutLink($to);
            // "porque consultaste esta empresa" era cierto cuando la vigilancia se derivaba
            // del historial. Ahora la crea el usuario al pulsar "Vigilar", y decirle que le
            // escribimos por haber mirado una ficha suena a que le seguimos sin permiso.
            $body .= "\n\n<p style='font-size:12px; color:#94a3b8; text-align:center; margin-top:30px;'>Recibes este aviso porque tienes esta empresa en vigilancia. <a href='{$optOutUrl}' style='color:#94a3b8; text-decoration:underline;'>Dejar de recibir alertas del Registro Mercantil</a>.</p>";
        } elseif (!in_array($slug, $transactionalSlugs) && strpos($body, 'unsubscribe') === false) {
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
            return [
                'success' => false,
                'body'    => '',
                'error'   => 'el envío SMTP falló: ' . trim(strip_tags((string) $error)),
            ];
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
    /**
     * ¿Debe bloquearse una alerta del BORME para este correo?
     *
     * La preferencia explícita (alerts_borme) manda sobre la baja general. Si no hay
     * preferencia, se hereda de `unsuscribe`.
     */
    private function isAlertsUnsubscribed(string $email): bool
    {
        $db   = \Config\Database::connect();
        $user = $db->table('users')
                   ->select('unsuscribe, alerts_borme')
                   ->where('email', $email)
                   ->get()
                   ->getRow();

        if (!$user) {
            return false;
        }

        if ($user->alerts_borme !== null) {
            return (int) $user->alerts_borme === 0;
        }

        return (int) ($user->unsuscribe ?? 0) === 1;
    }

    /**
     * Enlace de baja SOLO de las alertas, de un clic y sin pantalla de confirmación.
     */
    public function generateAlertsOptOutLink(string $email): string
    {
        $hash = hash_hmac('sha256', 'alerts:' . $email, env('encryption.key', 'apiempresas-secret-key'));
        return site_url("unsubscribe/alertas/{$hash}?email=" . urlencode($email));
    }

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
