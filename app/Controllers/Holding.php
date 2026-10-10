<?php

namespace App\Controllers;

/**
 * /grupos-empresariales/<slug> (10-10-2026): RETIRADO, responde 410.
 *
 * Esos "grupos" (generar_holdings.py, tablas holdings y company_holdings) juntaban empresas con
 * algún cargo de nombre parecido y encadenado: no eran grupos empresariales y la ficha afirmaba
 * cosas falsas ("forma parte de un ecosistema corporativo de N empresas"). Solo el 30 % tenía una
 * persona común a todas sus empresas, y ese vínculo ya lo cuenta la ficha ("Quién está detrás") y la
 * página del administrador. El grupo empresarial de la ficha es ahora el de GLEIF (company_lei).
 * Antes: writable/propuestas/grupos/antes/Holding.php
 */
class Holding extends BaseController
{
    public function show($slug = null)
    {
        return $this->response->setStatusCode(410)->setBody(view('errors/html/error_410', [
            'message' => 'Esta página de grupo empresarial ya no está disponible.',
        ]));
    }
}
