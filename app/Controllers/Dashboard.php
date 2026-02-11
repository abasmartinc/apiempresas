<?php

namespace App\Controllers;
use App\Models\ApikeysModel;
use App\Models\ApiRequestsModel;
use App\Models\UserModel;
use App\Models\UsersuscriptionsModel;


class Dashboard extends BaseController
{
    /** @var UserModel */
    protected $userModel;
    protected $ApikeysModel;
    protected $UsersuscriptionsModel;
    protected $ApiRequestsModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->ApikeysModel = new ApikeysModel();
        $this->UsersuscriptionsModel = new UsersuscriptionsModel();
        $this->ApiRequestsModel = new ApiRequestsModel();
    }
    public function index()
    {
        if (! session('logged_in')) {
            return redirect()->to(site_url('enter'))
                ->with('error', 'Debes iniciar sesión para acceder al panel.');
        }

        $userModel = new UserModel();

        $userId = session('user_id');
        $user   = $userModel->find($userId);
        $data['user'] = $user;
        // Log dashboard visit
        log_activity('dashboard_visit');

        if ($user->is_admin ?? false) {
            $data['title'] = 'Panel de Administración';
            return view('admin/dashboard', $data);
        }

        // --- Personalization stats for non-admins ---
        $data['api_key'] = $this->ApikeysModel->where(['user_id' => $userId, 'is_active' => 1])->first();
        $data['plan'] = $this->UsersuscriptionsModel->getActivePlanByUserId($userId);
        $data['api_request_total_month'] = $this->ApiRequestsModel->countRequestsForMonth(date('Y-m'), ['user_id' => $userId]);
        $data['avg_latency'] = $this->ApiRequestsModel->getAverageLatency(['user_id' => $userId]);
        $data['error_rate']  = $this->ApiRequestsModel->getErrorRate(['user_id' => $userId]);

        // Si tiene plan activo, va al dashboard de pago
        if ($data['plan']) {
            return view('dashboard_paid', $data);
        }

        // Si tiene acceso API habilitado manualmente (gradual access)
        if (($user->api_access ?? 0) == 1) {
            return view('dashboard', $data);
        }

        return view('dashboard_construction', $data);
    }

    /**
     * Terminar impersonación y volver a admin
     */
    public function stopImpersonating()
    {
        $impersonatorId = session('impersonator_id');
        
        if (!$impersonatorId) {
            return redirect()->to(site_url('dashboard'));
        }

        $adminUser = $this->userModel->find($impersonatorId);
        
        if (!$adminUser || !$adminUser->is_admin) {
             // Fallback raro: el impersonador ya no existe o no es admin
             session()->destroy();
             return redirect()->to(site_url('enter'))->with('error', 'Sesión de administrador no válida.');
        }

        // Restaurar sesión de admin
        session()->regenerate();
        session()->set([
            'user_id'    => $adminUser->id,
            'user_email' => $adminUser->email,
            'user_name'  => $adminUser->name ?? '',
            'is_admin'   => 1,
            'logged_in'  => true,
        ]);

        return redirect()->to(site_url('admin/users'))->with('message', 'Has vuelto a tu cuenta de administrador.');
    }
}


