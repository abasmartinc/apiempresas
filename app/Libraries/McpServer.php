<?php

namespace App\Libraries;

/**
 * Servidor MCP remoto de APIEmpresas (16-10-2026).
 *
 * Protocolo Model Context Protocol sobre HTTP ("Streamable HTTP"), sin estado: cada POST trae
 * uno o varios mensajes JSON-RPC 2.0 y se responde con JSON (sin SSE ni sesiones). Es lo que
 * piden Claude.ai, ChatGPT y otros clientes remotos, y los directorios que solo aceptan una URL.
 *
 * Las 8 herramientas son las mismas que el paquete npm apiempresas-mcp (sdks/mcp/src/index.ts) y
 * llaman a la API pública con la clave del usuario: mismo cobro, mismos límites y mismos planes.
 * La llamada HTTP se inyecta ($http) para poder probar el protocolo sin red.
 */
class McpServer
{
    public const VERSION = '1.0.0';
    /** Versiones del protocolo que se aceptan; si el cliente pide otra, se responde con la primera. */
    public const PROTOCOLOS = ['2025-06-18', '2025-03-26', '2024-11-05'];
    /** Las respuestas grandes (historial del BORME, contratos) se recortan para no llenar el contexto. */
    public const MAX_CHARS = 60000;
    public const SIGNUP_URL = 'https://apiempresas.es/register?intent=api&plan=free&source=mcp_remote';

    /** @var callable(string $method, string $path, ?array $body): array{status:int, body:mixed} */
    private $http;
    private string $apiKey;

    public function __construct(string $apiKey, callable $http)
    {
        $this->apiKey = trim($apiKey);
        $this->http = $http;
    }

    // ------------------------------------------------------------------
    // JSON-RPC
    // ------------------------------------------------------------------

    /**
     * Procesa el cuerpo de un POST. Devuelve [estado HTTP, respuesta o null].
     * null = solo había notificaciones: se responde 202 sin cuerpo.
     */
    public function handle(string $raw): array
    {
        $msg = json_decode($raw, true);
        if (!is_array($msg)) {
            return [400, self::error(null, -32700, 'JSON no válido')];
        }
        $esLote = array_is_list($msg) && $msg !== [];
        $mensajes = $esLote ? $msg : [$msg];
        $respuestas = [];
        foreach ($mensajes as $m) {
            $r = $this->procesar(is_array($m) ? $m : []);
            if ($r !== null) {
                $respuestas[] = $r;
            }
        }
        if ($respuestas === []) {
            return [202, null];
        }
        return [200, $esLote ? $respuestas : $respuestas[0]];
    }

    private function procesar(array $m): ?array
    {
        $tieneId = array_key_exists('id', $m);
        $id = $m['id'] ?? null;
        $metodo = $m['method'] ?? null;

        // Respuestas del cliente o notificaciones (sin id): no se contestan
        if (!is_string($metodo)) {
            return $tieneId && !isset($m['result']) && !isset($m['error']) ? self::error($id, -32600, 'Petición no válida') : null;
        }
        if (!$tieneId) {
            return null; // notifications/initialized, notifications/cancelled...
        }
        $params = is_array($m['params'] ?? null) ? $m['params'] : [];

        switch ($metodo) {
            case 'initialize':
                $pedida = (string) ($params['protocolVersion'] ?? '');
                return self::ok($id, [
                    'protocolVersion' => in_array($pedida, self::PROTOCOLOS, true) ? $pedida : self::PROTOCOLOS[0],
                    'capabilities'    => ['tools' => ['listChanged' => false]],
                    'serverInfo'      => ['name' => 'apiempresas', 'title' => 'APIEmpresas', 'version' => self::VERSION],
                    'instructions'    => 'Datos de empresas españolas (sociedades) a partir del Registro Mercantil (BORME) y otras '
                        . 'fuentes públicas. Si tienes el CIF, usa get_company; si solo tienes el nombre, search_companies (una '
                        . 'empresa) o find_cif_by_name (una lista). Para dar de alta a un cliente o proveedor, verify_company. '
                        . 'Cada llamada gasta consultas del plan del usuario. No hay datos de autónomos.',
                ]);
            case 'ping':
                return self::ok($id, new \stdClass());
            case 'tools/list':
                return self::ok($id, ['tools' => self::herramientas()]);
            case 'tools/call':
                $nombre = (string) ($params['name'] ?? '');
                $args = is_array($params['arguments'] ?? null) ? $params['arguments'] : [];
                $nombres = array_column(self::herramientas(), 'name');
                if (!in_array($nombre, $nombres, true)) {
                    return self::error($id, -32602, 'Herramienta desconocida: ' . $nombre);
                }
                return self::ok($id, $this->llamarHerramienta($nombre, $args));
            case 'resources/list':
                return self::ok($id, ['resources' => []]);
            case 'prompts/list':
                return self::ok($id, ['prompts' => []]);
            default:
                return self::error($id, -32601, 'Método no soportado: ' . $metodo);
        }
    }

