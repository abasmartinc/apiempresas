<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;
use CodeIgniter\HTTP\ResponseInterface;

class CompanyMapV2Controller extends Controller
{
    public function index()
    {
        return view('map/companies_map', [
            'title' => 'Mapa inteligente de empresas',
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
        $sectionId = (int)($this->request->getGet('section_id') ?? 0);
        if ($sectionId <= 0) {
            return $this->jsonError(422, 'VALIDATION_ERROR', 'section_id requerido');
        }

        try {
            $db = Database::connect();
            $rows = $db->table('cnae_groups')
                ->select('id, name')
                ->where('parent_section_id', $sectionId)
                ->orderBy('id', 'ASC')
                ->get()->getResultArray();

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

        $limit = max(100, min(5000, (int)($req->getGet('limit') ?? 1500)));

        $provinceName = trim((string)($req->getGet('province') ?? ''));
        $municipalityName = trim((string)($req->getGet('municipality') ?? ''));
        $estado = trim((string)($req->getGet('estado') ?? ''));
        $hasPhone = (int)($req->getGet('has_phone') ?? 0);
        $onlyGeocoded = (int)($req->getGet('only_geocoded') ?? 1);
        $useBbox = (int)($req->getGet('use_bbox') ?? 1);

        $cnaePrefix = trim((string)($req->getGet('cnae_prefix') ?? ''));
        $cnaeText   = trim((string)($req->getGet('cnae_text') ?? ''));

        try {
            $db = Database::connect();
            $b  = $db->table('empresia_company_details');

            $b->select([
                'id',
                'company_name',
                'address',
                'cif',
                'cnae_code',
                'cnae_label',
                'registro_mercantil',
                'estado',
                'estado_fecha',
                'phone',
                'phone_mobile',
                'lat_num AS lat',
                'lng_num AS lng',
            ]);

            if ($onlyGeocoded === 1) {
                $b->where('lat_num IS NOT NULL', null, false)
                    ->where('lng_num IS NOT NULL', null, false);
            }

            if ($useBbox === 1) {
                $b->where('lat_num >=', $south)
                    ->where('lat_num <=', $north)
                    ->where('lng_num >=', $west)
                    ->where('lng_num <=', $east);
            }

            if ($provinceName !== '') {
                $b->where('registro_mercantil', $provinceName);
            }

            if ($municipalityName !== '') {
                $b->like('address', $municipalityName, 'both');
            }

            if ($estado !== '') {
                $b->where('estado', $estado);
            }

            // CNAE (prefijo directo; tu cnae_code ya viene sin puntos)
            if ($cnaePrefix !== '') {
                $norm = preg_replace('/[^0-9]/', '', $cnaePrefix); // por si llega "62.01" desde UI
                if ($norm !== '') {
                    $b->like('cnae_code', $norm, 'after'); // cnae_code LIKE 'norm%'
                }
            } elseif ($cnaeText !== '') {
                $b->like('cnae_label', $cnaeText, 'both');
            }


            if ($hasPhone === 1) {
                $b->groupStart()
                    ->groupStart()
                    ->where('phone IS NOT NULL', null, false)
                    ->where('phone !=', '')
                    ->groupEnd()
                    ->orGroupStart()
                    ->where('phone_mobile IS NOT NULL', null, false)
                    ->where('phone_mobile !=', '')
                    ->groupEnd()
                    ->groupEnd();
            }

            $b->orderBy('estado_fecha', 'DESC');
            $b->limit($limit);

            $rows = $b->get()->getResultArray();

            $meta = [
                'count' => count($rows),
                'limit' => $limit,
                'with_phone' => 0,
                'top_cnae' => [],
            ];

            $cnaeAgg = [];
            foreach ($rows as $r) {
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
                'data'    => $rows,
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

    private function toFloat($v): ?float
    {
        if ($v === null) return null;
        $v = str_replace(',', '.', (string)$v);
        if ($v === '' || !is_numeric($v)) return null;
        return (float)$v;
    }
}
