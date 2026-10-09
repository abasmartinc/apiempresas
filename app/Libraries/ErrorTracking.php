<?php

namespace App\Libraries;

/**
 * Gestion del registro de errores de apiempresas (tablas error_issues, error_events y error_issue_activity), igual que la de
 * abasmart_tools. App\Libraries\Errors\ErrorRecorder escribe los issues y los eventos; desde aqui solo se leen y se cambian
 * los campos de gestion (status, resolved_*, notes), dejando una fila en error_issue_activity por cada accion.
 *
 * En apiempresas no hay agencias: group_name es el canal (web | api | admin | cli) y, en vez de "agencias afectadas",
 * se cuentan los usuarios afectados.
 */
class ErrorTracking
{
    public const STATUSES = ['open', 'regressed', 'resolved', 'ignored'];
    public const TYPES    = ['exception', 'log', 'js'];
    public const CHANNELS = ['web', 'api', 'admin', 'cli'];

    public const STATUS_LABELS  = ['open' => 'Abierto', 'regressed' => 'Reabierto', 'resolved' => 'Resuelto', 'ignored' => 'Ignorado'];
    public const TYPE_LABELS    = ['exception' => 'Excepción', 'log' => 'Log', 'js' => 'JavaScript'];
    public const CHANNEL_LABELS = ['web' => 'Web', 'api' => 'API', 'admin' => 'Admin', 'cli' => 'CLI (spark)'];
    public const ACTION_LABELS  = ['created' => 'Creado', 'regressed' => 'Ha vuelto (reabierto)', 'resolved' => 'Resuelto', 'reopened' => 'Reabierto', 'ignored' => 'Ignorado', 'note' => 'Nota'];

    public const ISSUES_PER_PAGE = 25;
    public const EVENTS_PER_PAGE = 20;

    private static ?int $pending = null;

    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Issues abiertos + reabiertos, para el aviso del menu del admin. Una vez por peticion y nunca rompe la pagina
     * (si las tablas no existen, 0).
     */
    public static function pendingCount(): int
    {
        if (self::$pending === null) {
            self::$pending = 0;
            try {
                $row = \Config\Database::connect()->query("SELECT COUNT(*) AS n FROM error_issues WHERE status IN ('open', 'regressed')")->getRow();
                self::$pending = (int) ($row->n ?? 0);
            } catch (\Throwable $e) {
            }
        }

        return self::$pending;
    }

