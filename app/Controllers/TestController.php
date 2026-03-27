<?php
namespace App\Controllers;
use App\Models\CompanyModel;

class TestController extends BaseController
{
    public function index()
    {
        $model = new CompanyModel();
        
        $latest = $model->getLatestCompanies(10);
        foreach ($latest as $l) {
            echo "ID: {$l['id']} - Name: {$l['company_name']} - Date: {$l['fecha_constitucion']}\n";
        }

        echo "\nRadar Data:\n";
        $radar = new RadarController();
        $base = $radar->getRadarData('España', null, 'hoy', 5);
        foreach ($base['companies'] as $l) {
            echo "ID: {$l['id']} - Name: {$l['name']} - Date: {$l['fecha_constitucion']}\n";
        }
        
        echo "\nRadar Semana Data:\n";
        $base = $radar->getRadarData('España', null, 'semana', 5);
        foreach ($base['companies'] as $l) {
            echo "ID: {$l['id']} - Name: {$l['name']} - Date: {$l['fecha_constitucion']}\n";
        }
    }
}
