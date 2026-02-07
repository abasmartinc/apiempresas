<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\UserModel;

class Alerts extends BaseController
{
    public function confirm($cif)
    {
        // 1. Check Login
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'))->with('error', 'Debes iniciar sesión para crear una alerta.');
        }

        // 2. Validate Company
        $companyModel = new CompanyModel();
        $company = $companyModel->getByCif($cif);

        if (!$company) {
            return redirect()->back()->with('error', 'Empresa no encontrada.');
        }

        $data = [
            'cif' => $cif,
            'company_name' => $company['name'] ?? 'Empresa desconocida',
            'user' => session('user_name')
        ];

        return view('alerts/confirm', $data);
    }

    public function add()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = session('user_id');
        $cif = $this->request->getPost('cif');
        $companyName = $this->request->getPost('company_name');

        $db = \Config\Database::connect();
        
        // Check if already watching
        $exists = $db->table('borme_watchlist')
            ->where('user_id', $userId)
            ->where('cif', $cif)
            ->countAllResults();

        if ($exists > 0) {
            return redirect()->to(site_url('dashboard'))->with('message', 'Ya estás siguiendo a esta empresa.');
        }

        // Check Limit
        $userSubsModel = new \App\Models\UsersuscriptionsModel();
        $plan = $userSubsModel->getActivePlanByUserId($userId);
        
        // Default to 1 if no plan found (fallback)
        $maxAlerts = $plan->max_alerts ?? 1;

        $currentCount = $db->table('borme_watchlist')
            ->where('user_id', $userId)
            ->countAllResults();

        if ($currentCount >= $maxAlerts) {
            return redirect()
                ->to(site_url('billing'))
                ->with('upgrade_limit', "Has alcanzado el límite de <strong>$maxAlerts empresas</strong>.<br>Para seguir monitorizando las publicaciones en el BORME de tus clientes y empresas de interés, elige el plan <strong>PRO</strong>.");
        }

        $db->table('borme_watchlist')->insert([
            'user_id' => $userId,
            'cif' => $cif,
            'company_name' => $companyName,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to(site_url('dashboard'))->with('message', "¡Alerta activada correctamente para $companyName!");
    }
}
