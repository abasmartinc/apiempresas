<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\CorreosSalud;

/**
 * /admin/email-logs: ¿funcionan los correos? Comprobaciones automaticas (cron, SMTP, bienvenidas, cron vs envios), KPIs por
 * producto, envios por dia, resultado por correo e historial con filtros. Calculos en App\Libraries\CorreosSalud.
 * Sustituye a Admin\Dashboard::email_logs (misma URL y mismos filtros del historial, mas grupo y plantilla).
 */
class EmailHealth extends BaseController
{
    public function index()
    {
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $c = new CorreosSalud();
        $f = [];
        foreach (['q', 'status', 'user_id', 'opened', 'clicked', 'logged', 'date_from', 'date_to', 'grupo', 'plantilla'] as $k) {
            $f[$k] = trim((string) $this->request->getGet($k));
        }
        $page = max(1, (int) $this->request->getGet('page'));

        try {
            $d = $c->datos();
            $hist = $c->historial($f, $page);
            $plantillas = $c->plantillas();
            $error = null;
        } catch (\Throwable $e) {
            log_message('error', '[EmailHealth] ' . $e->getMessage());
            [$d, $hist, $plantillas, $error] = [null, ['rows' => [], 'total' => 0], [], $e->getMessage()];
        }

        // Si se filtra por un usuario, su nombre para ensenarlo en el filtro
        $usuarioFiltro = null;
        if ($f['user_id'] !== '' && ctype_digit($f['user_id'])) {
            $usuarioFiltro = \Config\Database::connect()->table('users')->select('id, name, email')->where('id', (int) $f['user_id'])->get()->getRowArray();
        }

        return $this->renderView('admin/email_logs', [
            'title'       => 'Correos',
            'd'           => $d,
            'error'       => $error,
            'f'           => $f,
            'hist'        => $hist,
            'page'        => $page,
            'pages'       => max(1, (int) ceil($hist['total'] / CorreosSalud::POR_PAGINA)),
            'plantillas'  => $plantillas,
            'usuarioFiltro' => $usuarioFiltro,
        ]);
    }

    /**
     * GET admin/email-logs/{id}/ver: los datos de un envio en JSON para el modal de la pagina (cabecera + el HTML del correo).
     * El HTML se pinta en un iframe con sandbox (sin scripts). Se le quita el pixel de apertura y los enlaces con
     * seguimiento (/e/c/...?t=destino) se cambian por su destino: mirarlo desde aqui no cuenta como apertura ni como clic.
     */
    public function ver(int $id)
    {
        if (!session('is_admin')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso denegado.']);
        }

        $r = \Config\Database::connect()->table('email_logs l')
            ->select('l.*, u.email AS user_email, u.name AS user_name, u.signup_intent')
            ->join('users u', 'u.id = l.user_id', 'left')
            ->where('l.id', $id)->get()->getRowArray();
        if (!$r) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'No existe ese correo.']);
        }

        $html = (string) $r['message'];
        $html = (string) preg_replace('#<img[^>]+/e/o/[^>]*>#i', '', $html);
        $html = (string) preg_replace_callback('#href=(["\'])([^"\']*/e/c/[^"\']*)\1#i', static function (array $m): string {
            parse_str((string) parse_url(html_entity_decode($m[2]), PHP_URL_QUERY), $q);
            $destino = is_string($q['t'] ?? null) && $q['t'] !== '' ? $q['t'] : '#';

            return 'href=' . $m[1] . esc($destino, 'attr') . $m[1];
        }, $html);

        // Hasta el 08-10-2026 solo se guardaban los 1.000 primeros caracteres: de un correo HTML queda solo el <head>.
        $esHtml = (bool) preg_match('#<(html|body|div|table|p)\b#i', $html);
        $recortado = $esHtml && strlen((string) $r['message']) >= 990 && stripos($html, '</body>') === false && stripos($html, '</html>') === false;
        $texto = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) preg_replace('#<(head|style|title)\b.*?(</\1>|$)#is', '', $html)))));
        if (!$esHtml && $html !== '') {
            $html = '<pre style="white-space:pre-wrap;font-family:-apple-system,Segoe UI,Roboto,sans-serif;font-size:14px;line-height:1.55;margin:16px">' . esc($html) . '</pre>';
        }
        // Los enlaces del correo, en una pestana nueva (el iframe no navega)
        if ($html !== '' && stripos($html, '<base') === false) {
            $html = preg_match('#<head[^>]*>#i', $html)
                ? (string) preg_replace('#<head([^>]*)>#i', '<head$1><base target="_blank">', $html, 1)
                : '<base target="_blank">' . $html;
        }

        $slug = (string) ($r['template_slug'] ?? '');
        $grupo = CorreosSalud::grupo($slug, $r['signup_intent']);

        return $this->response->setJSON([
            'id'         => (int) $r['id'],
            'asunto'     => (string) $r['subject'],
            'fecha'      => date('d/m/Y H:i:s', strtotime($r['created_at'])),
            'para'       => (string) ($r['user_email'] ?? ''),
            'nombre'     => (string) ($r['user_name'] ?? ''),
            'user_id'    => (int) $r['user_id'],
            'correo'     => $slug !== '' ? CorreosSalud::nombre($slug) : 'Sin plantilla (manual o anterior al 24/09)',
            'slug'       => $slug,
            'grupo'      => CorreosSalud::GRUPOS[$grupo] ?? $grupo,
            'estado'     => $r['status'],
            'error'      => $r['status'] === 'error' ? trim(strip_tags(str_replace(['<br>', '\n'], ' ', (string) $r['error_message']))) : '',
            'abierto'    => $r['opened_at'] ? date('d/m/Y H:i', strtotime($r['opened_at'])) : null,
            'clic'       => $r['clicked_at'] ? date('d/m/Y H:i', strtotime($r['clicked_at'])) : null,
            'login'      => $r['logged_in_at'] ? date('d/m/Y H:i', strtotime($r['logged_in_at'])) : null,
            'html'       => $recortado ? '' : $html,
            'recortado'  => $recortado,
            'texto'      => $recortado ? mb_substr($texto, 0, 600) : '',
            'vacio'      => trim((string) $r['message']) === '',
        ]);
    }
}
