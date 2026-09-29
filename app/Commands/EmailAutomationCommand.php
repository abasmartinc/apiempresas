<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;
use App\Models\EmailAutomationModel;
use App\Models\ApiUsageDailyModel;
use App\Models\CompanyModel;
use App\Services\EmailService;

class EmailAutomationCommand extends BaseCommand
{
    protected $group       = 'Automation';
    protected $name        = 'email:automation';
    protected $description = 'Procesa y envía emails automáticos basados en comportamiento (API y Perfil de Riesgo).';

    protected $userModel;
    protected $automationModel;
    protected $usageModel;
    protected $emailService;
    protected $companyModel;

    public function run(array $params)
    {
        $this->userModel       = new UserModel();
        $this->automationModel = new EmailAutomationModel();
        $this->usageModel      = new ApiUsageDailyModel();
        $this->emailService    = new EmailService();
        $this->companyModel    = new CompanyModel();

        CLI::write('🚀 Iniciando proceso de automatización de emails...', 'cyan');

        $db = \Config\Database::connect();

        // =========================================================================
        // BLOQUE 1: USUARIOS DE LA API (Solo usuarios con signup_intent = 'api')
        // =========================================================================
        CLI::write('📡 [1/6] Procesando automatizaciones de API...', 'cyan');
        
        $apiUsers = $db->table('users')
            ->select('users.*, user_subscriptions.plan_id')
            ->join('user_subscriptions', 'user_subscriptions.user_id = users.id')
            ->where('user_subscriptions.status', 'active')
            ->where('user_subscriptions.plan_id', 1) // 1 = FREE
            ->where('users.is_admin', 0)
            ->where('users.unsuscribe', 0)
            ->where('users.source_app', 'apiempresas')
            ->where('users.signup_intent', 'api')
            ->get()->getResultArray();

        CLI::write("  - Usuarios API Free detectados: " . count($apiUsers));

        foreach ($apiUsers as $user) {
            $this->bloque('1/usuario ' . ($user['id'] ?? '?'), fn () => $this->processApiTriggersForUser($user));
        }

        // =========================================================================
        // BLOQUE 2: FLUJO DE RIESGO, PAYWALL Y UPSELL PACKS
        // =========================================================================
        CLI::write('🛡️ [2/6] Procesando automatizaciones de Riesgo y Solvencia...', 'cyan');
        // Primero el ciclo de vida (pagos sin terminar, clientes de Pro, renovaciones):
        // con un correo de Solvencia al día como mucho, lo más importante va antes.
        $this->bloque('2/solvencia', fn () => $this->processSolvenciaCiclo());
        $this->bloque('2/riesgo', fn () => $this->processRiskPaywallTriggers());
        $this->bloque('2/packs', fn () => $this->processRiskPackUpsellTriggers());

        // =========================================================================
        // BLOQUE 3: USUARIOS CON ALTA TASA DE ERRORES 400 EN API
        // =========================================================================
        CLI::write('🔍 [3/6] Detectando usuarios con errores 400 en peticiones...', 'cyan');
        $this->bloque('3', fn () => $this->processBadRequestUsers());

        // =========================================================================
        // BLOQUE 4: CLIENTES DE PAGO DE LA API CERCA DEL CUPO DEL MES
        // =========================================================================
        CLI::write('💳 [4/6] Revisando el cupo mensual de los clientes de pago de la API...', 'cyan');
        $this->bloque('4', fn () => $this->processPaidApiQuota());
        // Resumen del mes (días 1-3) y aviso a quien paga y no usa la API
        $this->bloque('4b', fn () => $this->processPaidApiLifecycle());

        // =========================================================================
        // BLOQUE 5: RECUPERACIÓN DE QUIEN DEJÓ PRO O BUSINESS HACE UN MES
        // =========================================================================
        CLI::write('↩️  [5/6] Recuperación de bajas de planes de pago de la API...', 'cyan');
        $this->bloque('5', fn () => $this->processApiWinback());

        // =========================================================================
        // BLOQUE 6: PAGOS DE PRO/BUSINESS EMPEZADOS Y NO TERMINADOS
        // =========================================================================
        CLI::write('🛒 [6/6] Pagos de planes de la API sin terminar...', 'cyan');
        $this->bloque('6', fn () => $this->processApiCheckoutAbandoned());

        // Hitos del embudo (primera consulta, primera desde fuera del navegador, la
        // décima). No envía nada: solo deja los eventos para medir la activación.
        CLI::write('📊 Eventos del embudo (primeras consultas)...', 'cyan');
        CLI::write('  - Eventos nuevos: ' . \App\Libraries\Embudo::registrarLlamadas(48));

        CLI::write('✅ Proceso de automatización finalizado con éxito.', 'green');
    }

