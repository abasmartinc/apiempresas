<?php

namespace App\Controllers;

/**
 * Herramientas gratuitas de la web (12-10-2026).
 *
 * Páginas para búsquedas de gente que no programa ("validar CIF online"…): traen visitas,
 * no compiten con la home ("API de empresas") y llevan a la API.
 */
class Herramientas extends BaseController
{
    /** GET /validar-cif — validador de CIF: formato, letra y si la empresa existe */
    public function validarCif()
    {
        return view('herramientas/validar_cif');
    }
}
