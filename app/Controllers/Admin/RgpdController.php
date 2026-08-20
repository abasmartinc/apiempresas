<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class RgpdController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        // Load history of opt-outs
        $optouts = $db->table('admin_privacy_optouts')->orderBy('created_at', 'DESC')->get()->getResultArray();

        return view('admin/rgpd_dashboard', [
            'title' => 'Gestión RGPD',
            'optouts' => $optouts
        ]);
    }

    public function preview()
    {
        $name = $this->request->getPost('name');
        if (empty($name)) {
            return $this->response->setJSON(['success' => false, 'message' => 'El nombre es obligatorio']);
        }

        $db = \Config\Database::connect();

        // 1. Count company administrators
        $adminCount = $db->table('company_administrators')
                         ->like('name', trim($name))
                         ->countAllResults();

        // 2. Count borme posts
        $bormeCount = $db->table('borme_posts')
                         ->like('text', trim($name))
                         ->countAllResults();

        return $this->response->setJSON([
            'success' => true,
            'adminCount' => $adminCount,
            'bormeCount' => $bormeCount,
            'name' => $name
        ]);
    }

    public function execute()
    {
        $name = $this->request->getPost('name');
        $slug = $this->request->getPost('slug');

        if (empty($name) || empty($slug)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Faltan datos (nombre o slug)']);
        }

        $db = \Config\Database::connect();
        $name = trim($name);
        $slug = trim($slug);

        $db->transStart();

        // 1. Insert into admin_privacy_optouts
        $exists = $db->table('admin_privacy_optouts')->where('slug', $slug)->countAllResults() > 0;
        if (!$exists) {
            $db->table('admin_privacy_optouts')->insert([
                'slug' => $slug,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // 2. Update company_administrators
        $db->table('company_administrators')
           ->like('name', $name)
           ->update(['name' => 'Identidad Protegida (RGPD)']);

        // 3. Update borme_posts
        // using DB raw query for REPLACE
        $sql = "UPDATE borme_posts SET text = REPLACE(text, ?, 'Identidad Protegida (RGPD)') WHERE text LIKE ?";
        $db->query($sql, [$name, '%' . $name . '%']);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al procesar la solicitud en la base de datos']);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Datos anonimizados correctamente.'
        ]);
    }
}
