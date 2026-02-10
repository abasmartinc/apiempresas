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
    }

    /**
     * Listado de usuarios
     */
    public function index()
    {
        $q = $this->request->getGet('q');
        $active = $this->request->getGet('is_active');
        $admin = $this->request->getGet('is_admin');

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

        $data = [
            'title' => 'Gestión de Usuarios | APIEmpresas',
            'users' => $builder->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $this->userModel->pager,
            'q' => $q,
            'is_active' => $active,
            'is_admin' => $admin
        ];

        return view('admin/users', $data);
    }

    /**
     * Listado de logs de búsqueda
     */
    public function logs()
    {
        $httpStatus = $this->request->getGet('http_status');

        $builder = $this->searchLogModel;

        if ($httpStatus) {
            $builder->where('http_status', $httpStatus);
        }

        $data = [
            'title' => 'Logs de Búsqueda | APIEmpresas',
            'logs' => $builder->orderBy('created_at', 'DESC')->paginate(30),
            'pager' => $this->searchLogModel->pager,
            'http_status' => $httpStatus
        ];

        return view('admin/logs', $data);
    }

    /**
     * Cambiar estado de 'included' en un log
     */
    public function toggle_log_included($id)
    {
        $log = $this->searchLogModel->find($id);
        if (!$log) {
            return redirect()->back()->with('error', 'Log no encontrado.');
        }

        $newStatus = $log->included ? 0 : 1;
        $this->searchLogModel->update($id, ['included' => $newStatus]);

        return redirect()->back()->with('message', 'Estado actualizado correctamente.');
    }

    /**
     * Verificar si un CIF existe en la base de datos (AJAX)
     */
    public function check_cif()
    {
        $cif = $this->request->getGet('cif');
        if (!$cif) {
            return $this->response->setJSON(['exists' => false, 'error' => 'CIF no proporcionado']);
        }

        $exists = $this->companyModel->where('cif', $cif)->first();

        return $this->response->setJSON([
            'exists' => $exists ? true : false,
            'company_name' => $exists ? $exists->company_name : null
        ]);
    }

    /**
     * Listado de peticiones API
     */
    public function api_requests()
    {
        $q = $this->request->getGet('q');
        $userId = $this->request->getGet('user_id');
        $statusCode = $this->request->getGet('status_code');

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

        $data = [
            'title' => 'Peticiones API | APIEmpresas',
            'requests' => $builder->orderBy('created_at', 'DESC')->paginate(40, 'default'),
            'pager' => $this->apiRequestsModel->pager,
            'users' => $this->userModel->orderBy('name', 'ASC')->findAll(),
            'q' => $q,
            'user_id' => $userId,
            'status_code' => $statusCode
        ];

        return view('admin/api_requests', $data);
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
            'end_date' => $endDate
        ];

        return view('admin/usage_daily', $data);
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

        $data = [
            'title' => 'Redactar Email',
            'user' => $user,
        ];

        return view('admin/email_compose', $data);
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

        return view('admin/user_form', $data);
    }

    /**
     * Guardar nuevo usuario
     */
    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Revisa los errores: ' . implode(' ', $this->validator->getErrors()));
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

        return view('admin/user_form', $data);
    }

    /**
     * Actualizar usuario
     */
    public function update()
    {
        $id = $this->request->getPost('id');
        $rules = [
            'name' => 'required|min_length[3]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Revisa los errores.');
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
        $invoiceModel = new \App\Models\InvoiceModel();

        // Búsqueda simple
        $search = $this->request->getGet('search');
        if ($search) {
            $invoiceModel->groupStart()
                ->like('invoice_number', $search)
                ->orLike('billing_name', $search)
                ->orLike('billing_email', $search)
                ->groupEnd();
        }

        $data = [
            'title' => 'Gestión de Facturas',
            'invoices' => $invoiceModel->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $invoiceModel->pager,
            'search' => $search
        ];

        return view('admin/invoices', $data);
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

        $email = \Config\Services::email();

        $email->setTo($user->email);
        $email->setSubject($subject);

        // Usar plantilla HTML
        $body = view('emails/user_notification', [
            'user' => $user,
            'content' => $message,
            'subject' => $subject
        ]);

        $email->setMessage($body);

        $logData = [
            'user_id' => $userId,
            'subject' => $subject,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ];

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
        $selectAll = $this->request->getVar('select_all_filtered');

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
            $count = $builder->countAllResults(false); // false to not reset query for next call if needed, though here we just count
            $targetDescription = "Todos los usuarios filtrados ($count)";
        } elseif ($ids) {
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }
            $count = count($ids);
            $targetDescription = "$count usuarios seleccionados";
        } else {
            return redirect()->to(site_url('admin/users'))->with('error', 'No has seleccionado ningún usuario.');
        }

        $data = [
            'title' => 'Redactar Email Masivo',
            'count' => $count,
            'target_description' => $targetDescription,
            // Pass hidden inputs to the view
            'hidden_inputs' => [
                'user_ids' => is_array($ids) ? implode(',', $ids) : $ids,
                'q' => $filter_q,
                'is_active' => $filter_active,
                'is_admin' => $filter_admin,
                'select_all_filtered' => $selectAll
            ]
        ];

        return view('admin/email_compose_bulk', $data);
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
            // Reset email service for each iteration
            $emailService->clear();

            $emailService->setTo($user->email);
            $emailService->setSubject($subject);

            // Usar plantilla HTML
            $body = view('emails/user_notification', [
                'user' => $user,
                'content' => $message,
                'subject' => $subject
            ]);

            $emailService->setMessage($body);

            $logData = [
                'user_id' => $user->id,
                'subject' => $subject,
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($emailService->send()) {
                $logData['status'] = 'success';
                $sentCount++;
            } else {
                $logData['status'] = 'error';
                $logData['error_message'] = $emailService->printDebugger(['headers']);
                $errorCount++;
            }
            $this->emailLogModel->insert($logData);
        }

        $msg = "Proceso finalizado. Enviados: $sentCount.";
        if ($errorCount > 0) {
            $msg .= " Errores: $errorCount.";
            session()->setFlashdata('error', "Hubo algunos errores al enviar.");
        }

        return redirect()->to(site_url('admin/users'))->with('message', $msg);
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
            return view('admin/partials/companies_table', [
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

        return view('admin/companies', $data);
    }

    /**
     * Obtiene un KPI específico mediante AJAX
     */
    public function company_kpi_ajax($type)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody('Acceso no permitido');
        }

        $value = 0;
        switch ($type) {
            case 'total':
                $value = $this->companyModel->countAllResults();
                break;
            case 'sin_cif':
                $value = $this->companyModel->where('cif', '')->orWhere('cif', null)->countAllResults();
                break;
            case 'sin_direccion':
                $value = $this->companyModel->where('address', '')->orWhere('address', null)->countAllResults();
                break;
            case 'sin_estado':
                $value = $this->companyModel->where('estado', '')->orWhere('estado', null)->countAllResults();
                break;
            case 'sin_cnae':
                $value = $this->companyModel->where('cnae_code', '')->orWhere('cnae_code', null)->countAllResults();
                break;
            case 'sin_registro_mercantil':
                $value = $this->companyModel->where('registro_mercantil', '')->orWhere('registro_mercantil', null)->countAllResults();
                break;
            case 'added_today':
                $value = $this->companyModel->where('DATE(created_at)', date('Y-m-d'))->countAllResults();
                break;
        }

        return $this->response->setJSON([
            'value' => number_format($value, 0, ',', '.')
        ]);
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
        return view('admin/company_form', $data);
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
        return view('admin/company_form', $data);
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
        $data = [
            'title' => 'Gestión de Planes | APIEmpresas',
            'plans' => $this->planModel->orderBy('id', 'ASC')->findAll(),
        ];

        return view('admin/plans', $data);
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
        return view('admin/plan_form', $data);
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
        return view('admin/plan_form', $data);
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
        $this->apiKeyModel->select('api_keys.*, users.name as user_name, users.email as user_email');
        $this->apiKeyModel->join('users', 'users.id = api_keys.user_id', 'left');

        $data = [
            'title' => 'Gestión de API Keys | APIEmpresas',
            'keys' => $this->apiKeyModel->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $this->apiKeyModel->pager,
        ];

        return view('admin/api_keys', $data);
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
        return view('admin/api_key_form', $data);
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
        return view('admin/api_key_form', $data);
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
            'status' => $status
        ];

        return view('admin/subscriptions', $data);
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
        return view('admin/subscription_form', $data);
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
        return view('admin/subscription_form', $data);
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
     * Listado de Logs de Emails
     */
    public function email_logs()
    {
        $this->emailLogModel->select('email_logs.*, users.name as user_name, users.email as user_email');
        $this->emailLogModel->join('users', 'users.id = email_logs.user_id', 'left');

        $data = [
            'title' => 'Logs de Emails | APIEmpresas',
            'logs' => $this->emailLogModel->orderBy('created_at', 'DESC')->paginate(20),
            'pager' => $this->emailLogModel->pager,
        ];

        return view('admin/email_logs', $data);
    }
}
