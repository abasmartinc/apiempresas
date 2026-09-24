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
        CLI::write('📡 [1/5] Procesando automatizaciones de API...', 'cyan');
        
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
            $this->processApiTriggersForUser($user);
        }

        // =========================================================================
        // BLOQUE 2: FLUJO DE RIESGO, PAYWALL Y UPSELL PACKS
        // =========================================================================
        CLI::write('🛡️ [2/5] Procesando automatizaciones de Riesgo y Solvencia...', 'cyan');
        $this->processRiskPaywallTriggers();
        $this->processRiskPackUpsellTriggers();
        $this->processSolvenciaCiclo();

        // =========================================================================
        // BLOQUE 3: USUARIOS CON ALTA TASA DE ERRORES 400 EN API
        // =========================================================================
        CLI::write('🔍 [3/5] Detectando usuarios con errores 400 en peticiones...', 'cyan');
        $this->processBadRequestUsers();

        // =========================================================================
        // BLOQUE 4: CLIENTES DE PAGO DE LA API CERCA DEL CUPO DEL MES
        // =========================================================================
        CLI::write('💳 [4/5] Revisando el cupo mensual de los clientes de pago de la API...', 'cyan');
        $this->processPaidApiQuota();

        // =========================================================================
        // BLOQUE 5: RECUPERACIÓN DE QUIEN DEJÓ PRO O BUSINESS HACE UN MES
        // =========================================================================
        CLI::write('↩️  [5/5] Recuperación de bajas de planes de pago de la API...', 'cyan');
        $this->processApiWinback();

        CLI::write('✅ Proceso de automatización finalizado con éxito.', 'green');
    }

    /**
     * Procesa los disparadores para usuarios de la API
     */
    protected function processApiTriggersForUser(array $user)
    {
        $userId = (int)$user['id'];
        $totalRequests = $this->getTotalRequests($userId);
        $lastRequestTime = $this->getLastRequestTime($userId);
        $createdAt = $user['created_at'];

        // 0. TRIGGER: reached_100_percent_quota (Bono de 100)
        if ($totalRequests >= 100) {
            $this->checkAndSend($user, 'reached_100_percent_quota', 'email_sent_quota_max', [], true);
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

        // 2. TRIGGER: reached_5_requests
        if ($totalRequests >= 5) {
            $this->checkAndSend($user, 'reached_5_requests', 'email_sent_engaged');
            return;
        }

        // 3. TRIGGER: one_request_inactive_1h
        if ($totalRequests === 1 && $lastRequestTime) {
            $diffSeconds = time() - strtotime($lastRequestTime);
            if ($diffSeconds >= 3600) { // 1 hora
                $this->checkAndSend($user, 'one_request_inactive_1h', 'email_sent_first_usage');
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

        // 5. TRIGGER: monthly_report (Recurrente cada 30 días)
        if (!$this->automationModel->wasSentRecently($userId, 'monthly_report', 30)) {
            $usage30Days = $this->getUsageLast30Days($userId);
            if ($usage30Days > 0) {
                $this->checkAndSend($user, 'monthly_report', 'email_sent_monthly_report', ['usage' => $usage30Days], true);
            }
        }
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
        ", [$startOfMonth])->getResultArray();

        CLI::write("  - Candidatos de Riesgo / Freemium detectados: " . count($riskCandidates));

        $isEndOfMonth = ((int)date('j') >= 28);

        foreach ($riskCandidates as $user) {
            $userId = (int)$user['id'];

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
            if ($yaTrasCompra) {
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

    protected function checkAndSend(array $user, string $triggerType, string $trackingEvent, array $extraParams = [], bool $isRecurring = false)
    {
        $userId = (int)$user['id'];

        $alreadySent = $isRecurring 
            ? $this->automationModel->wasSentRecently($userId, $triggerType, 30)
            : $this->automationModel->wasSent($userId, $triggerType);

        if ($alreadySent) {
            return;
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
                $result = $this->emailService->sendMonthlyUsageReport($user, $usage);
                break;
        }

        if ($result['success']) {
            $this->automationModel->markAsSent($userId, $triggerType, $result['body']);
            $this->recordTracking($userId, $trackingEvent);
            CLI::write("     [SENT] {$triggerType} OK", 'yellow');
        }
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
        $this->cicloListaLlena();

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
            ORDER BY us.id DESC
        ")->getResultArray();

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
            }
        }

        CLI::write('  - Avisos de cupo enviados: ' . $enviados);
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
        $activos = array_flip(array_map('intval', array_column($db->table('user_subscriptions')
            ->select('user_id')
            ->where('status', 'active')
            ->whereIn('plan_id', [2, 3])
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
            JOIN user_subscriptions us ON us.user_id = u.id AND us.status = 'active' AND us.plan_id = 1
            JOIN api_requests r ON r.user_id = u.id AND DATE(r.created_at) = CURDATE()
            WHERE u.is_admin = 0
              AND u.unsuscribe = 0
              AND u.signup_intent = 'api'
              AND u.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
              AND u.id NOT IN (
                  SELECT user_id FROM user_email_automation
                  WHERE email_type = 'bad_request_help'
              )
            GROUP BY u.id, u.email, u.name, u.created_at
            HAVING bad_count >= 20
              AND (bad_count / total_count) >= 0.30
        ")->getResultArray();

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
                WHERE user_id = ? AND status_code = 400 AND DATE(created_at) = CURDATE()
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
