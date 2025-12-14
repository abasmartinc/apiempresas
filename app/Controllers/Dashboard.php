<?php

namespace App\Controllers;
use App\Models\UserModel;


class Dashboard extends BaseController
{
    public function index()
    {
        // Opcional (pero recomendable): proteger el dashboard
        if (! session('logged_in')) {
            return redirect()->to(site_url('enter'))
                ->with('error', 'Debes iniciar sesión para acceder al panel.');
        }

        $userModel = new UserModel();

        $userId = session('user_id');
        $user   = $userModel->find($userId);
        $data['user'] = $user;

        return view('dashboard_construction', $data);
    }
}


