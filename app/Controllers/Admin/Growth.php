<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\Crecimiento;

/**
 * /admin/crecimiento: evolucion mes a mes de las altas de la API y de Solvencia, activacion, paso a pago, suscripciones,
 * MRR y lo facturado, con la prevision del mes en curso y de los 3 siguientes. Los calculos estan en App\Libraries\Crecimiento.
 */
class Growth extends BaseController
{
    public function index()
    {
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        try {
            $d = (new Crecimiento())->datos();
            $error = null;
        } catch (\Throwable $e) {
            log_message('error', '[Crecimiento] ' . $e->getMessage());
            $d = null;
            $error = $e->getMessage();
        }

        return $this->renderView('admin/crecimiento', [
            'title' => 'Crecimiento',
            'd'     => $d,
            'error' => $error,
        ]);
    }
}
