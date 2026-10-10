<?php

namespace App\Controllers;

/**
 * /listado-de-grupos-empresariales (10-10-2026): RETIRADO, responde 410. Ver Holding.php.
 * Antes: writable/propuestas/grupos/antes/HoldingDirectory.php
 */
class HoldingDirectory extends BaseController
{
    public function index()
    {
        return $this->response->setStatusCode(410)->setBody(view('errors/html/error_410', [
            'message' => 'El directorio de grupos empresariales ya no está disponible.',
        ]));
    }
}
