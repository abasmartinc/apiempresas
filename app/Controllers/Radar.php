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
        $this->companyModel->select('companies.id, companies.company_name, companies.cif, companies.fecha_constitucion, companies.cnae_label, companies.registro_mercantil, companies.municipality, companies.objeto_social, companies.phone');
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
            $this->companyModel->like('companies.objeto_social', $q);
        }

        // Rango de tiempo
        if ($timeRange === 'hoy') {
            $dateLimit = date('Y-m-d');
        } else {
            $days = (int)$timeRange;
            $dateLimit = date('Y-m-d', strtotime("-$days days"));
        }
        $this->companyModel->where('companies.fecha_constitucion >=', $dateLimit);

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

        // Inyectar Score de calidad y estado de favorito a cada empresa
        $favoriteIds = $this->favoriteModel->getFavoriteIds($userId);
        foreach ($companies as &$co) {
            $co['lead_score'] = $this->getLeadScore($co);
            $co['is_favorite'] = in_array($co['id'], $favoriteIds);
        }

        // 3. Datos para Filtros
        $provinces = $db->query("SELECT province as name FROM seo_stats ORDER BY total_companies DESC LIMIT 52")->getResultArray();
        
        $data = [
            'stats' => $stats,
            'companies' => $companies,
            'pager' => $pager,
            'provinces' => $provinces,
            'filters' => [
                'provincia' => $province,
                'cnae' => $cnae,
                'rango' => $timeRange,
                'q' => $q,
                'per_page' => $perPage
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
        } else {
            $this->favoriteModel->insert([
                'user_id' => $userId,
                'company_id' => $companyId,
            ]);
            $status = 'added';
        }

        return $this->response->setJSON(['status' => 'success', 'favorite_status' => $status]);
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
        $favorites = $this->favoriteModel->getFavoritesWithCompanyData($userId);

        // Inyectar Score de calidad a cada favorito
        foreach ($favorites as &$f) {
            $f['lead_score'] = $this->getLeadScore($f);
        }

        $data = [
            'favorites' => $favorites,
            'title' => 'Mis Favoritos - Radar PRO'
        ];

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
            'ganado' => []
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

        $userId = session('user_id');
        $favoriteId = $this->request->getPost('favorite_id');
        $status = $this->request->getPost('status');

        $allowedStatuses = ['nuevo', 'contactado', 'negociacion', 'ganado'];
        if (!in_array($status, $allowedStatuses)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid status']);
        }

        $favorite = $this->favoriteModel->where(['id' => $favoriteId, 'user_id' => $userId])->first();

        if (!$favorite) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Favorite not found']);
        }

        $this->favoriteModel->update($favoriteId, ['status' => $status]);

        return $this->response->setJSON(['status' => 'success']);
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
        $builder->select('company_name, cif, fecha_constitucion, cnae_label, municipality, phone');
        $builder->where('fecha_constitucion IS NOT NULL');

        if ($province) {
            $builder->where('registro_mercantil', strtoupper(str_replace('-', ' ', $province)));
        }
        if ($q) {
            $builder->like('objeto_social', $q);
        }

        if ($timeRange === 'hoy') {
            $builder->where('fecha_constitucion >=', date('Y-m-d'));
        } else {
            $days = (int)$timeRange;
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime("-$days days")));
        }

        $companies = $builder->orderBy('fecha_constitucion', 'DESC')->get()->getResultArray();

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

        fputcsv($output, ['EMPRESA', 'CIF', 'CONSTITUCION', 'SECTOR', 'MUNICIPIO', 'TELEFONO']);

        foreach ($companies as $co) {
            fputcsv($output, $co);
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

        if ($timeRange === 'hoy') {
            $builder->where('fecha_constitucion >=', date('Y-m-d'));
        } else {
            $days = (int)$timeRange;
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime("-$days days")));
        }

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
     * Análisis IA bajo demanda del objeto social (Gemini 1.5 Flash)
     */
    public function aiAnalyze($id)
    {
        if (!session('logged_in')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No autorizado']);
        }

        $company = $this->companyModel->find($id);
        if (!$company) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Empresa no encontrada']);
        }

        $purpose = $company['objeto_social'] ?? '';
        if (empty($purpose)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'La empresa no tiene objeto social definido para analizar']);
        }

        $apiKey = trim(env('OPENAI_API_KEY'));
        if (empty($apiKey)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'IA no configurada. Por favor, añade OPENAI_API_KEY al archivo .env']);
        }

        $companyName = $company['company_name'] ?? 'Empresa';
        $location = $company['municipality'] ?? ($company['registro_mercantil'] ?? 'España');
        $sector = $company['cnae_label'] ?? 'Sector no especificado';

        $prompt = "Analiza el siguiente objeto social de una empresa española recién creada:
        - Nombre: $companyName
        - Ubicación: $location
        - Sector (CNAE): $sector
        - Objeto Social: \"$purpose\"

        Extrae y devuelve ÚNICAMENTE un objeto JSON con las siguientes claves:
        1. 'niche': El nicho comercial real en 2 o 3 palabras (ej: 'Paneles Solares', 'Consultoría IA').
        2. 'summary': Un resumen ejecutivo de 15 palabras máximo que explique qué hace la empresa de forma clara.
        3. 'target_persona': El cargo o perfil ideal de la persona con la que un vendedor debería contactar (ej: 'Gerente de Mantenimiento', 'Director de Operaciones').
        4. 'pain_points': Una lista de 3 puntos de dolor (retos o necesidades) que probablemente tenga esta empresa según su actividad (un array de strings).
        5. 'cold_call': Un script de apertura para llamada en frío de máx 25 palabras para captar su atención.
        6. 'email_hook': Un objeto con 'subject' (asunto potente) y 'opening' (primera frase gancho) para un email de prospección.";

        $url = "https://api.openai.com/v1/chat/completions";

        $data = [
            "model" => "gpt-4o-mini",
            "messages" => [
                [
                    "role" => "system",
                    "content" => "Eres un experto en análisis de datos empresariales y prospección B2B. Responde siempre en formato JSON puro."
                ],
                [
                    "role" => "user",
                    "content" => $prompt
                ]
            ],
            "response_format" => ["type" => "json_object"],
            "temperature" => 0.4
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $errorDetail = json_decode($response, true);
            $msg = $errorDetail['error']['message'] ?? 'Error desconocido';
            return $this->response->setJSON(['status' => 'error', 'message' => "IA Error ($httpCode): $msg"]);
        }

        $result = json_decode($response, true);
        $rawText = $result['choices'][0]['message']['content'] ?? null;

        if (!$rawText) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'La IA no devolvió contenido']);
        }

        $cleanJson = json_decode($rawText, true);

        if (!$cleanJson) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Error al procesar el formato de la IA']);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'niche' => $cleanJson['niche'] ?? 'N/D',
            'summary' => $cleanJson['summary'] ?? 'No disponible',
            'target_persona' => $cleanJson['target_persona'] ?? 'Gerente',
            'pain_points' => $cleanJson['pain_points'] ?? [],
            'cold_call' => $cleanJson['cold_call'] ?? 'No disponible',
            'email_hook' => $cleanJson['email_hook'] ?? ['subject' => 'Consulta', 'opening' => 'Hola']
        ]);
    }
}
