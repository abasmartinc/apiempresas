<?php

namespace App\Controllers;

/**
 * Fuentes de datos (13-10-2026): /fuentes-de-datos (español) y /data-sources (inglés).
 *
 * Una sola página con todas las fuentes, qué aporta cada una, cada cuánto se actualiza y
 * su licencia. Sirve para cumplir la atribución que piden algunas licencias (CC BY, CC
 * BY-SA, Ley 37/2007, TED), para dar confianza a quien va a contratar la API y como
 * página indexable. La home enlaza aquí desde "De dónde salen los datos".
 *
 * Si se añade o se deja de usar una fuente, cambiar las dos vistas y FECHA.
 */
class FuentesDatos extends BaseController
{
    /** Fecha de la última revisión de la lista (se muestra en la página). */
    public const FECHA = '2026-10-13';

    public function index()
    {
        return view('fuentes_datos', ['fecha' => self::FECHA]);
    }

    public function english()
    {
        return view('fuentes_datos_en', ['fecha' => self::FECHA]);
    }
}
