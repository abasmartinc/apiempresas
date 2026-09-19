<?php

namespace App\Services;

use App\Models\CompanyModel;
use App\Models\UserEventsModel;
use Config\Database;

class CompanyRiskService
{
    /**
     * Consultas gratuitas (empresas distintas) por mes natural.
     *
     * @deprecated Respaldo. La cifra que manda es Config\Solvencia::$consultasGratis,
     *             que es donde vive el resto de decisiones comerciales; esta constante
     *             solo se usa si el config no estuviera disponible. Estaban las dos a 3
     *             por separado, que es como acaban desincronizándose.
     */
    public const FREE_MONTHLY_VIEWS = 3;

    /**
     * Evento que marca una empresa desbloqueada por COMPRA del informe.
     *
     * Separado de 'view_risk_profile' porque ese es el que cuenta para la cuota
     * mensual gratuita. Ver `desbloquearPorCompra()`.
     *
     * El nombre tiene 17 caracteres, como 'view_risk_profile', y sigue a la familia
     * del 'purchase_risk_pack' que ya escribe Billing: la columna admite ese largo
     * con seguridad, sin tener que ir a mirar el esquema.
     */
    public const EVENTO_COMPRA = 'purchase_risk_pdf';

    protected CompanyModel $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
    }

    /** Consultas gratuitas del mes, desde el config. */
    protected function limiteGratuito(): int
    {
        helper('company');

        return (int) solvencia('consultasGratis', self::FREE_MONTHLY_VIEWS);
    }

    /**
     * Limpia y normaliza el CIF introducido (soporta formatos como B-12345678, B12345678, etc.).
     */
    /**
     * Evolución del score a lo largo del tiempo.
     *
     * El motor archiva el perfil anterior en `company_risk_profiles_history` cada
     * vez que recalcula, con `calculated_at` = el momento en que ESE score estuvo
     * vigente. Eso permite decir "hace 6 meses era 30", que para un cliente vale
     * más que el número de hoy: un 45 estable no es lo mismo que un 45 que hace
     * medio año era 20.
     *
     * Devuelve null si no hay con qué comparar: sin histórico, o con un histórico
     * tan reciente que la comparación no diría nada ("hace 3 días era 45").
     *
     * SOLO SE COMPARAN PUNTOS DEL MISMO MODELO
     * ----------------------------------------
     * Y esto no es un detalle: sin ello, la noche que se cambia de motor TODO el
     * catálogo archiva su perfil viejo a la vez, y al día siguiente cada ficha
     * anuncia un salto con su flecha roja y la frase "ha empeorado". Medido en un
     * caso real al pasar de 2.0.0 a 3.0.0: una empresa con tres ejercicios sin
     * depositar pasaba de 5 a 32 sin que en el BORME constara un solo acto nuevo.
     * Eso no es una evolución, son dos reglas distintas midiendo lo mismo, y el
     * cliente que vaya a comprobarlo no encuentra nada que lo explique.
     *
     * Consecuencia aceptada: tras un cambio de motor la gráfica desaparece hasta
     * que haya dos ejecuciones del nuevo. Es correcto — hasta entonces no hay
     * ninguna evolución real que enseñar.
     *
     * @param string|null $modelo Versión del modelo del perfil vigente. Sin ella
     *                            no se puede saber qué es comparable, así que no
     *                            se pinta nada.
     *
     * @return array{antes:int,ahora:int,delta:int,dias:int,periodo:string,puntos:array}|null
     */
    public function getScoreTrend(
        string $cif,
        int $ahora,
        int $mesesMax = 24,
        int $diasMinimos = 25,
        ?string $modelo = null
    ): ?array {
        $cleanCif = $this->cleanCif($cif);
        if ($cleanCif === '') {
            return null;
        }

        $modelo = trim((string) $modelo);
        if ($modelo === '') {
            // Perfil anterior a que el motor sellara la versión: no hay forma de
            // saber con qué regla se calculó, así que tampoco de compararlo.
            return null;
        }

        try {
            $db = Database::connect();
            if (!$db->tableExists('company_risk_profiles_history')) {
                return null;
            }

            $desde = date('Y-m-d H:i:s', strtotime('-' . $mesesMax . ' months'));

            $filas = $db->table('company_risk_profiles_history')
                ->select('risk_score, calculated_at')
                ->where('cif', $cleanCif)
                ->where('model_version', $modelo)   // ver la nota de arriba
                ->where('calculated_at >=', $desde)
                ->orderBy('calculated_at', 'ASC')
                ->limit(200)
                ->get()->getResultArray();

            if (empty($filas)) {
                return null;
            }

            // Punto de comparación: el más antiguo que tenga al menos unos días.
            // Si el motor corre a diario, los últimos puntos no aportan nada.
            $limite = time() - ($diasMinimos * 86400);
            $referencia = null;
            foreach ($filas as $f) {
                if (strtotime((string) $f['calculated_at']) <= $limite) {
                    $referencia = $f;
                    break;
                }
            }

            if ($referencia === null) {
                return null;
            }

            $antes = (int) $referencia['risk_score'];
            $dias  = max(1, (int) floor((time() - strtotime((string) $referencia['calculated_at'])) / 86400));

            // Serie para la mini-gráfica: el histórico + el valor de hoy.
            $puntos = [];
            foreach ($filas as $f) {
                $puntos[] = (int) $f['risk_score'];
            }
            $puntos[] = $ahora;

            // Si son muchos, se adelgaza para que la línea no sea ruido.
            if (count($puntos) > 24) {
                $paso = (int) ceil(count($puntos) / 24);
                $reducidos = [];
                foreach ($puntos as $i => $v) {
                    if ($i % $paso === 0) {
                        $reducidos[] = $v;
                    }
                }
                $reducidos[] = $ahora;
                $puntos = $reducidos;
            }

            return [
                'antes'   => $antes,
                'ahora'   => $ahora,
                'delta'   => $ahora - $antes,
                'dias'    => $dias,
                'periodo' => $this->periodoLegible($dias),
                'puntos'  => $puntos,
            ];
        } catch (\Throwable $e) {
            log_message('error', '[CompanyRiskService] getScoreTrend(' . $cleanCif . '): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * "hace 6 meses", "hace un año"... en vez de "hace 187 días".
     */
    private function periodoLegible(int $dias): string
    {
        if ($dias < 45) {
            return 'hace ' . $dias . ' días';
        }

        $meses = (int) round($dias / 30.4);
        if ($meses < 12) {
            return 'hace ' . $meses . ' meses';
        }

        $anios = (int) floor($meses / 12);
        if ($anios === 1) {
            return $meses >= 18 ? 'hace año y medio' : 'hace un año';
        }

        return 'hace ' . $anios . ' años';
    }

    public function cleanCif(string $cif): string
    {
        $raw = strtoupper(trim($cif));

        // Eliminar espacios, puntos y guiones
        $clean = preg_replace('/[\s\-\.]/', '', $raw);

        // Si empieza por patrón típico CIF (1 letra + 7 dígitos + 1 carácter de control)
        if (preg_match('/^[A-Z][0-9]{7}[A-Z0-9]/', $clean, $m)) {
            return $m[0];
        }

        // Fallback: caracteres alfanuméricos en mayúsculas
        return preg_replace('/[^A-Z0-9]/', '', $clean);
    }

    /**
     * LECTURA PURA del estado de cuota. No escribe nada: no registra eventos
     * ni descuenta créditos. Es el método que deben usar los renders de página
     * y cualquier comprobación de permisos.
     *
     * Claves relevantes del array devuelto:
     *  - allowed:     el dictamen puede mostrarse YA, sin coste (suscriptor, admin o empresa ya desbloqueada)
     *  - can_unlock:  no está permitido aún, pero el usuario puede desbloquearlo (cuota libre o créditos)
     *  - unlock_cost: 'free' | 'credit' | null  → qué se gastará al desbloquear
     *  - reason:      'unauthenticated' | 'limit_reached' cuando no hay nada que hacer
     */
    public function getQuotaStatus(int $userId, string $cif): array
    {
        $limit = $this->limiteGratuito();

        if ($userId <= 0) {
            return [
                'allowed'       => false,
                'can_unlock'    => false,
                'unlock_cost'   => null,
                'reason'        => 'unauthenticated',
                'is_subscriber' => false,
                'views_used'    => 0,
                'views_limit'   => $limit,
                'risk_credits'  => 0,
            ];
        }

        $db = Database::connect();
        $cleanCif = $this->cleanCif($cif);

        $userRow = $db->table('users')->select('is_admin, risk_credits')->where('id', $userId)->get()->getRow();
        $userRiskCredits = (int)($userRow->risk_credits ?? 0);
        $isAdmin = (bool)session('is_admin') || ($userRow && (int)$userRow->is_admin === 1);

        $activeRiskSub = $this->findActiveRiskSubscription($userId);

        // 1. Suscriptor de Solvencia (o admin): acceso inmediato, con un techo
        //    mensual que es técnico y no comercial.
        //
        //    Antes era 'unlimited' de verdad. Con consultas ilimitadas a 29 € al
        //    mes, cualquiera puede recorrer el catálogo y reconstruirse el
        //    directorio que vendemos aparte en CSV y por API. El tope está puesto
        //    donde ningún usuario real llega (diez empresas al día, todos los
        //    días) y donde un extractor choca enseguida.
        //
        //    Los admins quedan fuera del techo: son nosotros.
        if ($activeRiskSub || $isAdmin) {
            helper('company');
            $topePro = (int) solvencia('consultasPro', 300);
            $usadasPro = $isAdmin ? 0 : $this->countDistinctViewsThisMonth($userId);
            $dentro    = $isAdmin || $usadasPro < $topePro;

            if (!$dentro) {
                log_message('warning', '[CompanyRiskService] Suscriptor ' . $userId
                    . ' ha alcanzado el techo de ' . $topePro . ' consultas del mes.');
            }

            return [
                'allowed'       => $dentro,
                'can_unlock'    => false,
                'unlock_cost'   => null,
                'reason'        => $dentro ? null : 'subscriber_cap',
                'is_subscriber' => true,
                'plan_name'     => $activeRiskSub ? ($activeRiskSub->plan_name ?? 'Solvencia Pro') : 'Solvencia Pro (Admin)',
                'views_used'    => $usadasPro,
                'views_limit'   => $isAdmin ? 'unlimited' : $topePro,
                'risk_credits'  => $userRiskCredits,
            ];
        }

        $distinctCount = $this->countDistinctViewsThisMonth($userId);
        $alreadyViewedEver = $this->hasUnlockedCompany($userId, $cleanCif);

        // 2. Empresa ya desbloqueada anteriormente: acceso inmediato y sin coste
        if ($alreadyViewedEver) {
            return [
                'allowed'          => true,
                'can_unlock'       => false,
                'unlock_cost'      => null,
                'is_subscriber'    => false,
                'already_unlocked' => true,
                'from_pack'        => false,
                'views_used'       => $distinctCount,
                'views_limit'      => $limit,
                'risk_credits'     => $userRiskCredits,
            ];
        }

        // 3. Le quedan consultas gratuitas del mes
        if ($distinctCount < $limit) {
            return [
                'allowed'          => false,
                'can_unlock'       => true,
                'unlock_cost'      => 'free',
                'is_subscriber'    => false,
                'already_unlocked' => false,
                'views_used'       => $distinctCount,
                'views_remaining'  => $limit - $distinctCount,
                'views_limit'      => $limit,
                'risk_credits'     => $userRiskCredits,
            ];
        }

        // 4. Sin cuota gratuita pero con créditos comprados (pack)
        if ($userRiskCredits > 0) {
            return [
                'allowed'          => false,
                'can_unlock'       => true,
                'unlock_cost'      => 'credit',
                'is_subscriber'    => false,
                'already_unlocked' => false,
                'views_used'       => $distinctCount,
                'views_remaining'  => 0,
                'views_limit'      => $limit,
                'risk_credits'     => $userRiskCredits,
            ];
        }

        // 5. Límite alcanzado y sin créditos → paywall
        return [
            'allowed'       => false,
            'can_unlock'    => false,
            'unlock_cost'   => null,
            'reason'        => 'limit_reached',
            'is_subscriber' => false,
            'views_used'    => $distinctCount,
            'views_limit'   => $limit,
            'risk_credits'  => 0,
        ];
    }

    /**
     * CONSUME una consulta de perfil de riesgo. Registra el evento y, si procede,
     * descuenta un crédito. Debe invocarse ÚNICAMENTE desde una acción explícita
     * del usuario (click de desbloqueo o búsqueda manual de un CIF), nunca desde
     * el render de una página.
     *
     * Para suscriptores, admins y empresas ya desbloqueadas no hay coste: solo se
     * registra el evento para mantener el historial de consultas.
     */
    public function consumeRiskView(int $userId, string $cif): array
    {
        $status = $this->getQuotaStatus($userId, $cif);
        $cleanCif = $this->cleanCif($cif);

        // Nada que consumir: sin sesión o sin cuota ni créditos
        if (empty($status['allowed']) && empty($status['can_unlock'])) {
            return $status;
        }

        if ($cleanCif === '') {
            return $status;
        }

        $db = Database::connect();
        $userEventsModel = new UserEventsModel();

        // Acceso sin coste (suscriptor / admin / empresa ya desbloqueada): solo historial
        if (!empty($status['allowed'])) {
            $userEventsModel->logEvent($userId, 'view_risk_profile', $cleanCif);
            return $status;
        }

        // Desbloqueo con cargo a los créditos comprados
        if (($status['unlock_cost'] ?? null) === 'credit') {
            $db->table('users')
                ->where('id', $userId)
                ->where('risk_credits >', 0)
                ->set('risk_credits', 'risk_credits - 1', false)
                ->update();

            if ($db->affectedRows() < 1) {
                // Carrera: otro proceso agotó los créditos entre la lectura y el descuento
                return $this->getQuotaStatus($userId, $cif);
            }

            $userEventsModel->logEvent($userId, 'view_risk_profile', $cleanCif);

            $remainingCredits = max(0, (int)($status['risk_credits'] ?? 1) - 1);

            return array_merge($status, [
                'allowed'           => true,
                'can_unlock'        => false,
                'unlock_cost'       => null,
                'from_pack'         => true,
                'credits_remaining' => $remainingCredits,
                'risk_credits'      => $remainingCredits,
                'views_used'        => (int)($status['views_used'] ?? 0) + 1,
            ]);
        }

        // Desbloqueo con cargo a la cuota gratuita del mes
        $userEventsModel->logEvent($userId, 'view_risk_profile', $cleanCif);

        $viewsUsed = (int)($status['views_used'] ?? 0) + 1;

        return array_merge($status, [
            'allowed'         => true,
            'can_unlock'      => false,
            'unlock_cost'     => null,
            'from_pack'       => false,
            'views_used'      => $viewsUsed,
            'views_remaining' => max(0, $this->limiteGratuito() - $viewsUsed),
        ]);
    }

    /**
     * @deprecated Usa getQuotaStatus() para comprobar y consumeRiskView() para consumir.
     *             Se mantiene como alias NO destructivo para no romper llamadas antiguas.
     */
    public function getRiskViewQuota(int $userId, string $cif): array
    {
        return $this->getQuotaStatus($userId, $cif);
    }

    /**
     * Suscripción activa (o cancelada pero aún en periodo) del producto de Riesgo / Solvencia.
     */
    protected function findActiveRiskSubscription(int $userId)
    {
        return Database::connect()->table('user_subscriptions')
            ->select('user_subscriptions.*, api_plans.name as plan_name, api_plans.slug as plan_slug')
            ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
            ->where('user_subscriptions.user_id', $userId)
            ->groupStart()
                ->where('api_plans.product_type', 'risk')
                ->orWhere('api_plans.product_type', 'bundle')
                ->orWhere('api_plans.slug', 'risk_pro')
            ->groupEnd()
            ->groupStart()
                ->where('user_subscriptions.status', 'active')
                ->orGroupStart()
                    ->where('user_subscriptions.status', 'canceled')
                    ->where('user_subscriptions.current_period_end >', date('Y-m-d H:i:s'))
                ->groupEnd()
            ->groupEnd()
            ->orderBy('FIELD(user_subscriptions.status, "active", "canceled")', 'ASC', false)
            ->orderBy('user_subscriptions.current_period_end', 'DESC')
            ->get()->getRow();
    }

    /**
     * ¿Ha desbloqueado el usuario esta empresa alguna vez?
     *
     * Dos caminos valen: haberla consultado (`view_risk_profile`) o haber comprado
     * su informe (EVENTO_COMPRA). Son eventos distintos a propósito — ver
     * `desbloquearPorCompra()`.
     */
    protected function hasUnlockedCompany(int $userId, string $cleanCif): bool
    {
        if ($cleanCif === '') {
            return false;
        }

        return Database::connect()->table('user_events')
            ->where('user_id', $userId)
            ->whereIn('event_type', ['view_risk_profile', self::EVENTO_COMPRA])
            ->where('trigger_type', $cleanCif)
            ->countAllResults() > 0;
    }

    /**
     * Desbloquea una empresa porque el usuario ha PAGADO su informe.
     *
     * Hasta ahora comprar el PDF entregaba un fichero y nada más: el comprador
     * volvía a la ficha y se la seguía encontrando bloqueada, que es la peor cara
     * posible de un cobro. Y el documento que se llevaba era, palabra por palabra,
     * el que ya se descarga gratis quien haya consultado esa empresa alguna vez.
     * Así que lo que se vende no es el fichero, es el acceso a la empresa.
     *
     * Usa un `event_type` propio y NO 'view_risk_profile' por una razón concreta:
     * `countDistinctViewsThisMonth()` cuenta los 'view_risk_profile' del mes para
     * aplicar la cuota gratuita. Registrar la compra como una consulta le gastaría
     * al comprador una de sus 3 consultas del mes — cobrarle y además descontarle.
     *
     * Es idempotente: recargar la página de descarga no duplica el evento.
     */
    public function desbloquearPorCompra(int $userId, string $cif): bool
    {
        $cleanCif = $this->cleanCif($cif);

        if ($userId <= 0 || $cleanCif === '') {
            return false;
        }

        $yaEstaba = Database::connect()->table('user_events')
            ->where('user_id', $userId)
            ->where('event_type', self::EVENTO_COMPRA)
            ->where('trigger_type', $cleanCif)
            ->countAllResults() > 0;

        if ($yaEstaba) {
            return false;
        }

        (new UserEventsModel())->logEvent($userId, self::EVENTO_COMPRA, $cleanCif);

        return true;
    }

    /**
     * La cuenta de arriba, para quien la necesite fuera del servicio.
     *
     * Existe porque el panel la calculaba por su cuenta con su propia consulta, y
     * así es como el contador de la ficha y el del panel acaban diciendo números
     * distintos el día que la regla cambia — que es hoy.
     */
    public function consultasDelMes(int $userId): int
    {
        return $userId > 0 ? $this->countDistinctViewsThisMonth($userId) : 0;
    }

    /**
     * Empresas que consumen cuota este mes: las ABIERTAS POR PRIMERA VEZ este mes
     * y que no se han comprado.
     *
     * Antes contaba "empresas con alguna vista este mes", y esa regla cobra dos
     * veces por lo mismo. Acceder a una empresa ya desbloqueada es gratis —lo dice
     * la propia ficha, "Ya incluido con esta consulta"— pero cada visita registra
     * igualmente un `view_risk_profile` con la fecha de hoy, así que la empresa
     * entraba en el recuento del mes en curso. Dos casos reales:
     *
     *  - Compras el informe de una empresa (3,90 €) y al volver a la ficha, la
     *    visita la mete en el recuento: has pagado Y has gastado una de las tres.
     *    Se vio en los eventos del usuario 577: compra a las 16:13 y vista a las
     *    16:14 de la misma empresa, con la píldora bajando de 1 a 0.
     *  - Consultas tres empresas en enero y en febrero vuelves a abrirlas para
     *    releerlas: febrero se queda sin cuota sin haber abierto ninguna nueva.
     *
     * Contar la PRIMERA vista arregla los dos: una empresa consume cuota el mes en
     * que se abrió, una sola vez y para siempre. Y las compradas no consumen nunca,
     * que es justo lo que se paga.
     *
     * El mismo criterio vale para el techo mensual del suscriptor: lo que ese tope
     * protege es la extracción del catálogo, y releer una empresa que ya se tenía
     * no aporta ningún dato nuevo.
     */
    protected function countDistinctViewsThisMonth(int $userId): int
    {
        $db = Database::connect();

        // Se agrupa por empresa y se mira su PRIMERA vista. El filtro va en PHP y no
        // en un HAVING para no depender de cómo escape el constructor la función de
        // agregación; el volumen es el de un usuario, no el de la tabla.
        $filas = $db->table('user_events')
            ->select('trigger_type, MIN(created_at) AS primera')
            ->where('user_id', $userId)
            ->where('event_type', 'view_risk_profile')
            ->where('trigger_type IS NOT NULL')
            ->where('trigger_type !=', '')
            ->groupBy('trigger_type')
            ->get()->getResultArray();

        $inicioMes = date('Y-m-01 00:00:00');
        $delMes    = [];

        foreach ($filas as $fila) {
            $cif = trim((string) ($fila['trigger_type'] ?? ''));
            if ($cif === '') {
                continue;
            }

            if ((string) ($fila['primera'] ?? '') >= $inicioMes) {
                $delMes[$cif] = true;
            }
        }

        if (empty($delMes)) {
            return 0;
        }

        // Las compradas quedan fuera: el pago ya las desbloquea por su cuenta.
        $compradas = $db->table('user_events')
            ->select('trigger_type')
            ->where('user_id', $userId)
            ->where('event_type', self::EVENTO_COMPRA)
            ->whereIn('trigger_type', array_keys($delMes))
            ->get()->getResultArray();

        foreach ($compradas as $fila) {
            unset($delMes[trim((string) ($fila['trigger_type'] ?? ''))]);
        }

        return count($delMes);
    }

    /**
     * Obtiene el conjunto completo de datos para la evaluación del perfil de riesgo.
     *
     * @param bool $consume true solo cuando la llamada procede de una acción explícita
     *                      del usuario (búsqueda manual de un CIF). En ese caso la cuota
     *                      se consume ÚNICAMENTE si la empresa tiene perfil calculado.
     */
    public function getRiskData(string $cif, ?int $userId = null, bool $consume = false): array
    {
        $cleanCif = $this->cleanCif($cif);
        if (empty($cleanCif)) {
            return [
                'found'       => false,
                'cleanCif'    => '',
                'company'     => null,
                'riskProfile' => null,
                'error'       => 'EMPTY_CIF'
            ];
        }

        $db = Database::connect();

        // 1. Buscar empresa
        $company = $this->companyModel->getByCif($cleanCif);
        if (!$company && $cleanCif !== strtoupper(trim($cif))) {
            $company = $this->companyModel->getByCif(strtoupper(trim($cif)));
        }
        if (!$company) {
            $company = $this->companyModel->where('cif', $cleanCif)->first();
        }
        if (!$company && !empty($cif)) {
            $rawTerm = trim($cif);
            $company = $this->companyModel->where('company_name', $rawTerm)->first();
            if (!$company && strlen($rawTerm) >= 4) {
                $company = $this->companyModel->like('company_name', $rawTerm)->first();
            }
        }

        if (!$company) {
            return [
                'found'       => false,
                'cleanCif'    => $cleanCif,
                'company'     => null,
                'riskProfile' => null,
                'error'       => 'COMPANY_NOT_FOUND'
            ];
        }

        helper('company');
        $company['name'] = company_display_name(
            $company['name'] ?? ($company['company_name'] ?? ''),
            'Empresa'
        );

        $targetCif = (string)($company['cif'] ?? $cleanCif);

        // 2. Buscar perfil de riesgo calculado
        $riskRow = $db->table('company_risk_profiles')->where('cif', $targetCif)->get()->getRowArray();
        $riskProfile = null;
        if ($riskRow) {
            $riskProfile = $riskRow;
            if (!empty($riskProfile['risk_profile'])) {
                $riskProfile['data'] = json_decode($riskProfile['risk_profile'], true);
            }
        }

        // 3. Contratos públicos y subvenciones
        $contracts = $db->table('company_contracts')
            ->where('company_cif', $targetCif)
            ->orderBy('fecha_adjudicacion', 'DESC')
            ->get()->getResultArray();

        $subsidies = $db->table('company_subsidies')
            ->where('company_cif', $targetCif)
            ->orderBy('fecha_concesion', 'DESC')
            ->get()->getResultArray();

        // 4. Cuota del usuario. Solo se consume ante acción explícita Y con dictamen disponible:
        //    nunca se cobra una consulta por una empresa sin perfil calculado.
        $effectiveUserId = (int)($userId ?? session('user_id') ?? 0);
        $riskQuota = $this->getQuotaStatus($effectiveUserId, $targetCif);

        if ($consume && $riskProfile) {
            // Gastar un crédito COMPRADO siempre requiere confirmación explícita:
            // se devuelve el estado bloqueado para que el usuario pulse el botón.
            if (($riskQuota['unlock_cost'] ?? null) !== 'credit') {
                $riskQuota = $this->consumeRiskView($effectiveUserId, $targetCif);
            }
        }

        return [
            'found'       => true,
            'cleanCif'    => $targetCif,
            'company'     => $company,
            'riskProfile' => $riskProfile,
            'contracts'   => $contracts,
            'subsidies'   => $subsidies,
            'riskQuota'   => $riskQuota
        ];
    }
}
