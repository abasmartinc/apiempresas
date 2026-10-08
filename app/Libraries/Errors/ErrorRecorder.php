<?php

namespace App\Libraries\Errors;

use App\Filters\ApiKeyFilter;
use CodeIgniter\Database\BaseConnection;
use Throwable;

/**
 * El registro de errores propio (el mismo que abasmart): guarda en error_issues / error_events / error_issue_activity de la
 * base de apiempresas. Se gestiona desde el admin: /admin/errores ({@see \App\Controllers\Admin\ErrorTracking}).
 *
 * Tres entradas:
 *   - exception():  las excepciones que llegan al manejador de CodeIgniter (sin capturar, errores PHP y fatales).
 *   - log():        los log_message('error'|'critical'|...) del codigo, que son errores que se capturaron y se siguio.
 *   - js():         los errores del navegador (POST errors/js).
 *
 * Reglas, por orden de importancia:
 *   1. NUNCA rompe nada: todo va en try/catch y, si no puede guardar, se calla (el log en fichero sigue como siempre).
 *   2. Conexion PROPIA a la base (no la compartida): si el error ocurre dentro de una transaccion (un cobro, un alta...), su
 *      rollback no se lleva el registro y el registro no se mezcla con ella.
 *   3. Sin datos personales: todo pasa por {@see ErrorScrubber} antes de guardarse.
 *   4. Sin bucles: si guardar falla y eso vuelve a pasar por aqui, se ignora (un solo registro a la vez).
 *
 * Columnas que en abasmart eran de la agencia, aqui:
 *   - group_name:   el canal donde salto: web | api | admin | cli (para filtrar en el gestor).
 *   - company_id:   la API key (api_keys.id) si fue una llamada a la API.
 *   - user_*:       el usuario de la sesion, o el dueno de la API key en una llamada a la API (user_role = api).
 */
final class ErrorRecorder
{
    /** Eventos que se guardan como mucho por issue y peticion: un bucle que falla mil veces no llena la tabla. */
    private const MAX_PER_REQUEST = 20;

    private static bool $busy = false;

    private static ?BaseConnection $db = null;

    /** @var array<string, int> */
    private static array $perRequest = [];

    private static ?string $release = null;

    public static function exception(Throwable $e, int $statusCode = 500): void
    {
        if (! self::enabled()) {
            return;
        }

        $previous = [];
        $p = $e->getPrevious();

        for ($i = 0; $p !== null && $i < 5; $i++, $p = $p->getPrevious()) {
            $previous[] = [
                'class' => $p::class,
                'message' => ErrorScrubber::text($p->getMessage(), 500),
                'file' => ErrorScrubber::path($p->getFile()),
                'line' => $p->getLine(),
            ];
        }

        // El "donde": si salta dentro del framework o de vendor (un fallo de SQL salta en system/Database), la primera linea de
        // NUESTRO codigo de la pila, que es la que hay que arreglar (como el "in app" de Sentry). Tambien agrupa por ese sitio.
        $traza = ErrorScrubber::trace($e->getTrace());
        $fichero = ErrorScrubber::path($e->getFile());
        $linea = $e->getLine();

        if (! str_starts_with($fichero, 'app/')) {
            foreach ($traza as $f) {
                if (str_starts_with($f['file'], 'app/')) {
                    [$fichero, $linea] = [$f['file'], $f['line']];
                    break;
                }
            }
        }

        self::store([
            'type' => 'exception',
            'level' => 'critical',
            'class' => $e::class,
            'message' => $e->getMessage(),
            'file' => $fichero,
            'line' => $linea,
            'trace' => $traza,
            'previous' => $previous,
            'status' => $statusCode,
            'sql' => self::lastQuery($e),
        ]);
    }

    /**
     * Un log_message de nivel error o mas grave. El "donde" es quien llamo a log_message (se busca en la pila saltandose el
     * propio logger), asi dos log_message iguales en sitios distintos son issues distintos.
     */
    public static function log(string $level, string $message): void
    {
        if (! self::enabled()) {
            return;
        }

        $pila = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 30);
        $origen = ['file' => '', 'line' => 0];
        $desde = 0;