    private static function ok($id, $result): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'result' => $result];
    }

    private static function error($id, int $code, string $message): array
    {
        return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => $code, 'message' => $message]];
    }

    // ------------------------------------------------------------------
    // Herramientas
    // ------------------------------------------------------------------

    public static function herramientas(): array
    {
        $cif = ['type' => 'string', 'minLength' => 9, 'maxLength' => 15,
                'description' => 'CIF/NIF de la sociedad española, p. ej. A46103834 (se aceptan espacios y guiones)'];
        $ro = ['readOnlyHint' => true, 'destructiveHint' => false, 'idempotentHint' => true, 'openWorldHint' => true];
        $t = static fn(string $name, string $title, string $desc, array $props, array $req = []) => [
            'name' => $name,
            'title' => $title,
            'description' => $desc,
            'inputSchema' => ['type' => 'object', 'properties' => $props ?: new \stdClass(), 'required' => $req, 'additionalProperties' => false],
            'annotations' => $ro + ['title' => $title],
        ];

        return [
            $t('get_company', 'Empresa por CIF',
                'Ficha de UNA empresa española cuando ya tienes su CIF/NIF. Úsala para saber si existe y está activa, su razón '
                . 'social, forma jurídica (con código ISO 20275), código LEI, CNAE, provincia y fecha de constitución; con plan '
                . 'Pro, también domicilio, tramo de facturación, socio único, matriz del grupo y, con include_directors, los '
                . 'administradores vigentes. Si no tienes el CIF, usa search_companies; para decidir un alta (KYB), '
                . 'verify_company. Gasta 1 consulta. Spanish company lookup by tax ID.',
                ['cif' => $cif, 'include_directors' => ['type' => 'boolean', 'description' => 'true para incluir administradores y cargos vigentes (Pro y Business)']],
                ['cif']),
            $t('search_companies', 'Buscar empresas por nombre',
                'Busca empresas españolas por nombre o parte del nombre y devuelve las candidatas con su CIF, provincia y '
                . 'estado. Úsala cuando NO tienes el CIF de una empresa concreta; después, get_company con el CIF elegido. '
                . 'Para convertir una lista de muchos nombres en CIF, mejor find_cif_by_name. Search Spanish companies by name.',
                ['query' => ['type' => 'string', 'minLength' => 2, 'description' => 'Nombre o parte del nombre de la empresa'],
                 'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 25, 'description' => 'Número máximo de resultados (por defecto 10)']],
                ['query']),
            $t('verify_company', 'Verificación KYB',
                'Verificación para dar de alta a un cliente o proveedor (KYB) en una llamada: comprueba que la sociedad existe '
                . 'y opera, que la razón social que te han dado coincide (name), que quien firma es administrador vigente o socio '
                . 'único (person) y, si se pide, el NIF-IVA en VIES (vat). Devuelve alertas y una recomendación pass / review / '
                . 'fail, además de forma jurídica, LEI, matriz y socio único. Úsala para decidir; para solo consultar datos, '
                . 'get_company. Plan Pro; gasta 2 consultas. KYB check for a Spanish company.',
                ['cif' => $cif,
                 'name' => ['type' => 'string', 'description' => 'Razón social que ha declarado el cliente, para comprobar que coincide'],
                 'person' => ['type' => 'string', 'description' => 'Nombre y apellidos de quien firma, para comprobar si es administrador o socio único'],
                 'vat' => ['type' => 'boolean', 'description' => 'true para comprobar además el NIF-IVA intracomunitario en VIES']],
                ['cif']),
            $t('find_cif_by_name', 'De nombre a CIF (lista)',
                'Convierte una LISTA de nombres de empresa (hasta 100) en sus CIF, con resultado match, ambiguous o no_match '
                . 'para cada uno. Úsala para completar hojas de clientes o proveedores que solo tienen el nombre. Solo los match '
                . 'gastan consulta. Para un solo nombre, search_companies. Plan Pro.',
                ['names' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 100, 'items' => ['type' => 'string', 'minLength' => 2], 'description' => 'Nombres de empresas'],
                 'province' => ['type' => 'string', 'description' => 'Provincia (p. ej. VALENCIA) para desempatar; se aplica a todos los nombres']],
                ['names']),
            $t('get_borme_history', 'Historial del BORME',
                'Todos los actos publicados en el BORME de una empresa, con fecha y PDF oficial: constitución, nombramientos y '
                . 'ceses, ampliaciones de capital, cambios de domicilio o de nombre, disolución, concurso... Úsala para la '
                . 'historia y los cambios recientes; para el estado actual basta get_company. Plan Pro.',
                ['cif' => $cif], ['cif']),
            $t('get_risk_profile', 'Perfil de riesgo',
                'Nivel de riesgo de una empresa y sus motivos, calculado a partir de sus publicaciones en el BORME (concursos, '
                . 'disoluciones, cierres de hoja registral, cuentas no depositadas, cambios de administradores...). Úsala antes '
                . 'de dar crédito o firmar con un proveedor. Plan Business.',
                ['cif' => $cif], ['cif']),
            $t('get_public_contracts', 'Contratos públicos',
                'Contratos del sector público adjudicados a una empresa (Plataforma de Contratación del Sector Público): '
                . 'organismo, objeto, importe y fecha. Plan Business.',
                ['cif' => $cif,
                 'page' => ['type' => 'integer', 'minimum' => 1, 'description' => 'Página (por defecto 1)'],
                 'limit' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'description' => 'Contratos por página (por defecto 20)']],
                ['cif']),
            $t('get_usage', 'Consumo de la cuenta',
                'Plan de la cuenta del usuario, consultas usadas y disponibles y saldo de créditos. No gasta consultas. Úsala si '
                . 'una herramienta responde que se ha agotado el cupo.',
                []),
        ];
    }

    /** Ejecuta una herramienta: siempre devuelve un resultado MCP (los fallos van con isError). */
    public function llamarHerramienta(string $nombre, array $a): array
    {
        if ($this->apiKey === '') {
            return self::texto('Falta la API key de APIEmpresas. Crea una cuenta gratis (100 consultas, sin tarjeta) en '
                . self::SIGNUP_URL . ', copia la clave del panel y añádela a la conexión: en la cabecera Authorization '
                . '("Bearer TU_CLAVE") o en la dirección del servidor (https://apiempresas.es/api/mcp/TU_CLAVE).', true);
        }

        $cif = self::limpiarCif((string) ($a['cif'] ?? ''));
        $necesitaCif = !in_array($nombre, ['search_companies', 'find_cif_by_name', 'get_usage'], true);
        if ($necesitaCif && (strlen($cif) < 9 || strlen($cif) > 15)) {
            return self::texto('Falta el CIF o no tiene un formato válido (p. ej. A46103834).', true);
        }

        switch ($nombre) {
            case 'get_company':
                return $this->get('/companies', ['cif' => $cif, 'admin' => !empty($a['include_directors']) ? 'true' : null]);
            case 'search_companies':
                $q = trim((string) ($a['query'] ?? ''));
                if (mb_strlen($q) < 2) {
                    return self::texto('Escribe al menos 2 letras del nombre de la empresa.', true);
                }
                return $this->get('/companies/search', ['q' => $q, 'multiple' => 'true', 'limit' => self::entero($a['limit'] ?? 10, 1, 25)]);
            case 'verify_company':
                return $this->get('/companies/verify', [
                    'cif' => $cif,
                    'name' => isset($a['name']) ? (string) $a['name'] : null,
                    'person' => isset($a['person']) ? (string) $a['person'] : null,
                    'vat' => !empty($a['vat']) ? 'true' : null,
                ]);
            case 'find_cif_by_name':
                $nombres = array_values(array_filter(array_map(static fn($n) => trim((string) $n), (array) ($a['names'] ?? [])), static fn($n) => mb_strlen($n) >= 2));
                if ($nombres === [] || count($nombres) > 100) {
                    return self::texto('Pasa entre 1 y 100 nombres de empresa en "names".', true);
                }
                $prov = trim((string) ($a['province'] ?? ''));
                $items = array_map(static fn($n) => $prov !== '' ? ['name' => $n, 'province' => $prov] : ['name' => $n], $nombres);
                return $this->resultado(($this->http)('POST', '/companies/reconcile', ['items' => $items]));
            case 'get_borme_history':
                return $this->get('/companies/borme', ['cif' => $cif]);
            case 'get_risk_profile':
                return $this->get('/companies/risk-profile', ['cif' => $cif]);
            case 'get_public_contracts':
                return $this->get('/companies/contracts', ['cif' => $cif, 'page' => self::entero($a['page'] ?? 1, 1, 100000), 'limit' => self::entero($a['limit'] ?? 20, 1, 50)]);
            case 'get_usage':
                return $this->get('/usage', []);
        }
        return self::texto('Herramienta desconocida.', true);
    }

    private function get(string $path, array $params): array
    {
        $params = array_filter($params, static fn($v) => $v !== null && $v !== '');
        $url = $path . ($params ? '?' . http_build_query($params) : '');
        return $this->resultado(($this->http)('GET', $url, null));
    }

    private function resultado(array $r): array
    {
        $status = (int) ($r['status'] ?? 0);
        $body = $r['body'] ?? null;
        if ($status === 0) {
            $detalle = is_array($body) && !empty($body['detalle']) ? ' (detalle: ' . $body['detalle'] . ')' : '';
            return self::texto('No se pudo conectar con la API de APIEmpresas. Prueba de nuevo en un momento.' . $detalle, true);
        }
        if ($status < 200 || $status >= 300) {
            return self::texto(self::mensajeError($status, $body), true);
        }
        $t = json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($t === false) {
            $t = (string) (is_string($body) ? $body : '');
        }
        if (mb_strlen($t) > self::MAX_CHARS) {
            $t = mb_substr($t, 0, self::MAX_CHARS) . "\n… (respuesta recortada: pide menos resultados o una página concreta)";
        }
        return self::texto($t);
    }

    private static function texto(string $t, bool $error = false): array
    {
        $r = ['content' => [['type' => 'text', 'text' => $t]]];
        if ($error) {
            $r['isError'] = true;
        }
        return $r;
    }

    public static function limpiarCif(string $cif): string
    {
        return strtoupper((string) preg_replace('/[\s.\-]/', '', $cif));
    }

    private static function entero($v, int $min, int $max): int
    {
        $n = is_numeric($v) ? (int) $v : $min;
        return max($min, min($max, $n));
    }

    /** Qué ha pasado y qué hacer, con los enlaces de compra que trae la propia API. Igual que el paquete npm. */
    public static function mensajeError(int $status, $body): string
    {
        $b = is_array($body) ? $body : [];
        $primero = (isset($b['messages']) && is_array($b['messages'])) ? (string) (array_values($b['messages'])[0] ?? '') : '';
        $msg = (string) ($b['message'] ?? $b['detail'] ?? (is_string($b['error'] ?? null) ? $b['error'] : '') ?: $primero);
        $code = is_string($b['code'] ?? null) ? $b['code'] : '';
        $partes = ['Error ' . $status . ($code !== '' ? " ({$code})" : '') . ': ' . ($msg !== '' ? $msg : 'la API no ha devuelto detalle')];

        $plan = isset($b['required_plan']) && is_string($b['required_plan']) ? ' (necesita el plan ' . ($b['required_plan'] === 'business' ? 'Business' : 'Pro') . ')' : '';
        $consejos = [
            'API_KEY_MISSING' => 'Falta la API key. Se obtiene gratis en ' . self::SIGNUP_URL . '.',
            'API_KEY_INVALID' => 'La API key no es válida. Revisa la clave de la conexión o crea una gratis en ' . self::SIGNUP_URL . '.',
            'API_KEY_INACTIVE' => 'La API key o la cuenta están desactivadas. Revísalo en el panel de APIEmpresas.',
            'COUNTRY_NOT_ALLOWED' => 'La API key no admite peticiones desde este país (se configura en el panel).',
            'PLAN_RESTRICTION' => 'Este dato no está incluido en el plan actual de la cuenta' . $plan . '.',
            'UPGRADE_REQUIRED' => 'Este dato no está incluido en el plan actual de la cuenta' . $plan . '.',
            'QUOTA_EXCEEDED' => 'Se han agotado las consultas del plan y no queda saldo. Reintentar no sirve.',
            'TOO_MANY_REQUESTS' => 'Demasiadas peticiones por segundo: espera un momento y vuelve a intentarlo.',
            'IP_LIMIT_EXCEEDED' => 'Se ha alcanzado el límite de seguridad por IP del plan Free.',
            'PREVIEW_LIMIT_EXCEEDED' => 'Se ha alcanzado el límite diario de vistas previas gratuitas. Vuelve mañana o mejora el plan.',
            'COMPANY_NOT_FOUND' => 'No hay ninguna empresa con ese CIF o nombre en la base de datos.',
            'AUTONOMO_NOT_SUPPORTED' => 'Es un DNI o NIE: la API solo tiene datos de sociedades, no de autónomos.',
            'FAKE_CIF_NOT_ALLOWED' => 'Es un CIF de prueba: usa un CIF real.',
            'INVALID_CIF_FORMAT' => 'El CIF no tiene un formato válido.',
            'RISK_PROFILE_NOT_AVAILABLE' => 'La empresa existe, pero no tiene perfil de riesgo calculado.',
        ];
        if ($code !== '' && isset($consejos[$code])) {
            $partes[] = $consejos[$code];
        } elseif ($status === 401) {
            $partes[] = $consejos['API_KEY_INVALID'];
        } elseif ($status === 403) {
            $partes[] = 'Este dato no está incluido en el plan actual de la cuenta.';
        }
        $etiquetas = ['starter_url' => 'Bono de 1.000 créditos (pago único)', 'checkout_url' => 'Mejorar plan', 'recharge_url' => 'Recargar créditos', 'upgrade_url' => 'Planes'];
        $up = (isset($b['upsell_opportunities']) && is_array($b['upsell_opportunities'])) ? $b['upsell_opportunities'] : [];
        $vistos = [];
        foreach ($etiquetas as $k => $etiqueta) {
            $url = (is_string($b[$k] ?? null) && $b[$k] !== '') ? $b[$k] : ((is_string($up[$k] ?? null) && $up[$k] !== '') ? $up[$k] : '');
            if ($url !== '' && !isset($vistos[$url])) {
                $vistos[$url] = true;
                $partes[] = "{$etiqueta}: {$url}";
            }
        }
        return implode("\n", $partes);
    }
}
