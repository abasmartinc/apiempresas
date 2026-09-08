<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\SearchLogModel;
use App\Models\ApiRequestsModel;
use App\Models\ApiUsageDailyModel;
use App\Models\CompanyAdminModel;
use App\Models\ApiPlanModel;
use App\Models\ApikeysModel;
use App\Models\SubscriptionModel;
use App\Models\EmailLogModel;
use App\Models\InvoiceModel;
use App\Models\BlockedIpModel;

class Dashboard extends BaseController
{
    protected $userModel;
    protected $searchLogModel;
    protected $apiRequestsModel;
    protected $apiUsageDailyModel;
    protected $companyModel;
    protected $planModel;
    protected $apiKeyModel;
    protected $subscriptionModel;
    protected $emailLogModel;
    protected $invoiceModel;
    protected $blockedIpModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->searchLogModel = new SearchLogModel();
        $this->apiRequestsModel = new ApiRequestsModel();
        $this->apiUsageDailyModel = new ApiUsageDailyModel();
        $this->companyModel = new CompanyAdminModel();
        $this->planModel = new ApiPlanModel();
        $this->apiKeyModel = new ApikeysModel();
        $this->subscriptionModel = new SubscriptionModel();
        $this->emailLogModel = new EmailLogModel();
        $this->invoiceModel = new InvoiceModel();
        $this->blockedIpModel = new BlockedIpModel();
    }

    /**
     * Listado de usuarios
     */
    public function index()
    {
        $q = $this->request->getGet('q');
        $active = $this->request->getGet('is_active');
        $admin = $this->request->getGet('is_admin');
        $signupIntent = $this->request->getGet('signup_intent');

        $builder = $this->userModel;

        if ($q) {
            $builder->groupStart()
                ->like('name', $q)
                ->orLike('email', $q)
                ->orLike('company', $q)
                ->groupEnd();
        }

        if ($active !== null && $active !== '') {
            $builder->where('is_active', $active);
        }

        if ($admin !== null && $admin !== '') {
            $builder->where('is_admin', $admin);
        }

        if ($signupIntent !== null && $signupIntent !== '') {
            $builder->where('signup_intent', $signupIntent);
        }

        $db = \Config\Database::connect();
        $startThisMonth = date('Y-m-01 00:00:00');
        $startLastMonth = date('Y-m-01 00:00:00', strtotime('first day of last month'));

        $monthNames = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        $prevMonthName = $monthNames[(int)date('n', strtotime('first day of last month'))] ?? 'mes anterior';

        // Helper para calcular variación porcentual y tendencia
        $calcTrend = function($current, $previous) {
            $diff = $current - $previous;
            if ($previous == 0) {
                $percent = $current > 0 ? 100 : 0;
            } else {
                $percent = round(($diff / $previous) * 100, 1);
            }
            return [
                'current' => (int)$current,
                'previous' => (int)$previous,
                'diff' => (int)$diff,
                'percent' => abs($percent),
                'direction' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'neutral'),
                'formatted_percent' => ($diff > 0 ? '+' : ($diff < 0 ? '-' : '')) . abs($percent) . '%'
            ];
        };

        // 1. Total usuarios
        $totalUsers = $db->table('users')->countAllResults();
        $prevTotalUsers = $db->table('users')->where('created_at <', $startThisMonth)->countAllResults();
        $trendTotal = $calcTrend($totalUsers, $prevTotalUsers);

        // 2. Nuevos usuarios este mes
        $newThisMonth = $db->table('users')->where('created_at >=', $startThisMonth)->countAllResults();
        $newLastMonth = $db->table('users')->where('created_at >=', $startLastMonth)->where('created_at <', $startThisMonth)->countAllResults();
        $trendNew = $calcTrend($newThisMonth, $newLastMonth);

        // 3. Activos últimos 30 días
        $active30d = $db->table('users')->where('last_login_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))->countAllResults();
        $activePrior30d = $db->table('users')->where('last_login_at >=', date('Y-m-d H:i:s', strtotime('-60 days')))->where('last_login_at <', date('Y-m-d H:i:s', strtotime('-30 days')))->countAllResults();
        $trendActive = $calcTrend($active30d, $activePrior30d);

        // 4. Administradores
        $adminCount = $db->table('users')->where('is_admin', 1)->countAllResults();
        $adminLastMonth = $db->table('users')->where('is_admin', 1)->where('created_at <', $startThisMonth)->countAllResults();
        $trendAdmin = $calcTrend($adminCount, $adminLastMonth);

        $data = [
            'title' => 'Gestión de Usuarios | APIEmpresas',
            'users' => $builder->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $this->userModel->pager,
            'q' => $q,
            'is_active' => $active,
            'is_admin' => $admin,
            'signup_intent' => $signupIntent,
            'stats' => [
                'total_users' => $totalUsers,
                'new_users_month' => $newThisMonth,
                'active_users_30d' => $active30d,
                'admin_users' => $adminCount,
                'api_users' => $db->table('users')->where('signup_intent', 'api')->countAllResults(),
                'risk_profile_users' => $db->table('users')->where('signup_intent', 'view_risk_profile')->countAllResults(),
                'radar_users' => $db->table('users')->where('signup_intent', 'radar')->countAllResults(),
                'prev_month_name' => $prevMonthName,
                'trend_total' => $trendTotal,
                'trend_new' => $trendNew,
                'trend_active' => $trendActive,
                'trend_admin' => $trendAdmin,
            ]
        ];

        return $this->renderView('admin/users', $data);
    }


    /**
     * Listado de peticiones API
     */
    public function api_requests()
    {
        $q = $this->request->getGet('q');
        $userId = $this->request->getGet('user_id');
        $statusCode = $this->request->getGet('status_code');
        $date = $this->request->getGet('date');

        $builder = $this->apiRequestsModel;
        $builder->select('api_requests.*, users.name as user_name, users.email as user_email');
        $builder->join('users', 'users.id = api_requests.user_id', 'left');

        if ($q) {
            $builder->like('endpoint', $q);
        }

        if ($userId) {
            $builder->where('api_requests.user_id', $userId);
        }

        if ($statusCode) {
            $builder->where('status_code', $statusCode);
        }

        if ($date) {
            $builder->where('api_requests.created_at >=', $date . ' 00:00:00');
            $builder->where('api_requests.created_at <=', $date . ' 23:59:59');
        }

        $data = [
            'title' => 'Peticiones API | APIEmpresas',
            'requests' => $builder->orderBy('created_at', 'DESC')->paginate(40, 'default'),
            'pager' => $this->apiRequestsModel->pager,
            'users' => $this->userModel->orderBy('name', 'ASC')->findAll(),
            'q' => $q,
            'user_id' => $userId,
            'status_code' => $statusCode,
            'date' => $date,
            'stats' => [
                'requests_24h' => $this->apiRequestsModel->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))->countAllResults(),
                'avg_latency' => $this->apiRequestsModel->getAverageLatency(['created_at >=' => date('Y-m-d H:i:s', strtotime('-24 hours'))]),
                'error_rate' => $this->apiRequestsModel->getErrorRate(['created_at >=' => date('Y-m-d H:i:s', strtotime('-24 hours'))]),
                'active_users' => $this->apiRequestsModel->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))->select('user_id')->groupBy('user_id')->countAllResults(),
            ]
        ];

        return $this->renderView('admin/api_requests', $data);
    }

    /**
     * Listado de uso diario de la API
     */
    public function usage_daily()
    {
        $userId = $this->request->getGet('user_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $builder = $this->apiUsageDailyModel;
        $builder->select('api_usage_daily.*, users.name as user_name, users.email as user_email');
        $builder->join('users', 'users.id = api_usage_daily.user_id', 'left');

        if ($userId) {
            $builder->where('api_usage_daily.user_id', $userId);
        }

        if ($startDate) {
            $builder->where('date >=', $startDate);
        }

        if ($endDate) {
            $builder->where('date <=', $endDate);
        }

        $data = [
            'title' => 'Uso Diario API | APIEmpresas',
            'usage' => $builder->orderBy('date', 'DESC')->paginate(30, 'default'),
            'pager' => $this->apiUsageDailyModel->pager,
            'users' => $this->userModel->orderBy('name', 'ASC')->findAll(),
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'stats' => [
                'total_30d' => $this->apiUsageDailyModel->where('date >=', date('Y-m-d', strtotime('-30 days')))->selectSum('requests_count')->get()->getRowArray()['requests_count'] ?? 0,
                'avg_daily' => round($this->apiUsageDailyModel->where('date >=', date('Y-m-d', strtotime('-30 days')))->selectAvg('requests_count')->get()->getRowArray()['requests_count'] ?? 0, 1),
                'peak_daily' => $this->apiUsageDailyModel->where('date >=', date('Y-m-d', strtotime('-30 days')))->selectMax('requests_count')->get()->getRowArray()['requests_count'] ?? 0,
                'current_month' => $this->apiUsageDailyModel->where('date >=', date('Y-m-01'))->selectSum('requests_count')->get()->getRowArray()['requests_count'] ?? 0,
            ]
        ];

        return $this->renderView('admin/usage_daily', $data);
    }

    /**
     * Listado de IPs bloqueadas
     */
    public function blocked_ips()
    {
        $q = $this->request->getGet('q');

        $builder = $this->blockedIpModel;

        if ($q) {
            $builder->like('ip_address', $q)
                    ->orLike('reason', $q);
        }

        $data = [
            'title' => 'Centro de Seguridad | APIEmpresas',
            'blocked_ips' => $builder->orderBy('blocked_at', 'DESC')->paginate(30, 'default'),
            'pager' => $this->blockedIpModel->pager,
            'q' => $q
        ];

        return $this->renderView('admin/blocked_ips', $data);
    }


    /**
     * Formulario para redactar email
     */
    public function compose($userId)
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            return redirect()->back()->with('error', 'Usuario no encontrado.');
        }

        $marketingConfig = new \Config\MarketingTemplates();

        $data = [
            'title' => 'Redactar Email',
            'user' => $user,
            'templates' => $marketingConfig->templates,
        ];

        return $this->renderView('admin/email_compose', $data);
    }

    /**
     * Formulario para crear usuario
     */
    public function create()
    {
        $data = [
            'title' => 'Crear Usuario',
            'user' => null, // Para que la vista sepa que es creación
        ];

        return $this->renderView('admin/user_form', $data);
    }

    /**
     * Guardar nuevo usuario
     */
    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email',
            'password' => 'required|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Revisa los errores: ' . implode(' ', $this->validator->getErrors()));
        }

        $email = $this->request->getPost('email');
        if ($this->userModel->where('email', $email)->where('source_app', 'apiempresas')->first()) {
            return redirect()->back()->withInput()->with('error', 'Ya existe una cuenta con este correo en APIEmpresas.');
        }

        $this->userModel->save([
            'name' => $this->request->getPost('name'),
            'company' => $this->request->getPost('company'),
            'email' => $this->request->getPost('email'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'is_admin' => $this->request->getPost('is_admin') ? 1 : 0,
            'api_access' => $this->request->getPost('api_access') ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('admin/users'))->with('message', 'Usuario creado correctamente.');
    }

    /**
     * Formulario para editar usuario
     */
    public function edit($id)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Usuario no encontrado.');
        }

        $data = [
            'title' => 'Editar Usuario',
            'user' => $user,
        ];

        return $this->renderView('admin/user_form', $data);
    }

    /**
     * Actualizar usuario
     */
    public function update()
    {
        $id = $this->request->getPost('id');
        $rules = [
            'name' => 'required|min_length[3]',
            'email' => "required|valid_email",
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Revisa los errores.');
        }

        $email = $this->request->getPost('email');
        $existing = $this->userModel->where('email', $email)->where('source_app', 'apiempresas')->first();
        if ($existing && $existing->id != $id) {
            return redirect()->back()->withInput()->with('error', 'Ya existe una cuenta con este correo en APIEmpresas.');
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'company' => $this->request->getPost('company'),
            'email' => $this->request->getPost('email'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'is_admin' => $this->request->getPost('is_admin') ? 1 : 0,
            'api_access' => $this->request->getPost('api_access') ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Solo actualizar password si se envía
        if ($this->request->getPost('password')) {
            $data['password_hash'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $data);

        return redirect()->to(site_url('admin/users'))->with('message', 'Usuario actualizado correctamente.');
    }

    /**
     * Eliminar usuario
     */
    public function delete($id)
    {
        // Evitar que el admin se borre a sí mismo
        if ((int) $id === (int) session()->get('user_id')) {
            return redirect()->to(site_url('admin/users'))->with('error', 'No puedes eliminarte a ti mismo.');
        }

        $this->userModel->delete($id);

        return redirect()->to(site_url('admin/users'))->with('message', 'Usuario eliminado correctamente.');
    }

    /**
     * Alternar acceso a la API (Dashboard Real)
     */
    public function toggle_api_access($id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Usuario no encontrado.');
        }

        $newStatus = ($user->api_access ?? 0) == 1 ? 0 : 1;
        $this->userModel->update($id, ['api_access' => $newStatus]);

        return redirect()->back()->with('message', 'Acceso API actualizado correctamente.');
    }

    /**
     * Impersonar usuario (Login As)
     */
    public function impersonate($id)
    {
        // Doble verificación de seguridad: Solo admins
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Usuario no encontrado.');
        }

        // Evitar impersonarse a sí mismo (redundante pero limpia historial)
        if ($user->id == session('user_id')) {
            return redirect()->back()->with('message', 'Ya estás logueado como tú mismo.');
        }

        // Log de seguridad
        log_activity('admin_impersonate', ['details' => "Admin " . session('user_email') . " logged in as " . $user->email]);

        // Regenerar sesión
        session()->regenerate();

        // Establecer sesión del usuario objetivo
        session()->set([
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name ?? '',
            'is_admin' => $user->is_admin ?? 0,
            'logged_in' => true,
            'impersonator_id' => session('user_id') // Opcional: para saber quién era el admin original si quisiéramos botón de "volver"
        ]);

        return redirect()->to(site_url('dashboard'));
    }

    /**
     * Listado de facturas
     */
    public function invoices()
    {
        $db = \Config\Database::connect();
        $invoiceModel = new \App\Models\InvoiceModel();

        // Parámetros de filtro
        $search = trim($this->request->getGet('search') ?? $this->request->getGet('q') ?? '');
        $userId = $this->request->getGet('user_id');
        $status = $this->request->getGet('status');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');
        $datePreset = $this->request->getGet('date_preset');
        $orderBy = $this->request->getGet('order_by') ?? 'created_at_desc';

        // Manejo de date_preset rápido
        if ($datePreset) {
            switch ($datePreset) {
                case 'this_month':
                    $dateFrom = date('Y-m-01');
                    $dateTo = date('Y-m-t');
                    break;
                case 'last_month':
                    $dateFrom = date('Y-m-01', strtotime('first day of last month'));
                    $dateTo = date('Y-m-t', strtotime('last month'));
                    break;
                case 'last_30d':
                    $dateFrom = date('Y-m-d', strtotime('-30 days'));
                    $dateTo = date('Y-m-d');
                    break;
                case 'this_year':
                    $dateFrom = date('Y-01-01');
                    $dateTo = date('Y-12-31');
                    break;
                case 'all':
                    $dateFrom = '';
                    $dateTo = '';
                    break;
            }
        }

        // Construir consulta con JOIN a usuarios
        $builder = $invoiceModel;
        $builder->select('invoices.*, users.name as user_account_name, users.email as user_account_email, users.company as user_company');
        $builder->join('users', 'users.id = invoices.user_id', 'left');

        // Filtro de búsqueda textual
        if ($search !== '') {
            $builder->groupStart()
                ->like('invoices.invoice_number', $search)
                ->orLike('invoices.billing_name', $search)
                ->orLike('invoices.billing_email', $search)
                ->orLike('invoices.billing_vat', $search)
                ->orLike('invoices.stripe_invoice_id', $search)
                ->orLike('users.name', $search)
                ->orLike('users.email', $search)
                ->orLike('users.company', $search)
                ->groupEnd();
        }

        // Filtro por usuario
        if ($userId !== null && $userId !== '') {
            $builder->where('invoices.user_id', (int)$userId);
        }

        // Filtro por estado
        if ($status !== null && $status !== '') {
            $builder->where('invoices.status', $status);
        }

        // Filtros por rango de fecha
        if ($dateFrom) {
            $builder->where('invoices.created_at >=', $dateFrom . ' 00:00:00');
        }
        if ($dateTo) {
            $builder->where('invoices.created_at <=', $dateTo . ' 23:59:59');
        }

        // Ordenación
        switch ($orderBy) {
            case 'created_at_asc':
                $builder->orderBy('invoices.created_at', 'ASC');
                break;
            case 'amount_desc':
                $builder->orderBy('invoices.total_amount', 'DESC');
                break;
            case 'amount_asc':
                $builder->orderBy('invoices.total_amount', 'ASC');
                break;
            case 'number_desc':
                $builder->orderBy('invoices.invoice_number', 'DESC');
                break;
            case 'number_asc':
                $builder->orderBy('invoices.invoice_number', 'ASC');
                break;
            case 'created_at_desc':
            default:
                $builder->orderBy('invoices.created_at', 'DESC');
                break;
        }

        $invoices = $builder->paginate(20);
        $pager = $invoiceModel->pager;

        // Lista de usuarios con facturas para el desplegable de filtro
        $usersWithInvoices = $db->table('invoices')
            ->select('invoices.user_id, users.name, users.email, COUNT(invoices.id) as invoice_count')
            ->join('users', 'users.id = invoices.user_id', 'left')
            ->where('invoices.user_id IS NOT NULL', null, false)
            ->groupBy('invoices.user_id, users.name, users.email')
            ->orderBy('users.name', 'ASC')
            ->get()
            ->getResultArray();

        // Fechas para comparativa mensual en KPIs
        $startThisMonth = date('Y-m-01 00:00:00');
        $startLastMonth = date('Y-m-01 00:00:00', strtotime('first day of last month'));

        $monthNames = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        $prevMonthName = $monthNames[(int)date('n', strtotime('first day of last month'))] ?? 'mes anterior';

        // Helper para calcular métricas de variación
        $calcTrend = function($current, $previous) {
            $diff = $current - $previous;
            if ($previous == 0) {
                $percent = $current > 0 ? 100 : 0;
            } else {
                $percent = round(($diff / $previous) * 100, 1);
            }
            return [
                'current' => $current,
                'previous' => $previous,
                'diff' => $diff,
                'percent' => abs($percent),
                'direction' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'neutral'),
                'formatted_percent' => ($diff > 0 ? '+' : ($diff < 0 ? '-' : '')) . abs($percent) . '%'
            ];
        };

        // Facturación este mes vs mes anterior
        $revThisMonth = (float)($db->table('invoices')->where('status', 'paid')->where('created_at >=', $startThisMonth)->selectSum('total_amount')->get()->getRowArray()['total_amount'] ?? 0);
        $revLastMonth = (float)($db->table('invoices')->where('status', 'paid')->where('created_at >=', $startLastMonth)->where('created_at <', $startThisMonth)->selectSum('total_amount')->get()->getRowArray()['total_amount'] ?? 0);
        $trendRevenue = $calcTrend($revThisMonth, $revLastMonth);

        // Cantidad de facturas emitidas este mes vs mes anterior
        $countThisMonth = $db->table('invoices')->where('created_at >=', $startThisMonth)->countAllResults();
        $countLastMonth = $db->table('invoices')->where('created_at >=', $startLastMonth)->where('created_at <', $startThisMonth)->countAllResults();
        $trendCount = $calcTrend($countThisMonth, $countLastMonth);

        // Ticket medio este mes vs mes anterior
        $avgThisMonth = (float)($db->table('invoices')->where('status', 'paid')->where('created_at >=', $startThisMonth)->selectAvg('total_amount')->get()->getRowArray()['total_amount'] ?? 0);
        $avgLastMonth = (float)($db->table('invoices')->where('status', 'paid')->where('created_at >=', $startLastMonth)->where('created_at <', $startThisMonth)->selectAvg('total_amount')->get()->getRowArray()['total_amount'] ?? 0);
        $trendAvg = $calcTrend($avgThisMonth, $avgLastMonth);

        // Facturas pendientes
        $pendingCount = $db->table('invoices')->where('status !=', 'paid')->countAllResults();
        $pendingLastMonth = $db->table('invoices')->where('status !=', 'paid')->where('created_at >=', $startLastMonth)->where('created_at <', $startThisMonth)->countAllResults();
        $trendPending = $calcTrend($pendingCount, $pendingLastMonth);

        // Contadores globales por estado
        $paidCount = $db->table('invoices')->where('status', 'paid')->countAllResults();
        $failedCount = $db->table('invoices')->whereIn('status', ['failed', 'uncollectible'])->countAllResults();
        $totalInvoicesCount = $db->table('invoices')->countAllResults();

        $data = [
            'title' => 'Gestión de Facturas | APIEmpresas',
            'invoices' => $invoices,
            'pager' => $pager,
            'search' => $search,
            'user_id' => $userId,
            'status' => $status,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'date_preset' => $datePreset,
            'order_by' => $orderBy,
            'users_with_invoices' => $usersWithInvoices,
            'stats' => [
                'revenue_month' => $revThisMonth,
                'count_month' => $countThisMonth,
                'avg_ticket' => round($avgThisMonth, 2),
                'pending_count' => $pendingCount,
                'paid_count' => $paidCount,
                'failed_count' => $failedCount,
                'total_invoices' => $totalInvoicesCount,
                'prev_month_name' => $prevMonthName,
                'trend_revenue' => $trendRevenue,
                'trend_count' => $trendCount,
                'trend_avg' => $trendAvg,
                'trend_pending' => $trendPending,
            ]
        ];

        return $this->renderView('admin/invoices', $data);
    }

    /**
     * Descargar factura PDF
     */
    public function invoice_download($id)
    {
        $invoiceModel = new \App\Models\InvoiceModel();
        $invoice = $invoiceModel->find($id);

        if (!$invoice || !$invoice->pdf_path) {
            return redirect()->back()->with('error', 'Factura no encontrada o PDF no generado.');
        }

        // Robustez: Usar WRITEPATH
        $relativePath = preg_replace('#^writable/#', '', $invoice->pdf_path);
        $fullPath = WRITEPATH . $relativePath;

        if (!file_exists($fullPath)) {
            $altPath = ROOTPATH . $invoice->pdf_path;
            if (file_exists($altPath)) {
                $fullPath = $altPath;
            } else {
                return redirect()->back()->with('error', 'El archivo físico de la factura no existe en el servidor.');
            }
        }

        return $this->response->download($fullPath, null)->setFileName($invoice->invoice_number . '.pdf');
    }

    /**
     * Enviar email
     */
    public function send()
    {
        $userId = $this->request->getPost('user_id');
        $subject = $this->request->getPost('subject');
        $message = $this->request->getPost('message');

        $user = $this->userModel->find($userId);

        if (!$user) {
            return redirect()->to(site_url('admin/users'))->with('error', 'Usuario no encontrado.');
        }

        // Check unsubscribe status
        if ((int)($user->unsuscribe ?? 0) === 1) {
            return redirect()->to(site_url('admin/users'))->with('error', 'El usuario se ha dado de baja y no desea recibir correos.');
        }

        $email = \Config\Services::email();

        $email->setTo($user->email);
        $email->setSubject($subject);

        $trackingCode = bin2hex(random_bytes(16));

        $logData = [
            'user_id' => $userId,
            'subject' => $subject,
            'message' => $message,
            'tracking_code' => $trackingCode,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Reemplazo de variables
        $finalMessage = str_replace(
            ['{NOMBRE}', '{EMPRESA}', '{SITE_URL}'], 
            [$user->name ?: 'cliente', $user->company ?: 'su empresa', site_url()], 
            $message
        );

        // Usar plantilla HTML
        $body = view('emails/user_notification', [
            'user' => $user,
            'content' => nl2br($finalMessage),
            'subject' => $subject,
            'tracking_code' => $trackingCode,
            'unsubscribe_url' => (new \App\Services\EmailService())->generateUnsubscribeLink($user->email)
        ]);

        $email->setMessage($body);

        if ($email->send()) {
            $logData['status'] = 'success';
            $this->emailLogModel->insert($logData);
            return redirect()->to(site_url('admin/users'))->with('message', 'Email enviado correctamente a ' . $user->email);
        } else {
            $logData['status'] = 'error';
            $logData['error_message'] = $email->printDebugger(['headers']);
            $this->emailLogModel->insert($logData);
            return redirect()->back()->withInput()->with('error', 'Error al enviar el email: ' . $email->printDebugger(['headers']));
        }
    }

    /**
     * Formulario para redactar email masivo
     */
    public function compose_bulk()
    {
        $ids = $this->request->getVar('user_ids'); // Array of IDs
        $filter_q = $this->request->getVar('q');
        $filter_active = $this->request->getVar('is_active');
        $filter_admin = $this->request->getVar('is_admin');
        $filter_intent = $this->request->getVar('signup_intent');
        $selectAll = $this->request->getVar('select_all_filtered');
        $returnTo = $this->request->getVar('return_to') ?: 'admin/users';

        $count = 0;
        $usersPreview = [];
        $targetDescription = "";

        if ($selectAll) {
            // Apply filters to count
            $builder = $this->userModel;
            if ($filter_q) {
                $builder->groupStart()
                    ->like('name', $filter_q)
                    ->orLike('email', $filter_q)
                    ->orLike('company', $filter_q)
                    ->groupEnd();
            }
            if ($filter_active !== null && $filter_active !== '') {
                $builder->where('is_active', $filter_active);
            }
            if ($filter_admin !== null && $filter_admin !== '') {
                $builder->where('is_admin', $filter_admin);
            }
            if ($filter_intent !== null && $filter_intent !== '') {
                $builder->where('signup_intent', $filter_intent);
            }
            $count = $builder->countAllResults(false); // false to not reset query for next call if needed, though here we just count
            $targetDescription = "Todos los usuarios filtrados ($count)";
        } elseif ($ids) {
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            $count = count($ids);
            $targetDescription = "$count usuarios seleccionados";
        } else {
            return redirect()->back()->with('error', 'No has seleccionado ningún usuario.');
        }

        $marketingConfig = new \Config\MarketingTemplates();

        $data = [
            'title' => 'Redactar Email Masivo',
            'count' => $count,
            'target_description' => $targetDescription,
            'templates' => $marketingConfig->templates,
            // Pass hidden inputs to the view
            'hidden_inputs' => [
                'user_ids' => is_array($ids) ? implode(',', $ids) : $ids,
                'q' => $filter_q,
                'is_active' => $filter_active,
                'is_admin' => $filter_admin,
                'signup_intent' => $filter_intent,
                'select_all_filtered' => $selectAll,
                'return_to' => $returnTo
            ]
        ];

        return $this->renderView('admin/email_compose_bulk', $data);
    }

    /**
     * Enviar email masivo
     */
    public function send_bulk()
    {
        $subject = $this->request->getPost('subject');
        $message = $this->request->getPost('message');

        $ids = $this->request->getPost('user_ids');
        $selectAll = $this->request->getPost('select_all_filtered');

        $users = [];

        if ($selectAll) {
            $filter_q = $this->request->getPost('q');
            $filter_active = $this->request->getPost('is_active');
            $filter_admin = $this->request->getPost('is_admin');
            $filter_intent = $this->request->getPost('signup_intent');

            $builder = $this->userModel;
            if ($filter_q) {
                $builder->groupStart()
                    ->like('name', $filter_q)
                    ->orLike('email', $filter_q)
                    ->orLike('company', $filter_q)
                    ->groupEnd();
            }
            if ($filter_active !== null && $filter_active !== '') {
                $builder->where('is_active', $filter_active);
            }
            if ($filter_admin !== null && $filter_admin !== '') {
                $builder->where('is_admin', $filter_admin);
            }
            if ($filter_intent !== null && $filter_intent !== '') {
                $builder->where('signup_intent', $filter_intent);
            }
            $users = $builder->findAll();
        } elseif ($ids) {
            $idArray = explode(',', $ids);
            $users = $this->userModel->whereIn('id', $idArray)->findAll();
        }

        if (empty($users)) {
            return redirect()->to(site_url('admin/users'))->with('error', 'No hay destinatarios para enviar el correo.');
        }

        $emailService = \Config\Services::email();
        $sentCount = 0;
        $errorCount = 0;

        foreach ($users as $user) {
            // Skip if unsubscribed
            if ((int)($user->unsuscribe ?? 0) === 1) {
                log_message('info', "[AdminDashboard] Email masivo saltado para {$user->email} por unsuscribe=1");
                continue;
            }

            // Reset email service for each iteration
            $emailService->clear();

            $emailService->setTo($user->email);
            $emailService->setSubject($subject);

            $trackingCode = bin2hex(random_bytes(16));

            $logData = [
                'user_id' => $user->id,
                'subject' => $subject,
                'message' => $message,
                'tracking_code' => $trackingCode,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Reemplazo de variables
            $finalMessage = str_replace(
                ['{NOMBRE}', '{EMPRESA}', '{SITE_URL}'], 
                [$user->name ?: 'cliente', $user->company ?: 'su empresa', site_url()], 
                $message
            );

            // Usar plantilla HTML
            $body = view('emails/user_notification', [
                'user' => $user,
                'content' => nl2br($finalMessage),
                'subject' => $subject,
                'tracking_code' => $trackingCode,
                'unsubscribe_url' => (new \App\Services\EmailService())->generateUnsubscribeLink($user->email)
            ]);

            $emailService->setMessage($body);

            if ($emailService->send()) {
                $logData['status'] = 'success';
                $sentCount++;
            } else {
                $logData['status'] = 'error';
                $logData['error_message'] = $emailService->printDebugger(['headers']);
                $errorCount++;
            }
            $logData['message'] = $finalMessage; // Guardar el mensaje procesado
            $this->emailLogModel->insert($logData);
        }

        $msg = "Proceso finalizado. Enviados: $sentCount.";
        if ($errorCount > 0) {
            $msg .= " Errores: $errorCount.";
            session()->setFlashdata('error', "Hubo algunos errores al enviar.");
        }

        $returnTo = $this->request->getPost('return_to') ?: 'admin/users';
        return redirect()->to(site_url($returnTo))->with('message', $msg);
    }

    /**
     * Listado de empresas (CRUD)
     */
    public function companies()
    {
        $q = $this->request->getGet('q');
        $noCif = $this->request->getGet('no_cif');
        $noAddress = $this->request->getGet('no_address');
        $noStatus = $this->request->getGet('no_status');
        $noCnae = $this->request->getGet('no_cnae');
        $noMercantile = $this->request->getGet('no_mercantile');
        $today = $this->request->getGet('today');

        $filters = [
            'no_cif' => $noCif,
            'no_address' => $noAddress,
            'no_status' => $noStatus,
            'no_cnae' => $noCnae,
            'no_mercantile' => $noMercantile,
            'today' => $today,
        ];

        $companies = $this->companyModel->searchAdmin($q, 20, $filters);
        $pager = $this->companyModel->pager;

        // Si es una petición AJAX, solo devolvemos la tabla
        if ($this->request->isAJAX()) {
            return $this->renderView('admin/partials/companies_table', [
                'companies' => $companies,
                'pager' => $pager
            ]);
        }

        $data = [
            'title' => 'Gestión de Empresas | APIEmpresas',
            'companies' => $companies,
            'pager' => $pager,
            'q' => $q,
            'filters' => $filters
        ];

        return $this->renderView('admin/companies', $data);
    }

    /**
     * Obtiene todos los KPIs mediante una sola petición AJAX (Optimizado con Caché)
     */
    public function all_kpis_ajax()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody('Acceso no permitido');
        }

        $ym = date('Y-m');
        $today = date('Y-m-d');
        $midnight = $today . ' 00:00:00';

        // Inicializar con valores por defecto
        $data = [
            // KPIs de Dashboard (Dashboard.php)
            'users_total' => '...',
            'users_active' => '...',
            'subs_active' => '...',
            'api_today' => '...',
            'api_month' => '...',
            'api_error_rate' => '...',
            'api_latency_avg' => '...',
            'revenue_month' => '...',
            'blocked_ips_count' => '...',
            'searches_zero_results' => '...',
            'searches_resolved_count' => '...',
            
            'stats_updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            // KPIs Rápidos (Dashboard)
            $data['users_total'] = number_format($this->userModel->countAllResults(), 0, ',', '.');
            $data['users_active'] = number_format($this->userModel->where('is_active', 1)->countAllResults(), 0, ',', '.');
            $data['subs_active'] = number_format($this->subscriptionModel->where('status', 'active')->countAllResults(), 0, ',', '.');
            
            $data['api_today'] = number_format($this->apiRequestsModel->where('created_at >=', $midnight)->countAllResults(), 0, ',', '.');
            $data['api_month'] = number_format($this->apiRequestsModel->countRequestsForMonth($ym), 0, ',', '.');
            $data['api_error_rate'] = $this->apiRequestsModel->getErrorRate() . '%';
            $data['api_latency_avg'] = $this->apiRequestsModel->getAverageLatency() . 'ms';
            
            $data['revenue_month'] = number_format($this->invoiceModel->getMonthlyRevenue($ym)->total ?? 0, 2, ',', '.') . ' €';
            $data['blocked_ips_count'] = number_format($this->blockedIpModel->countAllResults(), 0, ',', '.');
            $data['searches_zero_results'] = number_format($this->searchLogModel->countZeroResults($ym), 0, ',', '.');
            $data['searches_resolved_count'] = number_format($this->searchLogModel->countResolvedGaps(), 0, ',', '.');

        } catch (\Exception $e) {
            log_message('error', 'Error loading fast KPIs: ' . $e->getMessage());
        }



        // --- REAL TIME STATS (Calculated every time) ---
        $fiveMinutesAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $data['total_online'] = $this->userModel->where('last_active_at >=', $fiveMinutesAgo)->countAllResults();
        
        $onlineUsers = $this->userModel->where('last_active_at >=', $fiveMinutesAgo)
                                       ->orderBy('last_active_at', 'DESC')
                                       ->limit(10)
                                       ->find();
        
        $html = '';
        foreach ($onlineUsers as $ou) {
            $name = esc($ou->name ?: $ou->email);
            $time = date('H:i', strtotime($ou->last_active_at));
            $html .= "<div style='background: white; padding: 8px 16px; border-radius: 100px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 500; color: #1e293b;'>
                        <span style='width: 8px; height: 8px; background: #10b981; border-radius: 50%;'></span>
                        $name
                        <span style='color: #94a3b8; font-size: 0.75rem; font-weight: 400;'>$time</span>
                      </div>";
        }
        if ($data['total_online'] > count($onlineUsers)) {
            $diff = $data['total_online'] - count($onlineUsers);
            $html .= "<div style='padding: 8px 16px; color: #64748b; font-size: 0.9rem;'>y $diff más...</div>";
        }
        $data['online_users_html'] = $html;

        return $this->response->setJSON($data);
    }






    /**
     * Formulario crear empresa
     */
    public function company_create()
    {
        $data = [
            'title' => 'Nueva Empresa | APIEmpresas',
            'company' => null
        ];
        return $this->renderView('admin/company_form', $data);
    }

    /**
     * Guardar nueva empresa
     */
    public function company_store()
    {
        $data = $this->request->getPost();

        // Validación básica
        if (
            !$this->validate([
                'company_name' => 'required|min_length[3]',
                'cif' => 'required|is_unique[companies.cif]',
            ])
        ) {
            return redirect()->back()->withInput()->with('error', 'Datos inválidos o CIF duplicado.');
        }

        $this->companyModel->insert($data);
        return redirect()->to(site_url('admin/companies'))->with('message', 'Empresa creada correctamente.');
    }

    /**
     * Formulario editar empresa
     */
    public function company_edit($id)
    {
        $company = $this->companyModel->find($id);
        if (!$company) {
            return redirect()->to(site_url('admin/companies'))->with('error', 'Empresa no encontrada.');
        }

        $data = [
            'title' => 'Editar Empresa | APIEmpresas',
            'company' => $company
        ];
        return $this->renderView('admin/company_form', $data);
    }

    /**
     * Actualizar empresa
     */
    public function company_update()
    {
        $id = $this->request->getPost('id');
        $data = $this->request->getPost();

        if (
            !$this->validate([
                'company_name' => 'required|min_length[3]',
                'cif' => "required|is_unique[companies.cif,id,{$id}]",
            ])
        ) {
            return redirect()->back()->withInput()->with('error', 'Datos inválidos o CIF duplicado.');
        }

        $this->companyModel->update($id, $data);
        return redirect()->to(site_url('admin/companies'))->with('message', 'Empresa actualizada correctamente.');
    }

    /**
     * Eliminar empresa
     */
    public function company_delete($id)
    {
        $this->companyModel->delete($id);
        return redirect()->to(site_url('admin/companies'))->with('message', 'Empresa eliminada correctamente.');
    }

    /**
     * Listado de planes API (CRUD)
     */
    public function plans()
    {
        $q = $this->request->getGet('q');
        $isActive = $this->request->getGet('is_active');

        $builder = $this->planModel;

        if ($q) {
            $builder->groupStart()
                    ->like('name', $q)
                    ->orLike('slug', $q)
                    ->groupEnd();
        }

        if ($isActive !== null && $isActive !== '') {
            $builder->where('is_active', $isActive);
        }

        $data = [
            'title' => 'Gestión de Planes | APIEmpresas',
            'plans' => $builder->orderBy('id', 'ASC')->paginate(20),
            'pager' => $this->planModel->pager,
            'q' => $q,
            'is_active' => $isActive,
        ];

        return $this->renderView('admin/plans', $data);
    }

    /**
     * Formulario crear plan
     */
    public function plan_create()
    {
        $data = [
            'title' => 'Nuevo Plan | APIEmpresas',
            'plan' => null
        ];
        return $this->renderView('admin/plan_form', $data);
    }

    /**
     * Guardar nuevo plan
     */
    public function plan_store()
    {
        $data = $this->request->getPost();

        if (
            !$this->validate([
                'name' => 'required|min_length[3]',
                'slug' => 'required|is_unique[api_plans.slug]',
                'monthly_quota' => 'required|numeric',
                'price_monthly' => 'required|decimal',
            ])
        ) {
            return redirect()->back()->withInput()->with('error', 'Datos del plan inválidos.');
        }

        $this->planModel->insert($data);
        return redirect()->to(site_url('admin/plans'))->with('message', 'Plan creado correctamente.');
    }

    /**
     * Formulario editar plan
     */
    public function plan_edit($id)
    {
        $plan = $this->planModel->find($id);
        if (!$plan) {
            return redirect()->to(site_url('admin/plans'))->with('error', 'Plan no encontrado.');
        }

        $data = [
            'title' => 'Editar Plan | APIEmpresas',
            'plan' => $plan
        ];
        return $this->renderView('admin/plan_form', $data);
    }

    /**
     * Actualizar plan
     */
    public function plan_update()
    {
        $id = $this->request->getPost('id');
        $data = $this->request->getPost();

        if (
            !$this->validate([
                'name' => 'required|min_length[3]',
                'slug' => "required|is_unique[api_plans.slug,id,{$id}]",
                'monthly_quota' => 'required|numeric',
                'price_monthly' => 'required|decimal',
            ])
        ) {
            return redirect()->back()->withInput()->with('error', 'Datos del plan inválidos.');
        }

        $this->planModel->update($id, $data);
        return redirect()->to(site_url('admin/plans'))->with('message', 'Plan actualizado correctamente.');
    }

    /**
     * Eliminar plan
     */
    public function plan_delete($id)
    {
        $this->planModel->delete($id);
        return redirect()->to(site_url('admin/plans'))->with('message', 'Plan eliminado correctamente.');
    }

    /**
     * Listado de API Keys (CRUD)
     */
    public function api_keys()
    {
        $q = $this->request->getGet('q');
        $userId = $this->request->getGet('user_id');
        $isActive = $this->request->getGet('is_active');

        $builder = $this->apiKeyModel;
        $builder->select('api_keys.*, users.name as user_name, users.email as user_email');
        $builder->join('users', 'users.id = api_keys.user_id', 'left');

        if ($q) {
            $builder->groupStart()
                    ->like('api_keys.name', $q)
                    ->orLike('api_keys.api_key', $q)
                    ->groupEnd();
        }

        if ($userId) {
            $builder->where('api_keys.user_id', $userId);
        }

        if ($isActive !== null && $isActive !== '') {
            $builder->where('api_keys.is_active', $isActive);
        }

        $data = [
            'title' => 'Gestión de API Keys | APIEmpresas',
            'keys' => $builder->orderBy('api_keys.created_at', 'DESC')->paginate(20),
            'pager' => $this->apiKeyModel->pager,
            'users' => $this->userModel->orderBy('name', 'ASC')->findAll(),
            'q' => $q,
            'user_id' => $userId,
            'is_active' => $isActive,
        ];

        return $this->renderView('admin/api_keys', $data);
    }

    /**
     * Formulario crear API Key
     */
    public function api_key_create()
    {
        $data = [
            'title' => 'Nueva API Key | APIEmpresas',
            'key' => null,
            'users' => $this->userModel->orderBy('name', 'ASC')->findAll(),
            'generated_key' => bin2hex(random_bytes(32)) // Generar una key por defecto
        ];
        return $this->renderView('admin/api_key_form', $data);
    }

    /**
     * Guardar nueva API Key
     */
    public function api_key_store()
    {
        $data = $this->request->getPost();

        if (
            !$this->validate([
                'user_id' => 'required|numeric',
                'name' => 'required|min_length[3]',
                'api_key' => 'required|is_unique[api_keys.api_key]',
            ])
        ) {
            return redirect()->back()->withInput()->with('error', 'Datos de la API Key inválidos.');
        }

        $this->apiKeyModel->insert($data);
        return redirect()->to(site_url('admin/api-keys'))->with('message', 'API Key creada correctamente.');
    }

    /**
     * Formulario editar API Key
     */
    public function api_key_edit($id)
    {
        $key = $this->apiKeyModel->find($id);
        if (!$key) {
            return redirect()->to(site_url('admin/api-keys'))->with('error', 'API Key no encontrada.');
        }

        $data = [
            'title' => 'Editar API Key | APIEmpresas',
            'key' => $key,
            'users' => $this->userModel->orderBy('name', 'ASC')->findAll()
        ];
        return $this->renderView('admin/api_key_form', $data);
    }

    /**
     * Actualizar API Key
     */
    public function api_key_update()
    {
        $id = $this->request->getPost('id');
        $data = $this->request->getPost();

        if (
            !$this->validate([
                'user_id' => 'required|numeric',
                'name' => 'required|min_length[3]',
                'api_key' => "required|is_unique[api_keys.api_key,id,{$id}]",
            ])
        ) {
            return redirect()->back()->withInput()->with('error', 'Datos de la API Key inválidos.');
        }

        $this->apiKeyModel->update($id, $data);
        return redirect()->to(site_url('admin/api-keys'))->with('message', 'API Key actualizada correctamente.');
    }

    /**
     * Eliminar API Key
     */
    public function api_key_delete($id)
    {
        $this->apiKeyModel->delete($id);
        return redirect()->to(site_url('admin/api-keys'))->with('message', 'API Key eliminada correctamente.');
    }

    /**
     * Listado de Suscripciones (CRUD)
     */
    public function subscriptions()
    {
        $userId = $this->request->getGet('user_id');
        $planId = $this->request->getGet('plan_id');
        $status = $this->request->getGet('status');

        $builder = $this->subscriptionModel;
        $builder->select('user_subscriptions.*, users.name as user_name, users.email as user_email, api_plans.name as plan_name');
        $builder->join('users', 'users.id = user_subscriptions.user_id', 'left');
        $builder->join('api_plans', 'api_plans.id = user_subscriptions.plan_id', 'left');

        if ($userId) {
            $builder->where('user_subscriptions.user_id', $userId);
        }

        if ($planId) {
            $builder->where('user_subscriptions.plan_id', $planId);
        }

        if ($status) {
            $builder->where('user_subscriptions.status', $status);
        }

        $data = [
            'title' => 'Gestión de Suscripciones | APIEmpresas',
            'subscriptions' => $builder->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $this->subscriptionModel->pager,
            'users' => $this->userModel->orderBy('name', 'ASC')->findAll(),
            'plans' => $this->planModel->orderBy('name', 'ASC')->findAll(),
            'user_id' => $userId,
            'plan_id' => $planId,
            'status' => $status,
        ];

        // Calcular stats por plan (suscripciones activas)
        $freeCount = 0; $proCount = 0; $businessCount = 0;
        $activeSubs = $this->subscriptionModel->select('api_plans.name as plan_name')
            ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id', 'left')
            ->where('user_subscriptions.status', 'active')
            ->findAll();

        foreach ($activeSubs as $s) {
            $name = strtolower($s->plan_name ?: '');
            if (strpos($name, 'free') !== false) $freeCount++;
            elseif (strpos($name, 'pro') !== false) $proCount++;
            elseif (strpos($name, 'business') !== false) $businessCount++;
        }

        $data['stats'] = [
            'free' => $freeCount,
            'pro' => $proCount,
            'business' => $businessCount,
        ];

        return $this->renderView('admin/subscriptions', $data);
    }

    /**
     * Formulario crear suscripción
     */
    public function subscription_create()
    {
        $data = [
            'title' => 'Nueva Suscripción | APIEmpresas',
            'subscription' => null,
            'users' => $this->userModel->orderBy('name', 'ASC')->findAll(),
            'plans' => $this->planModel->where('is_active', 1)->findAll()
        ];
        return $this->renderView('admin/subscription_form', $data);
    }

    /**
     * Guardar nueva suscripción
     */
    public function subscription_store()
    {
        $data = $this->request->getPost();

        if (
            !$this->validate([
                'user_id' => 'required|numeric',
                'plan_id' => 'required|numeric',
                'status' => 'required',
                'current_period_start' => 'required|valid_date',
                'current_period_end' => 'required|valid_date',
            ])
        ) {
            return redirect()->back()->withInput()->with('error', 'Datos de la suscripción inválidos.');
        }

        $this->subscriptionModel->insert($data);
        return redirect()->to(site_url('admin/subscriptions'))->with('message', 'Suscripción creada correctamente.');
    }

    /**
     * Formulario editar suscripción
     */
    public function subscription_edit($id)
    {
        $subscription = $this->subscriptionModel->find($id);
        if (!$subscription) {
            return redirect()->to(site_url('admin/subscriptions'))->with('error', 'Suscripción no encontrada.');
        }

        $data = [
            'title' => 'Editar Suscripción | APIEmpresas',
            'subscription' => $subscription,
            'users' => $this->userModel->orderBy('name', 'ASC')->findAll(),
            'plans' => $this->planModel->where('is_active', 1)->findAll()
        ];
        return $this->renderView('admin/subscription_form', $data);
    }

    /**
     * Actualizar suscripción
     */
    public function subscription_update()
    {
        $id = $this->request->getPost('id');
        $data = $this->request->getPost();

        if (
            !$this->validate([
                'user_id' => 'required|numeric',
                'plan_id' => 'required|numeric',
                'status' => 'required',
                'current_period_start' => 'required|valid_date',
                'current_period_end' => 'required|valid_date',
            ])
        ) {
            return redirect()->back()->withInput()->with('error', 'Datos de la suscripción inválidos.');
        }

        $this->subscriptionModel->update($id, $data);
        return redirect()->to(site_url('admin/subscriptions'))->with('message', 'Suscripción actualizada correctamente.');
    }

    /**
     * Eliminar suscripción
     */
    public function subscription_delete($id)
    {
        $this->subscriptionModel->delete($id);
        return redirect()->to(site_url('admin/subscriptions'))->with('message', 'Suscripción eliminada correctamente.');
    }

    /**
     * Dashboard de KPIs de emails
     */
    public function email_logs()
    {
        // Stats generales
        $totalSent = $this->emailLogModel->where('status', 'success')->countAllResults();
        $totalOpened = $this->emailLogModel->where('opened_at IS NOT NULL')->countAllResults();
        $totalClicked = $this->emailLogModel->where('clicked_at IS NOT NULL')->countAllResults();
        $totalLogged = $this->emailLogModel->where('logged_in_at IS NOT NULL')->countAllResults();

        $openRate = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 1) : 0;
        $clickRate = $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 1) : 0;
        $conversionRate = $totalSent > 0 ? round(($totalLogged / $totalSent) * 100, 1) : 0;

        // Filtros para historial
        $q = $this->request->getGet('q');
        $status = $this->request->getGet('status');
        $userId = $this->request->getGet('user_id');
        $opened = $this->request->getGet('opened');
        $clicked = $this->request->getGet('clicked');
        $logged = $this->request->getGet('logged');
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');

        // Historial reciente con joins
        $builder = $this->emailLogModel->select('email_logs.*, users.name as user_name, users.email as user_email');
        $builder->join('users', 'users.id = email_logs.user_id', 'left');

        if ($q) {
            $builder->groupStart()
                    ->like('users.name', $q)
                    ->orLike('users.email', $q)
                    ->orLike('email_logs.subject', $q)
                    ->groupEnd();
        }

        if ($status) {
            $builder->where('email_logs.status', $status);
        }

        if ($userId) {
            $builder->where('email_logs.user_id', $userId);
        }

        if ($opened === 'yes') {
            $builder->where('email_logs.opened_at IS NOT NULL');
        } elseif ($opened === 'no') {
            $builder->where('email_logs.opened_at IS NULL');
        }

        if ($clicked === 'yes') {
            $builder->where('email_logs.clicked_at IS NOT NULL');
        } elseif ($clicked === 'no') {
            $builder->where('email_logs.clicked_at IS NULL');
        }

        if ($logged === 'yes') {
            $builder->where('email_logs.logged_in_at IS NOT NULL');
        } elseif ($logged === 'no') {
            $builder->where('email_logs.logged_in_at IS NULL');
        }

        if ($dateFrom) {
            $builder->where('email_logs.created_at >=', $dateFrom . ' 00:00:00');
        }

        if ($dateTo) {
            $builder->where('email_logs.created_at <=', $dateTo . ' 23:59:59');
        }
        
        $logs = $builder->orderBy('email_logs.created_at', 'DESC')->paginate(30);

        $data = [
        'title' => 'KPIs de Emails | APIEmpresas',
        'logs' => $logs,
        'pager' => $this->emailLogModel->pager,
        'stats' => [
            'total_sent' => $totalSent,
            'total_opened' => $totalOpened,
            'total_clicked' => $totalClicked,
            'total_logged' => $totalLogged,
            'open_rate' => $openRate,
            'click_rate' => $clickRate,
            'conversion_rate' => $conversionRate
        ],
        'q' => $q,
        'status' => $status,
        'user_id' => $userId,
        'opened' => $opened,
        'clicked' => $clicked,
        'logged' => $logged,
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
        'all_users' => $this->userModel->orderBy('name', 'ASC')->findAll()
    ];

        return $this->renderView('admin/email_logs', $data);
    }
}