        foreach ($pila as $i => $f) {
            if (($f['function'] ?? '') === 'log_message' && isset($f['file'])) {
                $origen = ['file' => (string) $f['file'], 'line' => (int) ($f['line'] ?? 0)];
                $desde = $i + 1;
                break;
            }
        }

        self::store([
            'type' => 'log',
            'level' => $level,
            'class' => null,
            'message' => $message,
            'file' => $origen['file'] !== '' ? ErrorScrubber::path($origen['file']) : null,
            'line' => $origen['line'] ?: null,
            'trace' => ErrorScrubber::trace(array_slice($pila, $desde)),
            'previous' => [],
            'status' => null,
            'sql' => null,
        ]);
    }

    /**
     * Un error de JavaScript que manda el navegador.
     *
     * @param array{message?: mixed, source?: mixed, line?: mixed, column?: mixed, stack?: mixed, page?: mixed} $d
     */
    public static function js(array $d): void
    {
        if (! self::enabled()) {
            return;
        }

        $fuente = is_string($d['source'] ?? null) ? (string) parse_url($d['source'], PHP_URL_PATH) : '';
        $pila = is_string($d['stack'] ?? null) ? ErrorScrubber::text($d['stack'], 3000) : '';
        $pagina = is_string($d['page'] ?? null) ? (string) parse_url($d['page'], PHP_URL_PATH) : '';

        self::store([
            'type' => 'js',
            'level' => 'error',
            'class' => null,
            'message' => is_string($d['message'] ?? null) ? $d['message'] : 'JavaScript error',
            'file' => $fuente !== '' ? mb_substr($fuente, 0, 500) : null,
            'line' => is_numeric($d['line'] ?? null) ? (int) $d['line'] : null,
            'trace' => [],
            'previous' => [],
            'status' => null,
            'sql' => null,
            'extra' => ['column' => is_numeric($d['column'] ?? null) ? (int) $d['column'] : null, 'stack' => $pila, 'page' => mb_substr($pagina, 0, 500)],
        ]);
    }

    private static function enabled(): bool
    {
        return ! self::$busy && ENVIRONMENT !== 'testing' && (string) env('errors.tracking', '1') !== '0';
    }

    /**
     * @param array{type: string, level: string, class: ?string, message: string, file: ?string, line: ?int, trace: list<array<string, mixed>>, previous: list<array<string, mixed>>, status: ?int, sql: ?string, extra?: array<string, mixed>} $e
     */
    private static function store(array $e): void
    {
        self::$busy = true;

        try {
            $mensaje = ErrorScrubber::text($e['message']);
            $huella = sha1(implode('|', [$e['type'], (string) $e['class'], (string) $e['file'], (string) $e['line'], ErrorScrubber::normalize($e['message'])]));

            self::$perRequest[$huella] = (self::$perRequest[$huella] ?? 0) + 1;

            if (self::$perRequest[$huella] > self::MAX_PER_REQUEST) {
                return;
            }

            $db = self::db();

            if ($db === null) {
                return;
            }

            $ahora = date('Y-m-d H:i:s');
            $ctx = self::context($db);

            // El issue: nuevo, o una vez mas. Si estaba resuelto y vuelve, pasa a "regressed" (reabierto).
            $antes = $db->query('SELECT id, status FROM error_issues WHERE fingerprint = ?', [$huella])->getRow();

            $db->query(
                'INSERT INTO error_issues (fingerprint, type, level, exception_class, message, file, line, status, occurrences, first_seen, last_seen, last_group_name, last_release)
                 VALUES (?, ?, ?, ?, ?, ?, ?, \'open\', 1, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE occurrences = occurrences + 1, last_seen = VALUES(last_seen), last_group_name = VALUES(last_group_name),
                     last_release = VALUES(last_release), status = IF(status = \'resolved\', \'regressed\', status)',
                [$huella, $e['type'], $e['level'], $e['class'], $mensaje, $e['file'], $e['line'], $ahora, $ahora, $ctx['group_name'], self::release()]
            );

            $issueId = (int) ($antes->id ?? $db->insertID());

            if ($issueId <= 0) {
                $issueId = (int) ($db->query('SELECT id FROM error_issues WHERE fingerprint = ?', [$huella])->getRow()->id ?? 0);
            }

            if ($antes === null) {
                self::activity($db, $issueId, 'created', $ahora);
            } elseif ($antes->status === 'resolved') {
                self::activity($db, $issueId, 'regressed', $ahora);
            }

            $db->table('error_events')->insert([
                'issue_id' => $issueId,
                'created_at' => $ahora,
                'environment' => ENVIRONMENT,
                'app_release' => self::release(),
                'server' => mb_substr((string) gethostname(), 0, 128),
                'message' => $mensaje,
                'status_code' => $e['status'],
                'trace' => json_encode($e['trace'], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE),
                'previous' => $e['previous'] !== [] ? json_encode($e['previous'], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) : null,
                'sql_query' => $e['sql'] ? ErrorScrubber::sql($e['sql']) : null,
                'extra' => json_encode(($e['extra'] ?? []) + [
                    'php' => PHP_VERSION,
                    'memory_mb' => round(memory_get_peak_usage(true) / 1048576, 1),
                    'duration_ms' => isset($_SERVER['REQUEST_TIME_FLOAT']) ? (int) round((microtime(true) - (float) $_SERVER['REQUEST_TIME_FLOAT']) * 1000) : null,
                    'host' => $ctx['host'],
                    'query_keys' => $ctx['query_keys'],
                ], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE),
            ] + $ctx['row']);

            $db->query('UPDATE error_issues SET last_event_id = ? WHERE id = ?', [(int) $db->insertID(), $issueId]);
        } catch (Throwable $t) {
            // Callado: el registro nunca puede ser el que rompa la pantalla. El fallo original ya esta en el log en fichero.
        } finally {
            self::$busy = false;
        }
    }

    private static function activity(BaseConnection $db, int $issueId, string $accion, string $cuando): void
    {
        $db->table('error_issue_activity')->insert(['issue_id' => $issueId, 'created_at' => $cuando, 'action' => $accion, 'actor' => 'system']);
    }

    /** Conexion propia (no compartida) a la base de apiempresas. */
    private static function db(): ?BaseConnection
    {
        if (self::$db === null) {
            $c = \Config\Database::connect('default', false);
            $c->initialize();
            self::$db = $c;
        }

        return self::$db;
    }

    /**
     * Quien, donde y como: el usuario de la sesion (o el de la API key), el canal, la ruta sin query string, los parametros limpios.
     *
     * @return array{group_name: ?string, host: ?string, query_keys: string, row: array<string, mixed>}
     */
    private static function context(BaseConnection $db): array
    {
        $cli = is_cli();
        $fila = ['is_cli' => $cli ? 1 : 0];
        $canal = $cli ? 'cli' : 'web';
        $claves = '';
        $host = null;

        if (! $cli) {
            $ruta = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
            $host = mb_substr((string) ($_SERVER['HTTP_HOST'] ?? ''), 0, 100) ?: null;

            if (ApiKeyFilter::$apiMeta !== [] || preg_match('#/api/#', $ruta . '/') === 1) {
                $canal = 'api';
            } elseif (preg_match('#/admin(/|$)#', $ruta) === 1) {
                $canal = 'admin';
            }
        }

        $fila['group_name'] = $canal;

        // Usuario de la sesion. Solo si ya hay sesion (o la cookie de una): en una llamada a la API no se abre una sesion nueva
        // (mandaria una cookie en la respuesta de la API).
        try {
            if (session_status() === PHP_SESSION_ACTIVE || (! $cli && isset($_COOKIE[config(\Config\Session::class)->cookieName]))) {
                $s = session();

                if ($s->get('logged_in')) {
                    $fila += [
                        'user_id' => is_numeric($s->get('user_id')) ? (int) $s->get('user_id') : null,
                        'user_email' => is_scalar($s->get('user_email')) ? mb_substr((string) $s->get('user_email'), 0, 255) : null,
                        'user_role' => $s->get('is_admin') ? 'admin' : 'user',
                    ];
                }
            }
        } catch (Throwable $t) {
        }

        // Llamada a la API: el dueno de la API key.
        try {
            $meta = ApiKeyFilter::$apiMeta;

            if (! isset($fila['user_id']) && ! empty($meta['user_id'])) {
                $uid = (int) $meta['user_id'];
                $email = $db->query('SELECT email FROM users WHERE id = ?', [$uid])->getRow()->email ?? null;
                $fila += [
                    'user_id' => $uid,
                    'user_email' => is_scalar($email) ? mb_substr((string) $email, 0, 255) : null,
                    'user_role' => 'api',
                ];
            }

            if (! empty($meta['api_key_id'])) {
                $fila['company_id'] = (int) $meta['api_key_id'];
                $fila['company_name'] = 'API key #' . (int) $meta['api_key_id'];
            }
        } catch (Throwable $t) {
        }

        try {
            if ($cli) {
                $argv = $_SERVER['argv'] ?? [];
                $fila['path'] = mb_substr('spark ' . implode(' ', array_slice(is_array($argv) ? $argv : [], 1, 3)), 0, 500);
            } else {
                $req = service('request');
                $uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
                $claves = ErrorScrubber::queryKeys((string) parse_url($uri, PHP_URL_QUERY));
                $router = service('router');
                $ref = (string) ($_SERVER['HTTP_REFERER'] ?? '');

                $fila += [
                    'method' => strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')),
                    'path' => mb_substr((string) parse_url($uri, PHP_URL_PATH), 0, 500),
                    'route' => mb_substr(ltrim((string) $router->controllerName(), '\\') . '::' . $router->methodName(), 0, 255),
                    'ip' => $req instanceof \CodeIgniter\HTTP\IncomingRequest ? $req->getIPAddress() : null,
                    'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
                    'referer_path' => $ref !== '' ? mb_substr((string) parse_url($ref, PHP_URL_PATH), 0, 500) : null,
                    'request' => json_encode(['get' => ErrorScrubber::params($_GET), 'post' => ErrorScrubber::params($_POST)], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE),
                ];
            }
        } catch (Throwable $t) {
        }

        return ['group_name' => $canal, 'host' => $host, 'query_keys' => $claves, 'row' => $fila];
    }

    /** La ultima consulta de la conexion compartida, solo si el error es de base de datos. */
    private static function lastQuery(Throwable $e): ?string
    {
        if (! $e instanceof \CodeIgniter\Database\Exceptions\DatabaseException && ! $e instanceof \mysqli_sql_exception) {
            return null;
        }

        try {
            $q = \Config\Database::connect()->getLastQuery();

            return $q !== null ? (string) $q : null;
        } catch (Throwable $t) {
            return null;
        }
    }

    /** El commit desplegado: writable/RELEASE si existe (lo escribe el despliegue); si no, el HEAD de .git. */
    private static function release(): ?string
    {
        if (self::$release !== null) {
            return self::$release ?: null;
        }

        self::$release = '';

        try {
            if (is_file(WRITEPATH . 'RELEASE')) {
                self::$release = mb_substr(trim((string) file_get_contents(WRITEPATH . 'RELEASE')), 0, 64);
            } elseif (is_file(ROOTPATH . '.git/HEAD')) {
                $head = trim((string) file_get_contents(ROOTPATH . '.git/HEAD'));
                $ref = str_starts_with($head, 'ref: ') ? ROOTPATH . '.git/' . substr($head, 5) : '';
                self::$release = substr($ref !== '' && is_file($ref) ? trim((string) file_get_contents($ref)) : $head, 0, 12);
            }
        } catch (Throwable $t) {
        }

        return self::$release ?: null;
    }
}
