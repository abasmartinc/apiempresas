<?php

namespace App\Services;

/**
 * Pedidos a medida (Config\PedidosMedida): cobro, registro del pago, correo con
 * el enlace y generación del CSV.
 *
 * QUIÉN PUEDE DESCARGAR
 * ---------------------
 * El token del pedido solo da acceso a la página de pago. Para descargar hace
 * falta el id de la sesión de Stripe (cs_…) de ESE pedido y que Stripe diga que
 * está cobrada. El id no se puede adivinar y se comprueba contra Stripe en cada
 * descarga, así que el enlace del correo funciona sin iniciar sesión y sin que
 * dependa de la sesión PHP (que se pierde en el salto a Stripe).
 *
 * DÓNDE SE APUNTA EL PAGO
 * -----------------------
 * En writable/pedidos/{token}.json. Lo escriben los dos que se enteran del
 * cobro —la página de gracias y el webhook—, el primero que llegue; el correo
 * con el enlace sale una sola vez. Sirve para que el enlace de pago, si se abre
 * otra vez, no deje pagar dos veces y enseñe la descarga.
 */
class PedidoMedidaService
{
    public const PLAN = 'pedido_medida';

    private \Config\PedidosMedida $config;

    public function __construct()
    {
        $this->config = config('PedidosMedida');
    }

    /* ------------------------------------------------------------------ */
    /*  Pedido y estado                                                    */
    /* ------------------------------------------------------------------ */

