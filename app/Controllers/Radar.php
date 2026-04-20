<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\UsersuscriptionsModel;
use App\Models\UserFavoriteModel;

class Radar extends BaseController
{
    protected $companyModel;
    protected $subscriptionModel;
    protected $favoriteModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->subscriptionModel = new UsersuscriptionsModel();
        $this->favoriteModel = new UserFavoriteModel();
        helper('company');
    }

    /**
     * Dashboard principal del Radar Profesional
     */
    /**
     * Devuelve los datos de una empresa para el QuickView
     */
    public function quickView($id)
    {
        if (!session('logged_in')) {
            return $this->response->setStatusCode(401);
        }

        $userId = session('user_id');
        $activePlan = $this->subscriptionModel->getActivePlanByUserId($userId);
        $isFree = (!$activePlan || !in_array($activePlan->product_type, ['radar', 'bundle']));

        $company = $this->companyModel->find($id);

        if (!$company) {
            return '<div style="padding:48px; text-align:center; color:#64748b;">
                <p style="font-size:18px; font-weight:700; color:#1e293b; margin-bottom:8px;">Empresa no encontrada</p>
                <p>No se han podido recuperar los datos de esta empresa.</p>
                <button type="button" class="ae-qv__btn ae-qv__btn--text" onclick="closeQuickView()" style="margin-top:24px;">Cerrar</button>
            </div>';
        }

        // Obtener administradores
        $db = \Config\Database::connect();
        $admins = $db->table('company_administrators')
                    ->where('company_id', $id)
                    ->get()
                    ->getResultArray();

        // Filtrar registros que no son personas (metadatos de Sociedades Civiles, etc)
        $excludeKeywords = ['CAPITAL', 'DOMICILIO', 'OBJETO SOCIAL', 'OTROS CONCEPTOS', 'COMIENZO DE OPERACIONES', 'INSCRIPCION', 'RESULTANTE', 'SUSCRITO', 'EURO'];
        $filteredAdmins = [];
        $seenAdmins = []; // Para evitar duplicados

        foreach ($admins as $admin) {
            $name = strtoupper($admin['name'] ?? '');
            $pos = strtoupper($admin['position'] ?? '');
            $text = $name . ' ' . $pos;

            // 1. Excluir por palabras clave
            $exclude = false;
            foreach ($excludeKeywords as $kw) {
                if (strpos($text, $kw) !== false) {
                    $exclude = true;
                    break;
                }
            }
            if ($exclude) continue;

            // 2. Excluir si el nombre contiene números (ej: "111.463,00 Euros")
            if (preg_match('/[0-9]+/', $name)) continue;

            // 3. Excluir duplicados (Nombre + Cargo)
            $uniqueKey = md5($name . '|' . $pos);
            if (isset($seenAdmins[$uniqueKey])) continue;

            $seenAdmins[$uniqueKey] = true;
            $filteredAdmins[] = $admin;
        }
        $admins = $filteredAdmins;

        $data = [
            'co' => $company,
            'admins' => $admins,
            'isFree' => $isFree
        ];

        return view('radar/partials/company_quickview', $data);
    }

    /**
     * Página de Demo Interactiva (Pública)
     * Optimizada para conversión sin necesidad de login
     */
    public function demo()
    {
        $db = \Config\Database::connect();
        $metricsService = new \App\Libraries\RadarMetricsService();

        // 1. Selección de Empresas Curadas (Top Oportunidades B2B)
        // Buscamos empresas con Score > 80 y sectores B2B
        $builder = $this->companyModel->builder();
        $builder->select('
            companies.id, 
            companies.company_name, 
            companies.fecha_constitucion, 
            companies.cnae_label, 
            companies.registro_mercantil, 
            companies.municipality, 
            companies.objeto_social, 
            crs.score_total,
            crs.priority_level,
            crs.score_reasons
        ');
        $builder->join('company_radar_scores crs', 'crs.company_id = companies.id', 'inner');
        
        // Filtro de "Elite" para la demo
        $builder->where('crs.score_total >=', 82);
        $builder->whereIn('crs.priority_level', ['alta', 'muy_alta']);
        
        // Priorizar sectores B2B para mayor credibilidad de venta
        $builder->groupStart()
            ->like('companies.cnae_label', 'Tecnolog')
            ->orLike('companies.cnae_label', 'Consult')
            ->orLike('companies.cnae_label', 'Inform')
            ->orLike('companies.cnae_label', 'Marketing')
            ->orLike('companies.cnae_label', 'Constru')
            ->orLike('companies.cnae_label', 'Ingenier')
        ->groupEnd();

        $builder->orderBy('crs.score_total', 'DESC');
        $builder->limit(12);
        
        $companies = $builder->get()->getResultArray();

        // 2. Cálculos de Pipeline Dinámicos (basados en el volumen total del día para dar escala)
        $todayCount = $this->countNewCompanies('hoy');
        $pipelineVolume = $todayCount > 100 ? $todayCount : 206; // Usar 206 como default si hay pocos datos (según request)
        $pipelineMetrics = $metricsService->getMetrics($pipelineVolume);

        // 3. Preparar estrategias para la demo (reutilizando lógica de score_reasons)
        foreach ($companies as &$co) {
            $co['strategy'] = $this->generateDemoStrategy($co);
        }

        $data = [
            'companies' => $companies,
            'metrics'   => $pipelineMetrics,
            'opps_count' => $pipelineVolume,
            'title'     => 'Radar Demo - Oportunidades de Negocio en Tiempo Real'
        ];

        return view('radar/demo', $data);
    }

    /**
     * Genera una estrategia simplificada para la demo basada en los motivos del score
     */
    private function generateDemoStrategy($co)
    {
        $reasons = json_decode($co['score_reasons'] ?? '[]', true);
        $sector = $co['cnae_label'] ?? 'su sector';
        $location = $co['municipality'] ?? 'España';
        
        // Estrategia base con tono comercial directo
        return [
            'motivo' => !empty($reasons) ? $reasons[0] : "Empresa de nueva creación con alta prioridad en sector estratégico ($sector).",
            'que_vender' => "Servicios profesionales B2B, consultoría de expansión, suministros industriales o software de gestión operativa.",
            'objecion' => "No tenemos presupuesto asignado todavía.",
            'enfoque' => "Enfocarse en la 'ventaja del primer paso'. Al ser una empresa en fase de constitución, aún no han cerrado contratos fijos con proveedores locales en $location.",
            'mensaje' => "Hola, he visto que acabáis de registrar la sociedad para [Proyecto]. Enhorabuena por el lanzamiento en $location. Te escribo porque ayudamos a empresas de $sector a optimizar su fase de arranque reduciendo costes operativos en un 15%..."
        ];
    }

    public function index()
    {
        if (!session('logged_in')) {
            return view('seo/radar_prices');
        }

        $userId = session('user_id');
        
        // Verificación de suscripción activa
        $activePlan = $this->subscriptionModel->getActivePlanByUserId($userId);
        
        // Si no tiene plan activo o es de tipo API, lo marcamos como 'free' para el Radar
        $isFree = (!$activePlan || !in_array($activePlan->product_type, ['radar', 'bundle']));

        $isTemporary = false;
        $expiryTime = null;
        $hoursLeft = null;

        if (!$isFree && $activePlan) {
            // Info para el banner de acceso temporal
            $isTemporary = (isset($activePlan->product_type) && $activePlan->product_type === 'radar_single') || (isset($activePlan->plan_name) && strpos(strtolower($activePlan->plan_name), 'single') !== false);
            $expiryTime = $isTemporary ? strtotime($activePlan->current_period_end) : null;
            $hoursLeft = $expiryTime ? ceil(($expiryTime - time()) / 3600) : null;
        }

        $db = \Config\Database::connect();

        // Obtener filtros desde la URL
        $province = $this->request->getGet('provincia');
        $cnae = $this->request->getGet('cnae');
        $timeRange = $this->request->getGet('rango') ?? 'hoy'; // Default hoy

        // 1. Estadísticas Rápidas (National)
        $stats = [
            'hoy' => $this->countNewCompanies('hoy'),
            'semana' => $this->countNewCompanies('semana'),
            'mes' => $this->countNewCompanies('mes')
        ];

        // 2. Query Principal del Listado con Paginación
        $this->companyModel->select('
            companies.id, 
            companies.company_name, 
            companies.cif, 
            companies.fecha_constitucion, 
            companies.cnae_label, 
            companies.registro_mercantil, 
            companies.municipality, 
            companies.objeto_social, 
            companies.phone,
            companies.capital_social_raw,
            crs.score_total,
            crs.priority_level,
            crs.score_reasons,
            crs.main_act_type,
            crs.last_borme_date
        ');
        $this->companyModel->join('company_radar_scores crs', 'crs.company_id = companies.id', 'left');
        $this->companyModel->where('companies.fecha_constitucion IS NOT NULL');

        if ($province) {
            $this->companyModel->where('companies.registro_mercantil', strtoupper(str_replace('-', ' ', $province)));
        }
        if ($cnae) {
            if (is_numeric($cnae) && strlen($cnae) >= 2) {
                // Filtro por código directo (retrocompatibilidad o directo)
                $this->companyModel->like('companies.cnae_code', $cnae, 'after');
            } else {
                // Filtro por Sección (ID de cnae_sections)
                $this->companyModel->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = companies.cnae_code', 'left');
                $this->companyModel->join('cnae_subclasses', 'cnae_subclasses.name = cnae_2009_2025.label_2009', 'left');
                $this->companyModel->join('cnae_classes', 'cnae_classes.id = cnae_subclasses.parent_class_id', 'left');
                $this->companyModel->join('cnae_groups', 'cnae_groups.id = cnae_classes.parent_group_id', 'left');
                $this->companyModel->where('cnae_groups.parent_section_id', $cnae);
            }
        }

        // 2.2 Filtro por Palabras Clave (Nicho) en Objeto Social
        $q = $this->request->getGet('q');
        if ($q) {
            $this->companyModel->groupStart()
                ->like('companies.objeto_social', $q)
                ->orLike('companies.company_name', $q)
                ->groupEnd();
        }

        // 2.3 Nuevos Filtros Radar Scoring
        $priority = $this->request->getGet('priority_level');
        if ($priority) {
            $this->companyModel->where('crs.priority_level', $priority);
        }

        $actType = $this->request->getGet('main_act_type');
        if ($actType) {
            $this->companyModel->where('crs.main_act_type', $actType);
        }
        
        $minScore = $this->request->getGet('min_score');
        $intel = $this->request->getGet('intel');
        $ai = $this->request->getGet('ai');

        if ($minScore || $intel === 'active') {
            $scoreLimit = $minScore ? (int)$minScore : 70;
            $this->companyModel->where('crs.score_total >=', $scoreLimit);
        }

        // 2.4 Filtro por Estado CRM (Sin contactar, Seguimiento, etc)
        $status = $this->request->getGet('status');
        if ($status) {
            $this->companyModel->join('user_favorites uf_filter', 'uf_filter.company_id = companies.id AND uf_filter.user_id = ' . (int)$userId, 'left');
            if ($status === 'nuevo') {
                $this->companyModel->where('(uf_filter.status IS NULL OR uf_filter.status = "nuevo")');
            } else {
                $this->companyModel->where('uf_filter.status', $status);
            }
        }

        // Rango de tiempo
        $today = date('Y-m-d');
        if ($timeRange === 'hoy') {
            $dateLimit = $today;
        } else {
            $days = (int)$timeRange;
            $dateLimit = date('Y-m-d', strtotime("-$days days"));
        }
        $this->companyModel->where('companies.fecha_constitucion >=', $dateLimit);
        // Eliminamos la restricción <= $today para incluir "pre-constituciones" detectadas para los próximos días

        // Orden por defecto: Score Total y luego fecha
        $this->companyModel->orderBy('crs.score_total', 'DESC');
        $this->companyModel->orderBy('crs.last_borme_date', 'DESC');
        $this->companyModel->orderBy('companies.fecha_constitucion', 'DESC');
        $this->companyModel->orderBy('companies.id', 'DESC');

        // Paginación real
        $perPage = $this->request->getGet('per_page') ?? 20;
        if (!in_array($perPage, [20, 50, 100])) $perPage = 20;

        $companies = $this->companyModel->paginate($perPage);
        $pager = $this->companyModel->pager;

        // Metadatos de paginación para los contadores
        $totalCount = $pager->getTotal();
        $currentPage = $pager->getCurrentPage();
        $startRecord = ($currentPage - 1) * $perPage + 1;
        $endRecord = min($currentPage * $perPage, $totalCount);

        // 3. Cálculo de ROI y Pipeline (Refuerzo de Valor)
        $metricsService = new \App\Libraries\RadarMetricsService();
        $pipelineMetrics = $metricsService->getMetrics($totalCount);
        $todayMetrics = $metricsService->getMetrics($stats['hoy']);
        $intelMetrics = $metricsService->getMetrics(round(($freshness['todayCount'] ?? 250) * 0.15)); // Proporción de alta prob.

        // 4. Estadísticas de progreso CRM (Ajuste 1)
        $crmStats = ['contactado' => 0, 'seguimiento' => 0, 'nuevo' => 0];
        $statusCounts = $db->table('user_favorites uf')
            ->select('uf.status, COUNT(*) as total')
            ->join('companies c', 'c.id = uf.company_id')
            ->where('uf.user_id', $userId)
            ->where('c.fecha_constitucion >=', $dateLimit);
        
        if ($province) {
            $statusCounts->where('c.registro_mercantil', strtoupper(str_replace('-', ' ', $province)));
        }
        if ($q) {
            $statusCounts->groupStart()
                ->like('c.objeto_social', $q)
                ->orLike('c.company_name', $q)
                ->groupEnd();
        }
        
        $statResults = $statusCounts->groupBy('uf.status')->get()->getResultArray();
        $totalManaged = 0;
        foreach ($statResults as $r) {
            $st = $r['status'] ?: 'nuevo';
            if (array_key_exists($st, $crmStats)) {
                $crmStats[$st] = (int)$r['total'];
            }
            $totalManaged += (int)$r['total'];
        }
        $crmStats['nuevo'] = max(0, $totalCount - $totalManaged);

        // Inyectar Score de calidad y estado de favorito a cada empresa
        $favoriteMap = $this->favoriteModel->getFavoriteMap($userId);
        $followingIds = array_column((new \App\Models\LeadFollowupModel())->where('user_id', $userId)->findAll(), 'company_id');
        
        // [ENGAGEMENT] Obtener mapa de interacción para booster de score
        $leadIds = array_column($companies, 'id');
        $engagementMap = $this->getEngagementMap($userId, $leadIds);
        
        // [LEARNING] Mapa de aprendizaje grupal (Sector + Provincia)
        $groupMap = $this->getGroupEngagementMap();
        
        // [PERSONALIZATION] Mapa de preferencias individuales (Aprendizaje de IA por usuario)
        $userPrefMap = $this->getUserPersonalizationMap($userId);

        foreach ($companies as &$co) {
            $groupKey = strtolower(trim($co['cnae_label'] ?? '')) . '|' . strtolower(trim($co['registro_mercantil'] ?? ''));
            $groupScore = $groupMap[$groupKey] ?? 0;
            $userScore = $userPrefMap[$groupKey] ?? 0;
            
            $co['lead_score_data'] = $this->getLeadScore($co, $engagementMap[$co['id']] ?? 0, $groupScore, $userScore);
            $co['lead_score'] = $co['lead_score_data']['label'];
            $co['status'] = $favoriteMap[$co['id']] ?? 'nuevo';
            $co['is_favorite'] = isset($favoriteMap[$co['id']]);
            $co['is_following'] = in_array($co['id'], $followingIds);
        }

        // [RANKING] Re-ordenar en PHP por score FINAL (Incluyendo boosters que el SQL no ve)
        usort($companies, function($a, $b) {
            return ($b['lead_score_data']['numeric'] ?? 0) <=> ($a['lead_score_data']['numeric'] ?? 0);
        });

        // 4. Datos para Filtros
        $provinces = $db->query("SELECT province as name FROM seo_stats ORDER BY total_companies DESC LIMIT 52")->getResultArray();
        
        $data = [
            'stats' => $stats,
            'crmStats' => $crmStats,
            'metricsService' => $metricsService,
            'pipelineMetrics' => $pipelineMetrics,
            'todayMetrics' => $todayMetrics,
            'intelMetrics' => $intelMetrics,
            'companies' => $companies,
            'pager' => $pager,
            'provinces' => $provinces,
            'filters' => [
                'provincia' => $province,
                'cnae' => $cnae,
                'rango' => $timeRange,
                'q' => $q,
                'per_page' => $perPage,
                'priority_level' => $priority,
                'main_act_type' => $actType,
                'min_score' => $minScore
            ],
            'pagination' => [
                'total' => $totalCount,
                'start' => $startRecord,
                'end' => $endRecord
            ],
            'isFree' => $isFree,
            'userPlan' => [
                'isTemporary' => $isTemporary,
                'hoursLeft' => $hoursLeft,
                'planName' => $activePlan ? $activePlan->plan_name : 'Gratuito',
                'status' => $activePlan ? $activePlan->status : 'none',
                'period_end' => $activePlan ? $activePlan->current_period_end : null,
            ],
            'freshness' => [
                'lastUpdate' => date('H:i', strtotime('-12 minutes')),
                'todayCount' => $stats['hoy']
            ]
        ];
        if ($this->request->isAJAX()) {
            return view('radar/partials/results_table', $data);
        }

        return view('radar/dashboard', $data);
    }

    /**
     * Contador rápido de empresas nuevas por periodo
     */
    private function countNewCompanies($period)
    {
        $builder = $this->companyModel->builder();
        if ($period === 'hoy') {
            $builder->where('fecha_constitucion >=', date('Y-m-d'));
        } elseif ($period === 'semana') {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')));
        } elseif ($period === 'mes') {
            $builder->where('fecha_constitucion >=', date('Y-m-01'));
        }
        return $builder->countAllResults();
    }

    /**
     * Alternar una empresa como favorita (AJAX)
     */
    public function toggleFavorite()
    {
        if (!session('logged_in')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $userId = session('user_id');
        $companyId = $this->request->getPost('company_id');

        if (!$companyId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Missing company id']);
        }

        $existing = $this->favoriteModel->where(['user_id' => $userId, 'company_id' => $companyId])->first();

        if ($existing) {
            $this->favoriteModel->delete($existing['id']);
            $status = 'removed';
            $id = null;
        } else {
            $id = $this->favoriteModel->insert([
                'user_id' => $userId,
                'company_id' => $companyId,
            ]);
            $status = 'added';
        }

        return $this->response->setJSON([
            'status' => 'success', 
            'favorite_status' => $status,
            'id' => $id
        ]);
    }

    /**
     * Guardar una nota privada para un favorito (AJAX)
     */
    public function saveNote()
    {
        if (!session('logged_in')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $userId = session('user_id');
        $companyId = $this->request->getPost('company_id');
        $notes = $this->request->getPost('notes');

        $favorite = $this->favoriteModel->where(['user_id' => $userId, 'company_id' => $companyId])->first();

        if (!$favorite) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Favorite not found']);
        }

        $this->favoriteModel->update($favorite['id'], ['notes' => $notes]);

        return $this->response->setJSON(['status' => 'success']);
    }

    /**
     * Listado de favoritos del usuario
     */
    public function favorites()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = session('user_id');
        
        // Parámetros de filtrado y paginación
        $search = $this->request->getGet('search') ?? '';
        $status = $this->request->getGet('status') ?? 'all';
        $page = (int) ($this->request->getGet('page') ?? 1);
        
        // Si no está en GET (carga inicial), probar en POST (fallback)
        if (!$search) $search = $this->request->getPost('search') ?? '';
        if ($status === 'all') $status = $this->request->getPost('status') ?? 'all';

        $limit = 12;
        $offset = ($page - 1) * $limit;

        $params = [
            'search' => $search,
            'status' => $status,
            'limit' => $limit,
            'offset' => $offset
        ];

        $favorites = $this->favoriteModel->getFavoritesWithCompanyData($userId, $params);
        $totalItems = $this->favoriteModel->countFilteredFavorites($userId, $params);
        $totalPages = ceil($totalItems / $limit);

        // [ENGAGEMENT] Mapa de booster
        $leadIds = array_column($favorites, 'id');
        $engagementMap = $this->getEngagementMap($userId, $leadIds);
        
        // [LEARNING] Mapa grupal
        $groupMap = $this->getGroupEngagementMap();
        
        // [PERSONALIZATION] Mapa individual
        $userPrefMap = $this->getUserPersonalizationMap($userId);

        // Añadir lead_score a cada favorito
        foreach ($favorites as &$f) {
            $groupKey = strtolower(trim($f['cnae_label'] ?? '')) . '|' . strtolower(trim($f['registro_mercantil'] ?? ''));
            $groupScore = $groupMap[$groupKey] ?? 0;
            $userScore = $userPrefMap[$groupKey] ?? 0;

            $f['lead_score_data'] = $this->getLeadScore($f, $engagementMap[$f['id']] ?? 0, $groupScore, $userScore);
            $f['lead_score'] = $f['lead_score_data']['label'];
        }

        // [RANKING] Re-ordenar por score dinámico
        usort($favorites, function($a, $b) {
            return ($b['lead_score_data']['numeric'] ?? 0) <=> ($a['lead_score_data']['numeric'] ?? 0);
        });

        $data = [
            'favorites' => $favorites,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'currentStatus' => $status,
            'currentSearch' => $search,
            'title' => 'Mis Favoritos - Radar'
        ];

        if ($this->request->isAJAX()) {
            return view('radar/partials/favorites_list', $data);
        }

        return view('radar/favorites', $data);
    }


    /**
     * Vista de Embudo de Ventas (Kanban)
     */
    public function kanban()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = session('user_id');
        $favorites = $this->favoriteModel->getFavoritesWithCompanyData($userId);

        // Agrupar por estado
        $columns = [
            'nuevo' => [],
            'contactado' => [],
            'negociacion' => [],
            'ganado' => [],
            'seguimiento' => []
        ];

        // [ENGAGEMENT] Mapa de booster
        $leadIds = array_column($favorites, 'id');
        $engagementMap = $this->getEngagementMap($userId, $leadIds);
        
        // [LEARNING] Mapa grupal
        $groupMap = $this->getGroupEngagementMap();
        
        // [PERSONALIZATION] Mapa individual
        $userPrefMap = $this->getUserPersonalizationMap($userId);

        foreach ($favorites as &$f) {
            $groupKey = strtolower(trim($f['cnae_label'] ?? '')) . '|' . strtolower(trim($f['registro_mercantil'] ?? ''));
            $groupScore = $groupMap[$groupKey] ?? 0;
            $userScore = $userPrefMap[$groupKey] ?? 0;

            $f['lead_score_data'] = $this->getLeadScore($f, $engagementMap[$f['id']] ?? 0, $groupScore, $userScore);
            $f['lead_score'] = $f['lead_score_data']['label'];
            
            $status = $f['status'] ?: 'nuevo';
            if (isset($columns[$status])) {
                $columns[$status][] = $f;
            } else {
                $columns['nuevo'][] = $f;
            }
        }

        // [RANKING] Re-ordenar dentro de cada columna del Kanban
        foreach ($columns as &$items) {
            usort($items, function($a, $b) {
                return ($b['lead_score_data']['numeric'] ?? 0) <=> ($a['lead_score_data']['numeric'] ?? 0);
            });
        }

        $data = [
            'columns' => $columns,
            'title' => 'Embudo de Ventas - Radar PRO'
        ];

        return view('radar/kanban', $data);
    }

    /**
     * Actualizar el estado de un favorito (AJAX para Kanban)
     */
    public function updateFavoriteStatus()
    {
        if (!session('logged_in')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        try {
            $userId = (int) session('user_id');
            $favoriteId = $this->request->getPost('favorite_id');
            $companyId = $this->request->getPost('company_id');
            $status = $this->request->getPost('status');

            $allowedStatuses = ['nuevo', 'contactado', 'negociacion', 'ganado', 'seguimiento'];
            if (!in_array($status, $allowedStatuses)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid status: ' . $status]);
            }

            if ($favoriteId) {
                $favorite = $this->favoriteModel->where(['id' => (int)$favoriteId, 'user_id' => $userId])->first();
            } elseif ($companyId) {
                $favorite = $this->favoriteModel->where(['company_id' => (int)$companyId, 'user_id' => $userId])->first();
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Missing both favorite_id and company_id']);
            }

            if (!$favorite) {
                if ($companyId) {
                    $insertId = $this->favoriteModel->insert([
                        'user_id' => $userId,
                        'company_id' => (int)$companyId,
                        'status' => $status,
                        'notes' => ''
                    ]);
                    
                    if (!$insertId) {
                        $error = $this->favoriteModel->errors();
                        log_message('error', '[Radar::updateFavoriteStatus] Insert failed: ' . json_encode($error));
                        return $this->response->setJSON(['status' => 'error', 'message' => 'Could not create favorite', 'details' => $error]);
                    }
                    
                    return $this->response->setJSON(['status' => 'success', 'id' => $insertId, 'action' => 'created']);
                }
                return $this->response->setJSON(['status' => 'error', 'message' => 'Record not found and no company_id provided']);
            }

            $ok = $this->favoriteModel->update($favorite['id'], ['status' => $status]);
            if (!$ok) {
                $error = $this->favoriteModel->errors();
                log_message('error', '[Radar::updateFavoriteStatus] Update failed: ' . json_encode($error));
                return $this->response->setJSON(['status' => 'error', 'message' => 'Could not update status', 'details' => $error]);
            }

            // [TRACKING] Registrar cambios a estados clave
            if ($status === 'contactado') {
                $this->logLeadEvent($userId, $companyId ?: ($favorite['company_id'] ?? null), 'status_contacted');
            } elseif ($status === 'seguimiento') {
                $this->logLeadEvent($userId, $companyId ?: ($favorite['company_id'] ?? null), 'status_followup');
            }

            return $this->response->setJSON(['status' => 'success', 'action' => 'updated']);

        } catch (\Throwable $e) {
            log_message('error', '[Radar::updateFavoriteStatus] Exception: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Server error', 
                'debug' => $e->getMessage()
            ]);
        }
    }

    /**
     * Exportar resultados actuales a CSV
     */
    /**
     * Exportar resultados actuales (CSV o Excel con estilo)
     */
    public function export()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = session('user_id');
        $activePlan = $this->subscriptionModel->getActivePlanByUserId($userId);
        
        if (!$activePlan || !in_array($activePlan->product_type, ['radar', 'bundle'])) {
            return redirect()->to(site_url('leads-empresas-nuevas'))->with('message', 'La exportación requiere un plan activo.');
        }

        $format = $this->request->getGet('format') ?? 'csv';

        // Obtener filtros
        $province = $this->request->getGet('provincia');
        $cnae = $this->request->getGet('cnae');
        $timeRange = $this->request->getGet('rango') ?? 'hoy';
        $q = $this->request->getGet('q');

        $builder = $this->companyModel->builder();
        $builder->select('
            companies.company_name, 
            companies.cif, 
            companies.fecha_constitucion, 
            companies.cnae_label, 
            companies.municipality, 
            companies.phone,
            crs.score_total,
            crs.priority_level,
            crs.score_reasons,
            crs.main_act_type,
            crs.last_borme_date
        ');
        $builder->join('company_radar_scores crs', 'crs.company_id = companies.id', 'left');
        $builder->where('companies.fecha_constitucion IS NOT NULL');

        if ($province) {
            $builder->where('companies.registro_mercantil', strtoupper(str_replace('-', ' ', $province)));
        }
        if ($q) {
            $builder->groupStart()
                ->like('companies.objeto_social', $q)
                ->orLike('companies.company_name', $q)
                ->groupEnd();
        }

        // Nuevos Filtros Radar Scoring en Exportación
        $priority = $this->request->getGet('priority_level');
        if ($priority) {
            $builder->where('crs.priority_level', $priority);
        }

        $actType = $this->request->getGet('main_act_type');
        if ($actType) {
            $builder->where('crs.main_act_type', $actType);
        }

        $today = date('Y-m-d');
        if ($timeRange === 'hoy') {
            $builder->where('companies.fecha_constitucion >=', $today);
        } else {
            $days = (int)$timeRange;
            $builder->where('companies.fecha_constitucion >=', date('Y-m-d', strtotime("-$days days")));
        }
        // Eliminamos restricción <= $today para coherencia con la vista
        
        $companies = $builder->orderBy('crs.score_total', 'DESC')
                            ->orderBy('crs.last_borme_date', 'DESC')
                            ->orderBy('companies.fecha_constitucion', 'DESC')
                            ->get()
                            ->getResultArray();

        if ($format === 'excel') {
            return $this->generateExcel($companies);
        }

        return $this->generateCsv($companies);
    }

    private function generateCsv($companies)
    {
        $filename = 'radar_export_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM

        fputcsv($output, ['EMPRESA', 'CIF', 'CONSTITUCION', 'SCORE_TOTAL', 'PRIORIDAD', 'MOTIVOS', 'TIPO_ACTO', 'ULTIMO_BORME', 'SECTOR', 'MUNICIPIO', 'TELEFONO']);

        foreach ($companies as $co) {
            $row = [
                $co['company_name'],
                $co['cif'],
                $co['fecha_constitucion'],
                $co['score_total'] ?? '-',
                $co['priority_level'] ?? 'sin_clasificar',
                $co['score_reasons'] ?? '-',
                $co['main_act_type'] ?? '-',
                $co['last_borme_date'] ?? '-',
                $co['cnae_label'],
                $co['municipality'],
                $co['phone']
            ];
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    private function generateExcel($companies)
    {
        $filename = 'radar_export_' . date('Y-m-d') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        echo '<table border="1">';
        echo '<thead>';
        echo '<tr style="background-color: #2563eb; color: #ffffff; font-weight: bold;">';
        echo '<th>EMPRESA</th>';
        echo '<th>CIF</th>';
        echo '<th>CONSTITUCION</th>';
        echo '<th>SCORE</th>';
        echo '<th>PRIORIDAD</th>';
        echo '<th>MOTIVOS</th>';
        echo '<th>TIPO ACTO</th>';
        echo '<th>ULT. BORME</th>';
        echo '<th>SECTOR</th>';
        echo '<th>MUNICIPIO</th>';
        echo '<th>TELEFONO</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        foreach ($companies as $co) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($co['company_name']) . '</td>';
            echo '<td>' . htmlspecialchars($co['cif']) . '</td>';
            echo '<td>' . htmlspecialchars($co['fecha_constitucion']) . '</td>';
            echo '<td>' . ($co['score_total'] ?? '-') . '</td>';
            echo '<td>' . ($co['priority_level'] ?? 'sin_clasificar') . '</td>';
            echo '<td>' . htmlspecialchars($co['score_reasons'] ?? '-') . '</td>';
            echo '<td>' . htmlspecialchars($co['main_act_type'] ?? '-') . '</td>';
            echo '<td>' . ($co['last_borme_date'] ?? '-') . '</td>';
            echo '<td>' . htmlspecialchars($co['cnae_label']) . '</td>';
            echo '<td>' . htmlspecialchars($co['municipality']) . '</td>';
            echo '<td>="' . htmlspecialchars($co['phone']) . '"</td>'; // Force string for phone numbers
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        exit;
    }

    /**
     * Obtener datos para la visualización del mapa (AJAX)
     */
    public function mapData()
    {
        if (!session('logged_in')) {
            return $this->response->setJSON([]);
        }

        $province = $this->request->getGet('provincia');
        $cnae = $this->request->getGet('cnae');
        $timeRange = $this->request->getGet('rango') ?? 'hoy';
        $q = $this->request->getGet('q');

        $builder = $this->companyModel->builder();
        $builder->select('companies.registro_mercantil as province, COUNT(*) as total');
        $builder->join('company_radar_scores crs', 'crs.company_id = companies.id', 'left');
        $builder->where('companies.registro_mercantil IS NOT NULL');
        $builder->where('crs.last_borme_date IS NOT NULL');

        // Aplicar mismos filtros que en el listado
        if ($province) {
            $builder->where('companies.registro_mercantil', strtoupper(str_replace('-', ' ', $province)));
        }
        if ($cnae) {
            // Simplificado para el mapa
            $builder->like('companies.cnae_code', $cnae, 'after');
        }
        if ($q) {
            $builder->groupStart()
                ->like('companies.objeto_social', $q)
                ->orLike('companies.company_name', $q)
                ->groupEnd();
        }

        $today = date('Y-m-d');
        if ($timeRange === 'hoy') {
            $builder->where('crs.last_borme_date >=', $today);
        } else {
            $days = (int)$timeRange;
            $builder->where('crs.last_borme_date >=', date('Y-m-d', strtotime("-$days days")));
        }

        $results = $builder->groupBy('companies.registro_mercantil')
                          ->orderBy('total', 'DESC')
                          ->get()
                          ->getResultArray();

        return $this->response->setJSON($results);
    }

    /**
     * Calcula un score de calidad para el lead (A, B, C) con booster de interacción real e inteligencia grupal/personalizado
     */
    private function getLeadScore($co, $engagementScore = 0, $groupScore = 0, $userScore = 0)
    {
        // 1. Algoritmo base (Fiel a la base de datos authoritative)
        $dbScore = (int)($co['score_total'] ?? 0);
        
        // Si no tenemos score en BD, calculamos el fallback dinámico
        if ($dbScore <= 0) {
            $baseScore = 50;
            if (!empty($co['phone'])) $baseScore += 30;
            if (!empty($co['cif']) && (!empty($co['cif'][0]) && $co['cif'][0] === 'B')) $baseScore += 10;
            if (strlen($co['objeto_social'] ?? '') > 150) $baseScore += 10;
        } else {
            $baseScore = $dbScore;
        }

        // 2. Capa de Engagement Real (Booster suave personal del usuario por Lead específico)
        $adjEngagement = min((int)$engagementScore, 50);
        
        // 3. Capa de Aprendizaje Grupal (Booster de sector/provincia global)
        $adjGroupBoost = min((int)$groupScore, 100);
        
        // 4. Capa de Personalización Individual (Booster de IA aprendida por Usuario según sus nichos favoritos)
        $adjUserPref = min((int)$userScore, 100);
        // FÓRMULA ÓPTIMA (Elastic Weighted): 80% Base + 20% Dynamic Boosters
        $dynamicBoosters = ($adjEngagement * 0.4) + ($adjGroupBoost * 0.3) + ($adjUserPref * 0.3);
        $finalScore = max(0, min(100, ($baseScore * 0.8) + ($dynamicBoosters * 0.2)));

        // 5. Proyección a Categorías Visuales (Umbrales 85/70/40 sincronizados con results_table.php)
        $scoreColor = '#94a3b8'; $scoreProb = 'POTENCIAL MEDIO'; $scoreIcon = '⚪'; $scoreBg = 'rgba(148, 163, 184, 0.1)';
        
        if ($finalScore >= 85) {
            $scoreColor = '#ef4444'; $scoreBg = 'rgba(239, 68, 68, 0.1)'; $scoreProb = 'LEAD CALIENTE'; $scoreIcon = '🔥';
        } elseif ($finalScore >= 70) {
            $scoreColor = '#f59e0b'; $scoreBg = 'rgba(245, 158, 11, 0.1)'; $scoreProb = 'OPORTUNIDAD ALTA'; $scoreIcon = '🟡';
        } elseif ($finalScore >= 40) {
            $scoreColor = '#10b981'; $scoreBg = 'rgba(16, 185, 129, 0.1)'; $scoreProb = 'CONTACTAR AHORA'; $scoreIcon = '🟢';
        }
        
        return [
            'label' => $scoreProb,
            'numeric' => $finalScore,
            'base' => $baseScore,
            'color' => $scoreColor,
            'icon' => $scoreIcon
        ];
    }

    /**
     * [PERSONALIZATION] Calcula el mapa de preferencias aprendidas para el usuario (con caché y umbral)
     */
    protected function getUserPersonalizationMap($userId)
    {
        if (!$userId) return [];
        
        $cacheKey = "radar_user_pref_{$userId}_v1";
        $cached = cache($cacheKey);
        if ($cached) return $cached;

        try {
            $db = \Config\Database::connect();
            
            // 1. Umbral de Aprendizaje: El usuario debe tener al menos 20 eventos totales registrados
            $totalUserEvents = $db->table('radar_lead_events')
                ->where('user_id', $userId)
                ->countAllResults();
            
            if ($totalUserEvents < 20) return [];

            // 2. Agrupar interacciones específicas del usuario por Sector + Provincia
            $userEngaged = $db->table('radar_lead_events rle')
                ->join('companies c', 'c.id = rle.lead_id')
                ->select("c.cnae_label, c.registro_mercantil, COUNT(DISTINCT c.id) as engaged_count")
                ->where('rle.user_id', $userId)
                ->groupBy('c.cnae_label, c.registro_mercantil')
                ->get()
                ->getResultArray();

            // 3. Calcular engagement_rate relativo del usuario frente al volumen del grupo
            $finalUserMap = [];
            foreach ($userEngaged as $ue) {
                // Denominador: Total de leads en este nicho (para normalizar el interés)
                $groupTotal = $db->table('companies')
                    ->where('cnae_label', $ue['cnae_label'])
                    ->where('registro_mercantil', $ue['registro_mercantil'])
                    ->countAllResults();
                
                if ($groupTotal > 0) {
                    $key = strtolower(trim($ue['cnae_label'])) . '|' . strtolower(trim($ue['registro_mercantil']));
                    $rate = (int)($ue['engaged_count'] / $groupTotal * 100);
                    $finalUserMap[$key] = $rate;
                }
            }

            // Guardar en caché 12h (específico por usuario)
            cache()->save($cacheKey, $finalUserMap, 43200);
            return $finalUserMap;
            
        } catch (\Throwable $e) {
            log_message('error', "[Radar::getUserPersonalizationMap] Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * [LEARNING] Calcula el mapa de engagement grupal por sector y provincia (con caché)
     */
    protected function getGroupEngagementMap()
    {
        $cacheKey = 'radar_group_learning_map_v1';
        $cached = cache($cacheKey);
        if ($cached) return $cached;

        try {
            $db = \Config\Database::connect();
            
            // 1. Volumen total por grupo (Sector + Provincia)
            $totals = $db->table('companies')
                ->select("cnae_label, registro_mercantil, COUNT(id) as total")
                ->where('cnae_label IS NOT NULL')
                ->where('registro_mercantil IS NOT NULL')
                ->groupBy('cnae_label, registro_mercantil')
                ->get()
                ->getResultArray();

            // 2. Leads con interacciones reales por grupo
            $engaged = $db->table('radar_lead_events rle')
                ->join('companies c', 'c.id = rle.lead_id')
                ->select("c.cnae_label, c.registro_mercantil, COUNT(DISTINCT c.id) as engaged_count")
                ->groupBy('c.cnae_label, c.registro_mercantil')
                ->get()
                ->getResultArray();

            // 3. Mapear participaciones para cruce rápido
            $engagedMap = [];
            foreach ($engaged as $e) {
                $key = strtolower(trim($e['cnae_label'])) . '|' . strtolower(trim($e['registro_mercantil']));
                $engagedMap[$key] = (int)$e['engaged_count'];
            }

            // 4. Calcular tasa de éxito (Rate) y generar mapa final
            $finalMap = [];
            foreach ($totals as $t) {
                $totalInGroup = (int)$t['total'];
                if ($totalInGroup < 20) continue; // Escudo estadístico: grupos pequeños no influyen

                $key = strtolower(trim($t['cnae_label'])) . '|' . strtolower(trim($t['registro_mercantil']));
                $countEngaged = $engagedMap[$key] ?? 0;
                
                $rate = ($countEngaged > 0) ? ($countEngaged / $totalInGroup) : 0;
                $finalMap[$key] = (int)($rate * 100); // 0-100 score
            }

            // Guardar en caché 24h
            cache()->save($cacheKey, $finalMap, 86400);
            return $finalMap;
            
        } catch (\Throwable $e) {
            log_message('error', '[Radar::getGroupEngagementMap] Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * [TRACKING] Calcula un mapa de engagement para una lista de leads
     */
    protected function getEngagementMap($userId, $leadIds)
    {
        if (empty($leadIds)) return [];

        try {
            $db = \Config\Database::connect();
            $results = $db->table('radar_lead_events')
                ->select("lead_id, 
                    SUM(
                        (CASE 
                            WHEN action = 'view_strategy' THEN 10 
                            WHEN action = 'click_contact' THEN 30
                            WHEN action = 'status_contacted' THEN 40
                            WHEN action = 'status_followup' THEN 60
                            ELSE 0 END)
                        *
                        (CASE 
                            WHEN DATEDIFF(NOW(), created_at) <= 3 THEN 1
                            WHEN DATEDIFF(NOW(), created_at) <= 7 THEN 0.6
                            WHEN DATEDIFF(NOW(), created_at) <= 14 THEN 0.3
                            ELSE 0.1 END)
                    ) as score")
                ->where('user_id', $userId)
                ->whereIn('lead_id', $leadIds)
                ->groupBy('lead_id')
                ->get()
                ->getResultArray();

            $map = [];
            foreach ($results as $row) {
                $map[$row['lead_id']] = (int)$row['score'];
            }
            return $map;
        } catch (\Throwable $e) {
            log_message('error', '[Radar::getEngagementMap] Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * [TRACKING] Registra un evento de interacción con un lead en radar_lead_events
     */
    protected function logLeadEvent($userId, $leadId, $action)
    {
        if (!$userId || !$leadId || !$action) {
            return false;
        }

        try {
            $db = \Config\Database::connect();
            return $db->table('radar_lead_events')->insert([
                'user_id'    => (int)$userId,
                'lead_id'    => (int)$leadId,
                'action'     => $action,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[Radar::logLeadEvent] Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * [AJAX API] Endpoint ligero para registrar eventos desde el frontend
     */
    public function logEvent()
    {
        if (!session('logged_in')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado']);
        }

        $userId = session('user_id');
        $leadId = $this->request->getPost('lead_id');
        $action = $this->request->getPost('action');

        if (!$leadId || !$action) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Faltan parámetros']);
        }

        $ok = $this->logLeadEvent($userId, $leadId, $action);

        return $this->response->setJSON([
            'status' => $ok ? 'success' : 'error',
            'logged' => $ok
        ]);
    }

    /**
     * Vista principal de Análisis de Tendencias
     */
    public function trends()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = session('user_id');
        $activePlan = $this->subscriptionModel->getActivePlanByUserId($userId);
        $isFree = (!$activePlan || !in_array($activePlan->product_type, ['radar', 'bundle']));

        if ($isFree) {
            return redirect()->to(site_url('leads-empresas-nuevas'))->with('message', 'El análisis de tendencias requiere un plan Radar PRO activo.');
        }

        $db = \Config\Database::connect();
        $provinces = $db->query("SELECT province as name FROM seo_stats ORDER BY total_companies DESC LIMIT 52")->getResultArray();
        $sections = $db->query("SELECT id, name FROM cnae_sections ORDER BY name ASC")->getResultArray();

        $data = [
            'title' => 'Análisis de Tendencias - Radar PRO',
            'provinces' => $provinces,
            'sections' => $sections
        ];

        return view('radar/trends', $data);
    }

    /**
     * Obtener datos históricos para gráficas (AJAX)
     */
    public function getTrendData()
    {
        if (!session('logged_in')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $db = \Config\Database::connect();
        $province = $this->request->getGet('provincia');
        $sectionId = $this->request->getGet('seccion');

        // 1. Evolución Mensual (últimos 12 meses, excluyendo futuro)
        $builder = $db->table('companies');
        $builder->select("DATE_FORMAT(fecha_constitucion, '%Y-%m') as month, COUNT(*) as total");
        $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-12 months')));
        $builder->where('fecha_constitucion <=', date('Y-m-d'));

        if ($province) {
            $builder->where('registro_mercantil', strtoupper(str_replace('-', ' ', $province)));
        }

        if ($sectionId) {
            $builder->join('cnae_2009_2025', 'CONVERT(cnae_2009_2025.cnae_2009 USING utf8mb4) = CONVERT(companies.cnae_code USING utf8mb4)', 'inner');
            $builder->join('cnae_subclasses', 'CONVERT(cnae_subclasses.name USING utf8mb4) = CONVERT(cnae_2009_2025.label_2009 USING utf8mb4)', 'inner');
            $builder->join('cnae_classes', 'cnae_classes.id = cnae_subclasses.parent_class_id', 'inner');
            $builder->join('cnae_groups', 'cnae_groups.id = cnae_classes.parent_group_id', 'inner');
            $builder->where('cnae_groups.parent_section_id', $sectionId);
        }

        $results = $builder->groupBy('month')
                               ->orderBy('month', 'ASC')
                               ->get()
                               ->getResultArray();

        // 1.1 Rellenar huecos para que siempre haya 12 meses
        $evolutionRaw = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = date('Y-m', strtotime("-$i months"));
            $found = false;
            foreach ($results as $row) {
                if ($row['month'] === $m) {
                    $evolutionRaw[] = $row;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $evolutionRaw[] = ['month' => $m, 'total' => 0];
            }
        }

        // 2. Comparativa por Sectores Top (últimos 6 meses, excluyendo futuro)
        $sectorsBuilder = $db->table('cnae_sections s');
        $sectorsBuilder->select('s.name as label, COUNT(c.id) as total');
        $sectorsBuilder->join('cnae_groups g', 'g.parent_section_id = s.id');
        $sectorsBuilder->join('cnae_classes cl', 'cl.parent_group_id = g.id');
        $sectorsBuilder->join('cnae_subclasses sub', 'sub.parent_class_id = cl.id');
        $sectorsBuilder->join('cnae_2009_2025 c2', 'CONVERT(c2.label_2009 USING utf8mb4) = CONVERT(sub.name USING utf8mb4)');
        $sectorsBuilder->join('companies c', 'CONVERT(c.cnae_code USING utf8mb4) = CONVERT(c2.cnae_2009 USING utf8mb4)');
        $sectorsBuilder->where('c.fecha_constitucion >=', date('Y-m-d', strtotime('-6 months')));
        $sectorsBuilder->where('c.fecha_constitucion <=', date('Y-m-d'));
        
        if ($province) {
            $sectorsBuilder->where('c.registro_mercantil', strtoupper(str_replace('-', ' ', $province)));
        }
        
        $sectorsRaw = $sectorsBuilder->groupBy('s.id, s.name')
                                    ->orderBy('total', 'DESC')
                                    ->limit(8)
                                    ->get()
                                    ->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'evolution' => $evolutionRaw,
            'sectors' => $sectorsRaw
        ]);
    }

    /**
     * Análisis IA bajo demanda del objeto social
     */
    public function aiAnalyze($id)
    {
        if (!session('logged_in')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado']);
        }

        $company = $this->companyModel
            ->select('companies.*, company_radar_scores.score_total, company_radar_scores.priority_level, company_radar_scores.score_reasons, company_radar_scores.main_act_type, company_radar_scores.last_borme_date')
            ->join('company_radar_scores', 'companies.id = company_radar_scores.company_id', 'left')
            ->find($id);

        if (!$company) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Empresa no encontrada']);
        }

        try {
            $analysis = \App\Libraries\RadarAnalyzer::analyze($company);
            
            // [TRACKING] Registro de clic en Estrategia
            $this->logLeadEvent(session('user_id'), $id, 'view_strategy');

            return $this->response->setJSON(['status' => 'success'] + $analysis);
        } catch (\Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Error al analizar: ' . $e->getMessage()]);
        }
    }

    /**
     * Prepara un lead para contacto posterior (seguimiento)
     */
    public function prepareContact($companyId)
    {
        if (!session('logged_in')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado']);
        }

        $userId = session('user_id');
        $messageBody = $this->request->getPost('message');
        $notes = $this->request->getPost('notes') ?? 'Lead preparado desde Modal IA';

        $fModel = new \App\Models\LeadFollowupModel();
        $mModel = new \App\Models\LeadPreparedMessageModel();

        // 1. Upsert Followup
        $followup = $fModel->getFollowup($userId, $companyId);
        $followupData = [
            'user_id'             => $userId,
            'company_id'          => $companyId,
            'status'              => 'seguimiento',
            'notify_when_contact' => 1,
            'prepared_at'         => date('Y-m-d H:i:s'),
            'notes'               => $notes
        ];

        if ($followup) {
            $fModel->update($followup['id'], $followupData);
        } else {
            $fModel->insert($followupData);
            
            // 1.1 Unificar con Favoritos (como sugirió el usuario)
            $favModel = new \App\Models\UserFavoriteModel();
            $existingFav = $favModel->where(['user_id' => $userId, 'company_id' => $companyId])->first();
            if (!$existingFav) {
                $favModel->insert([
                    'user_id'    => $userId,
                    'company_id' => $companyId,
                    'status'     => 'seguimiento',
                    'notes'      => 'Lead preparado para seguimiento (vía IA Modal)'
                ]);
            }
        }

        // 2. Upsert Message
        if ($messageBody) {
            $msg = $mModel->getMessage($userId, $companyId);
            $msgData = [
                'user_id'      => $userId,
                'company_id'   => $companyId,
                'message_type' => 'initial_contact',
                'message_body' => $messageBody,
                'source'       => 'ia_modal'
            ];

            if ($msg) {
                $mModel->update($msg['id'], $msgData);
            } else {
                $mModel->insert($msgData);
            }
        }

        return $this->response->setJSON([
            'status'              => 'success',
            'followup_status'     => 'seguimiento',
            'message_saved'       => true,
            'notify_when_contact' => true,
            'next_step'           => 'Contactar en cuanto aparezca web o teléfono'
        ]);
    }
}