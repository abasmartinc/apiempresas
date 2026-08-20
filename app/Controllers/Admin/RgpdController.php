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

        // 1. Count company administrators (Optimizamos usando WHERE para aprovechar índices B-Tree)
        $adminCount = $db->table('company_administrators')->where('name', trim($name))->countAllResults();

        // 2. Count borme posts
        $bormeCount = $db->table('borme_posts')->like('description', trim($name))->countAllResults();

        // 3. Recopilar URLs afectadas
        $companyIds = [];
        $adminCompanies = $db->table('company_administrators')->select('company_id')->where('name', trim($name))->get()->getResultArray();
        $bormeCompanies = $db->table('borme_posts')->select('company_id')->like('description', trim($name))->get()->getResultArray();

        foreach ($adminCompanies as $c) { if (!empty($c['company_id'])) $companyIds[] = $c['company_id']; }
        foreach ($bormeCompanies as $c) { if (!empty($c['company_id'])) $companyIds[] = $c['company_id']; }
        
        $companyIds = array_unique($companyIds);

        $urlsToPurge = [];
        if (!empty($companyIds)) {
            $companyModel = new \App\Models\CompanyModel();
            $companies = $db->table('companies')
                            ->select('cif, company_name')
                            ->whereIn('id', $companyIds)
                            ->get()->getResultArray();
                            
            foreach ($companies as $c) {
                if (!empty($c['cif']) && !empty($c['company_name'])) {
                    $slugEmpresa = $companyModel->generateSlug($c['company_name']);
                    $publicUrl = "https://apiempresas.es/" . $c['cif'] . '-' . $slugEmpresa;
                    $urlsToPurge[] = $publicUrl;
                }
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'adminCount' => $adminCount,
            'bormeCount' => $bormeCount,
            'urls' => $urlsToPurge,
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

        // PASO 1: LECTURA INOFENSIVA (Fuera de la transacción para no bloquear la base de datos)
        // Buscamos los IDs primarios (y company_id) afectados. 
        $adminRoles = $db->table('company_administrators')
                         ->select('id, company_id')
                         ->where('name', $name)
                         ->get()->getResultArray();
                         
        $bormePosts = $db->table('borme_posts')
                         ->select('id, company_id')
                         ->like('description', $name)
                         ->get()->getResultArray();

        $adminIds = array_column($adminRoles, 'id');
        $bormeIds = array_column($bormePosts, 'id');

        // Extraemos los IDs de las empresas para la caché
        $companyIds = [];
        foreach ($adminRoles as $c) { if (!empty($c['company_id'])) $companyIds[] = $c['company_id']; }
        foreach ($bormePosts as $c) { if (!empty($c['company_id'])) $companyIds[] = $c['company_id']; }
        $companyIds = array_unique($companyIds);

        // PASO 2: Recopilar las URLs exactas de esas empresas
        $urlsToPurge = [];
        if (!empty($companyIds)) {
            $companyModel = new \App\Models\CompanyModel();
            $companies = $db->table('companies')
                            ->select('cif, company_name')
                            ->whereIn('id', $companyIds)
                            ->get()->getResultArray();
                            
            foreach ($companies as $c) {
                if (!empty($c['cif']) && !empty($c['company_name'])) {
                    $slugEmpresa = $companyModel->generateSlug($c['company_name']);
                    $urlsToPurge[] = "https://apiempresas.es/" . $c['cif'] . '-' . $slugEmpresa;
                }
            }
        }

        // PASO 3: ACTUALIZACIÓN QUIRÚRGICA (Transacción ultra rápida usando Primary Keys)
        $db->transStart();

        $exists = $db->table('admin_privacy_optouts')->where('slug', $slug)->countAllResults() > 0;
        if (!$exists) {
            $db->table('admin_privacy_optouts')->insert([
                'slug' => $slug,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        if (!empty($adminIds)) {
            $db->table('company_administrators')
               ->whereIn('id', $adminIds)
               ->update(['name' => 'Identidad Protegida (RGPD)']);
        }

        if (!empty($bormeIds)) {
            // Creamos los placeholders dinámicos para el IN (?, ?, ?)
            $placeholders = implode(',', array_fill(0, count($bormeIds), '?'));
            $sql = "UPDATE borme_posts SET description = REPLACE(description, ?, 'Identidad Protegida (RGPD)') WHERE id IN ($placeholders)";
            
            // Los parámetros son: el $name (para el REPLACE) seguido de todos los IDs
            $params = array_merge([$name], $bormeIds);
            $db->query($sql, $params);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al procesar la solicitud en la base de datos']);
        }

        // PASO 4: Vaciar la caché en Cloudflare para las URLs afectadas
        $cfPurged = false;
        if (!empty($urlsToPurge)) {
            $cfZoneId = env('CLOUDFLARE_ZONE_ID');
            $cfToken = env('CLOUDFLARE_API_TOKEN');
            
            if ($cfZoneId && $cfToken) {
                $chunks = array_chunk($urlsToPurge, 30);
                $client = \Config\Services::curlrequest();
                
                foreach ($chunks as $chunk) {
                    try {
                        $client->post("https://api.cloudflare.com/client/v4/zones/{$cfZoneId}/purge_cache", [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $cfToken,
                                'Content-Type'  => 'application/json',
                            ],
                            'json' => ['files' => $chunk],
                            'http_errors' => false
                        ]);
                        $cfPurged = true;
                    } catch (\Exception $e) {
                        log_message('error', 'Error purgado Cloudflare RGPD: ' . $e->getMessage());
                    }
                }
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Datos anonimizados correctamente.' . ($cfPurged ? ' Caché limpia en ' . count($urlsToPurge) . ' empresas.' : '')
        ]);
    }
}