    public function pedido(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{16,64}$/', $token)) {
            return null;
        }
        $p = $this->config->pedidos[$token] ?? null;
        if (!$p) {
            return null;
        }
        $p['token'] = $token;
        $p['iva']   = round($p['importe'] * 0.21, 2);
        $p['total'] = round($p['importe'] + $p['iva'], 2);
        return $p;
    }

    public function caducado(array $pedido): bool
    {
        return !empty($pedido['caduca']) && date('Y-m-d') > $pedido['caduca'];
    }

    /** Pago registrado de este pedido (o null). */
    public function pago(string $token): ?array
    {
        $f = $this->ficheroPago($token);
        if (!is_file($f)) {
            return null;
        }
        $d = json_decode((string) file_get_contents($f), true);
        return is_array($d) && !empty($d['session_id']) ? $d : null;
    }

    /**
     * Apunta el pago y, la primera vez, manda el correo con el enlace de descarga.
     * Lo llaman la página de gracias y el webhook.
     */
    public function registrarPago(string $token, string $sessionId, string $email = '', ?int $importeCentimos = null): void
    {
        $pedido = $this->pedido($token);
        if (!$pedido) {
            return;
        }

        $f = $this->ficheroPago($token);
        if (!is_dir(dirname($f))) {
            mkdir(dirname($f), 0755, true);
            // Que un pago simulado en local no acabe subido a producción
            file_put_contents(dirname($f) . '/.gitignore', "*\n!.gitignore\n");
        }

        // Bloqueo para que el webhook y la página de gracias no manden dos correos
        $fp = fopen($f . '.lock', 'c');
        if ($fp) {
            flock($fp, LOCK_EX);
        }

        try {
            $previo = $this->pago($token);
            if ($previo && ($previo['session_id'] ?? '') === $sessionId && !empty($previo['email_enviado'])) {
                return;
            }
            // Segundo cobro del mismo pedido (dos pestañas, dos pagos): se deja el
            // primero apuntado y se avisa para devolverlo. Su descarga funciona igual.
            if ($previo && ($previo['session_id'] ?? '') !== $sessionId) {
                log_message('critical', "[PedidoMedida] Pedido {$pedido['referencia']} cobrado dos veces: {$previo['session_id']} y {$sessionId}. Devolver uno en Stripe.");
                return;
            }

            $user  = (new \App\Models\UserModel())->find((int) $pedido['user_id']);
            $email = $email !== '' ? $email : (string) ($user->email ?? '');

            $datos = [
                'session_id'   => $sessionId,
                'user_id'      => (int) $pedido['user_id'],
                'email'        => $email,
                'importe'      => $importeCentimos !== null ? $importeCentimos / 100 : $pedido['total'],
                'pagado_en'    => $previo['pagado_en'] ?? date('Y-m-d H:i:s'),
                'email_enviado' => false,
            ];

            // En local (simulador) la BD es la de producción: el correo le llegaría
            // al cliente de verdad. No se manda.
            if (str_starts_with($sessionId, 'sim_')) {
                log_message('info', "[PedidoMedida] Simulador: no se envía el correo de descarga a {$email}");
            } elseif ($email !== '') {
                $datos['email_enviado'] = $this->enviarCorreoDescarga($pedido, $email, (string) ($user->name ?? ''), $sessionId);
            }

            file_put_contents($f, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            log_message('info', "[PedidoMedida] Pago registrado {$pedido['referencia']} sesión {$sessionId}");
        } finally {
            if ($fp) {
                flock($fp, LOCK_UN);
                fclose($fp);
            }
        }
    }

    /**
     * ¿Esta sesión de pago es de este pedido y está cobrada?
     * Devuelve la sesión de Stripe (o un array en el simulador) o null.
     */
    public function sesionCobrada(string $token, string $sessionId)
    {
        if ($sessionId === '') {
            return null;
        }

        // Simulador (entorno local): solo vale la sesión que el propio simulador apuntó.
        if (str_starts_with($sessionId, 'sim_')) {
            $pago = $this->pago($token);
            return (env('BILLING_MODE') === 'simulator' && $pago && $pago['session_id'] === $sessionId)
                ? ['id' => $sessionId, 'simulada' => true]
                : null;
        }

        if (!str_starts_with($sessionId, 'cs_')) {
            return null;
        }

        try {
            $s = (new \Stripe\StripeClient(env('STRIPE_SECRET_KEY')))->checkout->sessions->retrieve($sessionId);
        } catch (\Throwable $e) {
            log_message('error', '[PedidoMedida] No se pudo consultar la sesión ' . $sessionId . ': ' . $e->getMessage());
            return null;
        }

        $esDeEste = ($s->metadata->plan ?? '') === self::PLAN
            && ($s->metadata->pedido ?? '') === $token;
        $cobrada = in_array((string) ($s->payment_status ?? ''), ['paid', 'no_payment_required'], true);

        return ($esDeEste && $cobrada) ? $s : null;
    }

    /* ------------------------------------------------------------------ */
    /*  Stripe                                                             */
    /* ------------------------------------------------------------------ */

    public function crearCheckout(array $pedido): string
    {
        $stripe  = new StripeService();
        $billing = new BillingService();
        $token   = $pedido['token'];

        $lineItem = $billing->buildSinglePaymentLineItem(
            $pedido['producto'],
            $pedido['descripcion'],
            (float) $pedido['importe'],
            $stripe->getTaxRateId()
        );

        $metadata = [
            'user_id'      => (string) $pedido['user_id'],
            'plan'         => self::PLAN,
            'period'       => 'single',
            'pedido'       => $token,
            'referencia'   => (string) $pedido['referencia'],
            'product_name' => mb_substr((string) $pedido['producto'], 0, 450),
        ];

        $params = [
            'mode'                       => 'payment',
            'line_items'                 => [$lineItem],
            'success_url'                => site_url("pedido/{$token}/gracias") . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'                 => site_url("pedido/{$token}"),
            'client_reference_id'        => (string) $pedido['user_id'],
            'customer_creation'          => 'if_required',
            'billing_address_collection' => 'required',
            'tax_id_collection'          => ['enabled' => true],
            'invoice_creation'           => [
                'enabled'      => true,
                'invoice_data' => ['metadata' => $metadata],
            ],
            'metadata' => $metadata,
        ];

        $user = (new \App\Models\UserModel())->find((int) $pedido['user_id']);
        if (!empty($user->stripe_customer_id)) {
            $params['customer'] = $user->stripe_customer_id;
        } elseif (!empty($user->email)) {
            $params['customer_email'] = $user->email;
        }

        return $stripe->createCheckoutSession($params)->url;
    }

    /* ------------------------------------------------------------------ */
    /*  Datos                                                              */
    /* ------------------------------------------------------------------ */

    public function contar(array $pedido): int
    {
        return count($this->idsEmpresas($pedido));
    }

    /** Columnas opcionales de `companies` que existen de verdad en esta BD. */
    public function columnasDisponibles(): array
    {
        $db = \Config\Database::connect();
        try {
            $campos = array_flip($db->getFieldNames('companies'));
        } catch (\Throwable $e) {
            $campos = [];
        }

        $anio = $this->config->columnaAnioCuentas;
        if ($anio === '' || !isset($campos[$anio])) {
            $anio = '';
            foreach ($this->config->candidatosAnioCuentas as $c) {
                if (isset($campos[$c])) {
                    $anio = $c;
                    break;
                }
            }
        }

        return [
            'ventas'      => isset($campos['ventas_raw']) ? 'ventas_raw' : '',
            'anio'        => $anio,
            'postal_code' => isset($campos['postal_code']) ? 'postal_code' : '',
        ];
    }

    /**
     * Escribe el CSV en $fp: punto y coma y BOM, que es lo que hace que un Excel
     * en español lo abra en columnas y con los acentos bien.
     */
    /* ------------------------------------------------------------------ */
    /*  Archivo pregenerado                                                */
    /* ------------------------------------------------------------------ */

    /** Ruta del CSV ya generado de este pedido. */
    public function rutaFichero(string $token): string
    {
        return WRITEPATH . 'pedidos/' . $token . '.csv';
    }

    /** Datos del archivo ya generado (o null si no existe). */
    public function fichero(string $token): ?array
    {
        $csv  = $this->rutaFichero($token);
        $meta = $csv . '.json';
        if (!is_file($csv)) {
            return null;
        }
        $d = is_file($meta) ? (json_decode((string) file_get_contents($meta), true) ?: []) : [];
        return [
            'ruta'        => $csv,
            'bytes'       => filesize($csv),
            'filas'       => (int) ($d['filas'] ?? 0),
            'generado_en' => (string) ($d['generado_en'] ?? date('Y-m-d H:i:s', filemtime($csv))),
            'columnas'    => $d['columnas'] ?? [],
        ];
    }

    /**
     * Genera el CSV UNA vez y lo deja en writable/pedidos/{token}.csv. Así el
     * cliente descarga un archivo ya hecho y congelado: lo que se revisó es
     * exactamente lo que recibe, y la descarga no depende de la base de datos.
     *
     * Se escribe en un temporal y se renombra al final: una descarga que llegue
     * a mitad nunca ve un archivo a medias.
     */
    public function generarFichero(array $pedido): array
    {
        $dir = WRITEPATH . 'pedidos';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            file_put_contents($dir . '/.gitignore', "*\n!.gitignore\n");
        }

        @set_time_limit(900);
        $destino = $this->rutaFichero($pedido['token']);
        $tmp     = $destino . '.tmp';

        $fp = fopen($tmp, 'w');
        if (!$fp) {
            throw new \RuntimeException('No se puede escribir en ' . $tmp);
        }
        try {
            $filas = $this->escribirCsv($pedido, $fp);
        } finally {
            fclose($fp);
        }

        if (is_file($destino)) {
            unlink($destino);
        }
        rename($tmp, $destino);

        file_put_contents($destino . '.json', json_encode([
            'filas'       => $filas,
            'generado_en' => date('Y-m-d H:i:s'),
            'columnas'    => $this->columnasDisponibles(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        log_message('info', "[PedidoMedida] Archivo generado {$pedido['referencia']}: {$filas} empresas");

        return $this->fichero($pedido['token']);
    }

    public function escribirCsv(array $pedido, $fp): int
    {
        $db   = \Config\Database::connect();
        $cols = $this->columnasDisponibles();
        if ($cols['anio'] === '') {
            log_message('warning', '[PedidoMedida] No hay columna de año de cuentas en companies; la columna del CSV sale vacía.');
        }

        [$mapa2025, $etiquetas2025] = $this->mapaCnae2025();

        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, [
            'NIF',
            'Razón social',
            'Estado',
            'Fecha de constitución',
            'CNAE registrado',
            'Actividad (CNAE registrado)',
            'CNAE-2025',
            'Actividad (CNAE-2025)',
            'Tramo de facturación',
            'Año últimas cuentas',
            'Capital social',
            'Teléfono',
            'Dirección',
            'Código postal',
            'Municipio',
            'Provincia / Registro Mercantil',
            'Objeto social',
        ], ';');

        $select = 'id, cif, company_name, estado, fecha_constitucion, cnae_code, cnae_label, capital_social_raw, '
                . 'phone, phone_mobile, address, municipality, registro_mercantil, objeto_social';
        foreach (['ventas', 'anio', 'postal_code'] as $k) {
            if ($cols[$k] !== '') {
                $select .= ', ' . $cols[$k] . ' AS x_' . $k;
            }
        }

        $total = 0;
        foreach (array_chunk($this->idsEmpresas($pedido), 2000) as $ids) {
            $porId = [];
            foreach ($db->table('companies')->select($select)->whereIn('id', $ids)->get()->getResultArray() as $fila) {
                $porId[(int) $fila['id']] = $fila;
            }

            // En el orden de idsEmpresas() (alfabético por razón social)
            foreach ($ids as $id) {
                if (!isset($porId[$id])) {
                    continue;
                }
                $r = $porId[$id];
                $codigo = substr(preg_replace('/\D/', '', (string) $r['cnae_code']), 0, 4);

                if (isset($mapa2025[$codigo])) {
                    $c2025 = implode(' / ', array_keys($mapa2025[$codigo]));
                    $l2025 = implode(' / ', array_unique(array_values($mapa2025[$codigo])));
                } elseif (isset($etiquetas2025[$codigo])) {
                    // Ya registrada con el código de 2025
                    $c2025 = $codigo;
                    $l2025 = $etiquetas2025[$codigo];
                } else {
                    $c2025 = '';
                    $l2025 = '';
                }

                $telefono = trim((string) ($r['phone'] ?? '')) ?: trim((string) ($r['phone_mobile'] ?? ''));

                fputcsv($fp, [
                    $r['cif'],
                    $r['company_name'],
                    mb_strtoupper(trim((string) $r['estado'])) ?: 'ACTIVA',
                    $this->fecha($r['fecha_constitucion'] ?? ''),
                    $r['cnae_code'],
                    $r['cnae_label'],
                    $c2025,
                    $l2025,
                    trim((string) ($r['x_ventas'] ?? '')),
                    trim((string) ($r['x_anio'] ?? '')),
                    trim((string) ($r['capital_social_raw'] ?? '')),
                    $telefono,
                    $r['address'],
                    trim((string) ($r['x_postal_code'] ?? '')),
                    $r['municipality'],
                    $r['registro_mercantil'],
                    $this->limpiar((string) ($r['objeto_social'] ?? '')),
                ], ';');
                $total++;
            }
        }

        return $total;
    }

    public function nombreFichero(array $pedido): string
    {
        return ($pedido['nombre_fichero'] ?? 'APIEmpresas_Listado') . '_' . date('Ymd') . '.csv';
    }

    /* ------------------------------------------------------------------ */
    /*  Internos                                                           */
    /* ------------------------------------------------------------------ */

    /** Ids de las empresas del pedido (una consulta ligera, luego se leen por lotes). */
    private function idsEmpresas(array $pedido): array
    {
        $db = \Config\Database::connect();
        $b  = $db->table('companies')->select('id');

        $prefijos = $pedido['filtros']['cnae_prefijos'] ?? [];
        if ($prefijos) {
            $b->groupStart();
            foreach ($prefijos as $p) {
                $b->orLike('cnae_code', (string) $p, 'after');
            }
            $b->groupEnd();
        }

        if (!empty($pedido['filtros']['solo_activas'])) {
            // El estado viene escrito de varias formas ('ACTIVA', 'Activa').
            $b->where("UPPER(TRIM(estado)) = 'ACTIVA'", null, false);
        }

        $b->orderBy('company_name', 'ASC');

        return array_map('intval', array_column($b->get()->getResultArray(), 'id'));
    }

    /**
     * Equivalencias CNAE-2009 → CNAE-2025 de la tabla cnae_2009_2025.
     * Un código de 2009 puede repartirse en varios de 2025: se ponen todos.
     */
    private function mapaCnae2025(): array
    {
        $mapa = [];
        $etiquetas = [];
        try {
            $filas = \Config\Database::connect()->table('cnae_2009_2025')
                ->select('cnae_2009, cnae_2025, label_2025')
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', '[PedidoMedida] cnae_2009_2025: ' . $e->getMessage());
            $filas = [];
        }

        foreach ($filas as $f) {
            $c09 = substr(preg_replace('/\D/', '', (string) $f['cnae_2009']), 0, 4);
            $c25 = substr(preg_replace('/\D/', '', (string) $f['cnae_2025']), 0, 4);
            if ($c25 === '') {
                continue;
            }
            $etiquetas[$c25] = (string) $f['label_2025'];
            if ($c09 !== '') {
                $mapa[$c09][$c25] = (string) $f['label_2025'];
            }
        }

        return [$mapa, $etiquetas];
    }

    private function enviarCorreoDescarga(array $pedido, string $email, string $nombre, string $sessionId): bool
    {
        $url    = site_url("pedido/{$pedido['token']}/descargar") . '?session_id=' . urlencode($sessionId);
        $nombre = trim($nombre) !== '' ? explode(' ', trim($nombre))[0] : '';

        $html = '<div style="font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.6;color:#0f172a;max-width:560px">'
              . '<p>Hola' . ($nombre !== '' ? ' ' . esc($nombre) : '') . ',</p>'
              . '<p>Hemos recibido tu pago. Tu listado <strong>' . esc($pedido['titulo']) . '</strong> ya está listo para descargar:</p>'
              . '<p style="margin:24px 0"><a href="' . esc($url, 'attr') . '" style="background:#10b981;color:#fff;text-decoration:none;'
              . 'padding:14px 24px;border-radius:10px;font-weight:bold;display:inline-block">Descargar el CSV</a></p>'
              . '<p>El enlace es personal y puedes usarlo las veces que necesites. La factura te llega en un correo aparte.</p>'
              . '<p>El archivo está separado por punto y coma y en UTF-8, así que se abre directamente con Excel.</p>'
              . '<p>Si necesitas cualquier cosa, responde a este correo.</p>'
              . '<p>Un saludo,<br>Equipo de APIEmpresas</p>'
              . '<p style="font-size:12px;color:#64748b">Referencia: ' . esc($pedido['referencia']) . '</p>'
              . '</div>';

        try {
            $mail = \Config\Services::email();
            $mail->clear(true);
            $mail->setFrom(env('email.fromEmail', 'soporte@apiempresas.es'), env('email.fromName', 'APIEmpresas.es'));
            $mail->setTo($email);
            $mail->setBCC('papelo.amh@gmail.com');
            $mail->setSubject('Tu listado está listo para descargar · ' . $pedido['referencia']);
            $mail->setMailType('html');
            $mail->setMessage($html);
            $ok = (bool) $mail->send(false);
            if (!$ok) {
                log_message('error', '[PedidoMedida] No se pudo enviar el correo de descarga a ' . $email);
            }
            return $ok;
        } catch (\Throwable $e) {
            log_message('error', '[PedidoMedida] Correo de descarga: ' . $e->getMessage());
            return false;
        }
    }

    private function ficheroPago(string $token): string
    {
        return WRITEPATH . 'pedidos/' . $token . '.json';
    }

    private function fecha(string $v): string
    {
        $t = $v !== '' ? strtotime($v) : false;
        return ($t && $t > strtotime('1850-01-01')) ? date('d/m/Y', $t) : '';
    }

    private function limpiar(string $v): string
    {
        return trim(preg_replace('/\s+/u', ' ', $v) ?? $v);
    }
}
