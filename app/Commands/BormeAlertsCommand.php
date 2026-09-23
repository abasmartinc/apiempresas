<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\EmailAutomationModel;
use App\Models\TrackingEventModel;

/**
 * Alertas de cambio en el BORME.
 *
 * Lee lo que el importador diario deja en `borme_posts` y avisa a cada usuario de
 * los actos nuevos en las empresas que ha consultado. No toca ni depende del script
 * que hace la importación: su único contrato es la tabla.
 *
 * Uso:
 *   php spark alerts:borme --dry-run      (no envía nada ni mueve marcas de agua)
 *   php spark alerts:borme
 *   php spark alerts:borme --days=15 --user=42 --force
 *   php spark alerts:borme --backfill --dry-run   (alta inicial desde el histórico)
 */
class BormeAlertsCommand extends BaseCommand
{
    protected $group       = 'Alertas';
    protected $name        = 'alerts:borme';
    protected $description = 'Avisa a cada usuario de los actos nuevos del BORME en las empresas que ha consultado.';
    protected $usage       = 'alerts:borme [--dry-run] [--days 7] [--user ID] [--limit N] [--force] [--backfill]';

    /** Ventana de frescura: un acto con borme_date más antiguo no se considera noticia. */
    private const DIAS_POR_DEFECTO = 7;

    /** Empresas listadas en el correo antes de resumir el resto en "y N más". */
    private const MAX_EN_CORREO = 8;

    protected $db;
    protected $emailService;
    protected $automationModel;

    public function run(array $params)
    {
        $this->db              = \Config\Database::connect();
        $this->emailService    = new \App\Services\EmailService();
        $this->automationModel = new EmailAutomationModel();

        $dryRun   = $this->opcion('dry-run') !== null;
        $force    = $this->opcion('force') !== null;
        $days     = (int) ($this->opcion('days') ?: self::DIAS_POR_DEFECTO);
        $onlyUser = (int) ($this->opcion('user') ?: 0);
        $limit    = (int) ($this->opcion('limit') ?: 0);

        if ($onlyUser > 0) {
            CLI::write("Filtrado al usuario {$onlyUser}.", 'yellow');
        }
        if ($days > 400) {
            CLI::write("Ventana de {$days} días: eso es una prueba, no un uso normal.", 'yellow');
        }

        if (!$this->db->tableExists('user_company_watch')) {
            CLI::error('Falta la tabla user_company_watch. Ejecuta antes: php spark migrate');
            return;
        }

        $plantilla = $this->db->table('email_templates')->where('slug', 'borme_alert')->countAllResults();
        if ($plantilla === 0) {
            CLI::error("No existe la plantilla 'borme_alert' en email_templates.");
            CLI::write("Ejecuta: php spark db:seed_emails", 'yellow');
            return;
        }

        CLI::write('=== Alertas BORME ===', 'cyan');
        if ($dryRun) {
            CLI::write('MODO PRUEBA: no se envía ningún correo ni se mueve ninguna marca de agua.', 'yellow');
        }

        // La vigilancia ya NO se deriva automáticamente del historial: se crea cuando
        // el usuario desbloquea, busca o pulsa "Vigilar". Derivarla de user_events metía
        // en la lista toda empresa cuyo dictamen se abriera al hacer scroll, que es como
        // los suscriptores acababan siguiendo decenas de empresas que nunca pidieron.
        // --backfill queda para la pasada inicial que recupera el histórico.
        if ($this->opcion('backfill') !== null) {
            $nuevas = $this->sincronizarVigilancia($dryRun, $onlyUser);
            CLI::write("  - RELLENO INICIAL · vigilancias dadas de alta desde el histórico: {$nuevas}", 'yellow');
        }

        $porUsuario = $this->recogerCambios($days, $onlyUser);
        CLI::write('  - Usuarios con novedades: ' . count($porUsuario));

        if (empty($porUsuario)) {
            CLI::write('Nada que avisar.', 'green');
            return;
        }

        $enviados = 0;
        $saltados = 0;

        foreach ($porUsuario as $userId => $datos) {
            if ($limit > 0 && $enviados >= $limit) {
                CLI::write("  - Límite de {$limit} envíos alcanzado.", 'yellow');
                break;
            }

            // Una alerta al día como mucho: la marca de agua ya evita repetir contenido,
            // esto evita además encadenar correos si el comando se lanza varias veces.
            if (!$force && $this->automationModel->wasSentRecently((int) $userId, 'borme_alert', 1)) {
                $saltados++;
                continue;
            }

            if ($this->enviarAviso((int) $userId, $datos, $dryRun)) {
                $enviados++;
            }
        }

        CLI::write("Enviados: {$enviados} | Saltados por cadencia: {$saltados}", 'green');
    }

