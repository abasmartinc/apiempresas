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
            return $this->response->setStatusCode(410)->setBody(view('errors/html/error_410', ['message' => 'Perfil eliminado por privacidad RGPD.']));
        }
        // 09-10-2026: se vuelven a indexar todos los perfiles. El 08-10 se puso noindex a los de
        // una sola empresa, pero los datos de Search Console (3 meses) muestran que las páginas de
        // administrador reciben clics con un CTR del 22 % (la gente busca a la persona por su
        // nombre) y la mitad de las que más clics traían eran de una sola empresa. Las bajas por
        // RGPD siguen funcionando igual (410, arriba).
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
