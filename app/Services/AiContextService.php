<?php

namespace App\Services;

use App\Models\CompanyModel;

class AiContextService
{
    protected $wpService;
    protected $companyModel;

    public function __construct()
    {
        $this->wpService = new \App\Services\WordPressService();
        $this->companyModel = new CompanyModel();
    }

    /**
     * Define the tools (functions) available for the AI.
     */
    public function getAvailableTools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_company_info',
                    'description' => 'Obtiene información detallada de una empresa (Estado, Capital, Administradores, Objeto Social, Dirección, Teléfono, etc).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Nombre de la empresa o CIF a buscar.',
                            ],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_blog_posts',
                    'description' => 'Busca artículos en el blog de APIEmpresas sobre guías, noticias o ayuda técnica.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'keyword' => [
                                'type' => 'string',
                                'description' => 'Palabra clave o tema a buscar en el blog.',
                            ],
                        ],
                        'required' => ['keyword'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_borme_publications',
                    'description' => 'Busca publicaciones oficiales del BORME (Registro Mercantil) para una empresa por nombre o CIF.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Nombre de la empresa o CIF para buscar sus actos en el BORME.',
                            ],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_platform_stats',
                    'description' => 'Obtiene estadísticas globales de la plataforma (total de empresas registradas).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object)[],
                    ],
                ],
            ],
        ];
    }

    /**
     * Execute a tool called by the AI.
     */
    public function callTool(string $name, array $args): string
    {
        switch ($name) {
            case 'get_company_info':
                return $this->handleGetCompanyInfo($args['query'] ?? '');
            case 'search_blog_posts':
                return $this->handleSearchBlog($args['keyword'] ?? '');
            case 'get_borme_publications':
                return $this->handleGetBorme($args['query'] ?? '');
            case 'get_platform_stats':
                return $this->handleGetStats();
            default:
                return "Error: Herramienta interna no encontrada.";
        }
    }

    /**
     * TOOL: Search company in MariaDB
     */
    protected function handleGetCompanyInfo(string $query): string
    {
        if (empty($query)) return "No se proporcionó un criterio de búsqueda.";

        // Detectamos si es un CIF (Patrón: 1 letra + 8 dígitos)
        $isCif = preg_match('/^[A-Z][0-9]{8}$/i', $query);

        $db = \Config\Database::connect();
        $builder = $db->table('companies');
        
        // Seleccionamos campos enriquecidos de companies
        $builder->select('id, company_name, cif, estado as status, capital_social_raw, objeto_social, address, municipality, registro_mercantil, cnae_label, phone, phone_mobile, fecha_constitucion');

        if ($isCif) {
            $builder->where('cif', $query);
        } else {
            $builder->like('company_name', $query, 'after');
        }

        $company = $builder->limit(1)->get()->getRowArray();

        if (!$company) return "No se encontró ninguna empresa con el nombre o CIF: " . $query;

        // 1. Obtener Administradores desde la tabla específica
        $adminBuilder = $db->table('company_administrators');
        $admins = $adminBuilder->where('company_id', $company['id'])
                             ->get()
                             ->getResultArray();

        // Filtrar registros que no son personas (metadatos de Sociedades Civiles, etc) - Heurística de Radar.php
        $excludeKeywords = ['CAPITAL', 'DOMICILIO', 'OBJETO SOCIAL', 'OTROS CONCEPTOS', 'COMIENZO DE OPERACIONES', 'INSCRIPCION', 'RESULTANTE', 'SUSCRITO', 'EURO'];
        $filteredAdmins = [];
        $seenAdmins = [];

        foreach ($admins as $admin) {
            $nameStr = strtoupper($admin['name'] ?? '');
            $posStr = strtoupper($admin['position'] ?? '');
            $combinedText = $nameStr . ' ' . $posStr;

            $exclude = false;
            foreach ($excludeKeywords as $kw) {
                if (strpos($combinedText, $kw) !== false) {
                    $exclude = true;
                    break;
                }
            }
            if ($exclude || preg_match('/[0-9]+/', $nameStr)) continue;

            $uniqueKey = md5($nameStr . '|' . $posStr);
            if (isset($seenAdmins[$uniqueKey])) continue;

            $seenAdmins[$uniqueKey] = true;
            $filteredAdmins[] = $this->sanitizeUtf8($admin['name']) . " (" . $this->sanitizeUtf8($admin['position']) . ")";
        }

        // 2. Formatear datos de la empresa
        $cName = $this->sanitizeUtf8($company['company_name']);
        $cAddress = $this->sanitizeUtf8($company['address'] ?? 'No disponible');
        $cMuni = $this->sanitizeUtf8($company['municipality'] ?? 'No disponible');
        $cProv = $this->sanitizeUtf8($company['registro_mercantil'] ?? 'No disponible');
        $cObj = $this->sanitizeUtf8($company['objeto_social'] ?? 'No disponible');
        $cCapital = $this->sanitizeUtf8($company['capital_social_raw'] ?? 'No disponible');
        $cPhone = $this->sanitizeUtf8($company['phone'] ?: ($company['phone_mobile'] ?: 'No disponible'));

        $info = "Empresa: " . $cName . "\n";
        $info .= "CIF: " . $company['cif'] . "\n";
        $info .= "Estado: " . ($company['status'] ?? 'Activa') . "\n";
        $info .= "Fecha Constitución: " . ($company['fecha_constitucion'] ?? 'No disponible') . "\n";
        $info .= "Capital Social: " . $cCapital . "\n";
        $info .= "Sector (CNAE): " . $this->sanitizeUtf8($company['cnae_label'] ?? 'No disponible') . "\n";
        $info .= "Dirección: " . $cAddress . ", " . $cMuni . " (" . $cProv . ")\n";
        $info .= "Teléfono: " . $cPhone . "\n";
        $info .= "Objeto Social: " . (strlen($cObj) > 300 ? substr($cObj, 0, 300) . "..." : $cObj) . "\n";
        
        if (!empty($filteredAdmins)) {
            $info .= "Administradores:\n- " . implode("\n- ", $filteredAdmins);
        } else {
            $info .= "Administradores: No se han identificado administradores actuales en el registro.";
        }

        return "Datos encontrados en la base de datos oficial:\n" . $info;
    }

    /**
     * TOOL: Search WordPress (Mock Semantic/Text Search)
     */
    protected function handleSearchBlog(string $keyword): string
    {
        // Fetch posts matching the keyword
        $posts = $this->wpService->getPosts(5, $keyword);
        
        if (empty($posts)) {
            return "No he encontrado artículos específicos sobre '{$keyword}' en el blog, pero puedo intentar ayudarte con información general.";
        }

        $results = "He encontrado los siguientes artículos relevantes en el blog:\n";
        foreach ($posts as $post) {
            $results .= "- " . ($post['title']['rendered'] ?? 'Sin título') . " (https://apiempresas.es/blog/" . ($post['slug'] ?? '') . ")\n";
        }
        
        return $results;
    }

    /**
     * TOOL: Stats
     */
    protected function handleGetStats(): string
    {
        $total = $this->companyModel->countAllResults();
        return "Actualmente APIEmpresas cuenta con " . number_format($total, 0, ',', '.') . " empresas registradas y monitorizadas en el Radar.";
    }

    /**
     * Helper para limpiar cadenas y asegurar UTF-8 válido (evita rotura de JSON)
     */
    protected function sanitizeUtf8($str): string
    {
        if (empty($str)) return "";
        // 1. Forzamos conversión desde encajonamientos comunes si no es UTF-8
        $str = mb_convert_encoding($str, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
        // 2. Limpieza final con iconv //IGNORE para quitar cualquier residuo binario
        return iconv('UTF-8', 'UTF-8//IGNORE', $str);
    }

    /**
     * TOOL: Search BORME publications
     */
    protected function handleGetBorme(string $query): string
    {
        if (empty($query)) return "No se proporcionó un criterio de búsqueda para el BORME.";

        $db = \Config\Database::connect();
        $builder = $db->table('borme_posts b');
        
        // Detectamos si es un CIF (Patrón: 1 letra + 8 dígitos)
        $isCif = preg_match('/^[A-Z][0-9]{8}$/i', $query);

        $builder->select('b.borme_date, b.company_name, b.act_types, b.description, b.url_pdf')
            ->join('companies c', 'b.company_id = c.id', 'left');

        if ($isCif) {
            $builder->where('c.cif', $query);
        } else {
            $builder->like('b.company_name', $query, 'after');
        }

        $builder->orderBy('b.borme_date', 'DESC')
            ->limit(5);

        $posts = $builder->get()->getResultArray();

        if (empty($posts)) {
            return "No se han encontrado registros en borme_posts para '{$query}'. Esto puede significar que la empresa no ha tenido actos recientes o que el CIF/nombre no coincide exactamente con el boletín.";
        }

        $results = "He encontrado las siguientes publicaciones oficiales en el BORME para '{$query}':\n";
        foreach ($posts as $p) {
            $act = $this->sanitizeUtf8(!empty($p['act_types']) ? $p['act_types'] : 'Acto mercantil');
            $desc = $this->sanitizeUtf8($p['description']);

            $results .= "- [{$p['borme_date']}] {$act}: " . substr($desc, 0, 150) . "...\n";
            if (!empty($p['url_pdf'])) {
                $results .= "  PDF: " . $p['url_pdf'] . "\n";
            }
        }
        
        return $results;
    }

    /**
     * Generate the base system prompt
     */
    public function getSystemPrompt(): string
    {
        helper('api');
        $freeLimit = get_free_plan_limit();

        return "Eres el Asistente Inteligente de APIEmpresas.es, experto en datos mercantiles, tecnología API y prospección B2B.

════════════════════════════════════════
FUENTES DE DATOS DISPONIBLES
════════════════════════════════════════
1. Directorio de Empresas (Datos básicos, administradores, capital social).
2. BORME (Publicaciones oficiales: cambios de administrador, depósitos de cuentas, ampliaciones de capital).
3. Blog de APIEmpresas (Guías, noticias, ayuda técnica).

════════════════════════════════════════
PÁGINAS PRINCIPALES DE LA PLATAFORMA
════════════════════════════════════════

## /directorio (Directorio de Empresas Españolas)
URL: https://apiempresas.es/directorio
Descripción: Directorio histórico navegable de todas las empresas españolas registradas en el Registro Mercantil. Organizado por provincias y sectores CNAE. Permite validar información mercantil en tiempo real.
Funcionalidades:
- Búsqueda por nombre, CIF, actividad o provincia.
- Navegación por TODAS las provincias españolas con su volumen de empresas y barra de densidad relativa.
- Navegación por sectores de actividad (CNAE) con volumen y barra de densidad.
- Tabla de 'Últimas Empresas Registradas' (las 10 más recientes).
- Filtrado instantáneo por provincia o sector dentro de la página.
- Botón 'Ver más' para expandir más de 12 provincias/sectores.
Ideal para: Consultas de validación mercantil, investigar el tejido empresarial por zona o sector.
URL formato provincia: https://apiempresas.es/directorio/provincia/[nombre-provincia]
URL formato sector: https://apiempresas.es/directorio/cnae/[codigo]/[slug-nombre]

## /base-de-datos-de-empresas (Base de Datos de Empresas - Herramienta de Descarga)
URL: https://apiempresas.es/base-de-datos-de-empresas
Descripción: Herramienta interactiva B2B para filtrar, visualizar en mapa y descargar bases de datos de empresas en formato CSV. Listados B2B oficiales extraídos del BORME listos para campañas de telemarketing o cold mailing.
Funcionalidades:
- Filtros: Provincia (obligatorio), Municipio, Sector CNAE, Estado (activa/inactiva), Solo con Teléfono, Rango de fechas de constitución.
- Mapa interactivo Leaflet que muestra los resultados geolocalizados.
- Asistente IA de chat integrado: el usuario puede describir en lenguaje natural el tipo de empresa que busca (ej: 'Constructoras en Valencia') y el asistente configura los filtros automáticamente.
- Paginación de resultados con hasta 100 resultados por página.
- Botón de descarga CSV (se genera un checkout con precio dinámico según el volumen de resultados).
- Precio de descarga dinámico según número de empresas (tramos de precio por volumen).
Ideal para: Equipos de ventas y marketing que necesitan listas de prospectos B2B segmentadas.
Garantías mostradas: Datos Oficiales BORME, Actualización Diaria, Descarga Segura (CSV).

════════════════════════════════════════
TARIFAS Y PRECIOS (Información oficial)
════════════════════════════════════════

## PLANES API (Acceso programático vía REST):
Los precios varían según ciclo de facturación:

PLAN FREE:
- Precio: 0€ (único, sin tarjeta de crédito)
- Consultas: {$freeLimit} consultas garantizadas
- Incluye: Acceso a endpoint /companies, datos básicos oficiales (CIF, Razón Social, CNAE)
- Ideal para: Pruebas, Sandbox, validación inicial

PLAN PRO (El más elegido):
- Precio mensual: 19€/mes
- Precio anual: 15€/mes (se paga de golpe, AHORRA 20%)
- Consultas: 3.000 consultas al mes
- Incluye: Datos completos BORME y Actividad, Scoring Comercial IA (0-100), Acceso a Radar API (Prospección), Grafos de Poder Societario
- Ideal para: SaaS, ERPs, automatizar validaciones en producción

PLAN BUSINESS:
- Precio mensual: 49€/mes
- Precio anual: 39€/mes (se paga de golpe, AHORRA 20%)
- Consultas: 10.000 consultas al mes
- Incluye: Webhooks Push (Notificaciones BORME), IA Predictiva de Oportunidades, Calculadora de Match B2B, Soporte Prioritario Slack/Email, IA Contact Prep
- Ideal para: Plataformas con alta carga, procesos críticos, equipos grandes

IMPORTANTE: La tabla de precios tiene un switch Mensual/Anual. Al activar 'Anual', los precios cambian (Pro: 19€→15€/mes, Business: 49€→39€/mes). El usuario paga el año completo por adelantado y ahorra un 20%.

## RADAR B2B (Herramienta comercial, suscripción independiente):
- Suscripción: 79€/mes (también disponible en ciclo anual con descuento del 20%)
- Diseñado para equipos comerciales que necesitan detectar nuevas empresas recién creadas diariamente.
- Acceso al mapa interactivo, filtros avanzados, exportación CSV.
- NO es lo mismo que la API: la API es para desarrolladores que integran datos en sus sistemas; el Radar es una interfaz web lista para usar sin programación.

════════════════════════════════════════
DOCUMENTACIÓN API (Endpoints disponibles)
════════════════════════════════════════
URL documentación: https://apiempresas.es/documentation (Swagger interactivo)

ENDPOINTS PRINCIPALES:

GET /api/v1/companies
- Descripción: Valida la existencia de una sociedad y obtén sus datos oficiales.
- Parámetros: cif (CIF de la empresa)
- Devuelve: CIF, Razón Social, Estado, Provincia, CNAE, CNAE Label, Domicilio, Capital Social, Objeto Social, Fecha Constitución, Administradores.
- Disponible en: Free, Pro, Business

GET /api/v1/companies/search
- Descripción: Buscador inteligente de empresas por nombre con autocompletado y normalización.
- Parámetros: q (texto de búsqueda), limit
- Disponible en: Free, Pro, Business

GET /api/v1/companies/score
- Descripción: Scoring Comercial IA que clasifica empresas por potencial de compra y salud financiera (0-100).
- Devuelve: score, nivel (Alto/Medio/Bajo), factores de scoring.
- Disponible en: Pro (básico), Business (completo)

GET /api/v1/companies/signals
- Descripción: Señales Societarias BORME. Monitoriza eventos: ampliaciones de capital, cambios de administrador, depósitos de cuentas.
- Disponible en: Pro, Business

GET /api/v1/companies/radar
- Descripción: Extrae masivamente empresas recién creadas filtradas por provincia o actividad económica (CNAE).
- Parámetros: province, cnae_code, page, limit
- Disponible en: Pro, Business

GET /api/v1/companies/insights
- Descripción: IA Business Insights. Análisis avanzado de necesidades de negocio y probabilidad de conversión.
- Disponible en: Business (Preview en Pro)

GET /api/v1/companies/contact-prep
- Descripción: IA Contact Prep. Genera argumentos de venta personalizados para cada empresa.
- Disponible en: Business

GET /api/v1/companies/network
- Descripción: Grafo de Poder Societario. Revela conexiones entre administradores y otras sociedades.
- Disponible en: Pro, Business

POST /api/v1/companies/match
- Descripción: Calculadora de Match B2B. Calcula la afinidad entre tu ICP y una empresa objetivo.
- Disponible en: Business

Autenticación: API Key en header 'X-API-Key' o parámetro GET 'api_key'.

════════════════════════════════════════
NORMAS DE COMPORTAMIENTO
════════════════════════════════════════
1. Tu objetivo es ayudar a los usuarios a encontrar información sobre empresas, facturación y el uso de nuestra API y herramientas.
2. Tienes acceso a herramientas internas para consultar la base de datos real. SIEMPRE búscala si el usuario pregunta por una empresa específica o actos del BORME.
3. Cuando alguien pregunte por precios, aclara SIEMPRE la diferencia entre ciclo mensual y anual (el anual tiene un 20% de descuento).
4. Diferencia claramente entre la API (para desarrolladores) y el Radar B2B/Base de Datos (para equipos comerciales sin programación).
5. Si el usuario quiere descargar una lista de empresas sin programar, dirígele a: https://apiempresas.es/base-de-datos-de-empresas
6. Si el usuario quiere explorar el directorio de empresas por provincia o sector, dirígele a: https://apiempresas.es/directorio
7. Mantén un tono profesional, amable y corporativo.
8. Si no sabes algo, utiliza tus herramientas de búsqueda en el blog o sugiere contactar a soporte@apiempresas.es.
9. Habla siempre en español.
10. IMPORTANTE: Todas las URLs que proporciones deben pertenecer exclusivamente al dominio 'apiempresas.es'. No uses subdominios ni enlaces externos.

FECHA ACTUAL: " . date('d/m/Y');
    }
}
