<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Prospeccion as P;

/**
 * /admin/prospeccion: clientes potenciales para el trabajo de freelance (16-10-2026).
 * Cálculos en App\Libraries\Prospeccion; licitaciones en App\Libraries\LicitacionesPlacsp.
 *
 * Los filtros se recuerdan en la sesión: al volver a la página (o cambiar de pestaña) se
 * abre con los últimos usados. "Limpiar" vuelve a toda España y todos los sectores.
 */
class Prospeccion extends BaseController
{
    private const CLAVE_SESION = 'prospeccion_filtros';

    public function index()
    {
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $pestana = (string) ($this->request->getGet('p') ?? 'resumen');
        if (!isset(P::PESTANAS[$pestana])) {
            $pestana = 'resumen';
        }
        $f = $this->filtros();

        $datos = null;
        $error = null;
        $sinEmail = false;
        try {
            P::asegurarTablas();
            $lib = new P();
            $sinEmail = !$lib->hayColumnaEmail();
            switch ($pestana) {
                case 'resumen':
                    $datos = $lib->resumen($f);
                    break;
                case 'licitaciones':
                    $datos = $lib->licitaciones($f);
                    $datos['pendientes_ia'] = $lib->pendientesIa();
                    $datos['hay_ia'] = !empty(env('OPENAI_API_KEY'));
                    break;
                case 'seguimiento':
                    $datos = $lib->seguimiento($f);
                    break;
                default:
                    $datos = $lib->empresas($pestana, $f);
            }
        } catch (\Throwable $e) {
            log_message('error', '[Prospeccion] ' . $e->getMessage());
            $error = $e->getMessage();
        }

        helper('company');

        return $this->renderView('admin/prospeccion', [
            'title'   => 'Prospección',
            'pestana' => $pestana,
            'f'       => $f,
            'd'       => $datos,
            'error'   => $error,
            'sinEmail' => $sinEmail,
        ]);
    }

    /** Filtros de la URL; si no viene ninguno, los últimos de la sesión. ?limpiar=1 los borra. */
    private function filtros(): array
    {
        $get = $this->request->getGet();
        unset($get['p']);
        if (!empty($get['limpiar'])) {
            session()->remove(self::CLAVE_SESION);
            $get = [];
        } elseif (!empty($get['f'])) {
            // Viene del formulario de filtros (lleva f=1, aunque todas las casillas estén vacías)
            session()->set(self::CLAVE_SESION, $get);
        } else {
            $get = (array) (session(self::CLAVE_SESION) ?? []);
        }

        return P::filtros($get);
    }