    /**
     * Ejecuta un bloque aislado: si falla, lo deja en el log y en pantalla y sigue con
     * los demás. Antes un error en cualquier bloque dejaba sin ejecutar todos los siguientes.
     */
    protected function bloque(string $nombre, callable $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            log_message('error', '[EmailAutomation] Bloque ' . $nombre . ': ' . $e->getMessage());
            CLI::write('  ⚠️ Error en el bloque ' . $nombre . ': ' . $e->getMessage(), 'red');
        }
    }

    /**
     * Procesa los disparadores para usuarios de la API
     */
    protected function processApiTriggersForUser(array $user)
    {
        $userId = (int)$user['id'];

        // El usuario del monitor de estado no es un cliente
        if ($userId === \App\Filters\ApiKeyFilter::MONITOR_USER_ID) {
            return;
        }

        // Tope global: como mucho UN correo de la API Free al día por usuario. Antes
        // podían caer dos en la misma hora (first_request y one_request_inactive_1h,
        // que dicen casi lo mismo). El que no sale hoy sale en otra pasada.
        if ($this->recibioHoyApi($userId)) {
            return;
        }

        $totalRequests = $this->getTotalRequests($userId);
        $lastRequestTime = $this->getLastRequestTime($userId);
        $createdAt = $user['created_at'];

        // 0. AGOTÓ EL FREE: aviso, seguimiento a los 3 y 10 días, y recordatorio espaciado
        if ($totalRequests >= 100) {
            $this->procesarFreeAgotado($user);
            return;
        }

        // 1. TRIGGER: limit_warning (Avisar a las 80 consultas)
        if ($totalRequests >= 80) {
            $this->checkAndSend($user, 'reached_80_requests', 'email_sent_limit_warning', [], true);
            return;
        }

        // 1b. TRIGGER: first_request — primera consulta con éxito (antes se enviaba
        //     dentro de la propia petición a la API). Solo si aún no llega a 5: con
        //     más, el correo que toca es el de reached_5_requests.
        //     Y solo si la última llamada es reciente: a quien probó hace meses no se le
        //     felicita ahora por su "primera consulta".
        $reciente = $lastRequestTime && (time() - strtotime($lastRequestTime)) < 2 * 86400;
        if ($totalRequests >= 1 && $totalRequests < 5 && $reciente && !$this->automationModel->wasSent($userId, 'first_request')) {
            $this->checkAndSend($user, 'first_request', 'email_sent_first_request');
            return;
        }

        // 2. TRIGGER: reached_5_requests (una vez). Antes hacía `return` aunque ya se
        //    hubiera enviado, y por eso nadie con 5 o más llegaba nunca al resumen
        //    mensual ni a nada posterior. Ahora solo corta si de verdad envía.
        if ($totalRequests >= 5 && $this->checkAndSend($user, 'reached_5_requests', 'email_sent_engaged')) {
            return;
        }

        // 3. TRIGGER: one_request_inactive_1h (una vez)
        if ($totalRequests === 1 && $lastRequestTime) {
            $diffSeconds = time() - strtotime($lastRequestTime);
            if ($diffSeconds >= 3600 && $this->checkAndSend($user, 'one_request_inactive_1h', 'email_sent_first_usage')) {
                return;
            }
        }

        // 3b. USÓ LA API Y SE PARÓ: 7 y 30 días desde la última llamada.
        //
        // Era el hueco más grande: quien hacía unas cuantas consultas y dejaba de llamar
        // no volvía a saber de nosotros (solo existía el aviso de 1 h para quien hizo
        // exactamente una). Ventanas cerradas (7-14 y 30-37 días) para no escribir de
        // golpe a toda la base antigua al desplegar, y un envío por parón: se cuenta
        // desde la última llamada, y como mucho uno de cada tipo cada 60 días.
        if ($totalRequests >= 1 && $lastRequestTime && !$this->tuvoPlanDePago($userId)) {
            $diasParado = (time() - strtotime($lastRequestTime)) / 86400;

            foreach ([['api_stalled_30d', 30, 37], ['api_stalled_7d', 7, 14]] as [$tipo, $desde, $hasta]) {
                if ($diasParado < $desde || $diasParado >= $hasta) {
                    continue;
                }
                if ($this->enviadoDesde($userId, $tipo, $lastRequestTime)
                    || $this->automationModel->wasSentRecently($userId, $tipo, 60)) {
                    return;
                }
                $this->checkAndSend($user, $tipo, 'email_sent_' . $tipo, [
                    'dias'      => (int) floor($diasParado),
                    'total'     => $totalRequests,
                    'empresas'  => $this->empresasConsultadas($userId),
                ], true);
                return;
            }
        }

        // 4. SECUENCIA SIN NINGUNA LLAMADA: 15 min, día 1 y día 3.
        //
        // Antes solo existía el aviso de los 15 minutos: quien no llamaba entonces no
        // volvía a saber de nosotros. Cada paso tiene ventana de edad (y no solo un
        // mínimo) para que al desplegar no le lleguen a toda la base antigua de golpe.
        if ($totalRequests === 0) {
            $edad = time() - strtotime($createdAt);

            if ($edad >= 3 * 86400 && $edad < 7 * 86400) {          // día 3 a 7
                $this->checkAndSend($user, 'no_requests_day3', 'email_sent_no_usage_day3');
                return;
            }
            if ($edad >= 86400 && $edad < 3 * 86400) {              // día 1 a 3
                $this->checkAndSend($user, 'no_requests_day1', 'email_sent_no_usage_day1');
                return;
            }
            if ($edad >= 900) {                                     // 15 minutos
                $this->checkAndSend($user, 'no_requests_15min', 'email_sent_no_usage');
                return;
            }
        }

        // 5. TRIGGER: monthly_report (cada 30 días, a quien ha usado la API en ese tiempo).
        //    Solo a cuentas de 4 semanas o más: antes, el primer mes ya lo cubren los
        //    correos de alta y de hitos.
        $edadCuenta = time() - strtotime((string) $createdAt);
        if ($totalRequests >= 1 && $edadCuenta >= 28 * 86400
            && !$this->automationModel->wasSentRecently($userId, 'monthly_report', 30)) {
            $usage30Days = $this->getUsageLast30Days($userId);
            if ($usage30Days > 0) {
                $this->checkAndSend($user, 'monthly_report', 'email_sent_monthly_report', [
                    'usage'    => $usage30Days,
                    'total'    => $totalRequests,
                    'empresas' => $this->empresasConsultadas($userId, 30),
                ], true);
            }
        }
    }

    /**
     * Free con las 100 consultas gastadas.
     *
     * Antes: el mismo "has agotado tus consultas" cada 30 días, para siempre, y nada en
     * medio. Ahora:
     *   - el aviso al agotarlas (reached_100_percent_quota), como siempre;
     *   - a los 3 días (api_exhausted_3d): cuántos días ha seguido llamando su
     *     integración (los 429 quedan en api_requests), las dudas de siempre antes de
     *     pagar y el bono como paso pequeño;
     *   - a los 10 días (api_exhausted_10d): corto, pregunta qué le frena;
     *   - el recordatorio de cada 30 días, como mucho 3 veces en total.
     * Quien tiene saldo en el monedero no está parado (consulta con saldo): nada.
     */
    protected function procesarFreeAgotado(array $user): void
    {
        $userId = (int) $user['id'];

        if ($this->saldoMonedero($userId) > 0 || $this->tuvoPlanDePago($userId)) {
            return;
        }

        [$desde, $veces] = $this->avisosAgotado($userId);

        if ($desde === null) {
            $this->checkAndSend($user, 'reached_100_percent_quota', 'email_sent_quota_max', [], true);
            return;
        }

        $dias  = (time() - strtotime($desde)) / 86400;

        foreach ([['api_exhausted_10d', 10, 17], ['api_exhausted_3d', 3, 7]] as [$tipo, $min, $max]) {
            if ($dias < $min || $dias >= $max) {
                continue;
            }
            // Uno por agotamiento y, como mucho, uno de cada tipo cada 180 días
            if ($this->enviadoDesde($userId, $tipo, $desde) || $this->automationModel->wasSentRecently($userId, $tipo, 180)) {
                return;
            }
            $this->checkAndSend($user, $tipo, 'email_sent_' . $tipo, [
                'dias_429' => $this->diasConRechazos($userId, $desde),
                'empresas' => $this->empresasConsultadas($userId),
            ], true);
            return;
        }

        if ($veces < 3) {
            $this->checkAndSend($user, 'reached_100_percent_quota', 'email_sent_quota_max', [], true);
        }
    }

    /**
     * Último aviso de "has agotado" y cuántos se han enviado en total.
     *
     * @return array{0: ?string, 1: int} [fecha del último o null, total]
     */
    protected function avisosAgotado(int $userId): array
    {
        $fila = \Config\Database::connect()->query(
            "SELECT MAX(sent_at) AS ultimo, COUNT(*) AS n FROM user_email_automation
             WHERE user_id = ? AND email_type = 'reached_100_percent_quota'",
            [$userId]
        )->getRowArray();
        return [$fila['ultimo'] ?? null, (int) ($fila['n'] ?? 0)];
    }

    /** Créditos en el monedero (bono) del usuario. */
    protected function saldoMonedero(int $userId): int
    {
        try {
            $fila = \Config\Database::connect()->table('user_wallets')
                ->select('balance')->where('user_id', $userId)->get()->getRowArray();
            return (int) ($fila['balance'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Días distintos, desde una fecha, en que la API le devolvió 429 (cupo agotado).
     * ApiKeyFilter::registrarRechazo guarda como mucho un rechazo por minuto, así que se
     * cuentan días y no peticiones.
     */
    protected function diasConRechazos(int $userId, string $desde): int
    {
        try {
            $fila = \Config\Database::connect()->query(
                'SELECT COUNT(DISTINCT DATE(created_at)) AS n FROM api_requests
                 WHERE user_id = ? AND status_code = 429 AND created_at >= ?',
                [$userId, $desde]
            )->getRowArray();
            return (int) ($fila['n'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * ¿Ha recibido ya en las últimas 20 h algún correo automático de la API Free?
     * (los de servicio de clientes de pago, como paid_quota_*, no cuentan)
     */
    protected function recibioHoyApi(int $userId): bool
    {
        return \Config\Database::connect()->table('user_email_automation')
            ->where('user_id', $userId)
            ->whereIn('email_type', [
                'first_request', 'no_requests_15min', 'no_requests_day1', 'no_requests_day3',
                'one_request_inactive_1h', 'reached_5_requests', 'reached_80_requests',
                'reached_100_percent_quota', 'monthly_report', 'bad_request_help',
                'api_stalled_7d', 'api_stalled_30d', 'api_checkout_abandoned',
                'api_exhausted_3d', 'api_exhausted_10d',
            ])
            ->where('sent_at >=', date('Y-m-d H:i:s', strtotime('-20 hours')))
            ->countAllResults() > 0;
    }

    /** ¿Se envió ese correo después de una fecha? (p. ej. después de la última llamada) */
    protected function enviadoDesde(int $userId, string $tipo, string $desde): bool
    {
        return \Config\Database::connect()->table('user_email_automation')
            ->where('user_id', $userId)
            ->where('email_type', $tipo)
            ->where('sent_at >=', $desde)
            ->countAllResults() > 0;
    }

    /** @var array<int, true>|null usuarios que han tenido Pro o Business alguna vez */
    protected ?array $exPago = null;

    /**
     * Quien ha pagado Pro o Business ya tiene su propio correo de recuperación
     * (api_winback): el de "te has parado" con el cupo gratuito no le corresponde.
     */
    protected function tuvoPlanDePago(int $userId): bool
    {
        if ($this->exPago === null) {
            $this->exPago = array_flip(array_map('intval', array_column(
                \Config\Database::connect()->table('user_subscriptions')
                    ->select('user_id')->distinct()
                    ->whereIn('plan_id', [2, 3])
                    ->get()->getResultArray(),
                'user_id'
            )));
        }
        return isset($this->exPago[$userId]);
    }

    /**
     * Las últimas empresas (distintas) que el usuario consultó con éxito, para que el
     * correo hable de SU uso: [['cif' => ..., 'nombre' => ...], ...]
     */
    protected function empresasConsultadas(int $userId, ?int $dias = null, int $max = 3): array
    {
        $db = \Config\Database::connect();
        try {
            $q = $db->table('api_requests')
                ->select('search_term, MAX(created_at) AS ultima', false)
                ->where('user_id', $userId)
                ->where('status_code', 200)
                ->where('search_term IS NOT NULL', null, false)
                ->where('search_term <>', '')
                ->where('created_at >=', \App\Filters\ApiKeyFilter::FREE_DESDE . ' 00:00:00');
            if ($dias !== null) {
                $q->where('created_at >=', date('Y-m-d H:i:s', strtotime("-{$dias} days")));
            }
            $filas = $q->groupBy('search_term')->orderBy('ultima', 'DESC')->limit($max)
                ->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', '[EmailAutomation::empresasConsultadas] ' . $e->getMessage());
            return [];
        }

        $terminos = array_values(array_filter(array_map(
            static fn ($f) => strtoupper(trim((string) $f['search_term'])),
            $filas
        )));
        if (empty($terminos)) {
            return [];
        }

        $nombres = [];
        try {
            $nombres = array_column($db->table('companies')->select('cif, company_name')
                ->whereIn('cif', $terminos)->get()->getResultArray(), 'company_name', 'cif');
        } catch (\Throwable $e) {
            // Sin nombre, se muestra el CIF
        }

        helper('company');
        $salida = [];
        foreach ($terminos as $t) {
            // Solo identificadores con forma de CIF/NIF: el search_term también puede
            // ser una búsqueda por nombre (q, name), que no sirve para ?probar=
            if (!preg_match('/^[A-Z0-9]{9}$/', $t)) {
                continue;
            }
            $nombre = isset($nombres[$t]) && $nombres[$t] !== ''
                ? (function_exists('company_display_name') ? company_display_name((string) $nombres[$t], $t) : (string) $nombres[$t])
                : '';
            $salida[] = ['cif' => $t, 'nombre' => $nombre];
        }
        return $salida;
    }

    /**
     * Procesa los disparadores del flujo de riesgo y paywall abandonado
     */
    protected function processRiskPaywallTriggers()
    {
        $db = \Config\Database::connect();
        $startOfMonth = date('Y-m-01 00:00:00');

        // Seleccionar usuarios que tengan intención de riesgo O hayan consultado perfiles de riesgo este mes
        // Y que NO tengan suscripción activa de tipo 'risk'
        $riskCandidates = $db->query("
            SELECT DISTINCT u.id, u.email, u.name, u.created_at, u.signup_intent
            FROM users u
            WHERE u.is_admin = 0
              AND u.unsuscribe = 0
              AND u.source_app = 'apiempresas'
              AND (
                  u.signup_intent = 'view_risk_profile'
                  OR u.id IN (
                      SELECT user_id FROM user_events 
                      WHERE event_type = 'view_risk_profile' 
                        AND created_at >= ?
                  )
              )
              -- Solo se excluye a quien YA tiene Solvencia: antes se excluía a cualquiera
              -- con un plan de pago, así que un cliente de Radar o de la API —que ya
              -- confía en ti y usa perfiles de riesgo— nunca recibía esta secuencia,
              -- siendo el candidato más barato que tienes para venderle Solvencia.
              AND u.id NOT IN (
                  SELECT us.user_id
                  FROM user_subscriptions us
                  JOIN api_plans ap ON ap.id = us.plan_id
                  WHERE us.status = 'active'
                    AND (ap.slug = 'risk_pro' OR ap.product_type = 'risk' OR ap.product_type = 'bundle')
              )
              -- Quien tiene créditos de un pack comprado no está limitado por las 3
              -- gratuitas: le llegaban \"límite 3/3 alcanzado\" y \"te quedan X gratis\"
              -- justo después de pagar. Su secuencia es la del pack.
              AND COALESCE(u.risk_credits, 0) = 0
              -- Quien compró un pack y lo ha gastado tampoco es un gratuito: recibía
              -- «has usado tus 3 consultas gratuitas» a la vez que la oferta del pack.
              AND u.id NOT IN (
                  SELECT user_id FROM user_events WHERE event_type = 'purchase_risk_pack'
              )
        ", [$startOfMonth])->getResultArray();

        CLI::write("  - Candidatos de Riesgo / Freemium detectados: " . count($riskCandidates));

        $isEndOfMonth = ((int)date('j') >= 28);

        foreach ($riskCandidates as $user) {
            $userId = (int)$user['id'];

            // Como mucho un correo de Solvencia al día por usuario (también estos)
            if ($this->recibioHoy($userId)) {
                continue;
            }

            // Obtener eventos de consulta de riesgo de este mes
            $events = $db->table('user_events')
                ->where('user_id', $userId)
                ->where('event_type', 'view_risk_profile')
                ->where('created_at >=', $startOfMonth)
                ->orderBy('created_at', 'DESC')
                ->get()->getResultArray();

            $distinctCifs = array_unique(array_filter(array_map('trim', array_column($events, 'trigger_type'))));
            $distinctCount = count($distinctCifs);
            $lastEvent = !empty($events) ? $events[0] : null;

            // =========================================================================
            // 1) TRIGGER: risk_paywall_abandoned_2h (Límite alcanzado >= 3 empresas y pasadas 2h)
            // =========================================================================
            if ($distinctCount >= 3 && $lastEvent) {
                $secondsSinceLastView = time() - strtotime($lastEvent['created_at']);
                if ($secondsSinceLastView >= 7200) { // >= 2 horas
                    if (!$this->automationModel->wasSentRecently($userId, 'risk_paywall_abandoned_2h', 30)) {
                        // Buscar datos de la última empresa consultada
                        $lastCif = trim((string)$lastEvent['trigger_type']);
                        $compData = [];
                        if ($lastCif) {
                            $compRow = $this->companyModel->where('cif', $lastCif)->first();
                            if ($compRow) {
                                $compData = [
                                    'id'   => $compRow['id'],
                                    'name' => $compRow['company_name'] ?? $compRow['name'] ?? $lastCif,
                                    'cif'  => $lastCif
                                ];
                            }
                        }

                        CLI::write("  -> Enviando 'risk_paywall_abandoned_2h' a {$user['email']}...");
                        $result = $this->emailService->sendRiskPaywallAbandoned($user, $compData);
                        if ($result['success']) {
                            $this->automationModel->markAsSent($userId, 'risk_paywall_abandoned_2h', $result['body']);
                            $this->recordTracking($userId, 'email_sent_risk_paywall_abandoned');
                            CLI::write("     [SENT] risk_paywall_abandoned_2h OK", 'yellow');
                        }
                        continue;
                    }
                }
            }

            // =========================================================================
            // 2) TRIGGER: risk_educational_savings_48h (A las 48h del límite)
            // =========================================================================
            if ($distinctCount >= 3 && $lastEvent) {
                $secondsSinceLastView = time() - strtotime($lastEvent['created_at']);
                if ($secondsSinceLastView >= 172800) { // >= 48 horas (2 días)
                    if (!$this->automationModel->wasSentRecently($userId, 'risk_educational_savings_48h', 30)) {
                        CLI::write("  -> Enviando 'risk_educational_savings_48h' a {$user['email']}...");
                        $result = $this->emailService->sendRiskEducationalSavings($user);
                        if ($result['success']) {
                            $this->automationModel->markAsSent($userId, 'risk_educational_savings_48h', $result['body']);
                            $this->recordTracking($userId, 'email_sent_risk_savings_48h');
                            CLI::write("     [SENT] risk_educational_savings_48h OK", 'yellow');
                        }
                        continue;
                    }
                }
            }

            // 3) risk_monthly_renewal: ya no sale aquí (días 28-31, "se renuevan") sino en
            //    processSolvenciaCiclo(), los días 1-3, cuando las consultas ya están.

            // =========================================================================
            // 4) TRIGGER: risk_first_query_nudge_24h (12h-72h tras la 1ª consulta para recuperar el 72.7% de abandonos)
            // =========================================================================
            if ($distinctCount === 1 && $lastEvent) {
                $secondsSinceLastView = time() - strtotime($lastEvent['created_at']);
                if ($secondsSinceLastView >= 43200 && $secondsSinceLastView <= 259200) { // Entre 12h y 72h
                    if (!$this->automationModel->wasSentRecently($userId, 'risk_first_query_nudge_24h', 30)) {
                        $lastCif = trim((string)$lastEvent['trigger_type']);
                        $compData = [];
                        if ($lastCif) {
                            $compRow = $this->companyModel->where('cif', $lastCif)->first();
                            if ($compRow) {
                                $compData = [
                                    'id'   => $compRow['id'],
                                    'name' => $compRow['company_name'] ?? $compRow['name'] ?? $lastCif,
                                    'cif'  => $lastCif
                                ];
                            }
                        }

                        CLI::write("  -> Enviando 'risk_first_query_nudge_24h' a {$user['email']}...");
                        $result = $this->emailService->sendRiskFirstQueryNudge($user, $compData);
                        if ($result['success']) {
                            $this->automationModel->markAsSent($userId, 'risk_first_query_nudge_24h', $result['body']);
                            $this->recordTracking($userId, 'email_sent_risk_first_query_nudge');
                            CLI::write("     [SENT] risk_first_query_nudge_24h OK", 'yellow');
                        }
                        continue;
                    }
                }
            }

            // =========================================================================
            // 5) TRIGGER: risk_unused_credits_48h (Recordatorio tras 24-72h si le quedan créditos gratis)
            // Solo para usuarios con intención específica de riesgo
            // =========================================================================
            $userAgeSeconds = time() - strtotime($user['created_at']);
            if (($user['signup_intent'] ?? '') === 'view_risk_profile' && $userAgeSeconds >= 86400 && $userAgeSeconds <= 604800 && $distinctCount < 3) {
                if (!$this->automationModel->wasSentRecently($userId, 'risk_unused_credits_48h', 30) &&
                    !$this->automationModel->wasSentRecently($userId, 'risk_first_query_nudge_24h', 2)) {
                    $remainingCredits = max(0, 3 - $distinctCount);
                    CLI::write("  -> Enviando 'risk_unused_credits_48h' a {$user['email']}...");
                    $result = $this->emailService->sendRiskUnusedCreditsReminder($user, $remainingCredits);
                    if ($result['success']) {
                        $this->automationModel->markAsSent($userId, 'risk_unused_credits_48h', $result['body']);
                        $this->recordTracking($userId, 'email_sent_risk_unused_credits');
                        CLI::write("     [SENT] risk_unused_credits_48h OK", 'yellow');
                    }
                }
            }
        }
    }

    /**
     * Procesa el upsell a Solvencia Pro para compradores de pack con créditos bajos (<= 1) o agotados
     */
    protected function processRiskPackUpsellTriggers()
    {
        $db = \Config\Database::connect();

        // Usuarios que han comprado un pack de solvencia, tienen <= 1 crédito, y NO tienen Solvencia Pro activo
        $packBuyers = $db->query("
            SELECT DISTINCT u.id, u.email, u.name, u.created_at, u.risk_credits
            FROM users u
            WHERE u.is_admin = 0
              AND u.unsuscribe = 0
              AND u.source_app = 'apiempresas'
              AND u.risk_credits <= 1
              AND u.id IN (
                  SELECT user_id FROM user_events 
                  WHERE event_type = 'purchase_risk_pack'
              )
              -- Misma definición de \"ya tiene Solvencia\" que el resto de la secuencia:
              -- antes solo miraba el slug risk_pro y a un cliente con bundle se le
              -- ofrecía pasar a Pro teniéndolo ya.
              AND u.id NOT IN (
                  SELECT us.user_id
                  FROM user_subscriptions us
                  JOIN api_plans ap ON ap.id = us.plan_id
                  WHERE (us.status = 'active' OR (us.status = 'canceled' AND us.current_period_end > NOW()))
                    AND (ap.slug = 'risk_pro' OR ap.product_type IN ('risk', 'bundle'))
              )
        ")->getResultArray();

        CLI::write("  - Compradores de pack con créditos bajos (<=1) detectados: " . count($packBuyers));

        foreach ($packBuyers as $user) {
            $userId = (int)$user['id'];
            $remainingCredits = (int)($user['risk_credits'] ?? 0);

            /*
             * UNA vez por pack comprado. Antes se repetía cada 30 días para siempre a
             * cualquiera que alguna vez compró un pack: a los seis meses seguía
             * recibiendo "te queda 1 crédito". Solo se envía si no se ha enviado ya
             * después de su última compra.
             */
            $ultimaCompra = $db->table('user_events')
                ->selectMax('created_at', 'ultima')
                ->where('user_id', $userId)
                ->where('event_type', 'purchase_risk_pack')
                ->get()->getRowArray()['ultima'] ?? null;
            $yaTrasCompra = $ultimaCompra && $db->table('user_email_automation')
                ->where('user_id', $userId)
                ->where('email_type', 'risk_credits_low_upsell')
                ->where('sent_at >=', $ultimaCompra)
                ->countAllResults() > 0;
            if ($yaTrasCompra || $this->recibioHoy($userId)) {
                continue;
            }

            if (!$this->automationModel->wasSentRecently($userId, 'risk_credits_low_upsell', 30)) {
                CLI::write("  -> Enviando 'risk_credits_low_upsell' a {$user['email']} (Créditos: {$remainingCredits})...");
                $result = $this->emailService->sendRiskCreditsLowUpsell($user, $remainingCredits);
                if ($result['success']) {
                    $this->automationModel->markAsSent($userId, 'risk_credits_low_upsell', $result['body']);
                    $this->recordTracking($userId, 'email_sent_risk_credits_low_upsell');
                    CLI::write("     [SENT] risk_credits_low_upsell OK", 'yellow');
                }
            }
        }
    }

    /**
     * @return bool true si el correo se ha enviado en esta llamada
     */
    protected function checkAndSend(array $user, string $triggerType, string $trackingEvent, array $extraParams = [], bool $isRecurring = false): bool
    {
        $userId = (int)$user['id'];

        $alreadySent = $isRecurring
            ? $this->automationModel->wasSentRecently($userId, $triggerType, 30)
            : $this->automationModel->wasSent($userId, $triggerType);

        if ($alreadySent) {
            return false;
        }

        CLI::write("  -> Intentando enviar '{$triggerType}' a {$user['email']}...");

        $result = ['success' => false, 'body' => ''];
        switch ($triggerType) {
            case 'first_request':
                $result = $this->emailService->sendFirstRequestMilestone($user);
                break;
            case 'no_requests_15min':
                $result = $this->emailService->sendNoUsage15Min($user);
                break;
            case 'no_requests_day1':
                $result = $this->emailService->sendQuickStartPrompt($user);
                break;
            case 'no_requests_day3':
                $result = $this->emailService->sendInactivityReminder($user);
                break;
            case 'one_request_inactive_1h':
                $result = $this->emailService->sendOneUsageInactive1H($user);
                break;
            case 'reached_5_requests':
                $result = $this->emailService->sendReached5Requests($user);
                break;
            case 'reached_80_requests':
                $result = $this->emailService->sendReached80Requests($user);
                break;
            case 'reached_100_percent_quota':
                $result = $this->emailService->sendQuotaExceeded($user);
                break;
            case 'monthly_report':
                $usage = $extraParams['usage'] ?? 0;
                $result = $this->emailService->sendMonthlyUsageReport(
                    $user,
                    (int) $usage,
                    (int) ($extraParams['total'] ?? 0),
                    $extraParams['empresas'] ?? []
                );
                break;
            case 'api_exhausted_3d':
            case 'api_exhausted_10d':
                $result = $this->emailService->sendFreeExhaustedFollowUp(
                    $user,
                    (int) ($extraParams['dias_429'] ?? 0),
                    $extraParams['empresas'] ?? [],
                    $triggerType === 'api_exhausted_10d'
                );
                break;
            case 'api_stalled_7d':
            case 'api_stalled_30d':
                $result = $this->emailService->sendApiStalled(
                    $user,
                    (int) ($extraParams['dias'] ?? 0),
                    (int) ($extraParams['total'] ?? 0),
                    $extraParams['empresas'] ?? [],
                    $triggerType === 'api_stalled_30d'
                );
                break;
        }

        // Como antes: un envío saltado (baja) también se marca, para no reintentarlo
        if (!empty($result['success'])) {
            $this->automationModel->markAsSent($userId, $triggerType, $result['body'] ?? '');
            $this->recordTracking($userId, $trackingEvent);
            CLI::write("     [SENT] {$triggerType} OK", 'yellow');
            return true;
        }
        return false;
    }

    /**
     * Consultas del cupo Free, contadas IGUAL que ApiKeyFilter: de por vida, pero
     * desde el 28-05-2026. Antes sumaba todo el histórico, y alguien con uso anterior
     * a esa fecha recibía "has agotado tus consultas" teniendo aún cupo.
     */
    protected function getTotalRequests(int $userId): int
    {
        $res = $this->usageModel->selectSum('requests_count')
            ->where('user_id', $userId)
            ->where('date >=', \App\Filters\ApiKeyFilter::FREE_DESDE)
            ->get()->getRowArray();
        return (int)($res['requests_count'] ?? 0);
    }

    protected function getLastRequestTime(int $userId): ?string
    {
        $res = $this->usageModel->select('updated_at')
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'DESC')
            ->first();
        return $res['updated_at'] ?? null;
    }

    protected function getUsageLast30Days(int $userId): int
    {
        $date = date('Y-m-d', strtotime('-30 days'));
        $res = $this->usageModel->selectSum('requests_count')
            ->where('user_id', $userId)
            ->where('date >=', $date)
            ->get()->getRowArray();
        return (int)($res['requests_count'] ?? 0);
    }

    // =========================================================================
    // CICLO DE VIDA DE SOLVENCIA (23-09-2026)
    //
    // Cuatro momentos que no tenían correo. Van por orden de prioridad y cada
    // usuario recibe como mucho UN correo de Solvencia al día (recibioHoy): el que
    // más importa gana y el resto espera a otra pasada o al mes siguiente.
    //
    //   1. risk_checkout_abandoned  empezó a pagar Pro y no terminó (1 h a 48 h)
    //   2. risk_watch_full          el gratuito llenó su lista de vigilancia
    //   3. risk_cartera_resumen     días 1-3: qué pasó el mes pasado en su vigilancia
    //   4. risk_monthly_renewal     días 1-3: ya tiene sus consultas gratuitas
    // =========================================================================
    protected function processSolvenciaCiclo(): void
    {
        helper('company');
        $this->cicloPagoSinTerminar();
        $this->cicloActivacionPro();
        $this->cicloSeguimientoPro();
        $this->cicloRenovacionAnual();
        $this->cicloListaLlena();
        $this->cicloDormidos();
        $this->cicloWinbackSolvencia();

        if ((int) date('j') <= 3) {
            $this->cicloResumenCartera();
            $this->cicloConsultasRenovadas();
        }
    }

    /** ¿Ha recibido ya hoy algún correo de Solvencia? */
    protected function recibioHoy(int $userId): bool
    {
        return \Config\Database::connect()->table('user_email_automation')
            ->where('user_id', $userId)
            ->like('email_type', 'risk_', 'after')
            ->where('sent_at >=', date('Y-m-d H:i:s', strtotime('-20 hours')))
            ->countAllResults() > 0;
    }

    /** Usuarios con Solvencia Pro (o bundle) activo, de una consulta. */
    protected function idsSuscriptores(): array
    {
        $filas = \Config\Database::connect()->query("
            SELECT DISTINCT us.user_id
            FROM user_subscriptions us
            JOIN api_plans ap ON ap.id = us.plan_id
            WHERE (ap.slug = 'risk_pro' OR ap.product_type IN ('risk', 'bundle'))
              AND (us.status = 'active' OR (us.status = 'canceled' AND us.current_period_end > NOW()))
        ")->getResultArray();

        return array_flip(array_map('intval', array_column($filas, 'user_id')));
    }

    /** Filas de usuario elegibles para correos comerciales, por id. */
    protected function usuariosElegibles(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return [];
        }

        $salida = [];
        foreach (array_chunk($ids, 500) as $trozo) {
            $filas = \Config\Database::connect()->table('users')
                ->select('id, email, name, created_at, signup_intent')
                ->whereIn('id', $trozo)
                ->where('is_admin', 0)
                ->where('unsuscribe', 0)
                ->where('source_app', 'apiempresas')
                ->get()->getResultArray();
            foreach ($filas as $f) {
                $salida[(int) $f['id']] = $f;
            }
        }

        return $salida;
    }

    protected function registrarEnvio(int $userId, string $tipo, array $resultado): void
    {
        if (!empty($resultado['success']) && empty($resultado['skipped'])) {
            $this->automationModel->markAsSent($userId, $tipo, $resultado['body'] ?? '');
            $this->recordTracking($userId, 'email_sent_' . $tipo);
            CLI::write("     [SENT] {$tipo} OK", 'yellow');
        }
    }

    /** 1. Empezó a pagar Solvencia Pro entre hace 48 h y hace 1 h, y no terminó. */
    protected function cicloPagoSinTerminar(): void
    {
        $db = \Config\Database::connect();

        $empezados = $db->table('tracking_events')
            ->select('user_id, metadata, created_at')
            ->where('event_name', 'checkout_started')
            ->where('user_id >', 0)
            ->like('metadata', '"plan":"risk_pro"')
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-48 hours')))
            ->where('created_at <=', date('Y-m-d H:i:s', strtotime('-1 hour')))
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        // El intento más reciente de cada usuario
        $porUsuario = [];
        foreach ($empezados as $e) {
            $uid = (int) $e['user_id'];
            if (!isset($porUsuario[$uid])) {
                $porUsuario[$uid] = $e;
            }
        }
        if (empty($porUsuario)) {
            return;
        }

        $suscriptores = $this->idsSuscriptores();
        $usuarios     = $this->usuariosElegibles(array_keys($porUsuario));

        CLI::write('  - Pagos de Solvencia Pro sin terminar: ' . count($porUsuario));

        foreach ($porUsuario as $uid => $intento) {
            if (isset($suscriptores[$uid]) || !isset($usuarios[$uid])) {
                continue;   // ya es Pro, o no admite correos comerciales
            }

            // ¿Terminó algún pago después de ese intento?
            $completado = $db->table('tracking_events')
                ->where('event_name', 'checkout_completed')
                ->where('user_id', $uid)
                ->where('created_at >=', $intento['created_at'])
                ->countAllResults() > 0;
            if ($completado
                || $this->automationModel->wasSentRecently($uid, 'risk_checkout_abandoned', 30)
                || $this->recibioHoy($uid)) {
                continue;
            }

            $meta    = json_decode((string) $intento['metadata'], true) ?: [];
            $usuario = $usuarios[$uid] + ['user_id' => $uid];

            CLI::write("  -> Enviando 'risk_checkout_abandoned' a {$usuario['email']}...");
            $this->registrarEnvio($uid, 'risk_checkout_abandoned',
                $this->emailService->sendRiskCheckoutAbandoned($usuario, (string) ($meta['period'] ?? 'monthly')));
        }
    }

    /**
     * Clientes de Solvencia Pro, para los avisos de servicio: sin el filtro de la baja
     * del marketing (esos correos son del servicio que pagan).
     */
    protected function usuariosCliente(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (empty($ids)) {
            return [];
        }
        $salida = [];
        foreach (array_chunk($ids, 500) as $trozo) {
            foreach (\Config\Database::connect()->table('users')
                        ->select('id, email, name, created_at, signup_intent')
                        ->whereIn('id', $trozo)
                        ->where('is_admin', 0)
                        ->get()->getResultArray() as $f) {
                $salida[(int) $f['id']] = $f;
            }
        }
        return $salida;
    }

    /** Suscripciones de Solvencia Pro (plan risk_pro o de tipo risk). */
    protected function suscripcionesSolvencia(): \CodeIgniter\Database\BaseBuilder
    {
        return \Config\Database::connect()->table('user_subscriptions us')
            ->join('api_plans ap', 'ap.id = us.plan_id')
            ->groupStart()->where('ap.slug', 'risk_pro')->orWhere('ap.product_type', 'risk')->groupEnd();
    }

    protected function vigilanciasActivas(int $userId): int
    {
        return \Config\Database::connect()->table('user_company_watch')
            ->where('user_id', $userId)->where('active', 1)->countAllResults();
    }

    protected function avisosDesde(int $userId, string $desde): int
    {
        return \Config\Database::connect()->table('user_email_automation')
            ->where('user_id', $userId)->where('email_type', 'borme_alert')
            ->where('sent_at >=', $desde)->countAllResults();
    }

    /**
     * Pro desde hace 2-10 días sin ninguna empresa en vigilancia: correo con las que
     * ya consultó para vigilarlas en un clic. Una vez por cliente.
     */
    protected function cicloActivacionPro(): void
    {
        $db = \Config\Database::connect();
        $subs = $this->suscripcionesSolvencia()
            ->select('us.user_id, us.created_at')
            ->where('us.status', 'active')
            ->where('us.created_at <=', date('Y-m-d H:i:s', strtotime('-2 days')))
            ->where('us.created_at >=', date('Y-m-d H:i:s', strtotime('-10 days')))
            ->get()->getResultArray();
        if (empty($subs)) {
            return;
        }

        $usuarios = $this->usuariosCliente(array_column($subs, 'user_id'));
        foreach ($usuarios as $uid => $u) {
            if ($this->vigilanciasActivas($uid) > 0
                || $this->automationModel->wasSentRecently($uid, 'risk_pro_activacion', 365)
                || $this->recibioHoy($uid)) {
                continue;
            }

            // Empresas que ya consultó (las más recientes primero)
            $cifs = array_column($db->table('user_events')
                ->select('trigger_type, MAX(created_at) AS ultima', false)
                ->where('user_id', $uid)->where('event_type', 'view_risk_profile')
                ->where('trigger_type IS NOT NULL', null, false)->where('trigger_type <>', '')
                ->groupBy('trigger_type')->orderBy('ultima', 'DESC')->limit(5)
                ->get()->getResultArray(), 'trigger_type');

            $consultadas = [];
            if ($cifs) {
                $nombres = array_column($db->table('companies')->select('cif, company_name')
                    ->whereIn('cif', $cifs)->get()->getResultArray(), 'company_name', 'cif');
                foreach ($cifs as $cif) {
                    $cif = strtoupper(trim((string) $cif));
                    $consultadas[] = [
                        'cif'    => $cif,
                        'nombre' => company_display_name((string) ($nombres[$cif] ?? $cif), $cif),
                    ];
                }
            }

            CLI::write("  -> Enviando 'risk_pro_activacion' a {$u['email']}...");
            $this->registrarEnvio($uid, 'risk_pro_activacion',
                $this->emailService->sendRiskProActivacion($u + ['user_id' => $uid], $consultadas));
        }
    }

    /**
     * Hacia el día 20 de su PRIMERA suscripción a Solvencia Pro: lo que ha hecho el
     * servicio por él (vigiladas, avisos, consultas). Una vez por cliente.
     */
    protected function cicloSeguimientoPro(): void
    {
        $db = \Config\Database::connect();
        $subs = $this->suscripcionesSolvencia()
            ->select('us.user_id, us.created_at')
            ->where('us.status', 'active')
            ->where('us.created_at <=', date('Y-m-d H:i:s', strtotime('-18 days')))
            ->where('us.created_at >=', date('Y-m-d H:i:s', strtotime('-24 days')))
            ->get()->getResultArray();
        if (empty($subs)) {
            return;
        }

        $inicio = [];
        foreach ($subs as $sub) {
            $inicio[(int) $sub['user_id']] = (string) $sub['created_at'];
        }
        $usuarios = $this->usuariosCliente(array_keys($inicio));

        foreach ($usuarios as $uid => $u) {
            // Solo en la primera suscripción: quien vuelve no necesita el seguimiento
            $anteriores = $this->suscripcionesSolvencia()
                ->where('us.user_id', $uid)->where('us.created_at <', $inicio[$uid])->countAllResults();
            if ($anteriores > 0
                || $this->automationModel->wasSentRecently($uid, 'risk_pro_seguimiento', 365)
                || $this->recibioHoy($uid)) {
                continue;
            }

            $consultas = (int) ($db->table('user_events')
                ->select('COUNT(DISTINCT trigger_type) AS n', false)
                ->where('user_id', $uid)->where('event_type', 'view_risk_profile')
                ->where('created_at >=', $inicio[$uid])
                ->get()->getRowArray()['n'] ?? 0);
            $dias = max(1, (int) floor((time() - strtotime($inicio[$uid])) / 86400));

            CLI::write("  -> Enviando 'risk_pro_seguimiento' a {$u['email']}...");
            $this->registrarEnvio($uid, 'risk_pro_seguimiento',
                $this->emailService->sendRiskProSeguimiento(
                    $u + ['user_id' => $uid],
                    $dias,
                    $this->vigilanciasActivas($uid),
                    $this->avisosDesde($uid, $inicio[$uid]),
                    $consultas
                ));
        }
    }

    /**
     * Plan anual de Solvencia Pro que se renueva: aviso a 30 y a 7 días. Solo planes
     * activos (los cancelados no se renuevan) con periodo de más de 300 días.
     */
    protected function cicloRenovacionAnual(): void
    {
        $ventanas = [
            'risk_renovacion_30' => [28, 30],
            'risk_renovacion_7'  => [5, 7],
        ];
        foreach ($ventanas as $tipo => [$min, $max]) {
            $subs = $this->suscripcionesSolvencia()
                ->select('us.user_id, us.current_period_start, us.current_period_end')
                ->where('us.status', 'active')
                ->where('us.current_period_end >=', date('Y-m-d H:i:s', strtotime('+' . $min . ' days')))
                ->where('us.current_period_end <=', date('Y-m-d H:i:s', strtotime('+' . $max . ' days')))
                ->where('DATEDIFF(us.current_period_end, us.current_period_start) > 300', null, false)
                ->get()->getResultArray();
            if (empty($subs)) {
                continue;
            }

            $porUsuario = [];
            foreach ($subs as $sub) {
                $porUsuario[(int) $sub['user_id']] = $sub;
            }
            $usuarios = $this->usuariosCliente(array_keys($porUsuario));

            foreach ($usuarios as $uid => $u) {
                if ($this->automationModel->wasSentRecently($uid, $tipo, 60) || $this->recibioHoy($uid)) {
                    continue;
                }
                $sub  = $porUsuario[$uid];
                $fin  = strtotime((string) $sub['current_period_end']);
                $dias = max(1, (int) ceil(($fin - time()) / 86400));

                CLI::write("  -> Enviando '{$tipo}' a {$u['email']}...");
                $this->registrarEnvio($uid, $tipo,
                    $this->emailService->sendRiskRenovacionAnual(
                        $u + ['user_id' => $uid],
                        date('d/m/Y', $fin),
                        $dias,
                        $this->vigilanciasActivas($uid),
                        $this->avisosDesde($uid, (string) $sub['current_period_start']),
                        $tipo
                    ));
            }
        }
    }

    /**
     * 30-37 días después de que TERMINE un Solvencia Pro cancelado, si no ha vuelto.
     * Una vez al año como mucho. Comercial: usuariosElegibles() descarta las bajas.
     */
    protected function cicloWinbackSolvencia(): void
    {
        $db = \Config\Database::connect();
        $conMotivo = in_array('cancellation_reason', $db->getFieldNames('user_subscriptions'), true);

        $filas = $this->suscripcionesSolvencia()
            ->select('us.user_id, us.current_period_end' . ($conMotivo ? ', us.cancellation_reason' : ''))
            ->where('us.status', 'canceled')
            ->where('us.current_period_end >=', date('Y-m-d H:i:s', strtotime('-37 days')))
            ->where('us.current_period_end <=', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->orderBy('us.current_period_end', 'DESC')
            ->get()->getResultArray();
        if (empty($filas)) {
            return;
        }

        $suscriptores = $this->idsSuscriptores();
        $usuarios     = $this->usuariosElegibles(array_column($filas, 'user_id'));
        $vistos       = [];

        foreach ($filas as $f) {
            $uid = (int) $f['user_id'];
            if (isset($vistos[$uid]) || isset($suscriptores[$uid]) || !isset($usuarios[$uid])) {
                continue;
            }
            $vistos[$uid] = true;
            if ($this->automationModel->wasSentRecently($uid, 'risk_winback', 365) || $this->recibioHoy($uid)) {
                continue;
            }

            CLI::write("  -> Enviando 'risk_winback' a {$usuarios[$uid]['email']}...");
            $this->registrarEnvio($uid, 'risk_winback',
                $this->emailService->sendRiskWinback($usuarios[$uid] + ['user_id' => $uid], (string) ($f['cancellation_reason'] ?? '')));
        }
    }

    /**
     * Registrados de Solvencia que no vuelven: a los 14 días (vigilar gratis la que
     * consultó) y a los 30 (subir su lista de clientes). Solo a quien no ha consultado
     * nada en los últimos 10 días, no vigila ninguna empresa y no es cliente.
     */
    protected function cicloDormidos(): void
    {
        $db = \Config\Database::connect();
        $ventanas = [
            'risk_dormido_14' => [14, 17],
            'risk_dormido_30' => [30, 33],
        ];
        $suscriptores = $this->idsSuscriptores();

        foreach ($ventanas as $tipo => [$min, $max]) {
            $ids = array_map('intval', array_column($db->table('users')
                ->select('id')
                ->where('signup_intent', 'view_risk_profile')
                ->where('created_at <=', date('Y-m-d H:i:s', strtotime('-' . $min . ' days')))
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-' . $max . ' days')))
                ->get()->getResultArray(), 'id'));
            if (empty($ids)) {
                continue;
            }

            foreach ($this->usuariosElegibles($ids) as $uid => $u) {
                if (isset($suscriptores[$uid])
                    || $this->vigilanciasActivas($uid) > 0
                    || $this->automationModel->wasSentRecently($uid, $tipo, 60)
                    || $this->recibioHoy($uid)) {
                    continue;
                }
                $reciente = $db->table('user_events')
                    ->where('user_id', $uid)->where('event_type', 'view_risk_profile')
                    ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-10 days')))
                    ->countAllResults() > 0;
                if ($reciente) {
                    continue;   // ha vuelto: no está dormido
                }

                if ($tipo === 'risk_dormido_14') {
                    $empresa = null;
                    $ultima  = $db->table('user_events')->select('trigger_type')
                        ->where('user_id', $uid)->where('event_type', 'view_risk_profile')
                        ->where('trigger_type IS NOT NULL', null, false)->where('trigger_type <>', '')
                        ->orderBy('created_at', 'DESC')->limit(1)->get()->getRowArray();
                    if ($ultima) {
                        $cif  = strtoupper(trim((string) $ultima['trigger_type']));
                        $fila = $db->table('companies')->select('company_name')->where('cif', $cif)->get()->getRowArray();
                        $empresa = [
                            'cif'    => $cif,
                            'nombre' => company_display_name((string) ($fila['company_name'] ?? $cif), $cif),
                        ];
                    }
                    CLI::write("  -> Enviando 'risk_dormido_14' a {$u['email']}...");
                    $res = $this->emailService->sendRiskDormido14($u + ['user_id' => $uid], $empresa);
                } else {
                    CLI::write("  -> Enviando 'risk_dormido_30' a {$u['email']}...");
                    $res = $this->emailService->sendRiskDormido30($u + ['user_id' => $uid]);
                }
                $this->registrarEnvio($uid, $tipo, $res);
            }
        }
    }

    /** 2. Gratuito con la lista de vigilancia llena. Como mucho una vez cada 30 días. */
    protected function cicloListaLlena(): void
    {
        $tope = (int) solvencia('vigilanciasGratis', 5);

        $filas = \Config\Database::connect()->query("
            SELECT w.user_id, COUNT(*) AS n
            FROM user_company_watch w
            WHERE w.active = 1
            GROUP BY w.user_id
            HAVING n >= ?
        ", [$tope])->getResultArray();

        if (empty($filas)) {
            return;
        }

        $suscriptores = $this->idsSuscriptores();
        $usuarios     = $this->usuariosElegibles(array_column($filas, 'user_id'));

        foreach ($usuarios as $uid => $u) {
            if (isset($suscriptores[$uid])
                || $this->automationModel->wasSentRecently($uid, 'risk_watch_full', 30)
                || $this->recibioHoy($uid)) {
                continue;
            }

            $nombres = array_map(
                static fn ($r) => company_display_name((string) ($r['company_name'] ?: $r['cif']), (string) $r['cif']),
                \Config\Database::connect()->table('user_company_watch w')
                    ->select('w.cif, c.company_name')
                    ->join('companies c', 'c.id = w.company_id', 'left')
                    ->where('w.user_id', $uid)->where('w.active', 1)
                    ->orderBy('w.id', 'ASC')->limit(5)
                    ->get()->getResultArray()
            );

            CLI::write("  -> Enviando 'risk_watch_full' a {$u['email']}...");
            $this->registrarEnvio($uid, 'risk_watch_full',
                $this->emailService->sendRiskWatchFull($u + ['user_id' => $uid], $nombres));
        }
    }

    /** 3. Días 1-3: resumen del mes anterior a quien vigila alguna empresa. */
    protected function cicloResumenCartera(): void
    {
        $db = \Config\Database::connect();

        $desde = date('Y-m-01', strtotime('first day of last month'));
        $hasta = date('Y-m-t', strtotime('last day of last month'));
        $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                  'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $mes   = $meses[(int) date('n', strtotime($desde))];

        $vigilancias = $db->table('user_company_watch w')
            ->select('w.user_id, w.company_id, w.cif, c.company_name')
            ->join('companies c', 'c.id = w.company_id', 'left')
            ->where('w.active', 1)
            ->where('w.company_id IS NOT NULL')
            ->get()->getResultArray();

        if (empty($vigilancias)) {
            return;
        }

        // Actos del mes pasado por empresa, de todas las vigiladas a la vez y por lotes
        $companyIds = array_values(array_unique(array_map(static fn ($v) => (int) $v['company_id'], $vigilancias)));
        $actosPorEmpresa = [];
        foreach (array_chunk($companyIds, 500) as $trozo) {
            $filas = $db->table('borme_posts')
                ->select('company_id, COUNT(*) AS n')
                ->whereIn('company_id', $trozo)
                ->where('borme_date >=', $desde)
                ->where('borme_date <=', $hasta)
                ->groupBy('company_id')
                ->get()->getResultArray();
            foreach ($filas as $f) {
                $actosPorEmpresa[(int) $f['company_id']] = (int) $f['n'];
            }
        }

        $porUsuario = [];
        foreach ($vigilancias as $v) {
            $porUsuario[(int) $v['user_id']][] = $v;
        }

        $suscriptores = $this->idsSuscriptores();
        $usuarios     = $this->usuariosElegibles(array_keys($porUsuario));

        CLI::write('  - Resumen mensual de vigilancia (' . $mes . '): ' . count($usuarios) . ' candidatos');

        foreach ($usuarios as $uid => $u) {
            if ($this->automationModel->wasSentRecently($uid, 'risk_cartera_resumen', 20) || $this->recibioHoy($uid)) {
                continue;
            }

            $conActos = [];
            foreach ($porUsuario[$uid] as $v) {
                $n = $actosPorEmpresa[(int) $v['company_id']] ?? 0;
                if ($n > 0) {
                    $conActos[] = [
                        'nombre' => company_display_name((string) ($v['company_name'] ?: $v['cif']), (string) $v['cif']),
                        'actos'  => $n,
                    ];
                }
            }
            usort($conActos, static fn ($a, $b) => $b['actos'] <=> $a['actos']);

            CLI::write("  -> Enviando 'risk_cartera_resumen' a {$u['email']}...");
            $this->registrarEnvio($uid, 'risk_cartera_resumen',
                $this->emailService->sendRiskCarteraResumen(
                    $u + ['user_id' => $uid],
                    $mes,
                    count($porUsuario[$uid]),
                    $conActos,
                    isset($suscriptores[$uid])
                ));
        }
    }

    /** 4. Días 1-3: gratuitos que consultaron algo el mes pasado ya tienen sus consultas. */
    protected function cicloConsultasRenovadas(): void
    {
        $db = \Config\Database::connect();
        $desde = date('Y-m-01 00:00:00', strtotime('first day of last month'));
        $hasta = date('Y-m-01 00:00:00');

        $filas = $db->table('user_events')
            ->select('user_id, COUNT(DISTINCT trigger_type) AS n', false)
            ->where('event_type', 'view_risk_profile')
            ->where('created_at >=', $desde)
            ->where('created_at <', $hasta)
            ->groupBy('user_id')
            ->get()->getResultArray();

        if (empty($filas)) {
            return;
        }

        $usadas = [];
        foreach ($filas as $f) {
            $usadas[(int) $f['user_id']] = (int) $f['n'];
        }

        $suscriptores = $this->idsSuscriptores();
        $usuarios     = $this->usuariosElegibles(array_keys($usadas));
        $gratis       = (int) solvencia('consultasGratis', 3);

        foreach ($usuarios as $uid => $u) {
            if (isset($suscriptores[$uid])
                || $this->automationModel->wasSentRecently($uid, 'risk_monthly_renewal', 20)
                || $this->recibioHoy($uid)) {
                continue;
            }

            CLI::write("  -> Enviando 'risk_monthly_renewal' a {$u['email']}...");
            $this->registrarEnvio($uid, 'risk_monthly_renewal',
                $this->emailService->sendRiskMonthlyRenewal($u + ['user_id' => $uid], $usadas[$uid] >= $gratis));
        }
    }

    /**
     * Aviso al 80 % y al 100 % del cupo del mes a los clientes de PAGO de la API.
     *
     * Antes no había nada: al agotar sus consultas recibían un 429 sin aviso. Se
     * cuenta igual que ApiKeyFilter (uso del mes natural con ese plan) y se envía como
     * mucho una vez por umbral y mes. Es aviso de servicio, así que no mira la baja
     * del marketing.
     */
    protected function processPaidApiQuota(): void
    {
        $db       = \Config\Database::connect();
        $mes      = date('Y-m');
        $desdeMes = date('Y-m-01 00:00:00');

        // Misma suscripción que elige ApiKeyFilter: la activa más reciente de la API
        $filas = $db->query("
            SELECT u.id, u.email, u.name,
                   us.id AS sub_id, us.plan_id,
                   ap.name AS plan_name, ap.monthly_quota, ap.product_type,
                   COALESCE(uw.balance, 0) AS wallet
            FROM user_subscriptions us
            JOIN users u      ON u.id = us.user_id
            JOIN api_plans ap ON ap.id = us.plan_id
            LEFT JOIN user_wallets uw ON uw.user_id = u.id
            WHERE us.plan_id IN (2, 3)          -- Pro y Business: los dos planes de pago de la API
              AND ap.monthly_quota > 0
              -- Igual que ApiKeyFilter: la cancelada conserva el plan hasta fin de periodo
              AND (
                    (us.status = 'active' AND (us.current_period_end IS NULL OR us.current_period_end > NOW()))
                 OR (us.status = 'canceled' AND us.current_period_end > NOW())
              )
              AND u.is_admin = 0
              -- El usuario del monitor de estado no es un cliente: no se le avisa
              AND u.id <> ?
            ORDER BY us.id DESC
        ", [\App\Filters\ApiKeyFilter::MONITOR_USER_ID])->getResultArray();

        $clientes = [];
        foreach ($filas as $f) {
            $clientes[(int) $f['id']] ??= $f;   // la primera es la de id más alto
        }
        if (empty($clientes)) {
            CLI::write('  - Sin clientes de pago de la API.', 'dark_gray');
            return;
        }

        // Uso del mes por usuario y plan, de una consulta
        $uso = [];
        foreach ($db->table('api_usage_daily')
                    ->select('user_id, plan_id, SUM(requests_count) AS n')
                    ->whereIn('user_id', array_keys($clientes))
                    ->like('date', $mes, 'after')
                    ->groupBy(['user_id', 'plan_id'])
                    ->get()->getResultArray() as $u) {
            $uso[(int) $u['user_id'] . ':' . (int) $u['plan_id']] = (int) $u['n'];
        }

        $siguientes = [];
        $enviados   = 0;

        foreach ($clientes as $uid => $c) {
            $cupo   = (int) $c['monthly_quota'];
            $usadas = $uso[$uid . ':' . (int) $c['plan_id']] ?? 0;
            $pct    = $cupo > 0 ? ($usadas / $cupo) * 100 : 0;

            if ($pct < 80) {
                continue;
            }
            $umbral = $pct >= 100 ? 100 : 80;
            $tipo   = 'paid_quota_' . $umbral;

            $yaEsteMes = $db->table('user_email_automation')
                ->where('user_id', $uid)
                ->where('email_type', $tipo)
                ->where('sent_at >=', $desdeMes)
                ->countAllResults() > 0;
            if ($yaEsteMes) {
                continue;
            }

            // Siguiente plan: de Pro (2) se sube a Business (3). Business es el tope:
            // a ese cliente se le ofrece el bono y un plan a medida.
            $clave = (int) $c['plan_id'];
            if (!array_key_exists($clave, $siguientes)) {
                $siguientes[$clave] = null;
                if ($clave === 2) {
                    try {
                        $siguientes[$clave] = $db->table('api_plans')
                            ->select('id, name, monthly_quota')
                            ->where('id', 3)
                            ->get()->getRowArray() ?: null;
                    } catch (\Throwable $e) {
                        log_message('error', '[EmailAutomation::paidQuota] plan Business: ' . $e->getMessage());
                    }
                }
            }

            CLI::write("  -> Enviando '{$tipo}' a {$c['email']} ({$usadas}/{$cupo})...");
            $res = $this->emailService->sendPaidQuotaWarning(
                ['id' => $uid, 'email' => $c['email'], 'name' => $c['name']],
                ['name' => $c['plan_name'], 'monthly_quota' => $cupo],
                $usadas,
                $umbral,
                (int) $c['wallet'],
                $siguientes[$clave]
            );
            if (!empty($res['success'])) {
                $this->automationModel->markAsSent($uid, $tipo, $res['body'] ?? '');
                $this->recordTracking($uid, 'email_sent_' . $tipo);
                $enviados++;
                CLI::write("     [SENT] {$tipo} OK", 'yellow');
            } else {
                CLI::write('     [FAIL] ' . $tipo . ': ' . ($res['error'] ?? $res['message'] ?? 'sin detalle'), 'red');
            }
        }

        CLI::write('  - Avisos de cupo enviados: ' . $enviados);
    }

    /**
     * Clientes de pago de la API (Pro y Business):
     *
     *  - paid_monthly_summary (días 1-3): el resumen del mes anterior. Hasta ahora el
     *    cliente de pago solo recibía la factura; el resumen es la prueba de que la
     *    suscripción se usa y el sitio natural para enseñarle errores que no ha visto.
     *  - paid_low_usage: lleva 14 días pagando sin hacer ni una consulta. Es la baja que
     *    viene, y la forma de evitarla es ayudarle a ponerlo en marcha. Una vez cada 60 días.
     *
     * Como mucho uno de los dos por usuario y pasada.
     */
    protected function processPaidApiLifecycle(): void
    {
        $db = \Config\Database::connect();

        $filas = $db->query("
            SELECT u.id, u.email, u.name, us.plan_id, us.created_at AS sub_desde,
                   ap.name AS plan_name, ap.monthly_quota
            FROM user_subscriptions us
            JOIN users u      ON u.id = us.user_id
            JOIN api_plans ap ON ap.id = us.plan_id
            WHERE us.plan_id IN (2, 3)
              AND us.status = 'active'
              AND (us.current_period_end IS NULL OR us.current_period_end > NOW())
              AND u.is_admin = 0
              AND u.id <> ?
            ORDER BY us.id DESC
        ", [\App\Filters\ApiKeyFilter::MONITOR_USER_ID])->getResultArray();

        $clientes = [];
        foreach ($filas as $f) {
            $clientes[(int) $f['id']] ??= $f;
        }
        if (empty($clientes)) {
            CLI::write('  - Sin clientes de pago activos de la API.', 'dark_gray');
            return;
        }

        $inicioMes   = date('Y-m-01 00:00:00');
        $mesPasado   = date('Y-m', strtotime('first day of last month'));
        $desdePasado = $mesPasado . '-01 00:00:00';
        $esPrincipio = (int) date('j') <= 3;
        $enviados    = 0;

        foreach ($clientes as $uid => $c) {
            $usuario = ['id' => $uid, 'user_id' => $uid, 'email' => $c['email'], 'name' => $c['name']];

            // 1. Resumen del mes anterior
            if ($esPrincipio
                && strtotime((string) $c['sub_desde']) < strtotime($inicioMes)
                && !$this->enviadoDesde($uid, 'paid_monthly_summary', $inicioMes)) {

                $usadas = (int) ($db->table('api_usage_daily')->selectSum('requests_count')
                    ->where('user_id', $uid)->like('date', $mesPasado, 'after')
                    ->get()->getRowArray()['requests_count'] ?? 0);

                if ($usadas > 0) {
                    $stats = $this->estadisticasMes($uid, $desdePasado, $inicioMes, $mesPasado);
                    CLI::write("  -> Enviando 'paid_monthly_summary' a {$c['email']} ({$usadas} consultas)...");
                    $res = $this->emailService->sendPaidMonthlySummary(
                        $usuario,
                        ['name' => $c['plan_name'], 'monthly_quota' => (int) $c['monthly_quota'], 'id' => (int) $c['plan_id']],
                        $mesPasado,
                        $usadas,
                        $stats
                    );
                    $this->registrarEnvio($uid, 'paid_monthly_summary', $res);
                    if (!empty($res['success'])) {
                        $enviados++;
                        continue;
                    }
                }
            }

            // 2. Paga y no usa: 14 días con el plan y ninguna consulta en esos 14 días
            if (time() - strtotime((string) $c['sub_desde']) < 14 * 86400
                || $this->automationModel->wasSentRecently($uid, 'paid_low_usage', 60)) {
                continue;
            }
            $recientes = (int) ($db->table('api_usage_daily')->selectSum('requests_count')
                ->where('user_id', $uid)->where('date >=', date('Y-m-d', strtotime('-14 days')))
                ->get()->getRowArray()['requests_count'] ?? 0);
            if ($recientes > 0) {
                continue;
            }

            $ultima = $this->getLastRequestTime($uid);
            CLI::write("  -> Enviando 'paid_low_usage' a {$c['email']}...");
            $res = $this->emailService->sendPaidLowUsage(
                $usuario,
                ['name' => $c['plan_name'], 'monthly_quota' => (int) $c['monthly_quota']],
                $ultima ? (int) floor((time() - strtotime($ultima)) / 86400) : null
            );
            $this->registrarEnvio($uid, 'paid_low_usage', $res);
            if (!empty($res['success'])) {
                $enviados++;
            }
        }

        CLI::write('  - Resúmenes y avisos de poco uso enviados: ' . $enviados);
    }

    /**
     * Cifras de un mes para el resumen del cliente de pago: día de más uso, errores 400,
     * días con rechazos 429 y endpoints más usados.
     */
    protected function estadisticasMes(int $userId, string $desde, string $hasta, string $mes): array
    {
        $db    = \Config\Database::connect();
        $stats = ['pico_dia' => null, 'pico_n' => 0, 'errores_400' => 0, 'dias_429' => 0, 'endpoints' => []];

        try {
            $pico = $db->table('api_usage_daily')
                ->select('date, SUM(requests_count) AS n')
                ->where('user_id', $userId)->like('date', $mes, 'after')
                ->groupBy('date')->orderBy('n', 'DESC')->limit(1)
                ->get()->getRowArray();
            if ($pico) {
                $stats['pico_dia'] = (string) $pico['date'];
                $stats['pico_n']   = (int) $pico['n'];
            }

            $fila = $db->query(
                'SELECT SUM(status_code = 400) AS e400,
                        COUNT(DISTINCT CASE WHEN status_code = 429 THEN DATE(created_at) END) AS d429
                 FROM api_requests
                 WHERE user_id = ? AND created_at >= ? AND created_at < ?',
                [$userId, $desde, $hasta]
            )->getRowArray();
            $stats['errores_400'] = (int) ($fila['e400'] ?? 0);
            $stats['dias_429']    = (int) ($fila['d429'] ?? 0);

            $stats['endpoints'] = $db->query(
                'SELECT endpoint, COUNT(*) AS n FROM api_requests
                 WHERE user_id = ? AND status_code = 200 AND created_at >= ? AND created_at < ?
                 GROUP BY endpoint ORDER BY n DESC LIMIT 3',
                [$userId, $desde, $hasta]
            )->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', '[EmailAutomation::estadisticasMes] ' . $e->getMessage());
        }

        return $stats;
    }

    /**
     * 30-37 días después de que TERMINE un Pro/Business cancelado, si no ha vuelto.
     * Una vez al año como mucho. Es comercial: usuariosElegibles() descarta las bajas.
     */
    protected function processApiWinback(): void
    {
        $db = \Config\Database::connect();

        $conMotivo = in_array('cancellation_reason', $db->getFieldNames('user_subscriptions'), true);

        $filas = $db->table('user_subscriptions us')
            ->select('us.user_id, us.plan_id, us.current_period_end, ap.name AS plan_name'
                . ($conMotivo ? ', us.cancellation_reason' : ''))
            ->join('api_plans ap', 'ap.id = us.plan_id', 'left')
            ->where('us.status', 'canceled')
            ->whereIn('us.plan_id', [2, 3])
            ->where('us.current_period_end >=', date('Y-m-d H:i:s', strtotime('-37 days')))
            ->where('us.current_period_end <=', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->orderBy('us.current_period_end', 'DESC')
            ->get()->getResultArray();

        if (empty($filas)) {
            CLI::write('  - Sin bajas de Pro/Business en la ventana de 30-37 días.', 'dark_gray');
            return;
        }

        // Quien ya ha vuelto a un plan de pago de la API no recibe nada
        // "Con plan" = activo o cancelado aún dentro del periodo pagado (p. ej. tras
        // pasar de Pro a Business y cancelar Business, que sigue vigente)
        $activos = array_flip(array_map('intval', array_column($db->table('user_subscriptions')
            ->select('user_id')
            ->whereIn('plan_id', [2, 3])
            ->groupStart()
                ->where('status', 'active')
                ->orGroupStart()->where('status', 'canceled')->where('current_period_end >', date('Y-m-d H:i:s'))->groupEnd()
            ->groupEnd()
            ->get()->getResultArray(), 'user_id')));

        $usuarios = $this->usuariosElegibles(array_column($filas, 'user_id'));
        $vistos   = [];

        foreach ($filas as $f) {
            $uid = (int) $f['user_id'];
            if (isset($vistos[$uid]) || isset($activos[$uid]) || !isset($usuarios[$uid])) {
                continue;
            }
            $vistos[$uid] = true;

            if ($this->automationModel->wasSentRecently($uid, 'api_winback', 365)) {
                continue;
            }

            CLI::write("  -> Enviando 'api_winback' a {$usuarios[$uid]['email']}...");
            $this->registrarEnvio($uid, 'api_winback', $this->emailService->sendApiWinback(
                $usuarios[$uid] + ['user_id' => $uid],
                ['name' => $f['plan_name'] ?? ''],
                (string) ($f['cancellation_reason'] ?? '')
            ));
        }
    }

    /**
     * Empezó el pago de Pro o Business (checkout_started de Billing) entre hace 48 h y hace
     * 1 h y no lo terminó. Un correo por usuario cada 30 días; solo a quien admite
     * correos comerciales y no tiene ya un plan de pago de la API.
     */
    protected function processApiCheckoutAbandoned(): void
    {
        $db = \Config\Database::connect();

        $empezados = $db->table('tracking_events')
            ->select('user_id, metadata, created_at')
            ->where('event_name', 'checkout_started')
            ->where('page', 'billing')
            ->where('user_id >', 0)
            ->groupStart()
                ->like('metadata', '"plan":"pro"')
                ->orLike('metadata', '"plan":"business"')
            ->groupEnd()
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-48 hours')))
            ->where('created_at <=', date('Y-m-d H:i:s', strtotime('-1 hour')))
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        $porUsuario = [];
        foreach ($empezados as $e) {
            $uid = (int) $e['user_id'];
            if (!isset($porUsuario[$uid])) {
                $porUsuario[$uid] = $e;   // el intento más reciente
            }
        }
        if (empty($porUsuario)) {
            CLI::write('  - Sin pagos de la API sin terminar.', 'dark_gray');
            return;
        }

        $conPlan = array_flip(array_map('intval', array_column($db->table('user_subscriptions')
            ->select('user_id')
            ->whereIn('plan_id', [2, 3])
            ->groupStart()
                ->where('status', 'active')
                ->orGroupStart()->where('status', 'canceled')->where('current_period_end >', date('Y-m-d H:i:s'))->groupEnd()
            ->groupEnd()
            ->get()->getResultArray(), 'user_id')));

        $usuarios = $this->usuariosElegibles(array_keys($porUsuario));
        $enviados = 0;

        foreach ($porUsuario as $uid => $intento) {
            if (isset($conPlan[$uid]) || !isset($usuarios[$uid])) {
                continue;
            }

            $completado = $db->table('tracking_events')
                ->where('event_name', 'checkout_completed')
                ->where('user_id', $uid)
                ->where('created_at >=', $intento['created_at'])
                ->countAllResults() > 0;
            // Si ha vuelto a intentarlo en la última hora puede estar pagando ahora mismo
            $reciente = $db->table('tracking_events')
                ->where('event_name', 'checkout_started')
                ->where('page', 'billing')
                ->where('user_id', $uid)
                ->where('created_at >', date('Y-m-d H:i:s', strtotime('-1 hour')))
                ->countAllResults() > 0;
            if ($completado || $reciente || $this->automationModel->wasSentRecently($uid, 'api_checkout_abandoned', 30)) {
                continue;
            }

            $meta = json_decode((string) $intento['metadata'], true) ?: [];
            CLI::write("  -> Enviando 'api_checkout_abandoned' a {$usuarios[$uid]['email']}...");
            $res = $this->emailService->sendApiCheckoutAbandoned(
                $usuarios[$uid] + ['user_id' => $uid],
                (string) ($meta['plan'] ?? 'pro'),
                (string) ($meta['period'] ?? 'monthly')
            );
            $this->registrarEnvio($uid, 'api_checkout_abandoned', $res);
            if (!empty($res['success']) && empty($res['skipped'])) {
                $enviados++;
            }
        }

        CLI::write('  - Pagos sin terminar: ' . count($porUsuario) . ' usuario(s); correos enviados: ' . $enviados
            . ' (el resto ya pagó, ya tenía plan, no admite correos o ya lo recibió).');
    }

    protected function recordTracking(int $userId, string $eventName)
    {
        $db = \Config\Database::connect();
        try {
            $db->table('tracking_events')->insert([
                'event_name' => $eventName,
                'user_id'    => $userId,
                'page'       => 'automation_email',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Ignorar si falla el tracking
        }
    }

    /**
     * Detecta usuarios Free con alta tasa de errores 400 hoy y les envía un correo de
     * ayuda con sus propios ejemplos. No toca el uso: los errores no se cobran.
     */
    protected function processBadRequestUsers()
    {
        $db = \Config\Database::connect();

        $results = $db->query("
            SELECT 
                u.id, u.email, u.name, u.created_at,
                SUM(CASE WHEN r.status_code = 400 THEN 1 ELSE 0 END) as bad_count,
                COUNT(r.id) as total_count
            FROM users u
            JOIN api_requests r ON r.user_id = u.id AND r.created_at >= CURDATE()
            WHERE u.is_admin = 0
              AND u.unsuscribe = 0
              AND u.id <> ?
              -- Free, Pro y Business. Antes solo Free con intent 'api' y alta de menos de
              -- 7 días: quien fallaba después (o ya pagaba) no recibía ayuda.
              AND EXISTS (
                  SELECT 1 FROM user_subscriptions us
                  WHERE us.user_id = u.id AND us.status = 'active' AND us.plan_id IN (1, 2, 3)
              )
              -- Como mucho una vez cada 30 días (antes, una vez en la vida)
              AND u.id NOT IN (
                  SELECT user_id FROM user_email_automation
                  WHERE email_type = 'bad_request_help'
                    AND sent_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              )
            GROUP BY u.id, u.email, u.name, u.created_at
            HAVING bad_count >= 20
              AND (bad_count / total_count) >= 0.30
        ", [\App\Filters\ApiKeyFilter::MONITOR_USER_ID])->getResultArray();

        if (empty($results)) {
            CLI::write('  - Sin usuarios con alta tasa de errores 400 hoy.', 'dark_gray');
            return;
        }

        foreach ($results as $user) {
            $userId   = (int)$user['id'];
            $badCount = (int)$user['bad_count'];

            // Las peticiones con error no se cobran (ApiKeyFilter solo factura las 200).
            // Antes aquí se restaban hasta 50 del uso de hoy "para devolverlas", y lo que
            // se restaba eran consultas buenas: se regalaban. Ahora solo se ayuda.
            $ejemplos = array_column($db->query("
                SELECT DISTINCT search_term
                FROM api_requests
                WHERE user_id = ? AND status_code = 400 AND created_at >= CURDATE()
                  AND search_term IS NOT NULL AND search_term <> ''
                LIMIT 3
            ", [$userId])->getResultArray(), 'search_term');

            CLI::write("  -> {$user['email']}: {$badCount} errores 400. Enviando ayuda...");

            $result = $this->emailService->sendBadRequestHelp($user, $badCount, $ejemplos);

            if ($result['success']) {
                $this->automationModel->markAsSent($userId, 'bad_request_help', $result['body']);
                $this->recordTracking($userId, 'email_sent_bad_request_help');
                CLI::write("     [SENT] bad_request_help OK", 'yellow');
            } else {
                CLI::write("     [ERROR] No se pudo enviar el email a {$user['email']}", 'red');
            }
        }
    }
}
