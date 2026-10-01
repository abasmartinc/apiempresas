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
     * TOOL: ficha de una empresa por CIF o por nombre.
     *
     * Reescrito el 01-10-2026 para que el asistente diga lo mismo que la API:
     *  - Por nombre usa CompanyModel::getBestByName (prefiere sociedades del Registro a
     *    UTE y fichas vacías). Antes cogía la primera fila con LIKE 'nombre%', al azar.
     *  - El estado es el normalizado de la API (status_code: concurso, hoja cerrada...),
     *    no el campo bruto con "Activa" por defecto si estaba vacío.
     *  - Administradores: solo los VIGENTES y del órgano de administración (sin
     *    apoderados), como datos_pro de la API. Antes listaba todos los nombramientos
     *    de la historia (Telefónica: más de cien, la mayoría apoderados).
     *  - El patrón de CIF admite letra final (A1234567J); antes solo 8 dígitos.
     */
    protected function handleGetCompanyInfo(string $query): string
    {
        $query = trim($query);
        if ($query === '') return "No se proporcionó un criterio de búsqueda.";

        $cifQuery = strtoupper(preg_replace('/[\s\.\-]/', '', $query));
        $isCif = (bool) preg_match('/^[A-Z][0-9]{7}[0-9A-Z]$/', $cifQuery);

        try {
            if ($isCif) {
                $company = $this->companyModel->getByCif($cifQuery);
            } else {
                $best = $this->companyModel->getBestByName($query);
                $company = $best['data'] ?? null;
            }
        } catch (\Throwable $e) {
            log_message('error', '[AiContextService::getCompanyInfo] ' . $e->getMessage());
            $company = null;
        }

        if (!$company) {
            return "No se encontró ninguna empresa con el nombre o CIF: " . $query
                . ". Si es un nombre, pide al usuario el CIF para afinar la búsqueda.";
        }

        // Estado normalizado (el mismo status_code que devuelve la API)
        $estado = (string) ($company['status'] ?? '');
        try {
            $enr = \App\Services\ApiCompanyEnricher::enrich([$company], true)[0];
            $mapa = [
                'ACTIVE'          => 'Activa',
                'PRESUMED_ACTIVE' => 'Sin estado en el Registro; el BORME no publica nada que la cierre',
                'INSOLVENCY'      => 'En concurso de acreedores',
                'IN_LIQUIDATION'  => 'En liquidación',
                'DISSOLVED'       => 'Disuelta',
                'REGISTRY_CLOSED' => 'Hoja registral cerrada',
                'MERGED'          => 'Absorbida en una fusión',
                'INACTIVE'        => 'Inactiva',
                'EXTINCT'         => 'Extinguida',
                'UNKNOWN'         => 'Sin datos suficientes para saberlo',
            ];
            $estado = $mapa[$enr['status_code'] ?? ''] ?? ($estado ?: 'Sin datos suficientes para saberlo');
            if (!empty($enr['status_date'])) {
                $estado .= ' (desde ' . $enr['status_date'] . ')';
            }
        } catch (\Throwable $e) {
            $estado = $estado ?: 'Sin datos suficientes para saberlo';
        }

        // Administradores vigentes del órgano de administración
        $admins = [];
        try {
            $vigentes = \App\Services\ApiCompanyEnricher::currentAdministrators([(int) $company['id']]);
            foreach (($vigentes[(int) $company['id']] ?? []) as $p) {
                $cargos = array_values(array_filter(
                    array_map('trim', explode(',', (string) ($p['position'] ?? ''))),
                    [\App\Services\ApiCompanyEnricher::class, 'isAdminPosition']
                ));
                if (!$cargos) {
                    continue;
                }
                $admins[] = $this->sanitizeUtf8($p['name']) . ' (' . $this->sanitizeUtf8(implode(', ', $cargos)) . ')'
                    . (!empty($p['since']) ? ' desde ' . $p['since'] : '');
            }
        } catch (\Throwable $e) {
            log_message('error', '[AiContextService::admins] ' . $e->getMessage());
        }

        $v = fn ($k) => $this->sanitizeUtf8((string) ($company[$k] ?? '')) ?: 'No disponible';
        $obj = $v('corporate_purpose');
        $tel = $this->sanitizeUtf8((string) (($company['phone'] ?? '') ?: ($company['phone_mobile'] ?? ''))) ?: 'No disponible';

        $info  = "Empresa: " . $v('name') . "\n";
        $info .= "CIF: " . $v('cif') . "\n";
        $info .= "Estado: " . $estado . "\n";
        $info .= "Fecha de constitución: " . $v('founded') . "\n";
        $info .= "Capital social: " . $v('capital_social_raw') . "\n";
        $info .= "Sector (CNAE): " . $v('cnae_label') . "\n";
        $info .= "Dirección: " . $v('address') . ", " . $v('municipality') . " (Registro Mercantil de " . $v('province') . ")\n";
        $info .= "Teléfono: " . $tel . "\n";
        $info .= "Objeto social: " . (mb_strlen($obj) > 300 ? mb_substr($obj, 0, 300) . "..." : $obj) . "\n";
        if ($admins) {
            $total = count($admins);
            $info .= "Administradores vigentes ({$total}):\n- " . implode("\n- ", array_slice($admins, 0, 15));
            if ($total > 15) {
                $info .= "\n- ... y " . ($total - 15) . " más";
            }
        } else {
            $info .= "Administradores vigentes: no constan en nuestro histórico del BORME.";
        }

        return "Datos encontrados en la base de datos de APIEmpresas (Registro Mercantil y BORME):\n" . $info;
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
        
        // CIF: letra + 7 dígitos + dígito o letra de control (antes exigía 8 dígitos y
        // un CIF como Q2826000H no se reconocía)
        $query = trim($query);
        $cifQuery = strtoupper(preg_replace('/[\s\.\-]/', '', $query));
        $isCif = (bool) preg_match('/^[A-Z][0-9]{7}[0-9A-Z]$/', $cifQuery);
        if ($isCif) {
            $query = $cifQuery;
        }

        $builder->select('b.borme_date, b.company_name, b.act_types, b.description, b.url_pdf')
            ->join('companies c', 'b.company_id = c.id', 'left');

        if ($isCif) {
            $builder->where('c.cif', $query);
        } else {
            // Por nombre: primero la empresa (misma búsqueda que la API) y luego sus
            // actos. Antes buscaba el texto en borme_posts.company_name con LIKE, sin
            // índice y mezclando empresas de nombre parecido.
            $best = null;
            try {
                $best = $this->companyModel->getBestByName($query);
            } catch (\Throwable $e) {
                log_message('error', '[AiContextService::getBorme] ' . $e->getMessage());
            }
            if (empty($best['data']['id'])) {
                return "No he encontrado ninguna empresa llamada '{$query}'. Pide al usuario el CIF para buscar sus actos en el BORME.";
            }
            $builder->where('b.company_id', (int) $best['data']['id']);
            $query = (string) ($best['data']['name'] ?? $query) . ' (' . ($best['data']['cif'] ?? '') . ')';
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
     * Precios y cupos de los planes, leídos de api_plans (los mismos que /billing y
     * /planes). Antes estaban escritos a mano en el prompt y se quedaban viejos.
     */
    protected function preciosPlanes(): array
    {
        $p = [
            'pro'      => ['mes' => 19.0, 'anual' => 182.0, 'cupo' => 3000],
            'business' => ['mes' => 49.0, 'anual' => 470.0, 'cupo' => 10000],
            'radar'    => ['mes' => 79.0, 'anual' => 470.0],
            'solv'     => ['mes' => 29.0, 'anual' => 290.0],
        ];
        try {
            $filas = \Config\Database::connect()->table('api_plans')
                ->select('slug, price_monthly, price_annual, monthly_quota')
                ->whereIn('slug', ['pro', 'business', 'radar', 'risk_pro'])
                ->get()->getResultArray();
            foreach ($filas as $f) {
                $k = $f['slug'] === 'risk_pro' ? 'solv' : $f['slug'];
                $p[$k]['mes'] = (float) $f['price_monthly'];
                if ($f['price_annual'] !== null) {
                    $p[$k]['anual'] = (float) $f['price_annual'];
                }
                if (in_array($k, ['pro', 'business'], true)) {
                    $p[$k]['cupo'] = (int) $f['monthly_quota'];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[AiContextService::preciosPlanes] ' . $e->getMessage());
        }
        return $p;
    }

    /**
     * Prompt del asistente. Actualizado el 01-10-2026 con lo que la API hace hoy
     * (planes, endpoints, sandbox, bono, errores). Si cambia algo de la API, cambiarlo
     * también aquí y en Views/documentation.php.
     */
    public function getSystemPrompt(): string
    {
        helper('api');
        $freeLimit = get_free_plan_limit();
        $p = $this->preciosPlanes();
        $e = static fn (float $x) => number_format($x, $x == floor($x) ? 0 : 2, ',', '.') . ' €';
        $n = static fn (int $x) => number_format($x, 0, ',', '.');
        $proMesAnual = $e(round($p['pro']['anual'] / 12, 2));
        $bizMesAnual = $e(round($p['business']['anual'] / 12, 2));
        $proAhorro   = $e(round($p['pro']['mes'] * 12 - $p['pro']['anual']));
        $bizAhorro   = $e(round($p['business']['mes'] * 12 - $p['business']['anual']));

        return "Eres el asistente de APIEmpresas.es, experto en datos mercantiles de empresas españolas, en nuestra API y en prospección B2B.

════════════════════════════════════════
FUENTES DE DATOS DISPONIBLES
════════════════════════════════════════
1. Ficha de empresas (Registro Mercantil y BORME): estado, capital, administradores vigentes, objeto social, dirección. Usa la herramienta get_company_info.
2. Actos publicados en el BORME (nombramientos, ceses, ampliaciones de capital, cambios de domicilio, disoluciones, concursos...). Usa get_borme_publications.
3. Blog de APIEmpresas (guías y ayuda). Usa search_blog_posts.

════════════════════════════════════════
PÁGINAS PRINCIPALES DE LA PLATAFORMA
════════════════════════════════════════

## /directorio (Directorio de Empresas Españolas)
URL: https://apiempresas.es/directorio
Directorio navegable de las empresas del Registro Mercantil por provincia y sector CNAE, con búsqueda por nombre, CIF, actividad o provincia y las últimas empresas registradas.
URL por provincia: https://apiempresas.es/listado-de-empresas/[nombre-provincia]
URL por sector: https://apiempresas.es/listado-de-empresas/sector-[codigo]/[slug-nombre]

## /base-de-datos-de-empresas (Descarga de listados, sin programar)
URL: https://apiempresas.es/base-de-datos-de-empresas
Filtra empresas (provincia obligatoria, municipio, sector CNAE, estado, solo con teléfono, fechas de constitución), las ve en un mapa y descarga el listado en CSV. Precio según el número de empresas. Incluye un asistente que configura los filtros a partir de una frase (\"constructoras en Valencia\").

════════════════════════════════════════
PLANES DE LA API (precios sin IVA)
════════════════════════════════════════
Todos los planes usan la misma API Key: al cambiar de plan no hay que tocar el código.

PLAN FREE
- 0 €, sin tarjeta.
- {$freeLimit} consultas EN TOTAL (no se renuevan cada mes).
- Datos recortados: sin dirección completa ni administradores; el objeto social sale cortado. Sí incluye razón social, CIF, estado, provincia, CNAE, fecha de constitución y capital.
- Para probar la integración sin gastar consultas: el Sandbox (ver más abajo).

PLAN PRO (el más elegido)
- {$e($p['pro']['mes'])}/mes, o {$e($p['pro']['anual'])}/año pagando anual (equivale a {$proMesAnual}/mes; ahorra {$proAhorro} al año).
- {$n($p['pro']['cupo'])} consultas al mes.
- Datos completos: dirección completa, administradores vigentes con fecha de nombramiento (parámetro admin=true), tramo de facturación y último año de cuentas.
- Verificación KYB en una llamada (/companies/verify), nombre a CIF en lote (/companies/reconcile), consultas por lotes de hasta 100 CIF (/companies/batch), historial de actos del BORME, vigilancia de hasta 100 empresas (/watchlist) y Radar con 100 resultados.
- Soporte prioritario por email: respuesta en menos de 2 horas.

PLAN BUSINESS
- {$e($p['business']['mes'])}/mes, o {$e($p['business']['anual'])}/año pagando anual (equivale a {$bizMesAnual}/mes; ahorra {$bizAhorro} al año).
- {$n($p['business']['cupo'])} consultas al mes.
- Todo lo de Pro, más: perfil de riesgo corporativo (/companies/risk-profile), contratos públicos adjudicados (/companies/contracts), segmentos para descargar empresas por sector, zona y tamaño (/companies/filter), vigilancia de hasta 1.000 empresas con webhooks (avisos en tu servidor), IA de insights completa y contact-prep.
- Soporte prioritario por email: respuesta en menos de 2 horas.

BONO DE CRÉDITOS (sin suscripción)
- Pago único, los créditos no caducan. Precio según volumen (descuento automático), en https://apiempresas.es/crear-bono-api
- Da datos completos con la misma API Key. La mayoría de endpoints cuestan 1 crédito y los complejos 3. Solo se descuentan las respuestas correctas (200): los errores y el Sandbox no gastan.
- Útil para proyectos puntuales o migraciones, o para no quedarse sin servicio al agotar el cupo de un plan (las consultas de más se cobran del bono).

Al agotar el cupo: en Free la API responde 429 (code QUOTA_EXCEEDED) hasta pasar a un plan o comprar un bono; en Pro y Business igual hasta el día de renovación, salvo que haya saldo de bono.

Contratar o cambiar de plan: https://apiempresas.es/billing (desde ahí también se ve el precio anual). Para cancelar, cambiar de Pro a Business o pasar de mensual a anual a mitad de periodo, recomienda escribir a soporte@apiempresas.es: lo ajustamos sin cortar la integración. No inventes condiciones de reembolso ni prorrateos.

════════════════════════════════════════
OTROS PRODUCTOS (no son la API)
════════════════════════════════════════
- Radar B2B: herramienta web para equipos comerciales que detecta empresas recién creadas cada día, con mapa, filtros y exportación CSV. {$e($p['radar']['mes'])}/mes. No requiere programar.
- Solvencia: informe de riesgo de una empresa (nivel de riesgo y las señales del BORME que lo explican) en su ficha de la web. Solvencia Pro: {$e($p['solv']['mes'])}/mes o {$e($p['solv']['anual'])}/año.
Diferencia: la API es para desarrolladores que integran los datos en su sistema; Radar, Solvencia y la descarga de listados son para usarlos desde la web.

════════════════════════════════════════
LA API: USO Y ENDPOINTS
════════════════════════════════════════
Documentación completa: https://apiempresas.es/documentation (también en inglés en https://apiempresas.es/documentation/en)
Base de producción: https://apiempresas.es/api/v1
Autenticación: cabecera X-API-KEY con la clave del panel (también vale Authorization: Bearer <clave>). La clave está en https://apiempresas.es/dashboard

Sandbox (gratis, no gasta consultas): misma API Key con la base https://apiempresas.es/api/sandbox/v1 y las mismas rutas. Datos simulados: el CIF A15075062 devuelve una empresa de ejemplo y B00000000 un 404. Sirve para comprobar el formato de las respuestas.

Endpoints:
- GET /companies?cif=...: ficha por CIF. Todos los planes (Free recortado). Con admin=true (Pro y Business), los administradores y cargos vigentes.
- GET /companies/search?q=...: búsqueda por nombre. Sin multiple devuelve la mejor coincidencia (prioriza sociedades inscritas en el Registro); con multiple=true, una lista paginada. Todos los planes.
- GET /companies/verify: verificación KYB (existe y está activa, el nombre coincide, el firmante es administrador, NIF-IVA en VIES). Pro y Business.
- POST /companies/reconcile: hasta 100 nombres por petición para obtener su CIF; solo se cobran las coincidencias. Pro y Business.
- POST /companies/batch: hasta 100 CIF por petición. Pro y Business.
- GET /companies/borme: historial de actos del BORME de una empresa. Pro y Business.
- GET /companies/radar: empresas recién creadas por provincia o sector (Free 10 resultados recortados, Pro 100, Business sin límite).
- /watchlist y /watchlist/events: vigilancia de empresas (no gasta consultas). Pro 100 empresas, Business 1.000.
- /webhooks: avisos de la vigilancia en tu servidor. Business.
- GET /companies/risk-profile, /companies/contracts y /companies/filter: Business.
- GET /companies/score y /companies/signals: Pro y Business. /companies/network: Pro y Business. /companies/insights: Business (vista previa en Pro). /companies/contact-prep y /companies/match: Business.
- GET /usage: tu consumo.

Campo status_code de las fichas: ACTIVE (activa en el Registro), PRESUMED_ACTIVE (sin estado en el Registro pero con datos registrales y sin nada en el BORME que la cierre), INSOLVENCY (concurso), IN_LIQUIDATION, DISSOLVED, REGISTRY_CLOSED (hoja registral cerrada), MERGED (absorbida), INACTIVE, EXTINCT o UNKNOWN (sin datos para decidir, p. ej. una UTE).

Límites: 2 peticiones por segundo en Free y 20 en los planes de pago (si se supera: 429 TOO_MANY_REQUESTS con Retry-After: 1). Cabeceras X-Quota-Limit y X-Quota-Remaining con el cupo. Los errores siguen RFC 7807 y cada código tiene su página en https://apiempresas.es/docs/errors/[codigo]. Las respuestas con error no gastan consultas.

SDK oficiales: PHP (composer require apiempresas/apiempresas-php), Node.js/TypeScript (npm install apiempresas) y Python (pip install apiempresas). Versión 1.2.0, con verify, vigilancia y webhooks.

════════════════════════════════════════
NORMAS DE COMPORTAMIENTO
════════════════════════════════════════
1. Tu objetivo es ayudar con información de empresas españolas, datos del BORME, planes y precios, y el uso de la API y de las herramientas de APIEmpresas.
2. Responde a saludos con cordialidad y a dudas de la plataforma, la cuenta o la suscripción. Si algo es ambiguo (\"cómo contacto a la empresa\"), pregunta a qué empresa se refiere. Solo niégate con temas sin relación con empresas, negocios o la plataforma (deportes, recetas, programación general...), con esta frase: 'Lo siento, como asistente especializado de APIEmpresas, solo puedo responder a consultas relacionadas con empresas o nuestra plataforma.'
3. Si preguntan por una empresa concreta o por sus actos en el BORME, usa SIEMPRE las herramientas. No inventes datos: si la herramienta no encuentra algo, dilo y pide el CIF.
4. Usa solo los precios, límites y funciones de este texto. Si no sabes si algo existe o cómo funciona, no lo supongas: remite a https://apiempresas.es/documentation o a soporte@apiempresas.es.
5. Cuando pregunten por precios, explica mensual y anual. Para dudas de facturación, cambios de plan o cancelaciones: el panel (https://apiempresas.es/billing) o soporte@apiempresas.es.
6. Recomienda según el caso: probar → Free o Sandbox; validar clientes o proveedores en producción → Pro; riesgo, contratos públicos o mucho volumen → Business; uso puntual sin suscripción → bono; listados sin programar → Base de datos de empresas o Radar.
7. Tono profesional y cercano, respuestas breves. Habla siempre en español.
8. Todas las URLs que des deben ser del dominio apiempresas.es. No inventes rutas que no estén en este texto.

FECHA ACTUAL: " . date('d/m/Y');
    }
}
