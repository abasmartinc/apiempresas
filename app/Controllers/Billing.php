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

    public function purchase_success()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }
        return view('purchase_success');
    }

    public function billing_manage()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }
        return view('billing_manage');
    }
}

