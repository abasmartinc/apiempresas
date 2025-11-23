<?php

namespace App\Controllers;


class Contact extends BaseController
{
    public function index()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }
        return view('contact');
    }
}

