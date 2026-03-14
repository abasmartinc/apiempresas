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
        
        // Sectores: Mostramos las secciones que han tenido actividad REAL reciente (empresas en los últimos 90 días)
        $topSectors = $db->query("
            SELECT s.id as code, s.name as label, COUNT(c.id) as total
            FROM cnae_sections s
            JOIN cnae_groups g ON g.parent_section_id = s.id
            JOIN cnae_classes cl ON cl.parent_group_id = g.id
            JOIN cnae_subclasses sub ON sub.parent_class_id = cl.id
            JOIN cnae_2009_2025 c2009 ON CONVERT(c2009.label_2009 USING utf8mb4) = CONVERT(sub.name USING utf8mb4)
            JOIN companies c ON CONVERT(c.cnae_code USING utf8mb4) = CONVERT(c2009.cnae_2009 USING utf8mb4)
            WHERE c.fecha_constitucion >= ?
            GROUP BY s.id, s.name
            ORDER BY total DESC
            LIMIT 12
        ", [date('Y-m-d', strtotime('-90 days'))])->getResultArray();

        // Si por alguna razón no hay datos suficientes, fallback a lista ordenada
        if (empty($topSectors)) {
            $topSectors = $db->query("SELECT id as code, name as label FROM cnae_sections ORDER BY name ASC LIMIT 12")->getResultArray();
        }

        $data = [
            'stats' => $stats,
            'companies' => $companies,
            'pager' => $pager,
            'provinces' => $provinces,
            'topSectors' => $topSectors,
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
                'planName' => $activePlan ? $activePlan->plan_name : 'Gratuito'
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
            return redirect()->to(site_url('precios-radar'))->with('message', 'La exportación requiere un plan activo.');
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
}
