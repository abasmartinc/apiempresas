<?php

namespace App\Controllers;


class Prices extends BaseController
{
    public function index()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }
        return view('prices');
    }
}