    /**
     * Lee una opción admitiendo tanto "--user 229" como "--user=229".
     *
     * El parser de CI4 solo entiende la primera forma: con la segunda crea una opción
     * llamada literalmente "user=229" con valor true, y el filtro se pierde en silencio
     * (que es justo lo que pasó la primera vez que se lanzó este comando).
     */
    private function opcion(string $nombre)
    {
        $valor = CLI::getOption($nombre);
        if ($valor !== null && $valor !== true) {
            return $valor;
        }

        foreach (array_keys(CLI::getOptions()) as $clave) {
            if (strpos((string) $clave, $nombre . '=') === 0) {
                return substr((string) $clave, strlen($nombre) + 1);
            }
        }

        return $valor;
    }

    /**
     * Collation real de companies.cif, para poder comparar contra ella sin que MySQL
     * proteste. Se lee del esquema en vez de codificarla: si mañana migras las tablas
     * a otra collation, esto sigue funcionando solo.
     */
    private function collacionCif(): string
    {
        static $collation = null;
        if ($collation !== null) {
            return $collation;
        }

        $nombre = '';
        try {
            $fila = $this->db->query("
                SELECT COLLATION_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'companies'
                  AND COLUMN_NAME = 'cif'
                LIMIT 1
            ")->getRowArray();
            $nombre = (string) ($fila['COLLATION_NAME'] ?? '');
        } catch (\Throwable $e) {
            log_message('error', 'collacionCif: ' . $e->getMessage());
        }

        // Este valor se concatena en SQL: solo se acepta un identificador limpio.
        $collation = preg_match('/^[A-Za-z0-9_]+$/', $nombre) ? $nombre : 'utf8mb4_unicode_ci';

        return $collation;
    }

    /**
     * Da de alta en vigilancia las empresas ya consultadas que aún no estaban.
     *
     * La marca de agua se inicializa en el ÚLTIMO acto existente de cada empresa: si
     * arrancara en 0, el primer envío sería una avalancha de actos antiguos que el
     * usuario ya vio cuando consultó la ficha.
     */
    private function sincronizarVigilancia(bool $dryRun, int $onlyUser = 0): int
    {
        // companies.cif, user_events.trigger_type y user_company_watch.cif no comparten
        // collation en esta base de datos, y MySQL se niega a comparar dos implícitas
        // distintas. Forzamos la del lado de companies: así la comparación es válida y
        // el índice de companies.cif se sigue usando (si forzáramos otra, full scan).
        $col = $this->collacionCif();

        $sql = "
            SELECT ue.user_id, UPPER(TRIM(ue.trigger_type)) AS cif, MAX(c.id) AS company_id,
                   MAX(ue.created_at) AS ultima_vista
            FROM user_events ue
            JOIN companies c ON c.cif = UPPER(TRIM(ue.trigger_type)) COLLATE {$col}
            LEFT JOIN user_company_watch w
                   ON w.user_id = ue.user_id AND w.cif = UPPER(TRIM(ue.trigger_type)) COLLATE {$col}
            JOIN users u ON u.id = ue.user_id
            WHERE ue.event_type = 'view_risk_profile'
              AND ue.trigger_type IS NOT NULL
              AND ue.trigger_type != ''
              AND w.id IS NULL
              " . ($onlyUser > 0
                    // Nombrar un usuario con --user es un acto deliberado del operador:
                    // ahí sí se incluye a los administradores, porque si no la única
                    // forma de probar el envío de verdad es tocar is_admin en la base
                    // de datos. En la pasada del cron (sin --user) siguen excluidos.
                    ? 'AND ue.user_id = ' . $onlyUser
                    : 'AND u.is_admin = 0') . "
            GROUP BY ue.user_id, cif
        ";

        $pendientes = $this->db->query($sql)->getResultArray();
        if (empty($pendientes)) {
            return 0;
        }

        // El relleno inserta a pelo, sin pasar por CompanyWatchService::watch(), así
        // que sin este recorte un gratuito que haya mirado treinta empresas saldría
        // de la primera pasada con treinta vigilancias y el panel diría "30 de 5".
        // Se recorta al tope dejándole las MÁS RECIENTES, que son las que todavía
        // le importan; los suscriptores no se tocan.
        $pendientes = $this->recortarAlCupo($pendientes);
        if (empty($pendientes)) {
            return 0;
        }

        // Último acto de cada empresa, de una sola consulta
        $companyIds = array_values(array_unique(array_map(static fn ($r) => (int) $r['company_id'], $pendientes)));
        $topes = [];
        foreach (array_chunk($companyIds, 500) as $trozo) {
            $filas = $this->db->table('borme_posts')
                ->select('company_id, MAX(id) AS max_id')
                ->whereIn('company_id', $trozo)
                ->groupBy('company_id')
                ->get()->getResultArray();
            foreach ($filas as $f) {
                $topes[(int) $f['company_id']] = (int) $f['max_id'];
            }
        }

        if ($dryRun) {
            return count($pendientes);
        }

        $ahora = date('Y-m-d H:i:s');
        $lote  = [];
        foreach ($pendientes as $p) {
            $companyId = (int) $p['company_id'];
            $lote[] = [
                'user_id'       => (int) $p['user_id'],
                'cif'           => $p['cif'],
                'company_id'    => $companyId,
                'source'        => 'auto',
                'last_borme_id' => $topes[$companyId] ?? 0,
                'active'        => 1,
                'created_at'    => $ahora,
                'updated_at'    => $ahora,
            ];
        }

        foreach (array_chunk($lote, 200) as $trozo) {
            // ignore: la clave única (user_id, cif) protege de carreras y re-ejecuciones
            $this->db->table('user_company_watch')->ignore(true)->insertBatch($trozo);
        }

        return count($lote);
    }

