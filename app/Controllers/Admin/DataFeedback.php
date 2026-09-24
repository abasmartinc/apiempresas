<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Avisos de datos de la ficha: "¿Son correctos estos datos?" (24-09-2026).
 *
 * Lee company_ratings. Las respuestas nuevas llevan en `feedback` el prefijo
 * "[DATOS]" (ver Company::submitDataFeedback); las antiguas son las estrellas de
 * "¿Te ha sido útil?", con su nota y, a veces, un comentario libre. Se pueden ver
 * las dos cosas, pero por defecto solo los avisos de error nuevos, que es lo que
 * hay que revisar.
 */
class DataFeedback extends BaseController
{
    private const CAMPOS = [
        'direccion'       => 'Dirección',
        'telefono'        => 'Teléfono',
        'actividad'       => 'Actividad (CNAE)',
        'estado'          => 'Estado de la empresa',
        'administradores' => 'Administradores',
        'otro'            => 'Otro dato',
    ];

    public function index()
    {
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $db = \Config\Database::connect();

        $vista = (string) ($this->request->getGet('vista') ?? 'errores');   // errores | todos | antiguas
        $campo = (string) ($this->request->getGet('campo') ?? '');
        $dias  = (int) ($this->request->getGet('dias') ?? 90);
        $dias  = min(max($dias, 1), 3650);
        if (!array_key_exists($campo, self::CAMPOS)) {
            $campo = '';
        }
        $desde = date('Y-m-d H:i:s', strtotime("-{$dias} days"));

        // --- Listado ---
        $b = $db->table('company_ratings r')
            ->select('r.id, r.company_id, r.rating, r.feedback, r.ip_address, r.created_at, c.company_name, c.cif')
            ->join('companies c', 'c.id = r.company_id', 'left')
            ->where('r.created_at >=', $desde);

        if ($vista === 'antiguas') {
            $b->groupStart()->where('r.feedback IS NULL')->orNotLike('r.feedback', '[DATOS]', 'after')->groupEnd();
        } else {
            $b->like('r.feedback', '[DATOS]', 'after');
            if ($vista === 'errores') {
                $b->where('r.rating', 1);
            }
            if ($campo !== '') {
                $b->like('r.feedback', '[DATOS] Campo: ' . $campo, 'after');
            }
        }

        $filas = $b->orderBy('r.id', 'DESC')->limit(500)->get()->getResultArray();
        foreach ($filas as &$f) {
            $f += $this->parsear((string) ($f['feedback'] ?? ''));
        }
        unset($f);

        // --- Resumen del periodo (solo formato nuevo) ---
        $base = static fn () => $db->table('company_ratings')
            ->where('created_at >=', $desde)
            ->like('feedback', '[DATOS]', 'after');

        $total    = $base()->countAllResults();
        $errores  = $base()->where('rating', 1)->countAllResults();

        $porCampo = [];
        foreach (self::CAMPOS as $k => $label) {
            $porCampo[$k] = $base()->like('feedback', '[DATOS] Campo: ' . $k, 'after')->countAllResults();
        }
        arsort($porCampo);

        // Empresas con más avisos de error: por dónde empezar a corregir.
        $topEmpresas = $db->table('company_ratings r')
            ->select('r.company_id, c.company_name, c.cif, COUNT(*) AS n')
            ->join('companies c', 'c.id = r.company_id', 'left')
            ->where('r.created_at >=', $desde)
            ->where('r.rating', 1)
            ->like('r.feedback', '[DATOS]', 'after')
            ->groupBy('r.company_id, c.company_name, c.cif')
            ->orderBy('n', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        return $this->renderView('admin/data_feedback', [
            'title'       => 'Avisos de datos',
            'filas'       => $filas,
            'campos'      => self::CAMPOS,
            'vista'       => $vista,
            'campo'       => $campo,
            'dias'        => $dias,
            'total'       => $total,
            'errores'     => $errores,
            'correctos'   => $total - $errores,
            'porCampo'    => $porCampo,
            'topEmpresas' => $topEmpresas,
        ]);
    }

    /**
     * "[DATOS] Campo: actividad | Correcto: X | Email: y" → ['d_campo' => ..., 'd_valor' => ..., 'd_email' => ...]
     */
    private function parsear(string $fb): array
    {
        $out = ['d_campo' => '', 'd_valor' => '', 'd_email' => '', 'd_nuevo' => strpos($fb, '[DATOS]') === 0];
        if (!$out['d_nuevo']) {
            return $out;
        }
        foreach (explode('|', substr($fb, 7)) as $parte) {
            [$k, $v] = array_pad(array_map('trim', explode(':', $parte, 2)), 2, '');
            if ($k === 'Campo')    { $out['d_campo'] = $v; }
            if ($k === 'Correcto') { $out['d_valor'] = $v; }
            if ($k === 'Email')    { $out['d_email'] = $v; }
        }
        return $out;
    }
}
