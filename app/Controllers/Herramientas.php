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

    /** GET /como-saber-si-una-empresa-esta-activa — guía + buscador de estado y administradores */
    public function empresaActiva()
    {
        return view('herramientas/empresa_activa');
    }

    /** GET /validar-cif-excel — tutorial: fórmula para validar CIF en Excel y Google Sheets */
    public function validarCifExcel()
    {
        return view('herramientas/validar_cif_excel');
    }
}
