<?php

namespace App\Services;

use App\Libraries\PaidExports;

/**
 * Correo con el enlace de descarga de un listado pagado.
 *
 * Antes no había: el acceso al listado vivía solo en la sesión PHP. Quien perdía
 * la sesión al volver de Stripe (otro navegador, cookies, cerrar la pestaña) o
 * compraba como invitado había pagado y no tenía forma de descargar.
 *
 * Lo llaman el webhook (checkout.session.completed / async_payment_succeeded) y
 * la página de éxito; el primero que llega lo manda y el otro no repite (un
 * fichero por sesión de pago con bloqueo, igual que PedidoMedidaService).
 *
 * El enlace es `billing/export-…?session_id=cs_…`: cada uso se comprueba contra
 * Stripe (PaidExports::sesionCobrada), vale 30 días y no necesita cuenta.
 */
class ListadoPagadoService
{
    /**
     * @param object $session Sesión de Checkout de Stripe
     */
    public function enviarCorreoDescarga($session): bool
    {
        $sessionId = (string) ($session->id ?? '');
        if ($sessionId === '' || !str_starts_with($sessionId, 'cs_')) {
            return false;
        }
        if (!in_array((string) ($session->metadata->plan ?? ''), PaidExports::PLANES, true)) {
            return false;
        }
        if (!in_array((string) ($session->payment_status ?? ''), ['paid', 'no_payment_required'], true)) {
            return false;   // SEPA aún sin cobrar: llegará por async_payment_succeeded
        }
        $ctx = json_decode((string) ($session->metadata->export_context ?? ''), true);
        if (!is_array($ctx) || !in_array($ctx['type'] ?? '', PaidExports::TIPOS, true)) {
            return false;
        }

        $userId = (int) ($session->client_reference_id ?? $session->metadata->user_id ?? 0);
        $email  = trim((string) ($session->customer_details->email ?? $session->customer_email ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $user   = $userId > 0 ? (new \App\Models\UserModel())->find($userId) : null;
            $email  = (string) ($user->email ?? '');
        }
        if ($email === '') {
            log_message('error', "[ListadoPagado] Sesión {$sessionId} cobrada sin email al que mandar la descarga");
            return false;
        }

        $f = WRITEPATH . 'listados/' . preg_replace('/[^A-Za-z0-9_]/', '', $sessionId) . '.json';
        if (!is_dir(dirname($f))) {
            mkdir(dirname($f), 0755, true);
            file_put_contents(dirname($f) . '/.gitignore', "*\n!.gitignore\n");
        }

        $fp = fopen($f . '.lock', 'c');
        if ($fp) {
            flock($fp, LOCK_EX);
        }

        try {
            $previo = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
            if (!empty($previo['email_enviado'])) {
                $this->indexar($userId, $sessionId);   // compras anteriores a "Mis listados"
                return true;
            }

            $enviado = $this->enviar($email, (string) ($session->customer_details->name ?? ''), $sessionId, $ctx);

            file_put_contents($f, json_encode([
                'session_id'    => $sessionId,
                'user_id'       => $userId,
                'email'         => $email,
                'plan'          => (string) ($session->metadata->plan ?? ''),
                'contexto'      => $ctx,
                'importe'       => isset($session->amount_total) ? ((int) $session->amount_total) / 100 : null,
                'pagado_en'     => $previo['pagado_en'] ?? date('Y-m-d H:i:s'),
                'email_enviado' => $enviado,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->indexar($userId, $sessionId);

            return $enviado;
        } finally {
            if ($fp) {
                flock($fp, LOCK_UN);
                fclose($fp);
            }
        }
    }

    /**
     * Enlace al mismo listado en Excel (.xlsx), o null si no se ofrece: solo listados de
     * empresas de hasta RadarController::XLSX_MAX filas (subvenciones y licitaciones, CSV).
     */
    public function urlXlsx(string $sessionId, array $ctx): ?string
    {
        if (!in_array($ctx['type'] ?? '', ['excel', 'directory_excel'], true)
            || !\App\Controllers\RadarController::ofreceXlsx((int) ($ctx['total_count'] ?? 0))) {
            return null;
        }

        return PaidExports::urlCorreo($sessionId, $ctx) . '&formato=xlsx';
    }

    /**
     * Apunta una descarga en la compra (writable/listados/{sesión}.json): cuántas veces
     * se ha descargado, cuándo y cómo acabó. Sirve para atender un "no me llegó el
     * archivo" y para ver si un enlace se está compartiendo.
     *
     * $estado: 'ok' (archivo completo), 'cortada' (el cliente cerró o perdió la conexión)
     * o 'error' (fallo nuestro: excepción, memoria o tiempo).
     */
    public function registrarDescarga(string $sessionId, string $estado, string $formato, int $filas, string $detalle = ''): void
    {
        $sessionId = preg_replace('/[^A-Za-z0-9_]/', '', $sessionId);
        $f = WRITEPATH . 'listados/' . $sessionId . '.json';
        if ($sessionId === '' || !is_file($f)) {
            return;   // compra sin ficha (modo simulador o anterior al 01-10-2026)
        }
        $fp = @fopen($f, 'c+');
        if (!$fp) {
            return;
        }
        try {
            flock($fp, LOCK_EX);
            $d = json_decode((string) stream_get_contents($fp), true);
            if (!is_array($d)) {
                return;
            }
            $d['descargas']        = (int) ($d['descargas'] ?? 0) + ($estado === 'ok' ? 1 : 0);
            $d['descargas_fallidas'] = (int) ($d['descargas_fallidas'] ?? 0) + ($estado === 'ok' ? 0 : 1);
            if ($estado === 'ok') {
                $d['ultima_descarga'] = date('Y-m-d H:i:s');
            }
            $log   = is_array($d['descargas_log'] ?? null) ? $d['descargas_log'] : [];
            $log[] = array_filter([
                'fecha'   => date('Y-m-d H:i:s'),
                'estado'  => $estado,
                'formato' => $formato,
                'filas'   => $filas,
                'detalle' => mb_substr($detalle, 0, 300, 'UTF-8'),
            ], static fn ($v) => $v !== '');
            $d['descargas_log'] = array_slice($log, -30);   // las 30 últimas

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    /**
     * Correo interno cuando una descarga pagada falla por nuestra parte. Antes solo se
     * sabía si el cliente escribía. Uno por compra y hora como mucho.
     */
    public function avisarFallo(string $sessionId, string $formato, int $filas, string $detalle): void
    {
        try {
            $cache = \Config\Services::cache();
            $clave = 'listado_fallo_' . md5($sessionId ?: $detalle);
            if ($cache->get($clave)) {
                return;
            }
            $cache->save($clave, 1, 3600);

            $limpio = preg_replace('/[^A-Za-z0-9_]/', '', $sessionId);
            $f = WRITEPATH . 'listados/' . $limpio . '.json';
            $d = ($limpio !== '' && is_file($f)) ? json_decode((string) file_get_contents($f), true) : null;
            $titulo  = is_array($d) && is_array($d['contexto'] ?? null) ? $this->titulo($d['contexto']) : '(compra sin ficha)';
            $cliente = is_array($d) ? (string) ($d['email'] ?? '') : '';
            $enlace  = is_array($d) && is_array($d['contexto'] ?? null) ? PaidExports::urlCorreo($limpio, $d['contexto']) : '';

            $html = '<div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#0f172a">'
                  . '<p><strong>Una descarga de listado pagado ha fallado.</strong></p>'
                  . '<ul>'
                  . '<li>Listado: ' . esc($titulo) . '</li>'
                  . '<li>Cliente: ' . esc($cliente !== '' ? $cliente : 'desconocido') . '</li>'
                  . '<li>Sesión de Stripe: ' . esc($sessionId !== '' ? $sessionId : 'desconocida') . '</li>'
                  . '<li>Formato: ' . esc($formato) . ' · filas escritas antes del fallo: ' . number_format($filas, 0, ',', '.') . '</li>'
                  . '<li>Motivo: ' . esc($detalle) . '</li>'
                  . '<li>Hora: ' . date('d/m/Y H:i:s') . '</li>'
                  . '</ul>'
                  . ($enlace !== '' ? '<p>Enlace de descarga del cliente (para probarlo): <a href="' . esc($enlace, 'attr') . '">' . esc($enlace) . '</a></p>' : '')
                  . '<p>El cliente no ha recibido ningún aviso. Si el fallo se repite, escríbele y envíale el archivo.</p>'
                  . '</div>';

            $mail = \Config\Services::email();
            $mail->clear(true);
            $mail->setFrom(env('email.fromEmail', 'soporte@apiempresas.es'), env('email.fromName', 'APIEmpresas.es'));
            $mail->setTo(env('LISTADOS_ALERTA_EMAIL', 'papelo.amh@gmail.com'));
            $mail->setSubject('⚠ Fallo en la descarga de un listado pagado');
            $mail->setMailType('html');
            $mail->setMessage($html);
            $mail->send(false);
        } catch (\Throwable $e) {
            log_message('error', '[ListadoPagado] No se pudo avisar del fallo de descarga: ' . $e->getMessage());
        }
    }

    /** Índice por usuario: writable/listados/usuarios/{id}.json → [session_id, …] */
    private function rutaIndice(int $userId): string
    {
        return WRITEPATH . 'listados/usuarios/' . $userId . '.json';
    }

    /**
     * Apunta la compra en el índice del usuario (para /billing/mis-listados). Las compras
     * como invitado (user_id 0) no se apuntan: su acceso es el enlace del correo.
     */
    private function indexar(int $userId, string $sessionId): void
    {
        if ($userId <= 0) {
            return;
        }
        $f = $this->rutaIndice($userId);
        if (!is_dir(dirname($f))) {
            @mkdir(dirname($f), 0755, true);
        }
        $fp = @fopen($f, 'c+');
        if (!$fp) {
            log_message('error', '[ListadoPagado] No se pudo abrir el índice de compras del usuario ' . $userId);
            return;
        }
        try {
            flock($fp, LOCK_EX);
            $ids = json_decode((string) stream_get_contents($fp), true);
            $ids = is_array($ids) ? $ids : [];
            if (!in_array($sessionId, $ids, true)) {
                $ids[] = $sessionId;
                ftruncate($fp, 0);
                rewind($fp);
                fwrite($fp, json_encode($ids));
            }
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    /**
     * Listados comprados por un usuario, del más reciente al más antiguo.
     *
     * @return array<int, array{titulo: string, fecha: string, importe: ?float, url: string, caduca: string, vigente: bool, ref: string}>
     */
    public function comprasDe(int $userId): array
    {
        if ($userId <= 0 || !is_file($this->rutaIndice($userId))) {
            return [];
        }
        $ids = json_decode((string) file_get_contents($this->rutaIndice($userId)), true);
        $out = [];
        foreach (is_array($ids) ? $ids : [] as $sessionId) {
            $sessionId = preg_replace('/[^A-Za-z0-9_]/', '', (string) $sessionId);
            $f = WRITEPATH . 'listados/' . $sessionId . '.json';
            $d = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
            // Solo las del propio usuario, aunque el índice dijera otra cosa
            if (!is_array($d) || (int) ($d['user_id'] ?? 0) !== $userId || !is_array($d['contexto'] ?? null)) {
                continue;
            }
            $pagado = strtotime((string) ($d['pagado_en'] ?? '')) ?: 0;
            $caduca = $pagado + PaidExports::TTL_CORREO;
            $out[]  = [
                'titulo'  => $this->titulo($d['contexto']),
                'fecha'   => $pagado ? date('d/m/Y', $pagado) : '',
                'importe' => isset($d['importe']) ? (float) $d['importe'] : null,
                'url'     => PaidExports::urlCorreo($sessionId, $d['contexto']),
                'url_xlsx' => $this->urlXlsx($sessionId, $d['contexto']),
                'caduca'  => date('d/m/Y', $caduca),
                'vigente' => $caduca > time(),
                'ref'     => 'EXC-' . strtoupper(substr($sessionId, -8)),
                'descargas'       => (int) ($d['descargas'] ?? 0),
                'ultima_descarga' => !empty($d['ultima_descarga']) ? date('d/m/Y', (int) strtotime((string) $d['ultima_descarga'])) : '',
                'orden'   => $pagado,
            ];
        }
        usort($out, static fn ($a, $b) => $b['orden'] <=> $a['orden']);

        return $out;
    }

    /**
     * Nombre legible del listado: "Hostelería en Madrid (12.345 empresas)".
     */
    public function titulo(array $ctx): string
    {
        $total = number_format((int) ($ctx['total_count'] ?? 0), 0, ',', '.');
        $type  = (string) ($ctx['type'] ?? '');

        if ($type === 'subsidies_excel') {
            return "Subvenciones públicas ({$total} registros)";
        }
        if ($type === 'contracts_excel') {
            return "Licitaciones públicas ({$total} registros)";
        }

        $provincia = trim((string) ($ctx['provincia'] ?? '')) ?: 'España';
        if (!empty($ctx['municipio'])) {
            $provincia = trim((string) $ctx['municipio']) . " ({$provincia})";
        }
        $sector    = trim((string) ($ctx['sector'] ?? ''));
        $que       = ($sector !== '' && mb_strtolower($sector) !== 'general' && $sector !== $provincia)
            ? "{$sector} en {$provincia}"
            : "Empresas de {$provincia}";

        $extras = [];
        if (($ctx['has_phone'] ?? '') === '1') {
            $extras[] = 'con teléfono';
        }
        if (!empty($ctx['estado'])) {
            $extras[] = mb_strtolower((string) $ctx['estado']);
        }

        return $que . ($extras ? ' · ' . implode(', ', $extras) : '') . " ({$total} empresas)";
    }

    private function enviar(string $email, string $nombre, string $sessionId, array $ctx): bool
    {
        $url    = PaidExports::urlCorreo($sessionId, $ctx);
        $xlsx   = $this->urlXlsx($sessionId, $ctx);
        $titulo = $this->titulo($ctx);
        $nombre = trim($nombre) !== '' ? explode(' ', trim($nombre))[0] : '';
        $ref    = 'EXC-' . strtoupper(substr($sessionId, -8));

        $html = '<div style="font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.6;color:#0f172a;max-width:560px">'
              . '<p>Hola' . ($nombre !== '' ? ' ' . esc($nombre) : '') . ',</p>'
              . '<p>Hemos recibido tu pago. Tu listado <strong>' . esc($titulo) . '</strong> está listo para descargar:</p>'
              . '<p style="margin:24px 0"><a href="' . esc($url, 'attr') . '" style="background:#10b981;color:#fff;text-decoration:none;'
              . 'padding:14px 24px;border-radius:10px;font-weight:bold;display:inline-block">Descargar el CSV</a></p>'
              . ($xlsx !== null ? '<p>¿Prefieres Excel? <a href="' . esc($xlsx, 'attr') . '" style="color:#047857;font-weight:bold">Descargar en formato Excel (.xlsx)</a>: mismas empresas y mismas columnas.</p>' : '')
              . '<p>El enlace es personal: puedes usarlo las veces que necesites durante 30 días, desde cualquier dispositivo y sin iniciar sesión. Guárdalo y no lo compartas.</p>'
              . '<p>El archivo está en formato CSV (UTF-8) y se abre con Excel, Google Sheets o tu CRM. Los listados grandes pueden tardar un poco en empezar a descargarse.</p>'
              . '<p>Si tienes cualquier problema con la descarga, responde a este correo y te lo enviamos.</p>'
              . '<p>Un saludo,<br>Equipo de APIEmpresas</p>'
              . '<p style="font-size:12px;color:#64748b">Referencia: ' . esc($ref) . '</p>'
              . '</div>';

        try {
            $mail = \Config\Services::email();
            $mail->clear(true);
            $mail->setFrom(env('email.fromEmail', 'soporte@apiempresas.es'), env('email.fromName', 'APIEmpresas.es'));
            $mail->setTo($email);
            $mail->setBCC('papelo.amh@gmail.com');
            $mail->setSubject('Tu listado está listo para descargar · ' . $ref);
            $mail->setMailType('html');
            $mail->setMessage($html);
            $ok = (bool) $mail->send(false);
            if (!$ok) {
                log_message('error', '[ListadoPagado] No se pudo enviar el correo de descarga a ' . $email . ' (' . $sessionId . ')');
            }
            return $ok;
        } catch (\Throwable $e) {
            log_message('error', '[ListadoPagado] Correo de descarga: ' . $e->getMessage());
            return false;
        }
    }
}
