<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class PlansController extends BaseController
{
    public function free()
    {
        $data = [
            'title' => 'Plan Free - Testing y Validación Básica | API Empresas',
            'meta_description' => 'Descubre los casos de uso del Plan Free de API Empresas. Ideal para probar la API y realizar validaciones básicas de CIF sin coste.'
        ];
        return view('planes/free', $data);
    }

    public function pro()
    {
        $data = [
            'title' => 'Plan Pro - Automatización B2B | API Empresas',
            'meta_description' => 'Casos de uso del Plan Pro: Automatiza la validación de clientes en tu ERP o SaaS, enriquecimiento de datos y scoring comercial.'
        ];
        return view('planes/pro', $data);
    }

    public function business()
    {
        $data = [
            'title' => 'Plan Business - Inteligencia de Cuentas Masiva | API Empresas',
            'meta_description' => 'Casos de uso del Plan Business: Webhooks en tiempo real, notificaciones del BORME e inteligencia de cuentas masiva para equipos de ventas.'
        ];
        return view('planes/business', $data);
    }
}