    /**
     * Issues creados en las ultimas 24 h que siguen sin gestionar (abiertos o reabiertos), para el dashboard del admin.
     * Nunca rompe la pagina (si las tablas no existen, 0).
     */
    public static function newCount(): int
    {
        try {
            $row = \Config\Database::connect()->query(
                "SELECT COUNT(*) AS n FROM error_issues WHERE status IN ('open', 'regressed') AND first_seen >= ?",
                [date('Y-m-d H:i:s', strtotime('-24 hours'))]
            )->getRow();

            return (int) ($row->n ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Issues que cumplen los filtros, los de actividad mas reciente primero.
     *
     * @param array{status?: string[], type?: string, group?: string, q?: string, from?: string, to?: string} $filters
     * @return array{rows: array, total: int}
     */
    public function issues(array $filters, int $page): array
    {
        $builder = $this->db->table('error_issues i');
        $this->applyFilters($builder, $filters);

        $total = (clone $builder)->countAllResults();

        $rows = $builder->select('i.*')
            ->orderBy('i.last_seen', 'DESC')
            ->limit(self::ISSUES_PER_PAGE, max(0, ($page - 1) * self::ISSUES_PER_PAGE))
            ->get()->getResultArray();

        // Usuarios afectados, solo de los issues de esta pagina
        if ($rows) {
            $users = array_column($this->db->table('error_events')
                ->select('issue_id, COUNT(DISTINCT user_id) AS users', false)
                ->whereIn('issue_id', array_column($rows, 'id'))
                ->groupBy('issue_id')->get()->getResultArray(), 'users', 'issue_id');
            foreach ($rows as &$row) {
                $row['users'] = (int) ($users[$row['id']] ?? 0);
            }
            unset($row);
        }

        return ['rows' => $rows, 'total' => $total];
    }

    /** Contadores de la cabecera del listado. */
    public function counters(): array
    {
        $row = $this->db->table('error_issues')
            ->select("SUM(status = 'open') AS open, SUM(status = 'regressed') AS regressed,
                SUM(first_seen >= " . $this->db->escape(date('Y-m-d H:i:s', strtotime('-24 hours'))) . ') AS new24h', false)
            ->get()->getRowArray();

        $out = ['open' => 0, 'regressed' => 0, 'new24h' => 0];
        foreach ($out as $k => $v) {
            $out[$k] = (int) ($row[$k] ?? 0);
        }

        // Eventos de las ultimas 24 h (todas las repeticiones, no solo issues nuevos)
        $out['events24h'] = (int) $this->db->table('error_events')->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))->countAllResults();

        return $out;
    }

    /** Canales que aparecen en los eventos, para el filtro. */
    public function channels(): array
    {
        $found = array_column($this->db->table('error_events')->select('group_name')
            ->where('group_name IS NOT NULL', null, false)->where('group_name !=', '')
            ->groupBy('group_name')->orderBy('group_name')->get()->getResultArray(), 'group_name');

        return array_values(array_unique(array_merge(self::CHANNELS, $found)));
    }

    public function issue(int $id): ?array
    {
        $issue = $this->db->table('error_issues')->where('id', $id)->get()->getRowArray();
        if (!$issue) {
            return null;
        }

        $issue['users'] = (int) $this->db->table('error_events')
            ->select('COUNT(DISTINCT user_id) AS n', false)->where('issue_id', $id)
            ->get()->getRow()->n;
        $issue['channels'] = array_column($this->db->table('error_events')
            ->select('group_name, COUNT(*) AS n')->where('issue_id', $id)
            ->groupBy('group_name')->orderBy('n', 'DESC')->get()->getResultArray(), 'n', 'group_name');
        $issue['stored_events'] = $this->db->table('error_events')->where('issue_id', $id)->countAllResults();

        return $issue;
    }

    /** Eventos guardados por dia en los ultimos $days dias (se guardan como mucho 200 por issue y 90 dias). */
    public function dailyCounts(int $issueId, int $days = 30): array
    {
        $rows = array_column($this->db->table('error_events')
            ->select('DATE(created_at) AS day, COUNT(*) AS n')
            ->where('issue_id', $issueId)
            ->where('created_at >=', date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days')))
            ->groupBy('day')->get()->getResultArray(), 'n', 'day');

        // Fechas de PHP, como las que escribe ErrorRecorder (date()): asi no influye la zona horaria de MySQL.
        $today = date('Y-m-d');
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("{$today} -{$i} days"));
            $out[] = ['day' => $day, 'label' => date('d/m', strtotime($day)), 'n' => (int) ($rows[$day] ?? 0)];
        }

        return $out;
    }

    /** @return array{rows: array, total: int} */
    public function events(int $issueId, int $page): array
    {
        $total = $this->db->table('error_events')->where('issue_id', $issueId)->countAllResults();
        $rows = $this->db->table('error_events')
            ->select('id, created_at, environment, app_release, group_name, company_id, company_name, user_id, user_email, user_role, is_cli, method, path, route, status_code')
            ->where('issue_id', $issueId)->orderBy('id', 'DESC')
            ->limit(self::EVENTS_PER_PAGE, max(0, ($page - 1) * self::EVENTS_PER_PAGE))
            ->get()->getResultArray();

        return ['rows' => $rows, 'total' => $total];
    }

    /** Un evento del issue con sus columnas JSON decodificadas. */
    public function event(int $issueId, int $eventId): ?array
    {
        $event = $this->db->table('error_events')->where('issue_id', $issueId)->where('id', $eventId)->get()->getRowArray();
        if (!$event) {
            return null;
        }

        foreach (['request', 'trace', 'previous', 'extra'] as $field) {
            $decoded = json_decode((string) $event[$field], true);
            $event[$field] = is_array($decoded) ? $decoded : [];
        }

        return $event;
    }

    public function latestEventId(int $issueId): ?int
    {
        $row = $this->db->table('error_events')->select('id')->where('issue_id', $issueId)->orderBy('id', 'DESC')->limit(1)->get()->getRow();

        return $row ? (int) $row->id : null;
    }

    public function activity(int $issueId): array
    {
        return $this->db->table('error_issue_activity')->where('issue_id', $issueId)->orderBy('id', 'DESC')->get()->getResultArray();
    }

    /**
     * Prompt listo para pegar en un asistente de IA con los datos tecnicos del issue y de un evento.
     * Solo datos tecnicos: sin email del usuario, IP ni navegador.
     */
    public function aiPrompt(array $issue, ?array $event): string
    {
        $line = fn(string $label, $value) => $value === null || $value === '' ? '' : "- {$label}: {$value}\n";
        $json = fn($value) => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $out = "Necesito ayuda para analizar un error de APIEmpresas (apiempresas.es), una aplicación web PHP 8 / CodeIgniter 4.5 "
            . "con MySQL/MariaDB (web pública, panel de usuario, API REST y comandos spark). "
            . "Explícame la causa más probable, señala el fichero y la línea exactos que hay que cambiar, arréglalo y pruébalo. "
            . "Si falta algo para estar seguro, dime qué tengo que comprobar.\n\n";

        $out .= "## Error\n"
            . $line('Tipo', $issue['type'] . ($issue['level'] ? ' (' . $issue['level'] . ')' : ''))
            . $line('Clase de la excepción', $issue['exception_class'])
            . $line('Mensaje', $issue['message'])
            . $line('Dónde', $issue['file'] . ($issue['line'] ? ':' . $issue['line'] : ''))
            . $line('Veces', $issue['occurrences'] . ' (primera ' . $issue['first_seen'] . ', última ' . $issue['last_seen'] . ')')
            . $line('Usuarios afectados', (string) ($issue['users'] ?? ''))
            . $line('Canales', !empty($issue['channels']) ? implode(', ', array_map(fn($c, $n) => ($c ?: '?') . " ({$n})", array_keys($issue['channels']), $issue['channels'])) : '')
            . $line('Estado', $issue['status'])
            . $line('Última versión (commit)', $issue['last_release']);

        if ($event) {
            $out .= "\n## Un caso (evento #{$event['id']}, {$event['created_at']})\n"
                . $line('Entorno', trim(($event['environment'] ?? '') . ' · ' . ($event['app_release'] ?? ''), ' ·'))
                . $line('Contexto', $event['is_cli'] ? 'Comando CLI (spark)' : 'Petición HTTP')
                . $line('Canal', $event['group_name'])
                . $line('Petición', trim(($event['method'] ?? '') . ' ' . ($event['path'] ?? '')))
                . $line('Ruta (controlador::método)', $event['route'])
                . $line('Código HTTP', (string) ($event['status_code'] ?? ''))
                . $line('Referer', $event['referer_path'])
                . $line('Rol del usuario', $event['user_role']);
            if (!empty($event['message']) && $event['message'] !== $issue['message']) {
                $out .= $line('Mensaje del evento', $event['message']);
            }

            if (!empty($event['request'])) {
                $out .= "\n### Parámetros de la petición (nombres; los valores son ids, CIF de sociedad o \"[text N]\")\n```json\n" . $json($event['request']) . "\n```\n";
            }
            if (!empty($event['trace'])) {
                $out .= "\n### Traza (la llamada más reciente primero)\n```\n";
                foreach ($event['trace'] as $i => $frame) {
                    $out .= '#' . $i . ' ' . ($frame['file'] ?? '[internal]') . (!empty($frame['line']) ? ':' . $frame['line'] : '') . '  ' . ($frame['function'] ?? '') . "\n";
                }
                $out .= "```\n";
            }
            if (!empty($event['previous'])) {
                $out .= "\n### Excepciones anteriores\n";
                foreach ($event['previous'] as $prev) {
                    $out .= '- ' . ($prev['class'] ?? '') . ': ' . ($prev['message'] ?? '') . ' en ' . ($prev['file'] ?? '') . (!empty($prev['line']) ? ':' . $prev['line'] : '') . "\n";
                }
            }
            if (!empty($event['sql_query'])) {
                $out .= "\n### Consulta SQL (valores cambiados por ?)\n```sql\n" . $event['sql_query'] . "\n```\n";
            }
            if (!empty($event['extra'])) {
                $out .= "\n### Extra\n```json\n" . $json($event['extra']) . "\n```\n";
            }
        }

        return $out;
    }

    // ───────────── Acciones ─────────────

    /**
     * Aplica una accion a un issue. Devuelve '' si va bien o el mensaje de error.
     * resolve: open|regressed|ignored → resolved; ignore: open|regressed|resolved → ignored;
     * reopen: resolved|ignored → open; note: cualquier estado.
     */
    public function act(int $issueId, string $action, string $actor, string $note = '', string $release = ''): string
    {
        $issue = $this->db->table('error_issues')->select('id, status')->where('id', $issueId)->get()->getRowArray();
        if (!$issue) {
            return 'No existe ese issue.';
        }

        $note = trim($note);
        $release = mb_substr(trim($release), 0, 64);
        $status = $issue['status'];

        $update = [];
        $escape = [];
        switch ($action) {
            case 'resolve':
                if ($status === 'resolved') {
                    return 'El issue ya está resuelto.';
                }
                $update = ['status' => 'resolved', 'resolved_by' => $actor, 'resolved_release' => $release !== '' ? $release : null];
                $update['resolved_at'] = date('Y-m-d H:i:s');
                $logAction = 'resolved';
                break;
            case 'ignore':
                if ($status === 'ignored') {
                    return 'El issue ya está ignorado.';
                }
                $update = ['status' => 'ignored'];
                $logAction = 'ignored';
                break;
            case 'reopen':
                if (!in_array($status, ['resolved', 'ignored'], true)) {
                    return 'Solo se pueden reabrir issues resueltos o ignorados.';
                }
                $update = ['status' => 'open', 'resolved_at' => null, 'resolved_by' => null, 'resolved_release' => null];
                $logAction = 'reopened';
                break;
            case 'note':
                if ($note === '') {
                    return 'Escribe primero la nota.';
                }
                $logAction = 'note';
                break;
            default:
                return 'Acción desconocida.';
        }

        // El issue se queda con la ultima nota escrita
        if ($note !== '') {
            $update['notes'] = $note;
        }

        $this->db->transStart();
        if ($update || $escape) {
            $builder = $this->db->table('error_issues')->where('id', $issueId);
            foreach ($escape as $field => $expression) {
                $builder->set($field, $expression, false);
            }
            $builder->set($update)->update();
        }
        $this->db->table('error_issue_activity')
            ->set(['created_at' => date('Y-m-d H:i:s'), 'issue_id' => $issueId, 'action' => $logAction, 'actor' => mb_substr($actor, 0, 255), 'note' => $note !== '' ? $note : null])
            ->insert();
        $this->db->transComplete();

        return $this->db->transStatus() ? '' : 'Error de base de datos: no se ha guardado nada.';
    }

    /**
     * La misma accion sobre varios issues. Devuelve [hechos, saltados].
     */
    public function bulk(array $ids, string $action, string $actor, string $note = ''): array
    {
        $done = 0;
        $skipped = 0;
        foreach (array_unique(array_map('intval', $ids)) as $id) {
            if ($id > 0 && $this->act($id, $action, $actor, $note) === '') {
                $done++;
            } else {
                $skipped++;
            }
        }

        return [$done, $skipped];
    }

    private function applyFilters($builder, array $filters): void
    {
        $statuses = array_values(array_intersect((array) ($filters['status'] ?? []), self::STATUSES));
        if ($statuses) {
            $builder->whereIn('i.status', $statuses);
        }
        if (in_array($filters['type'] ?? '', self::TYPES, true)) {
            $builder->where('i.type', $filters['type']);
        }
        if (!empty($filters['group'])) {
            $builder->where('EXISTS (SELECT 1 FROM error_events e WHERE e.issue_id = i.id AND e.group_name = ' . $this->db->escape($filters['group']) . ')', null, false);
        }
        if (!empty($filters['q'])) {
            $builder->groupStart()->like('i.message', $filters['q'])->orLike('i.file', $filters['q'])->orLike('i.exception_class', $filters['q'])->groupEnd();
        }
        // Rango de fechas sobre la ultima vez que ocurrio
        if (!empty($filters['from']) && $this->isDate($filters['from'])) {
            $builder->where('i.last_seen >=', $filters['from'] . ' 00:00:00');
        }
        if (!empty($filters['to']) && $this->isDate($filters['to'])) {
            $builder->where('i.last_seen <=', $filters['to'] . ' 23:59:59');
        }
    }

    private function isDate(string $value): bool
    {
        $parts = explode('-', $value);

        return count($parts) === 3 && checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
    }
}
