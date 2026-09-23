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
        CLI::write('📡 [1/3] Procesando automatizaciones de API...', 'cyan');
        
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
        CLI::write('🛡️ [2/3] Procesando automatizaciones de Riesgo y Solvencia...', 'cyan');
        $this->processRiskPaywallTriggers();
        $this->processRiskPackUpsellTriggers();
        $this->processSolvenciaCiclo();

        // =========================================================================
        // BLOQUE 3: USUARIOS CON ALTA TASA DE ERRORES 400 EN API
        // =========================================================================
        CLI::write('🔍 [3/3] Detectando usuarios con errores 400 en peticiones...', 'cyan');
        $this->processBadRequestUsers();

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

        // 4. TRIGGER: no_requests_15min
        if ($totalRequests === 0) {
            $diffSeconds = time() - strtotime($createdAt);
            if ($diffSeconds >= 900) { // 15 minutos
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
            case 'no_requests_15min':
                $result = $this->emailService->sendNoUsage15Min($user);
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

    protected function getTotalRequests(int $userId): int
    {
        $res = $this->usageModel->selectSum('requests_count')
            ->where('user_id', $userId)
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
     * Detecta usuarios Free con alta tasa de errores 400 hoy,
     * les envía un email de ayuda técnica y restaura las consultas fallidas.
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
            $restore  = min($badCount, 50);

            CLI::write("  -> {$user['email']}: {$badCount} errores 400. Restaurando {$restore} consultas...");

            $db->query("
                UPDATE api_usage_daily
                SET requests_count = GREATEST(0, requests_count - ?),
                    updated_at = NOW()
                WHERE user_id = ? AND date = CURDATE()
            ", [$restore, $userId]);

            $result = $this->emailService->sendBadRequestHelp($user, $restore);

            if ($result['success']) {
                $this->automationModel->markAsSent($userId, 'bad_request_help', $result['body']);
                CLI::write("     [SENT] bad_request_help OK — {$restore} consultas restauradas", 'yellow');
            } else {
                CLI::write("     [ERROR] No se pudo enviar el email a {$user['email']}", 'red');
            }
        }
    }
}
