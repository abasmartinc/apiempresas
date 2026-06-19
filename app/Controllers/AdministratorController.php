<?php

namespace App\Controllers;

use App\Models\CompanyAdministratorModel;

class AdministratorController extends BaseController
{
    public function show($slug)
    {

        $adminModel = new CompanyAdministratorModel();
        $adminData = $adminModel->getAdminInfoAndCompanies($slug);

        if (!$adminData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $adminName = $adminData['admin_name'];
        $companies = $adminData['companies'];

        // Remove duplicates if the admin has multiple positions in the same company
        // Group by company ID
        $uniqueCompanies = [];
        foreach ($companies as $c) {
            $cid = $c['id'];
            if (!isset($uniqueCompanies[$cid])) {
                $uniqueCompanies[$cid] = $c;
                $uniqueCompanies[$cid]['positions'] = [];
            }
            $uniqueCompanies[$cid]['positions'][] = [
                'position' => $c['position'],
                'action'   => $c['action'] ?? '-',
            ];
        }

        $title = "Cargos y empresas de " . esc($adminName) . " - APIEmpresas";
        $description = "Consulte la lista de empresas, cargos directivos y vinculaciones mercantiles de " . esc($adminName) . ". Nombramientos, ceses y estado de sus sociedades.";

        // Check if admin has requested privacy opt-out (Right to be Forgotten)
        $db = \Config\Database::connect();
        $isOptedOut = $db->table('admin_privacy_optouts')->where('slug', $slug)->countAllResults() > 0;
        if ($isOptedOut) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Perfil eliminado por privacidad.');
        }
        $robots = 'index,follow';

        return view('administrator', [
            'adminName'   => $adminName,
            'companies'   => array_values($uniqueCompanies),
            'title'       => $title,
            'description' => $description,
            'slug'        => $slug,
            'robots'      => $robots,
        ]);
    }
}