    /** POST company_id, estado, nota, proximo, origen. Responde JSON. */
    public function seguimiento()
    {
        if (!session('is_admin')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'Acceso denegado.']);
        }
        $r = (new P())->guardarSeguimiento(
            (int) $this->request->getPost('company_id'),
            (string) $this->request->getPost('estado'),
            trim((string) $this->request->getPost('nota')),
            (string) $this->request->getPost('proximo') ?: null,
            (string) $this->request->getPost('origen')
        );

        return $this->response->setJSON($r);
    }

    /** POST id, estado, nota (seguimiento de una licitación). Responde JSON. */
    public function licitacion()
    {
        if (!session('is_admin')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'Acceso denegado.']);
        }
        $r = (new P())->guardarLicitacion(
            (int) $this->request->getPost('id'),
            (string) $this->request->getPost('estado'),
            trim((string) $this->request->getPost('nota'))
        );

        return $this->response->setJSON($r);
    }

    /**
     * GET ?id=<licitación>: tabla HTML con quién ha ganado antes contratos de informática con ese
     * órgano, con los parecidos a esta licitación primero (ver Prospeccion::competencia).
     */
    public function competencia()
    {
        if (!session('is_admin')) {
            return $this->response->setStatusCode(403)->setBody('');
        }
        helper('company');
        @set_time_limit(120);
        try {
            P::asegurarTablas();
            $r = (new P())->competencia((int) $this->request->getGet('id'));
        } catch (\Throwable $e) {
            log_message('error', '[Prospeccion competencia] ' . $e->getMessage());
            return $this->response->setBody('<p class="pr-muted">No se ha podido consultar: ' . esc($e->getMessage()) . '</p>');
        }
        $pie = '<p class="pr-muted" style="margin-top:6px">Contratos adjudicados por este organismo en 3 años (' . (int) $r['revisados'] . ' revisados), '
            . ($r['origen'] === 'ia' ? 'clasificados con IA.' : 'filtrados por palabras clave (la IA no estaba disponible).')
            . ' No son las ofertas de esta licitación: eso no se publica hasta que se abren.</p>';
        if (!$r['filas']) {
            return $this->response->setBody('<p class="pr-muted">' . ($r['revisados'] > 0
                ? 'Ninguno de sus contratos de los últimos 3 años es de informática.'
                : 'No tenemos contratos de este organismo en los últimos 3 años (o viene con otro nombre).') . '</p>' . ($r['revisados'] > 0 ? $pie : ''));
        }
        $h = '<table class="pr-mini"><tr><th>Empresa</th><th>Contratos TI</th><th>Importe</th><th>Último</th></tr>';
        foreach ($r['filas'] as $f) {
            $nombre = $f['nombre'] ?: $f['cif'];
            $url = $f['nombre'] ? company_url(['cif' => $f['cif'], 'name' => $f['nombre']]) : null;
            $titulo = $f['titulo_parecido'] ?? $f['titulo'];
            $h .= '<tr><td>' . ($url ? '<a href="' . esc($url, 'attr') . '" target="_blank" rel="noopener">' . esc($nombre) . '</a>' : esc($nombre))
                . ($f['parecidos'] > 0 ? ' <span class="pr-chip pr-chip--contratos" title="Ha hecho trabajos parecidos a esta licitación para este organismo">'
                    . ($f['parecidos'] === 1 ? 'Parecido' : $f['parecidos'] . ' parecidos') . '</span>' : '')
                . '<div class="pr-muted">' . esc((string) $titulo) . '</div></td>'
                . '<td>' . (int) $f['n'] . '</td>'
                . '<td>' . ($f['total'] !== null ? number_format((float) $f['total'], 0, ',', '.') . ' €' : '–') . '</td>'
                . '<td>' . esc(date('d/m/Y', strtotime($f['ultima']))) . '</td></tr>';
        }

        return $this->response->setBody($h . '</table>' . $pie);
    }

    /** POST: evalúa con IA hasta 30 licitaciones pendientes (JSON: evaluadas, pendientes, error) */
    public function evaluar()
    {
        if (!session('is_admin')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'Acceso denegado.']);
        }
        @set_time_limit(300);
        try {
            P::asegurarTablas();
            $r = (new P())->evaluarLicitacionesIa(30);
        } catch (\Throwable $e) {
            log_message('error', '[Prospeccion evaluar] ' . $e->getMessage());
            $r = ['evaluadas' => 0, 'pendientes' => null, 'error' => $e->getMessage()];
        }

        return $this->response->setJSON(['ok' => $r['error'] === null] + $r);
    }

    /** POST id, rehacer: "Preparar oferta" de una licitación (JSON). Se genera una vez y se guarda. */
    public function oferta()
    {
        if (!session('is_admin')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'Acceso denegado.']);
        }
        @set_time_limit(120);
        try {
            P::asegurarTablas();
            $r = (new P())->prepararOferta((int) $this->request->getPost('id'), (bool) $this->request->getPost('rehacer'));
        } catch (\Throwable $e) {
            log_message('error', '[Prospeccion oferta] ' . $e->getMessage());
            $r = ['ok' => false, 'error' => $e->getMessage()];
        }

        return $this->response->setJSON($r);
    }

    /** POST company_id, variante: redacta un correo para esa empresa (JSON: para, asunto, cuerpo, origen) */
    public function correo()
    {
        if (!session('is_admin')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'Acceso denegado.']);
        }
        @set_time_limit(120);
        helper('company');
        try {
            $r = (new P())->redactarCorreo((int) $this->request->getPost('company_id'), (int) $this->request->getPost('variante'));
        } catch (\Throwable $e) {
            log_message('error', '[Prospeccion correo] ' . $e->getMessage());
            $r = ['ok' => false, 'error' => 'No se ha podido redactar: ' . $e->getMessage()];
        }

        return $this->response->setJSON($r);
    }

    /** GET ?id=...: busca si la empresa ya tiene un dominio con su nombre (HTML) */
    public function web()
    {
        if (!session('is_admin')) {
            return $this->response->setStatusCode(403)->setBody('');
        }
        @set_time_limit(60);
        try {
            $r = (new P())->posibleWeb((int) $this->request->getGet('id'));
        } catch (\Throwable $e) {
            log_message('error', '[Prospeccion web] ' . $e->getMessage());
            return $this->response->setBody('<span class="pr-muted">No se ha podido comprobar: ' . esc($e->getMessage()) . '</span>');
        }
        $encontrados = $r['encontrados'] ?? [];
        if (!$encontrados) {
            return $this->response->setBody('<span class="pr-muted">Ningún dominio con su nombre ('
                . esc(implode(', ', array_slice($r['probados'] ?? [], 0, 6))) . ').</span>');
        }
        $estilos = ['suya' => 'pr-web--ok', 'probable' => 'pr-web--ok', 'aparcado' => 'pr-web--aviso', 'sin_web' => 'pr-web--aviso', 'otra' => 'pr-web--no'];
        $h = '';
        foreach ($encontrados as $w) {
            $h .= '<div class="pr-web ' . ($estilos[$w['estado']] ?? '') . '"><a href="https://' . esc($w['dominio'], 'attr') . '" target="_blank" rel="noopener nofollow">'
                . esc($w['dominio']) . '</a> <span class="pr-muted">' . esc($w['detalle']) . '</span></div>';
        }

        return $this->response->setBody($h);
    }
}
