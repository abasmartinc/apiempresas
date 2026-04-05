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

    public function index()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
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
        if ($minScore) {
            $this->companyModel->where('crs.score_total >=', (int)$minScore);
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

        // 3. Estadísticas de progreso CRM (Ajuste 1)
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
        foreach ($companies as &$co) {
            $co['lead_score'] = $this->getLeadScore($co);
            $co['status'] = $favoriteMap[$co['id']] ?? 'nuevo';
            $co['is_favorite'] = isset($favoriteMap[$co['id']]);
            $co['is_following'] = in_array($co['id'], $followingIds);
        }

        // 4. Datos para Filtros
        $provinces = $db->query("SELECT province as name FROM seo_stats ORDER BY total_companies DESC LIMIT 52")->getResultArray();
        
        $data = [
            'stats' => $stats,
            'crmStats' => $crmStats,
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

        // Añadir lead_score a cada favorito
        foreach ($favorites as &$f) {
            $f['lead_score'] = $this->getLeadScore($f);
        }

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

        foreach ($favorites as &$f) {
            $f['lead_score'] = $this->getLeadScore($f);
            $status = $f['status'] ?: 'nuevo';
            if (isset($columns[$status])) {
                $columns[$status][] = $f;
            } else {
                $columns['nuevo'][] = $f;
            }
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
        $builder->select('registro_mercantil as province, COUNT(*) as total');
        $builder->where('registro_mercantil IS NOT NULL');
        $builder->where('fecha_constitucion IS NOT NULL');

        // Aplicar mismos filtros que en el listado
        if ($province) {
            $builder->where('registro_mercantil', strtoupper(str_replace('-', ' ', $province)));
        }
        if ($cnae) {
            // Simplificado para el mapa
            $builder->like('cnae_code', $cnae, 'after');
        }
        if ($q) {
            $builder->like('objeto_social', $q);
        }

        $today = date('Y-m-d');
        if ($timeRange === 'hoy') {
            $builder->where('fecha_constitucion >=', $today);
        } else {
            $days = (int)$timeRange;
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime("-$days days")));
        }
        $builder->where('fecha_constitucion <=', $today); // Excluir fechas futuras

        $results = $builder->groupBy('registro_mercantil')
                          ->orderBy('total', 'DESC')
                          ->get()
                          ->getResultArray();

        return $this->response->setJSON($results);
    }

    /**
     * Calcula un score de calidad para el lead (A, B, C)
     */
    private function getLeadScore($co)
    {
        // Lógica simple basada en si tiene teléfono y longitud del objeto social
        $score = 50;
        
        if (!empty($co['phone'])) $score += 30;
        if (!empty($co['cif']) && $co['cif'][0] === 'B') $score += 10; // Sociedades Limitadas suelen ser mejores leads B2B
        if (strlen($co['objeto_social'] ?? '') > 150) $score += 10;

        if ($score >= 90) return 'A+';
        if ($score >= 70) return 'A';
        if ($score >= 50) return 'B';
        return 'C';
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