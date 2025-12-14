<?php


namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class CompanyMapController extends Controller
{
    /**
     * Vista del módulo mapa
     */
    public function index()
    {
        return view('map/companies_map', [
            'title' => 'Mapa de Empresas',
        ]);
    }

    /**
     * API: devuelve empresas dentro de un bounding box + filtros (JSON)
     * Params:
     *  - north, south, east, west (required)
     *  - cnae (optional)  -> puede ser "62.01" o "6201" o prefijo
     *  - estado (optional)
     *  - has_phone (0/1 optional)
     *  - only_geocoded (0/1 optional) default 1
     *  - limit (optional) default 500 max 5000
     */
    public function companies()
    {
        $req = $this->request;

        $north = $this->toFloat($req->getGet('north'));
        $south = $this->toFloat($req->getGet('south'));
        $east = $this->toFloat($req->getGet('east'));
        $west = $this->toFloat($req->getGet('west'));

        if ($north === null || $south === null || $east === null || $west === null) {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => 'Missing bbox params: north,south,east,west',
            ]);
        }

        // límites razonables
        $limit = (int)($req->getGet('limit') ?? 500);
        if ($limit < 50) $limit = 50;
        if ($limit > 5000) $limit = 5000;

        $cnae = trim((string)($req->getGet('cnae') ?? ''));
        $estado = trim((string)($req->getGet('estado') ?? ''));
        $hasPhone = (int)($req->getGet('has_phone') ?? 0);
        $onlyGeocoded = (int)($req->getGet('only_geocoded') ?? 1);

        $db = Database::connect();

        // IMPORTANTE: esta query asume que creaste lat_num/lng_num (SQL Opción A)
        $builder = $db->table('empresia_company_details');

        $builder->select([
            'id',
            'company_name',
            'address',
            'cif',
            'cnae_code',
            'cnae_label',
            'estado',
            'estado_fecha',
            'phone',
            'phone_mobile',
            'lat_num AS lat',
            'lng_num AS lng',
        ]);

        if ($onlyGeocoded === 1) {
            $builder->where('lat_num IS NOT NULL', null, false)
                ->where('lng_num IS NOT NULL', null, false);
        }

        // Bounding box
        $builder->where('lat_num >=', $south)
            ->where('lat_num <=', $north)
            ->where('lng_num >=', $west)
            ->where('lng_num <=', $east);

        // Filtro CNAE (prefijo, tolerante)
        if ($cnae !== '') {
            // normalizar: quitar puntos/espacios
            $cnaeNorm = preg_replace('/[^0-9]/', '', $cnae);
            // cnae_code puede venir con punto, por eso comparamos por prefijo numérico “like”
            $builder->groupStart()
                ->like("REPLACE(cnae_code,'.','')", $cnaeNorm, 'after', false)
                ->orLike('cnae_label', $cnae, 'both')
                ->groupEnd();
        }

        if ($estado !== '') {
            $builder->where('estado', $estado);
        }

        if ($hasPhone === 1) {
            $builder->groupStart()
                ->where('phone IS NOT NULL', null, false)
                ->where('phone !=', '')
                ->orGroupStart()
                ->where('phone_mobile IS NOT NULL', null, false)
                ->where('phone_mobile !=', '')
                ->groupEnd()
                ->groupEnd();
        }

        // Orden: primero las que tienen nombre y datos más completos
        $builder->orderBy('estado_fecha', 'DESC');
        $builder->limit($limit);

        $rows = $builder->get()->getResultArray();

        // Insights básicos
        $total = count($rows);
        $withPhone = 0;
        $byEstado = [];
        $byCnae = [];

        foreach ($rows as $r) {
            if (!empty($r['phone']) || !empty($r['phone_mobile'])) $withPhone++;

            $st = $r['estado'] ?: 'N/D';
            $byEstado[$st] = ($byEstado[$st] ?? 0) + 1;

            $cc = $r['cnae_code'] ?: 'N/D';
            $byCnae[$cc] = ($byCnae[$cc] ?? 0) + 1;
        }

        arsort($byCnae);
        $topCnae = array_slice($byCnae, 0, 5, true);

        return $this->response->setJSON([
            'meta' => [
                'count' => $total,
                'with_phone' => $withPhone,
                'top_cnae' => $topCnae,
                'by_estado' => $byEstado,
                'limit' => $limit,
            ],
            'data' => $rows,
        ]);
    }

    /**
     * Export CSV del bbox + filtros (mismos params que companies)
     */
    public function export()
    {
        $req = $this->request;

        $north = $this->toFloat($req->getGet('north'));
        $south = $this->toFloat($req->getGet('south'));
        $east = $this->toFloat($req->getGet('east'));
        $west = $this->toFloat($req->getGet('west'));

        if ($north === null || $south === null || $east === null || $west === null) {
            return $this->response->setStatusCode(422)->setBody('Missing bbox params');
        }

        $limit = (int)($req->getGet('limit') ?? 2000);
        if ($limit < 100) $limit = 100;
        if ($limit > 20000) $limit = 20000;

        $cnae = trim((string)($req->getGet('cnae') ?? ''));
        $estado = trim((string)($req->getGet('estado') ?? ''));
        $hasPhone = (int)($req->getGet('has_phone') ?? 0);
        $onlyGeocoded = (int)($req->getGet('only_geocoded') ?? 1);

        $db = Database::connect();
        $builder = $db->table('empresia_company_details');

        $builder->select([
            'company_name',
            'cif',
            'address',
            'phone',
            'phone_mobile',
            'cnae_code',
            'cnae_label',
            'fecha_constitucion',
            'registro_mercantil',
            'ult_cuentas_anio',
            'estado',
            'estado_fecha',
            'lat_num AS lat',
            'lng_num AS lng',
        ]);

        if ($onlyGeocoded === 1) {
            $builder->where('lat_num IS NOT NULL', null, false)
                ->where('lng_num IS NOT NULL', null, false);
        }

        $builder->where('lat_num >=', $south)
            ->where('lat_num <=', $north)
            ->where('lng_num >=', $west)
            ->where('lng_num <=', $east);

        if ($cnae !== '') {
            $cnaeNorm = preg_replace('/[^0-9]/', '', $cnae);
            $builder->groupStart()
                ->like("REPLACE(cnae_code,'.','')", $cnaeNorm, 'after', false)
                ->orLike('cnae_label', $cnae, 'both')
                ->groupEnd();
        }

        if ($estado !== '') {
            $builder->where('estado', $estado);
        }

        if ($hasPhone === 1) {
            $builder->groupStart()
                ->where('phone IS NOT NULL', null, false)
                ->where('phone !=', '')
                ->orGroupStart()
                ->where('phone_mobile IS NOT NULL', null, false)
                ->where('phone_mobile !=', '')
                ->groupEnd()
                ->groupEnd();
        }

        $builder->orderBy('estado_fecha', 'DESC');
        $builder->limit($limit);

        $rows = $builder->get()->getResultArray();

        $filename = 'empresas_mapa_' . date('Ymd_His') . '.csv';

        $this->response->setHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

        // output stream
        $fp = fopen('php://output', 'w');

        // BOM UTF-8 para Excel
        fwrite($fp, "\xEF\xBB\xBF");

        // header
        fputcsv($fp, array_keys($rows[0] ?? [
            'company_name', 'cif', 'address', 'phone', 'phone_mobile', 'cnae_code', 'cnae_label',
            'fecha_constitucion', 'registro_mercantil', 'ult_cuentas_anio', 'estado', 'estado_fecha', 'lat', 'lng'
        ]), ';');

        foreach ($rows as $r) {
            fputcsv($fp, $r, ';');
        }

        fclose($fp);
        return $this->response;
    }

    private function toFloat($v): ?float
    {
        if ($v === null) return null;
        $v = str_replace(',', '.', (string)$v);
        if ($v === '' || !is_numeric($v)) return null;
        return (float)$v;
    }
}
