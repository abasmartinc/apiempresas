<?php

namespace App\Controllers;


class Documentation extends BaseController
{
    public function index()
    {
        return $this->renderView('documentation');
    }
}

