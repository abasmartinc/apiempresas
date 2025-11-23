<?php

namespace App\Controllers;


class Billing extends BaseController
{
    public function index()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }
        return view('billing');
    }
}

