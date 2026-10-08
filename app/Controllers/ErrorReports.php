<?php

namespace App\Controllers;

use App\Libraries\Errors\ErrorRecorder;
use CodeIgniter\Controller;

/**
 * POST errors/js: los errores de JavaScript que manda el navegador (ver partials/error_reporter.php) al registro propio.
 * Solo con sesion iniciada y como mucho 60 por sesion y hora: no es un sitio donde cualquiera pueda llenar la tabla.
 * Responde siempre 204.
 */
class ErrorReports extends Controller
{
    public function js()
    {
        $vacio = $this->response->setStatusCode(204);

        if (strtolower($this->request->getMethod()) !== 'post' || ! session('logged_in')) {
            return $vacio;
        }

        $hora = date('YmdH');
        $cupo = session('js_errors_quota');
        $cupo = is_array($cupo) && ($cupo['h'] ?? '') === $hora ? $cupo : ['h' => $hora, 'n' => 0];

        if ($cupo['n'] >= 60) {
            return $vacio;
        }

        session()->set('js_errors_quota', ['h' => $hora, 'n' => $cupo['n'] + 1]);
        session_write_close();

        $p = $this->request->getPost(['message', 'source', 'line', 'column', 'stack', 'page']);
        ErrorRecorder::js(is_array($p) ? $p : []);

        return $vacio;
    }
}
