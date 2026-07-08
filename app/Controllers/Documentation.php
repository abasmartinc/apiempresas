<?php

namespace App\Controllers;


class Documentation extends BaseController
{
    public function index()
    {
        return $this->renderView('documentation');
    }

    public function error($errorCode)
    {
        $data = [
            'errorCode' => $errorCode,
            'title' => 'Error: ' . strtoupper($errorCode)
        ];
        return $this->renderView('docs_error', $data);
    }
}

