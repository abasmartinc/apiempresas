<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\ContributionService;

/**
 * Datos de contacto aportados desde la ficha: "Añadir teléfono / web / email" (15-10-2026).
 *
 * Lee company_contributions (ver ContributionService). Arriba lo verificado (el comando
 * datos:verificar-aportaciones lo ha encontrado en la web de la empresa), luego lo
 * pendiente. "Aprobar" lo publica en la ficha (DataCorrectionService, vacía la caché);
 * "Rechazar" solo lo marca.
 */
class Contributions extends BaseController
{
    private const CAMPOS = ['telefono' => 'Teléfono', 'web' => 'Web', 'correo' => 'Email'];

    public function index()
    {
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $db = \Config\Database::connect();
        $estado = (string) ($this->request->getGet('estado') ?? 'abiertas');   // abiertas | aprobado | rechazado | todas

        $b = $db->table('company_contributions a')
            ->select('a.*, c.company_name, c.cif, c.phone AS actual_telefono, e.website_official AS actual_web, e.email AS actual_correo')
            ->join('companies c', 'c.id = a.company_id', 'left')
            ->join('company_enrichment e', 'e.company_id = a.company_id', 'left');
        if ($estado === 'abiertas') {
            $b->whereIn('a.estado', ['verificado', 'pendiente']);
        } elseif (in_array($estado, ['aprobado', 'rechazado'], true)) {
            $b->where('a.estado', $estado);
        }
        $filas = $b->orderBy("FIELD(a.estado, 'verificado', 'pendiente', 'aprobado', 'rechazado')", '', false)
            ->orderBy('a.id', 'DESC')->limit(500)->get()->getResultArray();

        $cuentas = [];
        foreach ($db->table('company_contributions')->select('estado, COUNT(*) AS n')->groupBy('estado')->get()->getResultArray() as $r) {
            $cuentas[$r['estado']] = (int) $r['n'];
        }

        helper('company');
        return $this->renderView('admin/aportaciones', [
            'title'   => 'Datos aportados',
            'filas'   => $filas,
            'estado'  => $estado,
            'cuentas' => $cuentas,
            'campos'  => self::CAMPOS,
        ]);
    }

    public function aprobar($id)
    {
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'));
        }
        $fila = \Config\Database::connect()->table('company_contributions')->where('id', (int) $id)->get()->getRowArray();
        if (!$fila || !in_array($fila['estado'], ['pendiente', 'verificado'], true)) {
            return redirect()->to(site_url('admin/aportaciones'))->with('error', 'Esa aportación ya no está abierta.');
        }

        $r = (new ContributionService())->aplicar($fila, false, 'Aprobado a mano');
        $msg = $r['aplicado']
            ? 'Publicado: ' . (self::CAMPOS[$fila['campo']] ?? $fila['campo']) . ' ' . $fila['valor']
                . (($r['anterior'] ?? '') !== '' ? ' (antes: ' . $r['anterior'] . ')' : '')
                . ($r['cache'] === false ? '. Ojo: no se ha podido vaciar la caché de Cloudflare.' : '.')
            : 'No se ha publicado: ' . ($r['motivo'] ?? '?');

        return redirect()->to(site_url('admin/aportaciones'))->with($r['aplicado'] ? 'success' : 'error', $msg);
    }

    public function rechazar($id)
    {
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'));
        }
        (new ContributionService())->rechazar((int) $id, mb_substr(trim((string) $this->request->getPost('nota')), 0, 255));
        return redirect()->to(site_url('admin/aportaciones'))->with('success', 'Aportación #' . (int) $id . ' rechazada.');
    }
}
