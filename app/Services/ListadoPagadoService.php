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

        $email = trim((string) ($session->customer_details->email ?? $session->customer_email ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $userId = (int) ($session->client_reference_id ?? $session->metadata->user_id ?? 0);
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
                return true;
            }

            $enviado = $this->enviar($email, (string) ($session->customer_details->name ?? ''), $sessionId, $ctx);

            file_put_contents($f, json_encode([
                'session_id'    => $sessionId,
                'email'         => $email,
                'plan'          => (string) ($session->metadata->plan ?? ''),
                'contexto'      => $ctx,
                'importe'       => isset($session->amount_total) ? ((int) $session->amount_total) / 100 : null,
                'pagado_en'     => $previo['pagado_en'] ?? date('Y-m-d H:i:s'),
                'email_enviado' => $enviado,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return $enviado;
        } finally {
            if ($fp) {
                flock($fp, LOCK_UN);
                fclose($fp);
            }
        }
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
        $titulo = $this->titulo($ctx);
        $nombre = trim($nombre) !== '' ? explode(' ', trim($nombre))[0] : '';
        $ref    = 'EXC-' . strtoupper(substr($sessionId, -8));

        $html = '<div style="font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.6;color:#0f172a;max-width:560px">'
              . '<p>Hola' . ($nombre !== '' ? ' ' . esc($nombre) : '') . ',</p>'
              . '<p>Hemos recibido tu pago. Tu listado <strong>' . esc($titulo) . '</strong> está listo para descargar:</p>'
              . '<p style="margin:24px 0"><a href="' . esc($url, 'attr') . '" style="background:#10b981;color:#fff;text-decoration:none;'
              . 'padding:14px 24px;border-radius:10px;font-weight:bold;display:inline-block">Descargar el CSV</a></p>'
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