    /**
     * El tope de vigilancias TAMBIÉN al enviar, no solo al dar de alta.
     *
     * `watch()` rechaza altas por encima del tope, pero nada miraba el tope al
     * enviar: un suscriptor que llegó a 25 y se daba de baja conservaba las 25
     * recibiendo avisos gratis — justo lo que había dejado de pagar.
     *
     * No se borra nada: el usuario sigue viendo toda su lista en el panel (en rojo,
     * "25 de 5") y puede elegir cuáles quita. Solo se avisa de las N más recientes,
     * el mismo criterio que usa el relleno inicial (recortarAlCupo).
     */
    private function limitarAlTope(array $watches): array
    {
        $servicio = new \App\Services\CompanyWatchService();
        $porUsuario = [];
        foreach ($watches as $w) {
            $porUsuario[(int) $w['user_id']][] = $w;
        }

        $salida = [];
        foreach ($porUsuario as $uid => $lista) {
            $tope = (int) ($servicio->estadoCupo($uid)['tope'] ?? 0);

            if ($tope <= 0 || count($lista) <= $tope) {
                array_push($salida, ...$lista);
                continue;
            }

            usort($lista, static fn ($a, $b) => (int) $b['watch_id'] <=> (int) $a['watch_id']);
            array_push($salida, ...array_slice($lista, 0, $tope));
            CLI::write("  - Usuario {$uid}: vigila " . count($lista) . " y su plan permite {$tope}; se avisa de las {$tope} más recientes.", 'yellow');
        }

        return $salida;
    }

