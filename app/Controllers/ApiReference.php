<?php

namespace App\Controllers;

use App\Libraries\ApiReferenceSpec;

/**
 * Referencia de la API con Scalar (13-10-2026): /api/referencia (español) y
 * /api/reference (inglés). /documentation (y /docs en inglés) es la guía; esta, la
 * referencia de cada ruta. Van con noindex: el contenido sale de la guía.
 */
class ApiReference extends BaseController
{
    public function index()
    {
        return $this->pagina('es');
    }

    public function english()
    {
        return $this->pagina('en');
    }

    public function json()
    {
        return $this->spec('es');
    }

    public function jsonEnglish()
    {
        return $this->spec('en');
    }

    private function pagina(string $lang)
    {
        // Orden de las rutas en el menú: el del overlay (Scalar no respeta el del archivo)
        $orden   = [];
        $overlay = json_decode((string) @file_get_contents(APPPATH . 'Docs/openapi_overlay_' . $lang . '.json'), true);
        foreach (($overlay['paths'] ?? []) as $path => $metodos) {
            foreach (array_keys($metodos) as $metodo) {
                $orden[] = strtolower($metodo) . ' ' . $path;
            }
        }

        return view('api/reference', ['orden' => $orden, 'lang' => $lang]);
    }

    private function spec(string $lang)
    {
        return $this->response
            ->setHeader('Cache-Control', 'no-cache') // el servidor ya la guarda una hora (ApiReferenceSpec)
            ->setJSON(ApiReferenceSpec::get($lang));
    }
}
