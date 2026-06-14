<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;
use CodeIgniter\HTTP\ResponseInterface;

class CompanyMapV2Controller extends Controller
{
    public function index()
    {
        helper('radar');
        return view('map/companies_map', [
            'title' => 'Base de datos de empresas | Compra listados B2B',
            'excerptText' => 'Descarga al instante tu base de datos de empresas españolas. Filtra por provincia y sector. Listados B2B extraídos del BORME listos para tu CRM o telemarketing.',
        ]);
    }

    // ---------- GEO (Provinces / Municipalities) ----------

    public function provinces()
    {
        try {
            $db = Database::connect();
            $rows = $db->table('provinces')
                ->select('id, pro_name')
                ->orderBy('pro_name', 'ASC')
                ->get()->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'data'    => $rows,
            ]);
        } catch (\Throwable $e) {
            return $this->jsonError(500, 'SERVER_ERROR', 'Error cargando provincias');
        }
    }

    public function municipalities()
    {
        $provinceId = (int)($this->request->getGet('province_id') ?? 0);
        if ($provinceId <= 0) {
            return $this->jsonError(422, 'VALIDATION_ERROR', 'province_id requerido');
        }

        try {
            $db = Database::connect();
            $rows = $db->table('municipalities')
                ->select('id, mun_name')
                ->where('mun_province_id', $provinceId)
                ->orderBy('mun_name', 'ASC')
                ->get()->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'data'    => $rows,
            ]);
        } catch (\Throwable $e) {
            return $this->jsonError(500, 'SERVER_ERROR', 'Error cargando municipios');
        }
    }

    // ---------- CNAE ----------

    public function cnaeSections()
    {
        try {
            $db = Database::connect();
            $rows = $db->table('cnae_sections')
                ->select('id, name')
                ->orderBy('id', 'ASC')
                ->get()->getResultArray();

            return $this->response->setJSON(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            return $this->jsonError(500, 'SERVER_ERROR', 'Error cargando secciones CNAE');
        }
    }

    public function cnaeGroups()
    {
        try {
            $db = Database::connect();
            $sql = "SELECT cnae_2009 as slug, MAX(label_2009) as name 
                    FROM cnae_2009_2025 
                    WHERE LENGTH(cnae_2009) = 2 
                    GROUP BY cnae_2009 
                    ORDER BY name ASC";
            $rows = $db->query($sql)->getResultArray();

            return $this->response->setJSON(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            return $this->jsonError(500, 'SERVER_ERROR', 'Error cargando grupos CNAE');
        }
    }

    public function cnaeClasses()
    {
        $groupId = (int)($this->request->getGet('group_id') ?? 0);
        if ($groupId <= 0) {
            return $this->jsonError(422, 'VALIDATION_ERROR', 'group_id requerido');
        }

        try {
            $db = Database::connect();
            $rows = $db->table('cnae_classes')
                ->select('id, name')
                ->where('parent_group_id', $groupId)
                ->orderBy('id', 'ASC')
                ->get()->getResultArray();

            return $this->response->setJSON(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            return $this->jsonError(500, 'SERVER_ERROR', 'Error cargando clases CNAE');
        }
    }

    public function cnaeSubclasses()
    {
        $classId = (int)($this->request->getGet('class_id') ?? 0);
        if ($classId <= 0) {
            return $this->jsonError(422, 'VALIDATION_ERROR', 'class_id requerido');
        }

        try {
            $db = Database::connect();
            $rows = $db->table('cnae_subclasses')
                ->select('id, name, slug')
                ->where('parent_class_id', $classId)
                ->orderBy('id', 'ASC')
                ->get()->getResultArray();

            return $this->response->setJSON(['success' => true, 'data' => $rows]);
        } catch (\Throwable $e) {
            return $this->jsonError(500, 'SERVER_ERROR', 'Error cargando subclases CNAE');
        }
    }

    // ---------- MAP SEARCH ----------

    public function search()
    {
        $req = $this->request;

        $north = $this->toFloat($req->getGet('north'));
        $south = $this->toFloat($req->getGet('south'));
        $east  = $this->toFloat($req->getGet('east'));
        $west  = $this->toFloat($req->getGet('west'));

        if ($north === null || $south === null || $east === null || $west === null) {
            return $this->jsonError(422, 'VALIDATION_ERROR', 'bbox requerido');
        }

        $provinceName = trim((string)($req->getGet('province') ?? ''));
        $municipalityName = trim((string)($req->getGet('municipality') ?? ''));
        $estado = trim((string)($req->getGet('estado') ?? ''));
        $limit      = (int)($req->getGet('limit') ?? 5000);
        $page       = (int)($req->getGet('page') ?? 1);
        $perPage    = 50;
        $offset     = ($page - 1) * $perPage;

        $onlyGeocoded = (int)($req->getGet('only_geocoded') ?? 0);
        $useBbox = (int)($req->getGet('use_bbox') ?? 1);

        $cnaePrefix = trim((string)($req->getGet('cnae_prefix') ?? ''));
        $cnaeText   = trim((string)($req->getGet('cnae_text') ?? ''));
        $hasPhone = (int)($req->getGet('has_phone') ?? 0);
        $dateMin = $req->getPost('date_min') ?? $req->getGet('date_min') ?? '';
        $dateMax = $req->getPost('date_max') ?? $req->getGet('date_max') ?? '';

        try {
            $db = Database::connect();
            $applyFilters = function($b) use ($provinceName, $municipalityName, $estado, $cnaePrefix, $cnaeText, $hasPhone, $dateMin, $dateMax) {
                if ($provinceName !== '') $b->where('registro_mercantil', $provinceName);
                if ($municipalityName !== '') $b->like('address', $municipalityName, 'both');
                if ($estado !== '') $b->where('estado', $estado);
                
                if ($cnaePrefix !== '') {
                    $norm = preg_replace('/[^0-9]/', '', $cnaePrefix);
                    if ($norm !== '') $b->like('cnae_code', $norm, 'after');
                } elseif ($cnaeText !== '') {
                    $b->like('cnae_label', $cnaeText, 'both');
                }

                if ($dateMin !== '') $b->where('estado_fecha >=', $dateMin);
                if ($dateMax !== '') $b->where('estado_fecha <=', $dateMax);

                if ($hasPhone === 1) {
                    $b->groupStart()
                        ->groupStart()->where('phone IS NOT NULL', null, false)->where('phone !=', '')->groupEnd()
                        ->orGroupStart()->where('phone_mobile IS NOT NULL', null, false)->where('phone_mobile !=', '')->groupEnd()
                        ->groupEnd();
                }
            };

            $fields = [
                'id', 'company_name', 'address', 'cif', 'cnae_code', 'cnae_label', 
                'registro_mercantil', 'estado', 'estado_fecha', 'phone', 'phone_mobile', 
                'lat_num AS lat', 'lng_num AS lng'
            ];

            // 1. Datos del Mapa (Solo página 1, limit 5000, respetando siempre geocoding y bbox)
            $mapData = [];
            if ($page === 1) {
                $bMap = $db->table('companies');
                $bMap->select($fields);
                $applyFilters($bMap);

                if ($onlyGeocoded === 1) {
                    $bMap->where('lat_num IS NOT NULL', null, false)
                         ->where('lng_num IS NOT NULL', null, false);
                }
                if ($useBbox === 1) {
                    $bMap->where('lat_num >=', $south)->where('lat_num <=', $north)
                         ->where('lng_num >=', $west)->where('lng_num <=', $east);
                }
                $bMap->orderBy('estado_fecha', 'DESC')->limit($limit);
                $mapData = $bMap->get()->getResultArray();
            }

            // 2. Datos de la Lista (Paginada, ignora bbox y geocoding si hay provincia/municipio)
            $bList = $db->table('companies');
            $bList->select($fields);
            $applyFilters($bList);

            $ignoreBbox = ($provinceName !== '' || $municipalityName !== '');
            if (!$ignoreBbox && $useBbox === 1) {
                $bList->where('lat_num >=', $south)->where('lat_num <=', $north)
                      ->where('lng_num >=', $west)->where('lng_num <=', $east);
            }

            $totalCount = $bList->countAllResults(false);
            $bList->orderBy('estado_fecha', 'DESC')->limit($perPage, $offset);
            $listData = $bList->get()->getResultArray();

            helper('pricing');
            
            $isPremium = false;
            if (!empty($dateMin) && strtotime($dateMin) >= strtotime('-90 days')) {
                $isPremium = true;
            }
            $priceData = calculate_directory_price($totalCount, $isPremium);

            $meta = [
                'total_count'   => $totalCount,
                'dynamic_price' => $priceData['base_price'] ?? 9,
                'limit'         => $limit,
                'with_phone'    => 0,
                'top_cnae'      => [],
                'page'          => $page,
                'per_page'      => $perPage,
                'total_pages'   => ceil($totalCount / $perPage),
            ];

            $cnaeAgg = [];
            foreach ($listData as $r) {
                if (!empty($r['phone']) || !empty($r['phone_mobile'])) {
                    $meta['with_phone']++;
                }
                $cc = $r['cnae_code'] ?: 'N/D';
                $cnaeAgg[$cc] = ($cnaeAgg[$cc] ?? 0) + 1;
            }

            arsort($cnaeAgg);
            $meta['top_cnae'] = array_slice($cnaeAgg, 0, 5, true);

            return $this->response->setJSON([
                'success' => true,
                'meta'    => $meta,
                'map_data' => $mapData,
                'list_data' => $listData,
            ]);
        } catch (\Throwable $e) {
            return $this->jsonError(500, 'SERVER_ERROR', 'Error ejecutando búsqueda');
        }
    }

    // ---------- HELPERS ----------

    private function jsonError(int $status, string $code, string $message): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON([
            'success' => false,
            'code'    => $code,
            'message' => $message,
        ]);
    }
    public function requestFreeSample()
    {
        $req = $this->request;
        $email = trim((string)$req->getPost('email'));
        
        $province = trim((string)$req->getPost('province'));
        $municipality = trim((string)$req->getPost('municipality'));
        $cnaePrefix = trim((string)$req->getPost('cnae_prefix'));
        $cnaeText = trim((string)$req->getPost('cnae_text'));
        $estado = trim((string)$req->getPost('estado'));
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonError(400, 'INVALID_EMAIL', 'Email inválido');
        }

        try {
            $db = Database::connect();
            
            // 1. Save Lead
            $db->table('leads_radar')->insert([
                'email' => $email,
                'province' => $province ?: null,
                'source' => 'map_free_sample',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // 2. Fetch 20 companies
            $b = $db->table('companies');
            $b->select('id, company_name as name, cif, cnae_label, registro_mercantil, municipality, address, phone, phone_mobile, objeto_social, fecha_constitucion');
            if ($province !== '') $b->where('registro_mercantil', $province);
            if ($municipality !== '') $b->like('address', $municipality, 'both');
            if ($estado !== '') $b->where('estado', $estado);
            
            if ($cnaePrefix !== '') {
                $norm = preg_replace('/[^0-9]/', '', $cnaePrefix);
                if ($norm !== '') $b->like('cnae_code', $norm, 'after');
            } elseif ($cnaeText !== '') {
                $b->like('cnae_label', $cnaeText, 'both');
            }

            // Ensure highest quality first (with phone, with objeto_social) and strict deterministic order
            $b->orderBy("IF(phone != '' OR phone_mobile != '', 1, 0)", 'DESC', false);
            $b->orderBy("IF(objeto_social IS NOT NULL AND objeto_social != '', 1, 0)", 'DESC', false);
            $b->orderBy('estado_fecha', 'DESC');
            $b->orderBy('id', 'DESC');
            $b->limit(20);
            
            $results = $b->get()->getResultArray();

            if (!empty($results)) {
                $companyIds = array_column($results, 'id');
                
                $adminRows = $db->table('company_administrators')
                    ->select('company_id, position, name')
                    ->whereIn('company_id', $companyIds)
                    ->get()->getResultArray();
                
                $adminsByCompany = [];
                foreach ($adminRows as $row) {
                    $cid = $row['company_id'];
                    $position = $row['position'] ?: 'Administrador';
                    $adminsByCompany[$cid][] = $position . ': ' . $row['name'];
                }

                $bormeRows = $db->table('borme_posts')
                    ->select('company_id, description')
                    ->whereIn('company_id', $companyIds)
                    ->get()->getResultArray();

                $bormeExtracted = [];
                foreach ($bormeRows as $row) {
                    $cid = $row['company_id'];
                    $desc = $row['description'] ?? '';
                    if (!isset($bormeExtracted[$cid])) $bormeExtracted[$cid] = ['capital' => '', 'socio_unico' => ''];
                    if (empty($bormeExtracted[$cid]['capital']) && preg_match('/Capital:\s*([\d\.,]+\s*Euros?)/iu', $desc, $m)) {
                        $bormeExtracted[$cid]['capital'] = trim($m[1]);
                    }
                    if (empty($bormeExtracted[$cid]['socio_unico']) && preg_match('/Socio único:\s*([^.]+)\./iu', $desc, $m)) {
                        $bormeExtracted[$cid]['socio_unico'] = trim($m[1]);
                    }
                }

                foreach ($results as &$c) {
                    $cid = $c['id'];
                    $c['administrators'] = isset($adminsByCompany[$cid]) ? implode(', ', $adminsByCompany[$cid]) : '';
                    $c['capital_social'] = $bormeExtracted[$cid]['capital'] ?? '';
                    $c['socio_unico'] = $bormeExtracted[$cid]['socio_unico'] ?? '';
                }
            }

            // 3. Generate XLS (HTML table format)
            $htmlExcel = $this->generateExcelHtmlSample($results);

            // 4. Send Email
            $emailService = \Config\Services::email();
            $emailService->setTo($email);
            $emailService->setSubject('Tu muestra gratuita de empresas - APIEmpresas');
            $emailService->setMessage("¡Hola!\n\nAdjunto encontrarás la muestra gratuita de 20 empresas que has solicitado en nuestro mapa interactivo en formato Excel.\n\nSi quieres descargar la base de datos completa, puedes volver a la web y realizar la compra.\n\nUn saludo.");
            
            // CI4 attach() treats the first argument as file content buffer if a mime type is provided
            $emailService->attach($htmlExcel, 'attachment', 'muestra_empresas.xls', 'application/vnd.ms-excel');
            
            $emailService->send();

            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            log_message('error', 'Error sending free sample: ' . $e->getMessage());
            return $this->jsonError(500, 'SERVER_ERROR', 'Error interno al procesar la muestra');
        }
    }

    private function generateExcelHtmlSample(array $companies): string
    {
        ob_start();
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>';
        
        $thStyle = 'background-color: #2563eb; color: #ffffff; font-weight: bold; border: 1px solid #000000; padding: 10px; text-align: center;';
        $tdStyle = 'border: 1px solid #cccccc; padding: 8px; vertical-align: top;';
        $textStyle = $tdStyle . ' mso-number-format:"\@";'; 
        
        echo '<table border="1">';
        echo '<thead><tr>';
        echo '<th style="' . $thStyle . '">Nombre de la Empresa</th>';
        echo '<th style="' . $thStyle . '">CIF</th>';
        echo '<th style="' . $thStyle . '">Dirección</th>';
        echo '<th style="' . $thStyle . '">Municipio</th>';
        echo '<th style="' . $thStyle . '">Provincia</th>';
        echo '<th style="' . $thStyle . '">Sector CNAE</th>';
        echo '<th style="' . $thStyle . '">Objeto Social</th>';
        echo '<th style="' . $thStyle . '">Fecha Registro</th>';
        echo '<th style="' . $thStyle . '">Administradores y Cargos</th>';
        echo '<th style="' . $thStyle . '">Socio Único</th>';
        echo '<th style="' . $thStyle . '">Capital Social</th>';
        echo '</tr></thead><tbody>';

        foreach ($companies as $company) {
            $rawDate   = $company['fecha_constitucion'] ?? '';
            $ts        = $rawDate ? strtotime(str_replace('/', '-', $rawDate)) : false;
            $cleanDate = ($ts && $ts >= strtotime('1900-01-01') && $ts <= strtotime('2100-01-01')) ? date('d/m/Y', $ts) : '';

            echo '<tr>';
            echo '<td style="' . $tdStyle . '">' . esc($company['name'] ?? '') . '</td>';
            echo '<td style="' . $textStyle . '">' . esc($company['cif'] ?? '') . '</td>';
            echo '<td style="' . $tdStyle . '">' . esc($company['address'] ?? '') . '</td>';
            echo '<td style="' . $tdStyle . '">' . esc($company['municipality'] ?? '') . '</td>';
            echo '<td style="' . $tdStyle . '">' . esc($company['registro_mercantil'] ?? '') . '</td>';
            echo '<td style="' . $tdStyle . '">' . esc($company['cnae_label'] ?? '') . '</td>';
            echo '<td style="' . $tdStyle . '">' . esc($company['objeto_social'] ?? '') . '</td>';
            echo '<td style="' . $tdStyle . '">' . $cleanDate . '</td>';
            echo '<td style="' . $tdStyle . '">' . esc($company['administrators'] ?? '') . '</td>';
            echo '<td style="' . $tdStyle . '">' . esc($company['socio_unico'] ?? '') . '</td>';
            echo '<td style="' . $tdStyle . '">' . esc($company['capital_social'] ?? '') . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table></body></html>';
        return ob_get_clean();
    }

    private function toFloat($v): ?float
    {
        if ($v === null) return null;
        $v = str_replace(',', '.', (string)$v);
        if ($v === '' || !is_numeric($v)) return null;
        return (float)$v;
    }
}
