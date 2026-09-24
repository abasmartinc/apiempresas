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
    private function logToDatabase($userId, $subject, $message, $status, $error = null, ?string $trackingCode = null, ?string $slug = null)
    {
        try {
            $fila = [
                'user_id'       => $userId,
                'subject'       => $subject,
                'message'       => substr($message, 0, 1000), // Evitar logs gigantes
                'status'        => $status,
                'error_message' => $error,
                'tracking_code' => $trackingCode,
                'created_at'    => date('Y-m-d H:i:s')
            ];
            // La plantilla solo se guarda si existe la columna (ver email_logs.template_slug)
            if ($slug !== null && self::logsTienenSlug()) {
                $fila['template_slug'] = $slug;
            }
            // Directo al query builder: EmailLogModel no tiene template_slug en allowedFields
            \Config\Database::connect()->table('email_logs')->insert($fila);
        } catch (\Throwable $e) {
            log_message('error', "[EmailService] Error al guardar log en BD: " . $e->getMessage());
        }
    }

    /** ¿Tiene email_logs la columna template_slug? (se consulta una vez por proceso) */
    private static function logsTienenSlug(): bool
    {
        static $tiene = null;
        if ($tiene === null) {
            try {
                $tiene = in_array('template_slug', \Config\Database::connect()->getFieldNames('email_logs'), true);
            } catch (\Throwable $e) {
                $tiene = false;
            }
        }
        return $tiene;
    }

    /**
     * Plantillas cuyos enlaces NO se envuelven para medir clics: avisos internos y
     * enlaces con token de un solo uso (entrar, poner contraseña).
     */
    private const SIN_SEGUIMIENTO = [
        'payment_notification', 'admin_registration', 'login_link', 'set_password', 'reset_password',
    ];

    /**
     * Envuelve los enlaces a nuestra web para medir clics y atribuir compras.
     *
     * Cada enlace pasa por /e/c/{código} (EmailTracking::click), que marca el clic en
     * email_logs, guarda en sesión de qué correo viene y redirige al destino con
     * ?source=email_{plantilla}. Billing usa ese source en checkout_started y
     * checkout_completed. No se tocan: bajas, enlaces con token, ni webs externas
     * (la factura de Stripe, por ejemplo).
     */
    private function trackLinks(string $body, string $slug, string $code): string
    {
        $bases = array_unique(array_filter([
            rtrim(site_url(), '/'),
            'https://apiempresas.es',
            'https://www.apiempresas.es',
        ]));
        $patronBases = implode('|', array_map(static fn ($b) => preg_quote($b, '#'), $bases));
        $clic = rtrim(site_url(), '/') . '/e/c/' . $code . '?t=';

        return preg_replace_callback(
            '#href=(["\'])((?:' . $patronBases . ')(?:/[^"\']*)?)\1#i',
            static function ($m) use ($slug, $clic) {
                $destino = html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5);
                if (preg_match('#/(unsubscribe|e/c/|e/o/|reset-password|acceso/)#i', $destino)) {
                    return $m[0];
                }
                if (!preg_match('/[?&]source=/', $destino)) {
                    $ancla = '';
                    if (($p = strpos($destino, '#')) !== false) {
                        $ancla   = substr($destino, $p);
                        $destino = substr($destino, 0, $p);
                    }
                    $destino .= (str_contains($destino, '?') ? '&' : '?') . 'source=email_' . $slug . $ancla;
                }
                return 'href=' . $m[1] . $clic . rawurlencode($destino) . $m[1];
            },
            $body
        );
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
        helper('api');   // get_free_plan_limit()
        $templateData = [
            'name'       => $userData['name'] ?? 'Usuario',
            'free_limit' => get_free_plan_limit(),
        ];

        return $this->sendTemplateEmail('welcome_email', $templateData, $userEmail, ['papelo.amh@gmail.com'], [], $userData['user_id'] ?? 0);
    }

    /**
     * Send welcome email specifically for risk profile users.
     */
    public function sendRiskWelcomeEmail(array $userData, string $redirectUrl = '', string $originCif = '')
    {
        $userEmail = $userData['email'];
        $redirectUrl = ltrim(trim($redirectUrl), '/');
        // Un destino de pago (billing/checkout) no sirve como botón de un correo:
        // abierto más tarde, sin la compra pendiente en la sesión, no lleva a nada útil.
        if ($redirectUrl === '' || str_starts_with($redirectUrl, 'billing')) {
            $redirectUrl = 'dashboard?view=risk';
        }
        $buttonUrl = site_url($redirectUrl);

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
     * El gratuito ve qué empresa se ha movido, el TIPO de acto (lo grave, en rojo) y
     * la fecha del último movimiento, más un bloque de Solvencia Pro. El detalle de
     * cada acto (quién entra, quién sale, qué capital) es lo que paga Pro.
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

        /*
         * BLOQUE DE PRO PARA EL GRATUITO.
         *
         * Este correo es el momento de más disposición a pagar de todo el producto: la
         * vigilancia acaba de demostrar que funciona, con una empresa suya. Hasta ahora
         * la única invitación era una línea gris al pie. Se dice lo que añade Pro sobre
         * lo que ya tiene (el detalle de cada acto y 25 empresas en vez de 5), el
         * precio y la garantía. Lo que el gratuito ya recibe (el tipo de acto, lo grave
         * en rojo) no cambia.
         */
        if (!$isSubscriber) {
            $vigGratis = (int) solvencia('vigilanciasGratis', 5);
            $vigPro    = (int) solvencia('vigilanciasPro', 25);
            $garantia  = solvencia('garantiaActiva', true)
                ? ' · ' . (int) solvencia('garantiaDias', 30) . ' días de garantía'
                : '';

            $filas .= '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:18px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px;">'
                . '<tr><td style="padding:16px 18px;">'
                . '<div style="font-size:15px; font-weight:800; color:#1e3a8a; margin-bottom:6px;">Con Solvencia Pro, el detalle en este mismo correo</div>'
                . '<div style="font-size:13.5px; color:#1e40af; line-height:1.55; margin-bottom:12px;">'
                . 'Quién entra y quién sale, qué capital y qué cargo, con la fecha de cada acto, sin tener que entrar a buscarlo. '
                . 'Y vigilas hasta <strong>' . $vigPro . ' empresas</strong> en vez de ' . $vigGratis . '.'
                . '</div>'
                . '<a href="' . site_url('billing?view=risk&plan=risk_pro') . '" style="display:inline-block; background:#2563eb; color:#ffffff; font-size:14px; font-weight:700; padding:10px 18px; border-radius:9px; text-decoration:none;">Ver Solvencia Pro</a>'
                . '<span style="font-size:12px; color:#3b82f6; margin-left:10px;">' . esc(solvencia('precios.pro_mensual', '29 €')) . '/mes + IVA · sin permanencia' . $garantia . '</span>'
                . '</td></tr></table>';
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
                // Al gratuito ya se lo cuenta el bloque de Pro de arriba; repetirlo aquí sobra.
                : '',
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
        return $this->sendTemplateEmail('quick_start', ['name' => $userData['name'] ?? 'Usuario'], $userData['email'], ['papelo.amh@gmail.com'], [], (int) ($userData['user_id'] ?? $userData['id'] ?? 0));
    }

    /**
     * Día 3 sin ninguna llamada: ofrecer ayuda con la integración.
     *
     * Antes contaba las empresas con fecha de constitución de HOY, que casi siempre
     * son 0 (el BORME publica con días de retraso): "Hoy hay 0 nuevas empresas".
     */
    public function sendInactivityReminder(array $userData)
    {
        return $this->sendTemplateEmail('inactivity_reminder', ['name' => $userData['name'] ?? 'Usuario'], $userData['email'], ['papelo.amh@gmail.com'], [], (int) ($userData['user_id'] ?? $userData['id'] ?? 0));
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
     * Avisos de la API con la plantilla común `automation_generic`.
     *
     * Cada aviso trae su asunto y su preheader: antes la plantilla tenía el asunto
     * fijo "Notificación APIEmpresas.es" y los siete avisos llegaban iguales. Pasa
     * además el user_id, para que el envío quede en email_logs (antes no quedaba).
     */
    private function sendApiAutomation(array $userData, string $asunto, string $preheader, string $contenidoHtml, string $botonTexto, string $botonUrl, string $tipo = ''): array
    {
        return $this->sendTemplateEmail('automation_generic', [
            // Los siete avisos comparten plantilla: el tipo distingue cada uno en
            // email_logs, en el source del enlace y en el informe.
            '_log_slug'   => $tipo,
            'subject'     => $asunto,
            'preheader'   => $preheader,
            'name'        => $userData['name'] ?? 'Usuario',
            'content'     => $contenidoHtml,
            'button_text' => $botonTexto,
            'button_url'  => $botonUrl,
        ], $userData['email'], ['papelo.amh@gmail.com'], [], (int) ($userData['user_id'] ?? $userData['id'] ?? 0));
    }

    /** Cupo del plan Free, para no escribir "100" a mano en los asuntos. */
    private function freeLimit(): int
    {
        helper('api');
        return get_free_plan_limit();
    }

    /**
     * Datos del plan Pro de la API (id 2) para los textos: nombre, cupo y precio.
     * price_monthly está en euros (Webhook lo compara con amount_subtotal / 100).
     */
    private function planPro(): array
    {
        static $pro = null;
        if ($pro === null) {
            $pro = ['name' => 'Pro', 'monthly_quota' => 3000, 'price_monthly' => null, 'price_annual' => null];
            try {
                $fila = \Config\Database::connect()->table('api_plans')
                    ->select('name, monthly_quota, price_monthly, price_annual')->where('id', 2)
                    ->get()->getRowArray();
                if ($fila) {
                    $pro = array_merge($pro, array_filter($fila, static fn ($v) => $v !== null && $v !== ''));
                }
            } catch (\Throwable $e) {
                // Sin BD, los textos salen sin precio
            }
        }
        return $pro;
    }

    /** "3.000 consultas cada mes por 19 €/mes + IVA", o sin precio si no se conoce. */
    private function lineaPro(): string
    {
        $pro    = $this->planPro();
        $precio = (float) ($pro['price_monthly'] ?? 0);
        return number_format((int) $pro['monthly_quota'], 0, ',', '.') . ' consultas cada mes'
            . ($precio > 0 ? ' por <strong>' . self::euros($precio) . ' €/mes + IVA</strong>, sin permanencia' . $this->anualPro() : ', sin permanencia');
    }

    /** Importe en euros sin decimales sobrantes: 19 → "19", 182,5 → "182,50" */
    private static function euros(float $x): string
    {
        return rtrim(rtrim(number_format($x, 2, ',', '.'), '0'), ',');
    }

    /**
     * " o 182 €/año si pagas el año entero (te ahorras un 20 %)", con enlace a la página
     * de precios con el anual ya marcado. Vacío si no hay precio anual en api_plans.
     */
    private function anualPro(): string
    {
        $pro     = $this->planPro();
        $mensual = (float) ($pro['price_monthly'] ?? 0);
        $anual   = (float) ($pro['price_annual'] ?? 0);
        if ($anual <= 0 || $mensual <= 0 || $anual >= $mensual * 12) {
            return '';
        }
        $ahorro = (int) round((1 - $anual / ($mensual * 12)) * 100);
        return ', o <a href="' . site_url('billing?plan=pro&period=annual') . '" style="color:#2563eb;font-weight:700;">'
            . self::euros($anual) . ' €/año</a> si pagas el año entero (te ahorras un ' . $ahorro . ' %)';
    }

    /** "Cuesta 19 €/mes + IVA, sin permanencia, o 182 €/año…" (sin precio si no se conoce) */
    private function precioPro(): string
    {
        $precio = (float) ($this->planPro()['price_monthly'] ?? 0);
        return $precio > 0
            ? 'Cuesta <strong>' . self::euros($precio) . ' €/mes + IVA</strong>, sin permanencia' . $this->anualPro() . '.'
            : 'Sin permanencia.';
    }

    /** Lo que desbloquea Pro, comprobado en el código (CompaniesByCif, PlanAccessService). */
    private function ventajasPro(): string
    {
        $li = static fn (string $h) => '<li style="margin:0 0 6px;">' . $h . '</li>';
        $c  = static fn (string $t) => '<code style="background:#f1f5f9;padding:1px 5px;border-radius:4px;font-size:13px;">' . $t . '</code>';
        $cupo = number_format((int) $this->planPro()['monthly_quota'], 0, ',', '.');
        return '<ul style="margin:0 0 14px; padding-left:20px;">'
            . $li('<strong>' . $cupo . ' consultas cada mes</strong>, que se renuevan el día 1. El Free son ' . $this->freeLimit() . ' en total y no se renuevan.')
            . $li('<strong>La dirección completa</strong> de cada empresa, que en Free llega enmascarada: es lo que necesitas para facturar o dar de alta un cliente. También las coordenadas.')
            . $li('<strong>Administradores y cargos</strong> de cada empresa, añadiendo ' . $c('&amp;admin=true') . '.')
            . $li('<strong>Scoring y señales de actividad</strong>: ' . $c('/api/v1/companies/score') . ' y ' . $c('/api/v1/companies/signals') . '.')
            . '</ul>';
    }

    /**
     * TRIGGER: no_requests_15min
     */
    public function sendNoUsage15Min(array $userData)
    {
        // CIF de una empresa real: con el ficticio B12345678 la primera prueba
        // gastaba una consulta y devolvía un error.
        return $this->sendApiAutomation(
            $userData,
            'Tu primera llamada a la API, lista para copiar',
            'Pega tu API Key en este curl y tendrás los datos de una empresa real en segundos.',
            'He visto que todavía no has lanzado tu primera validación técnica.<br><br>Para que no pierdas tiempo con la documentación, aquí tienes tu endpoint listo:<br><br><code style="background:#f1f5f9; padding:10px; display:block; border-radius:5px;">GET /api/v1/companies?cif=A15075062</code><br><br>No olvides incluir tu <b>X-API-KEY</b> en los headers. Si prefieres verlo antes de escribir código, el botón de abajo hace esa misma consulta desde tu panel. Y si necesitas un ejemplo en un lenguaje concreto, responde a este correo.',
            '▶ Probarla ahora con un clic',
            base_url('dashboard?probar=A15075062'),
            'no_requests_15min'
        );
    }

    /**
     * TRIGGER: one_request_inactive_1h
     */
    public function sendOneUsageInactive1H(array $userData)
    {
        return $this->sendApiAutomation(
            $userData,
            'Tu primera consulta ha funcionado. Esto es lo siguiente',
            'Lo que añade el Plan Pro a la respuesta que acabas de recibir.',
            'Tu primera consulta a la API ha funcionado. Lo que has recibido son datos reales, con una limitación importante del plan Free: la dirección llega enmascarada, y sin ella no puedes rellenar facturas ni fichas de cliente.<br><br>Cuando tu integración vaya a producción, el <b>Plan Pro</b> te da:' . $this->ventajasPro() . $this->precioPro() . ' No cambias ni tu API Key ni tu código.',
            'Ver el Plan Pro',
            base_url('billing?plan=pro'),
            'one_request_inactive_1h'
        );
    }

    /**
     * TRIGGER: reached_5_requests
     */
    public function sendReached5Requests(array $userData)
    {
        return $this->sendApiAutomation(
            $userData,
            'Ya has consultado 5 empresas: esto es lo que no estás viendo',
            'La dirección completa de cada empresa, sin asteriscos.',
            'Ya llevas 5 empresas consultadas. En tus respuestas habrás visto la dirección como <code>*** [ACTUALIZA A PRO PARA VER LA DIRECCION ]</code>: justo el dato que necesitas para facturar o dar de alta un cliente.<br><br>Con el <b>Plan Pro</b> recibes el dato completo en la misma llamada, sin cambiar tu código, y además puedes pedir los administradores y cargos de cada empresa con <code>&amp;admin=true</code>.<br><br>Son ' . $this->lineaPro() . '.',
            'Desbloquear datos Pro',
            base_url('billing?plan=pro'),
            'reached_5_requests'
        );
    }

    /**
     * TRIGGER: reached_80_requests
     */
    public function sendReached80Requests(array $userData)
    {
        $limite = $this->freeLimit();

        return $this->sendApiAutomation(
            $userData,
            'Has usado el 80 % de tus ' . $limite . ' consultas gratuitas',
            'Cuando llegues a ' . $limite . ', la API dejará de responder. Así lo evitas.',
            'Has alcanzado las 80 consultas de tus ' . $limite . ' gratuitas. Cuando llegues a ' . $limite . ', la API responderá con error 429 y tu integración se parará.<br><br>Para que no pase, el <b>Plan Pro</b>:' . $this->ventajasPro() . $this->precioPro() . '<br><br>¿Solo necesitas unas pocas consultas más? Un <b>bono de créditos</b>, sin suscripción y también con los datos completos: <a href="' . site_url('crear-bono-api') . '" style="color:#2563eb;font-weight:700;">crear bono</a>.',
            'Evitar el corte: ver Plan Pro',
            base_url('billing?plan=pro'),
            'reached_80_requests'
        );
    }

    /**
     * TRIGGER: bad_request_help
     *
     * Muchas peticiones de hoy devuelven 400 por el formato del CIF. Es solo ayuda:
     * las respuestas con error no se cobran (ApiKeyFilter solo factura las 200), así
     * que no hay nada que "devolver". Antes el correo decía que se devolvían y el
     * comando restaba esas consultas del uso de hoy, es decir, regalaba consultas
     * buenas. Los ejemplos son los CIF reales que ha enviado el usuario.
     *
     * @param list<string> $ejemplos valores de `cif` que han dado 400 hoy
     */
    public function sendBadRequestHelp(array $userData, int $errorCount, array $ejemplos = []): array
    {
        $code = static fn (string $t, string $fondo) => '<code style="background:' . $fondo . '; padding:6px 10px; display:inline-block; border-radius:4px; margin:4px 0;">' . $t . '</code>';

        $lista = '';
        foreach (array_slice($ejemplos, 0, 3) as $cif) {
            $lista .= $code('❌ /api/v1/companies?cif=' . esc(mb_substr((string) $cif, 0, 60)), '#f1f5f9') . '<br>';
        }
        $bloqueEjemplos = $lista !== ''
            ? '<b>Algunas de las que has enviado hoy:</b><br>' . $lista
            : '<b>Por ejemplo:</b><br>' . $code('❌ /api/v1/companies?cif=A08649477ELADJUDICATARIO', '#f1f5f9') . '<br>';

        return $this->sendApiAutomation(
            $userData,
            'Hoy ' . $errorCount . ' peticiones tuyas han fallado por el formato del CIF',
            'No te las hemos cobrado. Así se corrige el error 400.',
            "Hoy <b>{$errorCount} de tus peticiones</b> han devuelto error 400 (Bad Request). No te preocupes por el cupo: <b>las peticiones con error no se cobran</b>.<br><br>"
                . 'El 400 aparece cuando el parámetro <code>cif</code> no es un identificador fiscal español válido. Lo más habitual es enviar texto pegado al CIF al extraerlo de un documento o de una hoja de cálculo.<br><br>'
                . $bloqueEjemplos . '<br>'
                . '<b>El formato correcto es solo el identificador</b>, sin espacios ni texto añadido:<br>'
                . $code('✅ /api/v1/companies?cif=A08649477', '#dcfce7') . '<br><br>'
                . 'Un truco: antes de llamar, quédate solo con letras y números y comprueba que quedan 9 caracteres.<br><br>'
                . 'Si no ves de dónde sale el texto de más, responde a este correo con un ejemplo y te echamos un cable.',
            'Ver mi dashboard',
            base_url('dashboard'),
            'bad_request_help'
        );
    }

    /**
     * TRIGGER: reached_100_percent_quota
     */
    public function sendQuotaExceeded(array $userData)
    {
        $limite = $this->freeLimit();

        return $this->sendApiAutomation(
            $userData,
            'Has agotado tus ' . $limite . ' consultas gratuitas: tu integración está parada',
            'Actívala de nuevo en un minuto con el Plan Pro, sin cambiar tu código.',
            'Has agotado tus ' . $limite . ' consultas gratuitas. Desde ahora la API responde con error 429 y tu integración no recibe datos.<br><br>Tienes dos formas de reanudarla hoy mismo, sin cambiar tu código:<br><br>• <b>Plan Pro</b>: ' . $this->lineaPro() . ', con la respuesta completa.<br>• <b>Bono de créditos</b>, si solo necesitas unas pocas más, sin suscripción: <a href="' . site_url('crear-bono-api') . '" style="color:#2563eb;font-weight:700;">crear bono</a>.',
            'Reanudar con el Plan Pro',
            base_url('billing?plan=pro'),
            'reached_100_percent_quota'
        );
    }

    /**
     * TRIGGER: monthly_report
     */
    public function sendMonthlyUsageReport(array $userData, int $usage): array
    {
        return $this->sendApiAutomation(
            $userData,
            'Tu uso de la API en los últimos 30 días: ' . number_format($usage, 0, ',', '.') . ' consultas',
            'Resumen de actividad de tu cuenta de APIEmpresas.',
            "Aquí tienes el resumen de actividad de tu cuenta en los últimos 30 días:<br><br>• <b>Consultas a la API realizadas:</b> {$usage}<br><br>Si tu consumo sigue aumentando y necesitas asegurar disponibilidad, mayor tasa de peticiones y datos mercantiles completos sin restricciones, te recomendamos revisar nuestros planes:",
            'Ver Planes y Facturación',
            site_url('billing'),
            'monthly_report'
        );
    }

    /**
     * Aviso de cupo a un cliente de PAGO de la API (80 % o 100 % del mes).
     *
     * Antes no existía: al agotar sus consultas recibía un 429 sin aviso previo. Es
     * a la vez el aviso que evita una baja ("dejó de funcionar") y la venta más fácil
     * (un plan mayor o un bono de créditos).
     *
     * @param array      $plan      fila de api_plans del plan actual (name, monthly_quota)
     * @param array|null $siguiente siguiente plan de la API con más cupo, si hay
     */
    public function sendPaidQuotaWarning(array $userData, array $plan, int $usadas, int $umbral, int $saldoMonedero, ?array $siguiente = null): array
    {
        $cupo     = (int) $plan['monthly_quota'];
        $nombre   = trim((string) ($plan['name'] ?? 'tu plan'));
        $n        = static fn (int $x) => number_format($x, 0, ',', '.');
        $meses    = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                     'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $renueva  = '1 de ' . $meses[(int) date('n', strtotime('first day of next month'))];
        $urlPlan  = site_url('billing');
        $urlBono  = site_url('crear-bono-api');

        // Qué pasa al llegar al 100 %, según tenga o no saldo en el monedero
        $alLlegar = $saldoMonedero > 0
            ? 'se empezará a cobrar de tu monedero (saldo actual: <strong>' . $n($saldoMonedero) . ' créditos</strong>)'
            : 'la API devolverá error <strong>429</strong> hasta el ' . $renueva . ', cuando se renueva tu cupo';

        $opciones = '';
        if ($siguiente) {
            $opciones .= '<li style="margin:0 0 6px;"><strong>Pasar a ' . esc($siguiente['name']) . '</strong>: '
                . $n((int) $siguiente['monthly_quota']) . ' consultas al mes. El cambio es inmediato y no tocas tu código.</li>';
        }
        $opciones .= '<li style="margin:0 0 6px;"><strong>Comprar un bono de créditos</strong> para cubrir este mes sin cambiar de plan: '
            . '<a href="' . $urlBono . '" style="color:#2563eb;font-weight:700;">crear bono</a>.</li>';
        if (!$siguiente) {
            // Business es el plan más alto: si el volumen es estable, plan a medida
            $opciones .= '<li style="margin:0 0 6px;"><strong>Un plan a medida</strong> si vas a necesitar más de '
                . $n($cupo) . ' consultas al mes de forma estable: responde a este correo con tu volumen y te lo preparamos.</li>';
        }
        $lista = '<ul style="margin:0 0 14px; padding-left:20px;">' . $opciones . '</ul>';

        if ($umbral >= 100) {
            $estado = $saldoMonedero > 0
                ? 'Tu integración sigue funcionando, pero ahora cada consulta se descuenta de tu monedero (saldo: <strong>' . $n($saldoMonedero) . ' créditos</strong>).'
                : 'Desde ahora <strong>la API devuelve error 429</strong> a tus peticiones hasta el ' . $renueva . '.';

            $asunto    = $saldoMonedero > 0
                ? 'Has agotado las ' . $n($cupo) . ' consultas de tu plan: ya estás gastando saldo del monedero'
                : 'Has agotado las ' . $n($cupo) . ' consultas de tu plan: tu integración está parada';
            $preheader = $saldoMonedero > 0
                ? 'Las consultas se cobran ahora de tu monedero. Así evitas quedarte sin saldo.'
                : 'Tu cupo se renueva el ' . $renueva . '. Puedes reactivarla hoy mismo.';
            $contenido = $this->p('Has usado las <strong>' . $n($cupo) . ' consultas</strong> de tu plan ' . esc($nombre) . ' de este mes.')
                . $this->p($estado)
                . $this->p($saldoMonedero > 0 ? 'Para no depender del saldo:' : 'Para reanudar sin esperar:') . $lista;
            $boton = $siguiente ? 'Pasar a ' . $siguiente['name'] : 'Comprar un bono de créditos';
        } else {
            // Ritmo de consumo: a qué día del mes llegaría al 100 % si sigue igual
            $diaHoy    = max(1, (int) date('j'));
            $diasMes   = (int) date('t');
            $porDia    = $usadas / $diaHoy;
            $diaTope   = $porDia > 0 ? (int) ceil($cupo / $porDia) : 0;
            $prevision = ($diaTope > $diaHoy && $diaTope <= $diasMes)
                ? ' Al ritmo actual, lo agotarás hacia el <strong>' . $diaTope . ' de ' . $meses[(int) date('n')] . '</strong>.'
                : '';

            $asunto    = 'Has usado el ' . $umbral . ' % de las consultas de tu plan este mes';
            $preheader = $n($usadas) . ' de ' . $n($cupo) . ' consultas. Así evitas que tu integración se pare.';
            $contenido = $this->p('Llevas <strong>' . $n($usadas) . ' de ' . $n($cupo) . '</strong> consultas de tu plan ' . esc($nombre) . ' este mes.' . $prevision)
                . $this->p('Cuando llegues al 100 %, ' . $alLlegar . '. Si esperas más volumen, tienes dos opciones:')
                . $lista;
            $boton = $siguiente ? 'Pasar a ' . $siguiente['name'] : 'Comprar un bono de créditos';
        }

        return $this->sendTemplateEmail('quota_warning', [
            'subject'     => $asunto,
            'preheader'   => $preheader,
            // Sin nombre, la parte local del correo: "Hola Hola," queda descuidado.
            'name'        => esc(trim((string) ($userData['name'] ?? '')) ?: explode('@', (string) $userData['email'])[0]),
            'content'     => $contenido,
            'button_text' => esc($boton),
            'button_url'  => $siguiente ? $urlPlan : $urlBono,
        ], $userData['email'], ['papelo.amh@gmail.com'], [], (int) ($userData['user_id'] ?? $userData['id'] ?? 0));
    }

    /**
     * Cobro de una renovación rechazado (webhook `invoice.payment_failed`).
     *
     * @param array $pago plan_name, product_type, amount, currency, attempt,
     *                    next_attempt (timestamp o null si era el último), pay_url
     */
    public function sendPaymentFailed(array $userData, array $pago): array
    {
        $meses  = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                   'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $plan   = trim((string) ($pago['plan_name'] ?? '')) ?: 'tu suscripción';
        $moneda = ($pago['currency'] ?? 'EUR') === 'EUR' ? '€' : (string) $pago['currency'];
        $importe = number_format((float) ($pago['amount'] ?? 0), 2, ',', '.') . ' ' . $moneda;
        $ultimo = empty($pago['next_attempt']);
        $fecha  = $ultimo ? '' : (int) date('j', $pago['next_attempt']) . ' de ' . $meses[(int) date('n', $pago['next_attempt'])];
        $urlPagar  = trim((string) ($pago['pay_url'] ?? ''));
        $urlPortal = site_url('billing/portal');

        helper('company');   // solvencia()

        // Qué pierde si no se completa, según el producto
        $pierde = match ($pago['product_type'] ?? '') {
            'api'   => 'tu cuenta pasará al plan gratuito de la API y tu integración podría dejar de responder',
            'risk'  => 'perderás Solvencia Pro y dejarás de recibir los avisos de las empresas que vigilas por encima de las ' . (int) solvencia('vigilanciasGratis', 5) . ' del plan gratuito',
            default => 'perderás las ventajas de tu plan',
        };

        $contenido = $this->p('No hemos podido cobrar la renovación de tu plan <strong>' . esc($plan) . '</strong> (' . $importe . ').')
            . $this->p('Suele ser una tarjeta caducada, sin saldo o un cargo que el banco ha bloqueado por seguridad.')
            . ($ultimo
                ? $this->p('<strong>Era el último reintento automático.</strong> Si no se completa el pago, el plan se cancelará: ' . $pierde . '.')
                : $this->p('Volveremos a intentarlo el <strong>' . $fecha . '</strong>. Mientras tanto tu plan sigue activo. Si no se completa, se cancelará: ' . $pierde . '.'))
            . $this->p($urlPagar !== ''
                ? 'Puedes pagar ahora con otra tarjeta con el botón de abajo, sin iniciar sesión.'
                : 'Entra con el botón de abajo y actualiza tu tarjeta.')
            . $this->p('<span style="font-size:14px;color:#64748b;">Para cambiar la tarjeta de los próximos cobros, entra en <a href="' . $urlPortal . '" style="color:#2563eb;">tu panel de facturación</a>. Si crees que es un error, responde a este correo.</span>');

        return $this->sendTemplateEmail('payment_failed', [
            'subject'     => $ultimo
                ? 'Último aviso: tu plan ' . $plan . ' se cancelará si no se completa el pago'
                : 'No hemos podido cobrar tu plan ' . $plan . ': actualiza tu forma de pago',
            'preheader'   => $ultimo
                ? 'Era el último reintento. Paga la factura para no perder el plan.'
                : 'Lo intentaremos de nuevo el ' . $fecha . '. Mientras tanto tu plan sigue activo.',
            'name'        => esc(trim((string) ($userData['name'] ?? '')) ?: explode('@', (string) $userData['email'])[0]),
            'content'     => $contenido,
            'button_text' => $urlPagar !== '' ? 'Pagar la factura (' . $importe . ')' : 'Actualizar forma de pago',
            'button_url'  => $urlPagar !== '' ? $urlPagar : $urlPortal,
        ], $userData['email'], ['papelo.amh@gmail.com'], [], (int) ($userData['user_id'] ?? $userData['id'] ?? 0));
    }

    /**
     * Bienvenida al contratar un plan de pago de la API (Pro = 2, Business = 3).
     *
     * Antes solo llegaba la factura. Lo que se cuenta aquí está comprobado en el
     * código: el desenmascarado (mask_company_data solo se aplica al plan 1), los
     * administradores con ?admin=true, la matriz de PlanAccessService y el cupo por
     * mes natural de ApiKeyFilter.
     *
     * @param array $plan id, name, monthly_quota
     */
    public function sendApiPlanWelcome(array $userData, array $plan): array
    {
        $n       = static fn (int $x) => number_format($x, 0, ',', '.');
        $nombre  = trim((string) ($plan['name'] ?? '')) ?: 'de pago';
        $cupo    = (int) ($plan['monthly_quota'] ?? 0);
        $business = (int) ($plan['id'] ?? 0) === 3;
        $li = static fn (string $h) => '<li style="margin:0 0 8px;">' . $h . '</li>';
        $c  = static fn (string $t) => '<code style="background:#f1f5f9;padding:1px 5px;border-radius:4px;font-size:13px;">' . $t . '</code>';

        $cambios = $li('<strong>Datos sin enmascarar</strong>: dirección completa, objeto social íntegro y coordenadas (lat/lng).')
            . $li('<strong>Administradores y cargos</strong>: añade ' . $c('&amp;admin=true') . ' a ' . $c('/api/v1/companies') . '.')
            . $li('<strong>Scoring y señales</strong>: ' . $c('/api/v1/companies/score') . ' y ' . $c('/api/v1/companies/signals') . '.');
        if ($business) {
            $cambios .= $li('<strong>Webhooks</strong> (' . $c('/api/v1/webhooks') . '), <strong>contratos públicos</strong> (' . $c('/api/v1/companies/contracts') . ') y <strong>perfil de riesgo</strong> (' . $c('/api/v1/companies/risk-profile') . ').')
                . $li('<strong>Insights y mensajes con IA</strong> completos: ' . $c('/api/v1/companies/insights') . ' y ' . $c('/api/v1/companies/contact-prep') . '.');
        }

        $contenido = $this->p('Ya tienes activo el plan <strong>' . esc($nombre) . '</strong>. No tienes que cambiar nada: tu API Key y tu código siguen igual, y desde la próxima llamada las respuestas llegan completas.')
            . $this->p('<strong>Lo que cambia:</strong>')
            . '<ul style="margin:0 0 16px; padding-left:20px;">' . $cambios . '</ul>'
            . $this->p('<strong>' . $n($cupo) . ' consultas al mes</strong>, que se renuevan el día 1 de cada mes. Lo que te queda viene en la cabecera ' . $c('X-Quota-Remaining') . ' de cada respuesta y en ' . $c('/api/v1/usage') . ', que no gasta cupo. Te avisaremos por correo al llegar al 80 % y al 100 %.')
            . $this->p('<span style="font-size:14px;color:#64748b;">La factura te llega en un correo aparte. Si necesitas ayuda con la integración, responde a este correo.</span>');

        return $this->sendTemplateEmail('api_plan_welcome', [
            'subject'     => 'Tu plan ' . $nombre . ' de la API ya está activo: ' . $n($cupo) . ' consultas al mes',
            'preheader'   => 'No tienes que cambiar tu API Key ni tu código. Esto es lo que cambia en tus respuestas.',
            'name'        => esc(trim((string) ($userData['name'] ?? '')) ?: explode('@', (string) $userData['email'])[0]),
            'content'     => $contenido,
            'button_text' => 'Ir a mi panel',
            'button_url'  => site_url('dashboard'),
        ], $userData['email'], ['papelo.amh@gmail.com'], [], (int) ($userData['user_id'] ?? $userData['id'] ?? 0));
    }

    /** "24 de octubre" a partir de una fecha de la BD. */
    private function fechaLarga(?string $fecha): string
    {
        $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                  'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $t = $fecha ? strtotime($fecha) : false;
        return $t ? (int) date('j', $t) . ' de ' . $meses[(int) date('n', $t)] : '';
    }

    /**
     * Confirmación al cliente de que ha cancelado un plan de pago.
     *
     * Antes solo se avisaba al admin. Dice hasta cuándo conserva el plan, qué pasa
     * después y, según el motivo que marcó, una salida que no sea irse del todo.
     * No se ofrece "reactivar desde el portal de Stripe": el webhook no atiende
     * customer.subscription.updated y la BD seguiría diciendo "cancelada".
     *
     * @param array  $plan   name, product_type
     * @param string $motivo clave de Billing::cancel_subscription (too_expensive, low_usage...)
     */
    public function sendSubscriptionCanceled(array $userData, array $plan, ?string $accesoHasta, string $motivo = ''): array
    {
        helper('company');   // solvencia()
        $nombre = trim((string) ($plan['name'] ?? '')) ?: 'de pago';
        $tipo   = (string) ($plan['product_type'] ?? '');
        $fecha  = $this->fechaLarga($accesoHasta);
        $bono   = site_url('crear-bono-api');

        $despues = match ($tipo) {
            'api'   => 'A partir de entonces tu cuenta pasa al plan gratuito de la API. Tu API Key sigue siendo la misma, pero las respuestas vuelven a llegar con campos enmascarados y dejas de tener cupo mensual.',
            'risk'  => 'A partir de entonces pasas al plan gratuito de Solvencia: ' . (int) solvencia('consultasGratis', 3) . ' consultas al mes y ' . (int) solvencia('vigilanciasGratis', 5) . ' empresas vigiladas.',
            default => 'A partir de entonces tu cuenta pasa al plan gratuito.',
        };

        $extra = '';
        if ($tipo === 'api' && in_array($motivo, ['too_expensive', 'low_usage'], true)) {
            $extra = 'Si no llegabas a gastar el cupo del mes, un <strong>bono de créditos</strong> te permite pagar solo lo que consultas, sin suscripción: <a href="' . $bono . '" style="color:#2563eb;font-weight:700;">crear bono</a>.';
        } elseif (in_array($motivo, ['technical_issues', 'missing_features'], true)) {
            $extra = 'Nos ayudaría mucho saber qué falló o qué echaste en falta. Responde a este correo: lo lee una persona.';
        } elseif ($motivo === 'temporary_pause') {
            $extra = 'Tu cuenta y tu historial se quedan como están. Cuando quieras volver, contratas de nuevo y sigues donde lo dejaste.';
        }

        $contenido = $this->p('Confirmamos la cancelación de tu plan <strong>' . esc($nombre) . '</strong>. No se te volverá a cobrar'
                . ($fecha !== '' ? ' y mantienes todo lo que incluye hasta el <strong>' . $fecha . '</strong>.' : '.'))
            . $this->p($despues)
            . ($extra !== '' ? $this->p($extra) : '')
            . $this->p('<span style="font-size:14px;color:#64748b;">¿Ha sido un error o has cambiado de idea? Responde a este correo'
                . ($fecha !== '' ? ' antes del ' . $fecha : '') . ' y lo dejamos como estaba.</span>');

        return $this->sendTemplateEmail('subscription_canceled', [
            'subject'     => 'Hemos cancelado tu plan ' . $nombre . ($fecha !== '' ? ': tienes acceso hasta el ' . $fecha : ''),
            'preheader'   => 'No se te volverá a cobrar. Esto es lo que pasa a partir de esa fecha.',
            'name'        => esc(trim((string) ($userData['name'] ?? '')) ?: explode('@', (string) $userData['email'])[0]),
            'content'     => $contenido,
            'button_text' => 'Ver mi cuenta',
            'button_url'  => site_url('billing'),
        ], $userData['email'], ['papelo.amh@gmail.com'], [], (int) ($userData['user_id'] ?? $userData['id'] ?? 0));
    }

    /**
     * Recuperación: 30 días después de que termine un Pro/Business de la API cancelado.
     *
     * Sin descuento, porque no hay cupones configurados en Stripe. Se adapta al motivo
     * de la baja si se guardó.
     */
    public function sendApiWinback(array $userData, array $plan, string $motivo = ''): array
    {
        $nombre = trim((string) ($plan['name'] ?? '')) ?: 'de pago';
        $bono   = site_url('crear-bono-api');

        $porMotivo = match ($motivo) {
            'too_expensive', 'low_usage'
                => 'Si lo dejaste por precio o porque no gastabas el cupo del mes, un <strong>bono de créditos</strong> te permite pagar solo lo que consultas, sin suscripción: <a href="' . $bono . '" style="color:#2563eb;font-weight:700;">crear bono</a>.',
            'technical_issues', 'missing_features'
                => 'Si lo dejaste por algo que no funcionaba o que echabas en falta, cuéntanoslo respondiendo a este correo. Si ya está resuelto te lo diremos, y si no, nos ayudas a priorizarlo.',
            'switched_solution'
                => 'Si ahora usas otra solución y hay algo que no te convence, responde a este correo y te decimos si lo cubrimos.',
            default
                => 'Si nos cuentas en una línea por qué lo dejaste, respondiendo a este correo, nos ayudas mucho.',
        };

        $contenido = $this->p('Hace un mes que terminó tu plan <strong>' . esc($nombre) . '</strong> de la API.')
            . $this->p('Tu cuenta y tu API Key siguen activas, así que para volver basta con activar el plan: no tienes que cambiar nada en tu integración.')
            . $this->p($porMotivo);

        return $this->sendTemplateEmail('api_winback', [
            'subject'     => 'Tu API Key sigue activa: vuelve al plan ' . $nombre . ' cuando quieras',
            'preheader'   => 'Mismo código, misma clave. Y si el plan se te quedaba grande, hay bonos sin suscripción.',
            'name'        => esc(trim((string) ($userData['name'] ?? '')) ?: explode('@', (string) $userData['email'])[0]),
            'content'     => $contenido,
            'button_text' => 'Ver planes',
            'button_url'  => site_url('billing'),
        ], $userData['email'], ['papelo.amh@gmail.com'], [], (int) ($userData['user_id'] ?? $userData['id'] ?? 0));
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
     * Correo de Solvencia con la plantilla común `risk_generic`.
     *
     * $contenidoHtml es HTML ya construido: quien llama escapa lo que venga de datos
     * (nombres de empresa, etc.).
     */
    public function sendRiskGeneric(array $userData, string $asunto, string $contenidoHtml, string $botonTexto, string $botonUrl, string $preheader = '', string $tipo = ''): array
    {
        $nombre = trim((string) ($userData['name'] ?? ''));
        if ($nombre === '' && !empty($userData['email'])) {
            $nombre = explode('@', (string) $userData['email'])[0];
        }

        return $this->sendTemplateEmail('risk_generic', [
            '_log_slug'   => $tipo,
            'asunto'      => $asunto,
            'preheader'   => $preheader,
            'name'        => esc($nombre),
            'content'     => $contenidoHtml,
            'button_text' => esc($botonTexto),
            'button_url'  => $botonUrl,
        ], $userData['email'], ['papelo.amh@gmail.com'], [], (int) ($userData['user_id'] ?? $userData['id'] ?? 0));
    }

    /** Párrafo de correo con el estilo de la plantilla. */
    private function p(string $html): string
    {
        return '<p style="margin: 0 0 14px;">' . $html . '</p>';
    }

    /**
     * TRIGGER: risk_educational_savings_48h — 48 h después de agotar las 3 consultas.
     *
     * Decía "tarifa plana sin límites", "25-35 €" y "protege tu negocio de impagos":
     * las tres cosas se habían quitado del resto del producto (topes de 300 y 25,
     * ancla real de 20-44 € y el score no predice). Usaba además la plantilla de la
     * API, con asunto "Notificación APIEmpresas.es".
     */
    public function sendRiskEducationalSavings(array $userData): array
    {
        helper('company');   // solvencia()
        $vig = (int) solvencia('vigilanciasPro', 25);
        $contenido = $this->p('Un informe suelto de una empresa cuesta entre 20 y 44 € en un proveedor tradicional, y es una foto del día en que lo pides.')
            . $this->p('Con <strong>Solvencia Pro</strong> (29 €/mes + IVA) vigilas hasta <strong>' . $vig . ' empresas</strong> y te escribimos el día que el BORME publique algo de ellas: un concurso, una disolución, un cese de administrador o un cierre de hoja registral. Incluye ' . (int) solvencia('consultasPro', 300) . ' consultas al mes.')
            . $this->p('Sin permanencia y con ' . (int) solvencia('garantiaDias', 30) . ' días de garantía: si no te sirve, te devolvemos el dinero. Si lo pagas anual, 290 € (dos meses gratis).');

        return $this->sendRiskGeneric(
            $userData,
            'Un informe es una foto. Solvencia Pro te avisa cuando cambia',
            $contenido,
            'Ver Solvencia Pro',
            site_url('billing?view=risk&plan=risk_pro'),
            'Vigila hasta ' . $vig . ' clientes por 29 €/mes y entérate el día que el BORME publique algo.',
            'risk_educational_savings_48h'
        );
    }

    /**
     * TRIGGER: risk_monthly_renewal — días 1 a 3: "ya tienes tus consultas".
     *
     * Antes salía los días 28-31 diciendo "se renuevan" (todavía no se podían usar)
     * y con la plantilla de la API. Ahora sale cuando ya están disponibles.
     *
     * @param bool $agotoElMesPasado si gastó las gratuitas el mes anterior
     */
    public function sendRiskMonthlyRenewal(array $userData, bool $agotoElMesPasado = false): array
    {
        helper('company');   // solvencia()
        $gratis = (int) solvencia('consultasGratis', 3);
        $contenido = $this->p('Ya tienes de nuevo <strong>' . $gratis . ' consultas gratuitas</strong> de solvencia este mes: el dictamen completo de cualquier empresa de España, con cada acto del BORME y su fecha.');
        if ($agotoElMesPasado) {
            $contenido .= $this->p('El mes pasado las gastaste todas. Si revisas clientes a menudo, con Solvencia Pro tienes ' . (int) solvencia('consultasPro', 300) . ' al mes y vigilamos hasta ' . (int) solvencia('vigilanciasPro', 25) . ' empresas por ti. <a href="' . site_url('billing?view=risk&plan=risk_pro') . '" style="color:#2563eb;font-weight:700;">Ver Solvencia Pro</a>.');
        }

        return $this->sendRiskGeneric(
            $userData,
            'Ya tienes tus ' . $gratis . ' consultas de solvencia de este mes',
            $contenido,
            'Consultar una empresa',
            site_url('dashboard?view=risk'),
            'Ya puedes revisar ' . $gratis . ' empresas más este mes.',
            'risk_monthly_renewal'
        );
    }

    /**
     * TRIGGER: risk_watch_full — el gratuito ha llenado su lista de vigilancia.
     *
     * Config\Solvencia lo llama "un upsell mucho mejor que el paywall": para entonces
     * ya ha visto avisos y sabe lo que se lleva. No había ningún correo en ese momento.
     *
     * @param list<string> $empresas nombres de las que vigila
     */
    public function sendRiskWatchFull(array $userData, array $empresas): array
    {
        helper('company');   // solvencia()
        $tope = (int) solvencia('vigilanciasGratis', 5);
        $pro  = (int) solvencia('vigilanciasPro', 25);

        $lista = '';
        foreach (array_slice($empresas, 0, 5) as $n) {
            $lista .= '<li style="margin: 0 0 4px;">' . esc($n) . '</li>';
        }

        $contenido = $this->p('Tu lista de vigilancia está llena: vigilas <strong>' . $tope . ' de ' . $tope . '</strong> empresas.')
            . ($lista !== '' ? '<ul style="margin: 0 0 14px; padding-left: 20px; color: #334155;">' . $lista . '</ul>' : '')
            . $this->p('Para vigilar otra tienes que quitar una. Con <strong>Solvencia Pro</strong> vigilas hasta <strong>' . $pro . '</strong> y ves el detalle de cada acto en el aviso, por 29 €/mes + IVA, sin permanencia y con ' . (int) solvencia('garantiaDias', 30) . ' días de garantía.');

        return $this->sendRiskGeneric(
            $userData,
            'Tu lista de vigilancia está llena (' . $tope . ' de ' . $tope . ')',
            $contenido,
            'Vigilar hasta ' . $pro . ' empresas',
            site_url('billing?view=risk&plan=risk_pro'),
            'Para vigilar otra empresa tienes que quitar una.',
            'risk_watch_full'
        );
    }

    /**
     * TRIGGER: risk_checkout_abandoned — empezó a pagar Solvencia Pro y no terminó.
     *
     * El antiguo "paywall_abandoned" no era esto (saltaba al gastar las 3 consultas).
     * Aquí sí es un pago que se quedó a medias; lo más útil es ofrecer ayuda, porque
     * una parte son tarjetas rechazadas o dudas, no falta de interés.
     */
    public function sendRiskCheckoutAbandoned(array $userData, string $periodo = 'monthly'): array
    {
        helper('company');   // solvencia()
        $anual = $periodo === 'annual';
        $contenido = $this->p('Empezaste a activar <strong>Solvencia Pro</strong>' . ($anual ? ' en su modalidad anual' : '') . ' y el pago no llegó a completarse.')
            . $this->p('Si fue un problema con la tarjeta o tienes alguna duda sobre el plan, responde a este correo y te ayudamos. Si prefieres retomarlo, el botón te lleva de vuelta.')
            . $this->p('Recuerda: sin permanencia y con ' . (int) solvencia('garantiaDias', 30) . ' días de garantía.');

        return $this->sendRiskGeneric(
            $userData,
            'Tu activación de Solvencia Pro se quedó a medias',
            $contenido,
            'Retomar la activación',
            site_url('billing?view=risk&plan=risk_pro'),
            '¿Hubo algún problema con el pago? Te ayudamos.',
            'risk_checkout_abandoned'
        );
    }

    /**
     * TRIGGER: risk_cartera_resumen — días 1 a 3, a quien vigila alguna empresa.
     *
     * La mayoría de los meses no pasa nada, y quien vigila (o paga) y no recibe
     * ningún correo concluye que no sirve. Este resumen es la prueba de que se
     * está vigilando, pase algo o no.
     *
     * @param list<array{nombre:string,actos:int}> $conActos empresas con actos el mes pasado
     */
    public function sendRiskCarteraResumen(array $userData, string $mes, int $vigiladas, array $conActos, bool $esPro): array
    {
        helper('company');   // solvencia()
        $totalActos = array_sum(array_column($conActos, 'actos'));

        $contenido = $this->p('En ' . $mes . ' revisamos cada día el BORME de las <strong>' . $vigiladas . ' ' . ($vigiladas === 1 ? 'empresa' : 'empresas') . '</strong> que vigilas.');

        if (empty($conActos)) {
            $asunto = 'Tu cartera en ' . $mes . ': sin novedades en el BORME';
            $contenido .= $this->p('<strong>No se publicó nada sobre ninguna.</strong> Seguimos mirando cada día y te escribimos en cuanto aparezca algo.');
        } else {
            $asunto = 'Tu cartera en ' . $mes . ': ' . $totalActos . ' ' . ($totalActos === 1 ? 'acto publicado' : 'actos publicados');
            $lista = '';
            foreach (array_slice($conActos, 0, 8) as $e) {
                $lista .= '<li style="margin: 0 0 4px;">' . esc($e['nombre']) . ' — ' . (int) $e['actos'] . ' ' . ((int) $e['actos'] === 1 ? 'acto' : 'actos') . '</li>';
            }
            $contenido .= $this->p('Se publicaron actos de <strong>' . count($conActos) . ' ' . (count($conActos) === 1 ? 'empresa' : 'empresas') . '</strong>:')
                . '<ul style="margin: 0 0 14px; padding-left: 20px; color: #334155;">' . $lista . '</ul>'
                . $this->p('Tienes el detalle en su ficha.');
        }

        if (!$esPro) {
            $contenido .= $this->p('<span style="color:#64748b;font-size:14px;">Vigilas ' . $vigiladas . ' de ' . (int) solvencia('vigilanciasGratis', 5) . ' empresas con el plan gratuito. Con Solvencia Pro, hasta ' . (int) solvencia('vigilanciasPro', 25) . '.</span>');
        }

        return $this->sendRiskGeneric(
            $userData,
            $asunto,
            $contenido,
            'Ver mi vigilancia',
            site_url('dashboard?view=risk'),
            empty($conActos) ? 'Ninguna de tus empresas vigiladas se movió en ' . $mes . '.' : $totalActos . ' actos nuevos en tus empresas vigiladas.',
            'risk_cartera_resumen'
        );
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

        // Nombre con el que se registra el envío (email_logs, source del enlace). Por
        // defecto la plantilla; las plantillas compartidas pasan el tipo de aviso.
        $logSlug = !empty($data['_log_slug']) ? (string) $data['_log_slug'] : $slug;
        unset($data['_log_slug']);

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
            'risk_pro_welcome',
            // Aviso de servicio a clientes de pago: su integración se va a parar.
            'quota_warning',
            // Cobro de renovación rechazado: sin él, el cliente pierde el plan sin saber por qué.
            'payment_failed',
            // Bienvenida al contratar Pro o Business de la API (antes solo llegaba la factura)
            'api_plan_welcome',
            // Confirmación de una acción del propio cliente
            'subscription_canceled',
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
            $user = $db->table('users')->select('id, lang')->where('email', $to)->get()->getRow();
            if ($user && !empty($user->lang)) {
                $userLang = $user->lang;
            }
            // Para dejar el envío en email_logs aunque quien llama no pase el id
            if ($user) {
                $userId = (int) $user->id;
            }
        }

        // Determine correct subject and body based on language
        $subjectTemplate = ($userLang === 'en' && !empty($template->subject_en)) ? $template->subject_en : $template->subject;
        $bodyTemplate = ($userLang === 'en' && !empty($template->body_en)) ? $template->body_en : $template->body;

        // Valores comunes que el seed deja como marcadores ({year}, {date}...). Lo que
        // mande quien llama tiene prioridad.
        $data += [
            'year'     => date('Y'),
            'date'     => date('d/m/Y'),
            'datetime' => date('Y-m-d H:i:s'),
        ];

        $subject = $this->parsePlaceholders($subjectTemplate, $data);
        $body    = $this->parsePlaceholders($bodyTemplate, $data);

        // Si una plantilla con asunto variable se usa sin pasarlo, que no salga
        // "{subject}" en la bandeja de entrada.
        if (str_contains($subject, '{subject}') || trim($subject) === '') {
            $subject = 'APIEmpresas.es';
        }
        $body = str_replace('{preheader}', '', $body);

        // Medición de clics (antes de añadir los pies de baja, que no se envuelven)
        $trackingCode = null;
        if (!in_array($slug, self::SIN_SEGUIMIENTO, true) && $userId > 0) {
            $trackingCode = bin2hex(random_bytes(16));
            $body = $this->trackLinks($body, $logSlug, $trackingCode);
        }

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
                $this->logToDatabase($userId, $subject, $body, 'success', null, $trackingCode, $logSlug);
            }
            return ['success' => true, 'body' => $body];
        } else {
            $error = $email->printDebugger(['headers']);
            log_message('error', "[EmailService] Error al enviar [{$slug}] a {$to}: " . $error);
            if ($userId > 0) {
                $this->logToDatabase($userId, $subject, $body, 'error', $error, $trackingCode, $logSlug);
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
