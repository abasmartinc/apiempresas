<?php

namespace App\Controllers;

use App\Services\WordPressService;
use App\Services\SeoTemplateService;
use CodeIgniter\Exceptions\PageNotFoundException;

class SeoReportController extends BaseController
{
    protected $wpService;
    protected $seoService;
    protected $radarController;

    public function __construct()
    {
        $this->wpService = new WordPressService();
        $this->seoService = new SeoTemplateService();
        $this->radarController = new \App\Controllers\RadarController();
    }

    /**
     * Controlador central para informes SEO dinámicos.
     * URL: /informes/(:any)
     */
    public function handleReport(string $slug)
    {
        $templates = $this->wpService->getTemplatesByCategory(20);

        foreach ($templates as $template) {
            $title = html_entity_decode($template['title']['rendered'] ?? '', ENT_QUOTES, 'UTF-8');
            
            $templateSlugPattern = $this->seoService->slugifyWithPlaceholders($title);
            $regex = $this->seoService->templateToPattern($templateSlugPattern);

            if (preg_match($regex, $slug, $matches)) {
                $provinceSlug = $matches['provincia'] ?? null;
                $sectorSlug = $matches['sector'] ?? null;

                $provinceName = $provinceSlug ? $this->deSlugify($provinceSlug) : null;
                $sectorName = $this->resolveSectorSlug($sectorSlug);

                $radarData = $this->radarController->getRadarData($provinceName, $sectorName, '30days');

                // Renderizamos SIEMPRE que el patrón coincida, incluso si no hay datos.
                // La vista se encargará de mostrar el bloque de fallback.
                $type = ($sectorName && $provinceName) ? 'combined' : ($sectorName ? 'sector' : 'province');
                return $this->renderReport($template, $radarData ?? [], $type);
            }
        }

        // --- DIAGNÓSTICOS PARA EL USUARIO ---
        $availablePatterns = array_map(function($t) {
            $title = html_entity_decode($t['title']['rendered'] ?? '', ENT_QUOTES, 'UTF-8');
            return "Título: '{$title}' -> Slug Esperado: '" . $this->seoService->slugifyWithPlaceholders($title) . "'";
        }, $templates);

        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("No se encontró un informe válido para: $slug (Mismo patrón, pero falta coincidencia perfecta con el título en WordPress)");
    }

    /**
     * Mapeo de slugs largos a los alias cortos de RadarController.
     */
    private function resolveSectorSlug(?string $slug): ?string
    {
        if (!$slug) return null;

        $mapping = [
            'hosteleria-restaurantes-y-catering' => 'hosteleria',
            'restaurantes-y-puestos-de-comida'     => 'restaurantes',
            'programacion-informatica'            => 'programacion',
            'marketing-y-publicidad'              => 'marketing',
            'construccion-e-inmobiliaria'         => 'construccion',
            'transporte-y-logistica'              => 'transporte',
            'logistica-y-almacenamiento'          => 'logistica',
            'actividades-inmobiliarias'           => 'inmobiliaria',
            'actividades-sanitarias'              => 'sanidad',
            'seguros-y-finanzas'                  => 'finanzas'
        ];

        return $mapping[$slug] ?? $slug;
    }

    /**
     * Renderiza el informe combinando WP y Datos.
     */
    private function renderReport(array $template, array $radarData, string $type)
    {
        $rawContent = $template['content']['rendered'] ?? '';
        $variables = $this->seoService->resolveVariables($radarData);
        $injectedContent = $this->seoService->replacePlaceholders($rawContent, $variables);

        // Datos para el sidebar
        $sidebarData = [
            'provinces' => ['Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Málaga', 'Alicante', 'Murcia', 'Cádiz', 'Vizcaya'],
            'sectors'   => ['Hostelería', 'Construcción', 'Software', 'Marketing', 'Transporte', 'Salud', 'Inmobiliaria'],
            'templates' => $this->wpService->getTemplatesByCategory(20)
        ];

        $data = [
            'title'            => $radarData['title'] ?? ($template['title']['rendered'] ?? 'Informe SEO'),
            'wp_raw_title'     => html_entity_decode($template['title']['rendered'] ?? '', ENT_QUOTES, 'UTF-8'),
            'meta_description' => $radarData['meta_description'] ?? '',
            'wp_content'       => $injectedContent,
            'radar_data'       => $radarData,
            'report_type'      => $type,
            'canonical'        => current_url(),
            'robots'           => 'index, follow',
            'sidebar'          => $sidebarData,
            'seoService'       => $this->seoService // Para slugify en la vista
        ];

        return view('seo/report_template', $data);
    }

    /**
     * Helper copiado de RadarController para consistencia de nombres.
     */
    private function deSlugify($slug)
    {
        $provinces = [
            'madrid' => 'MADRID', 'barcelona' => 'BARCELONA', 'valencia' => 'VALENCIA',
            'sevilla' => 'SEVILLA', 'alicante' => 'ALICANTE', 'malaga' => 'MALAGA',
            'murcia' => 'MURCIA', 'cadiz' => 'CADIZ', 'vizcaya' => 'VIZCAYA',
            'coruna' => 'A CORUNA', 'asturias' => 'ASTURIAS', 'zaragoza' => 'ZARAGOZA',
            'pontevedra' => 'PONTEVEDRA', 'granada' => 'GRANADA', 'tarragona' => 'TARRAGONA',
            'cordoba' => 'CORDOBA', 'girona' => 'GIRONA', 'almeria' => 'ALMERIA',
            'toledo' => 'TOLEDO', 'badajoz' => 'BADAJOZ', 'navarra' => 'NAVARRA',
            'jaen' => 'JAEN', 'cantabria' => 'CANTABRIA', 'castellon' => 'CASTELLON',
            'huelva' => 'HUELVA', 'valladolid' => 'VALLADOLID', 'ciudad-real' => 'CIUDAD REAL',
            'leon' => 'LEON', 'lleida' => 'LLEIDA', 'caceres' => 'CACERES',
            'alava' => 'ALAVA', 'lugo' => 'LUGO', 'salamanca' => 'SALAMANCA',
            'burgos' => 'BURGOS', 'albacete' => 'ALBACETE', 'orense' => 'OURENSE',
            'rioja' => 'LA RIOJA', 'guipuzcoa' => 'GUIPUZCOA', 'huesca' => 'HUESCA',
            'cuenca' => 'CUENCA', 'zamora' => 'ZAMORA', 'palencia' => 'PALENCIA',
            'avila' => 'AVILA', 'segovia' => 'SEGOVIA', 'teruel' => 'TERUEL',
            'guadalajara' => 'GUADALAJARA', 'soria' => 'SORIA', 'baleares' => 'BALEARES',
            'las-palmas' => 'LAS PALMAS', 'tenerife' => 'STA CRUZ TENERIFE',
            'ceuta' => 'CEUTA', 'melilla' => 'MELILLA',
            'espana' => 'ESPAÑA',
        ];

        $key = strtolower($slug);
        return $provinces[$key] ?? strtoupper(str_replace('-', ' ', $slug));
    }
}