    /**
     * Deja fuera del relleno lo que no cabe en el cupo gratuito de cada usuario.
     *
     * @param array<int,array<string,mixed>> $pendientes
     * @return array<int,array<string,mixed>>
     */
    private function recortarAlCupo(array $pendientes): array
    {
        $servicio = new \App\Services\CompanyWatchService();
        $cupos    = [];   // user_id => huecos que le quedan (null = sin tope)
        $salida   = [];

        // Más recientes primero: si hay que cortar, que sobrevivan las de ahora.
        usort($pendientes, static function ($a, $b) {
            return strcmp((string) ($b['ultima_vista'] ?? ''), (string) ($a['ultima_vista'] ?? ''));
        });

        foreach ($pendientes as $p) {
            $uid = (int) $p['user_id'];

            if (!array_key_exists($uid, $cupos)) {
                $estado = $servicio->estadoCupo($uid);
                $cupos[$uid] = $estado['ilimitado'] ? null : (int) $estado['quedan'];
            }

            if ($cupos[$uid] === null) {
                $salida[] = $p;
                continue;
            }

            if ($cupos[$uid] <= 0) {
                continue;
            }

            $cupos[$uid]--;
            $salida[] = $p;
        }

        return $salida;
    }

    /**
     * Actos nuevos por usuario: id mayor que su marca de agua y borme_date reciente.
     *
     * El filtro por fecha es lo que distingue "publicado hoy" de "cargado hoy". Si el
     * importador rellena actos antiguos, sus ids son nuevos pero no son noticia.
     */
    private function recogerCambios(int $days, int $onlyUser = 0): array
    {
        $corte = date('Y-m-d', strtotime("-{$days} days"));

        // 1) PRIMERO las vigilancias. Antes se cargaban todos los actos de la ventana
        //    y se filtraba en PHP: con una ventana ancha eso se traga el BORME entero
        //    en memoria. Acotar por las empresas vigiladas deja la consulta minúscula.
        $builder = $this->db->table('user_company_watch w')
            ->select('w.id AS watch_id, w.user_id, w.cif, w.company_id, w.last_borme_id,
                      u.email, u.name, c.company_name')
            ->join('users u', 'u.id = w.user_id')
            ->join('companies c', 'c.id = w.company_id', 'left')
            ->where('w.active', 1)
            ->where('w.company_id IS NOT NULL')
            ->where('u.source_app', 'apiempresas')
            // Consentimiento: la preferencia explícita manda; si no se ha pronunciado
            // (NULL), se respeta la baja general de marketing.
            ->groupStart()
                ->where('u.alerts_borme', 1)
                ->orGroupStart()
                    ->where('u.alerts_borme IS NULL')
                    ->where('u.unsuscribe', 0)
                ->groupEnd()
            ->groupEnd();

        if ($onlyUser > 0) {
            $builder->where('w.user_id', $onlyUser);
        } else {
            // Los administradores quedan fuera del envío automático: sus consultas son
            // de soporte o de prueba, no interés comercial propio. Con --user sí entran,
            // que es la única forma de probar un envío real sin tocar la base de datos.
            $builder->where('u.is_admin', 0);
        }

        $watches = $this->limitarAlTope($builder->get()->getResultArray());
        if (empty($watches)) {
            // Sin esto el comando se calla y no hay forma de saber si no hay vigilancias,
            // si las hay pero el usuario está descartado, o si simplemente no hay actos.
            if ($onlyUser > 0) {
                $this->explicarSinVigilancias($onlyUser);
            } else {
                CLI::write('  - No hay vigilancias activas que cumplan los filtros.', 'yellow');
            }
            return [];
        }

        // Marca de agua más baja por empresa: es el suelo a partir del cual merece
        // la pena preguntar por actos de esa empresa.
        $suelo = [];
        foreach ($watches as $w) {
            $cid = (int) $w['company_id'];
            $wm  = (int) $w['last_borme_id'];
            if (!isset($suelo[$cid]) || $wm < $suelo[$cid]) {
                $suelo[$cid] = $wm;
            }
        }

        // 2) Actos nuevos SOLO de las empresas vigiladas, por lotes
        $porEmpresa = [];
        $totalActos = 0;
        foreach (array_chunk(array_keys($suelo), 300) as $trozo) {
            $minId = min(array_map(static fn ($cid) => $suelo[$cid], $trozo));

            $filas = $this->db->table('borme_posts')
                ->select('id, company_id, borme_date, act_types, description')
                ->whereIn('company_id', $trozo)
                ->where('id >', $minId)
                ->where('borme_date >=', $corte)
                ->orderBy('id', 'ASC')
                ->get()->getResultArray();

            foreach ($filas as $f) {
                $porEmpresa[(int) $f['company_id']][] = $f;
                $totalActos++;
            }
        }

        CLI::write("  - Empresas vigiladas: " . count($suelo) . " | actos candidatos: {$totalActos}");

        if (empty($porEmpresa)) {
            return [];
        }

        // 3) Cruce final: cada vigilante contra su propia marca de agua
        $porUsuario = [];
        foreach ($watches as $w) {
            $nuevos = array_filter(
                $porEmpresa[(int) $w['company_id']] ?? [],
                static fn ($a) => (int) $a['id'] > (int) $w['last_borme_id']
            );

            if (empty($nuevos)) {
                continue;
            }

            $userId = (int) $w['user_id'];
            if (!isset($porUsuario[$userId])) {
                $porUsuario[$userId] = [
                    'email'    => $w['email'],
                    // Sin nombre, la parte del email antes de la @: con 'Hola' de respaldo el
                    // correo empezaba "Hola, Hola:".
                    'name'     => $w['name'] ?: explode('@', (string) $w['email'])[0],
                    'empresas' => [],
                ];
            }

            $porUsuario[$userId]['empresas'][] = [
                'watch_id'   => (int) $w['watch_id'],
                'cif'        => $w['cif'],
                'company_id' => (int) $w['company_id'],
                'nombre'     => $w['company_name'] ?: $w['cif'],
                'actos'      => array_values($nuevos),
                'max_id'     => max(array_map(static fn ($a) => (int) $a['id'], $nuevos)),
            ];
        }

        return $porUsuario;
    }

    /**
     * Explica por qué un usuario concreto se ha quedado fuera. Solo se usa con --user,
     * como ayuda de depuración: en el cron diario no tiene sentido.
     */
    private function explicarSinVigilancias(int $userId): void
    {
        $filas = $this->db->table('user_company_watch w')
            ->select('w.cif, w.company_id, w.active, w.last_borme_id,
                      u.is_admin, u.unsuscribe, u.alerts_borme, u.source_app, u.email')
            ->join('users u', 'u.id = w.user_id', 'left')
            ->where('w.user_id', $userId)
            ->get()->getResultArray();

        if (empty($filas)) {
            CLI::write("  - El usuario {$userId} no tiene ninguna fila en user_company_watch.", 'yellow');
            CLI::write('    La vigilancia se crea al pulsar "Vigilar" en la ficha, o con --backfill');
            CLI::write('    a partir de sus eventos view_risk_profile (si no tiene ninguno, no hay nada que recuperar).');
            return;
        }

        CLI::write('  - El usuario tiene ' . count($filas) . ' vigilancia(s), pero ninguna pasa los filtros:', 'yellow');

        foreach ($filas as $f) {
            $motivos = [];
            if ((int) $f['active'] !== 1)              { $motivos[] = 'vigilancia inactiva (active=0)'; }
            if (empty($f['company_id']))              { $motivos[] = 'sin company_id resuelto'; }
            if ($f['alerts_borme'] !== null && (int) $f['alerts_borme'] === 0) {
                $motivos[] = 'ha desactivado las alertas (alerts_borme=0)';
            } elseif ($f['alerts_borme'] === null && (int) ($f['unsuscribe'] ?? 0) === 1) {
                $motivos[] = 'dado de baja de marketing y sin preferencia propia de alertas'
                    . ' (unsuscribe=1, alerts_borme=NULL)';
            }
            if (($f['source_app'] ?? '') !== 'apiempresas') {
                $motivos[] = "source_app = '" . ($f['source_app'] ?? 'NULL') . "' (se espera 'apiempresas')";
            }

            CLI::write('    · ' . $f['cif'] . ': ' . ($motivos ? implode(' | ', $motivos) : 'ninguno evidente, revisar a mano'));
        }
    }

    /**
     * Compone y envía el aviso de un usuario, y adelanta sus marcas de agua.
     */
    private function enviarAviso(int $userId, array $datos, bool $dryRun): bool
    {
        $empresas   = $datos['empresas'];
        $totalActos = array_sum(array_map(static fn ($e) => count($e['actos']), $empresas));
        $esPro      = $this->tieneSolvenciaPro($userId);

        if ($dryRun) {
            CLI::write("  [PRUEBA] {$datos['email']} · " . count($empresas) . ' empresa(s), '
                . "{$totalActos} acto(s)" . ($esPro ? ' · Pro' : ''), 'yellow');
            foreach ($empresas as $e) {
                CLI::write('      - ' . $e['nombre'] . ' (' . count($e['actos']) . ')');
            }
            return true;
        }

        helper('company');

        $resultado = $this->emailService->sendBormeAlert(
            ['user_id' => $userId, 'name' => $datos['name'], 'email' => $datos['email']],
            $empresas,
            $esPro
        );

        if (empty($resultado['success'])) {
            CLI::error("  Fallo al enviar a {$datos['email']}: " . ($resultado['error'] ?? 'motivo desconocido'));
            return false;
        }

        // Saltado por consentimiento: NO se toca la marca de agua. Si la avanzáramos,
        // ese acto quedaría marcado como avisado sin haberlo estado y se perdería.
        if (!empty($resultado['skipped'])) {
            CLI::write("  [SALTADO] {$datos['email']} · el usuario no acepta estas alertas", 'yellow');
            return false;
        }

        // Marcas de agua: a partir de aquí estos actos ya no son noticia para este usuario
        $ahora = date('Y-m-d H:i:s');
        foreach ($empresas as $e) {
            $this->db->table('user_company_watch')
                ->where('id', $e['watch_id'])
                ->update(['last_borme_id' => $e['max_id'], 'last_notified_at' => $ahora, 'updated_at' => $ahora]);
        }

        $this->automationModel->markAsSent($userId, 'borme_alert', $resultado['body'] ?? null);
        $this->registrarEvento($userId, count($empresas), $totalActos, $esPro);

        CLI::write("  [ENVIADO] {$datos['email']} · " . count($empresas) . " empresa(s)", 'green');
        return true;
    }

    private function tieneSolvenciaPro(int $userId): bool
    {
        return $this->db->table('user_subscriptions us')
            ->join('api_plans ap', 'ap.id = us.plan_id')
            ->where('us.user_id', $userId)
            ->where('us.status', 'active')
            ->groupStart()
                ->where('ap.slug', 'risk_pro')
                ->orWhere('ap.product_type', 'risk')
                ->orWhere('ap.product_type', 'bundle')
            ->groupEnd()
            ->countAllResults() > 0;
    }

    private function registrarEvento(int $userId, int $empresas, int $actos, bool $esPro): void
    {
        try {
            (new TrackingEventModel())->insert([
                'event_name'   => 'borme_alert_sent',
                'page'         => 'cli/alerts-borme',
                'user_id'      => $userId,
                'session_id'   => '',
                'anonymous_id' => '',
                'element'      => $esPro ? 'pro' : 'free',
                'metadata'     => json_encode(['empresas' => $empresas, 'actos' => $actos]),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'borme_alert_sent: ' . $e->getMessage());
        }
    }
}
