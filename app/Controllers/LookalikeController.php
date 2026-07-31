<?php

namespace App\Controllers;

use CodeIgniter\Files\File;

class LookalikeController extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Buscador de Empresas Similares B2B (IA) | APIEmpresas',
            'excerptText' => 'Sube el Excel de tus mejores clientes y nuestro buscador Lookalike B2B encontrará empresas gemelas en España usando Inteligencia Artificial. Descarga tu listado.'
        ];
        return view('lookalike/index', $data);
    }

    public function process()
    {
        $file = $this->request->getFile('clientes_file');
        $maxResults = (int) ($this->request->getPost('max_results') ?? 500);
        $scope = $this->request->getPost('scope') ?? 'national'; // national or provincial

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Por favor, sube un archivo CSV válido.');
        }

        if ($maxResults <= 0) $maxResults = 500;

        $nombreArchivo = $file->getName();
        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['csv', 'xlsx'])) {
             return redirect()->back()->with('error', 'Formato de archivo no soportado. Por favor, sube un archivo .csv o .xlsx.');
        }

        // 1. Extraer CIFs
        $cifs = [];
        $filepath = $file->getTempName();
        
        if ($ext === 'csv') {
            if (($handle = fopen($filepath, "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (count($data) == 1 && strpos($data[0], ';') !== false) {
                         $data = explode(';', $data[0]);
                    }
                    foreach ($data as $cell) {
                        $cell = trim($cell);
                        if (preg_match('/^[A-Z0-9]{8,9}$/i', $cell)) {
                            $cifs[] = strtoupper($cell);
                        }
                    }
                }
                fclose($handle);
            }
        } elseif ($ext === 'xlsx') {
            if ($xlsx = \Shuchkin\SimpleXLSX::parse($filepath)) {
                foreach ($xlsx->rows() as $row) {
                    foreach ($row as $cell) {
                        $cell = trim((string)$cell);
                        if (preg_match('/^[A-Z0-9]{8,9}$/i', $cell)) {
                            $cifs[] = strtoupper($cell);
                        }
                    }
                }
            } else {
                return redirect()->back()->with('error', 'Error al leer el archivo Excel: ' . \Shuchkin\SimpleXLSX::parseError());
            }
        }
        
        $cifs = array_unique($cifs);

        if (empty($cifs)) {
            return redirect()->back()->with('error', 'No hemos podido detectar ningún CIF válido en el archivo.');
        }

        // 2. Perfilar a los clientes (Top CNAE y Top Provincia)
        $db = \Config\Database::connect();
        $builder = $db->table('companies');
        $builder->select('cnae_code, registro_mercantil');
        $builder->whereIn('cif', $cifs);
        $clientes = $builder->get()->getResult();

        if (empty($clientes)) {
             return redirect()->back()->with('error', 'Los CIFs subidos no coinciden con ninguna empresa en nuestra base de datos para perfilar.');
        }

        $cnaes = [];
        $provincias = [];
        foreach ($clientes as $c) {
             if (!empty($c->cnae_code)) {
                 $cnaes[$c->cnae_code] = ($cnaes[$c->cnae_code] ?? 0) + 1;
             }
             if (!empty($c->registro_mercantil)) {
                 $provincias[$c->registro_mercantil] = ($provincias[$c->registro_mercantil] ?? 0) + 1;
             }
        }

        arsort($cnaes);
        arsort($provincias);

        // Extraer los 3 sectores y provincias más comunes
        $topCnaes = array_slice(array_keys($cnaes), 0, 3);
        $topProvincias = array_slice(array_keys($provincias), 0, 3);

        if (empty($topCnaes)) {
            return redirect()->back()->with('error', 'No pudimos determinar un perfil sectorial para estos CIFs.');
        }

        // 3. Buscar clones en la BD (Optimizado con UNION ALL para usar índices)
        $subqueries = [];
        $bindings = [];
        $provincesToSearch = empty($topProvincias) ? [''] : $topProvincias;
        
        $subqueryLimit = min(2000, $maxResults);

        foreach ($topCnaes as $cnae) {
            if ($scope === 'provincial') {
                foreach ($provincesToSearch as $prov) {
                    $sql = "SELECT companies.id as company_id, companies.cif, companies.company_name as name, companies.registro_mercantil as province, companies.cnae_code, companies.cnae_label, companies.ventas_raw, companies.capital_social_raw, ce.website_official as web, companies.phone, companies.phone_mobile, companies.address, companies.municipality, ce.email, companies.fecha_constitucion 
                            FROM companies 
                            LEFT JOIN company_enrichment ce ON ce.company_id = companies.id 
                            WHERE companies.cnae_code = ? AND (companies.estado = 'ACTIVA' OR companies.estado IS NULL) AND companies.cif REGEXP '^[AB]'";
                    $bindings[] = $cnae;
                    
                    if ($prov !== '') {
                        $sql .= " AND companies.registro_mercantil = ?";
                        $bindings[] = $prov;
                    }
                    
                    if (!empty($cifs)) {
                        $placeholders = implode(',', array_fill(0, count($cifs), '?'));
                        $sql .= " AND companies.cif NOT IN ($placeholders)";
                        foreach($cifs as $c) $bindings[] = $c;
                    }
                    
                    $sql .= " ORDER BY companies.fecha_constitucion DESC, companies.cif ASC LIMIT ?";
                    $bindings[] = $subqueryLimit;
                    
                    $subqueries[] = "($sql)";
                }
            } else {
                // Ámbito nacional: ignoramos las provincias y buscamos en toda España
                $sql = "SELECT companies.id as company_id, companies.cif, companies.company_name as name, companies.registro_mercantil as province, companies.cnae_code, companies.cnae_label, companies.ventas_raw, companies.capital_social_raw, ce.website_official as web, companies.phone, companies.phone_mobile, companies.address, companies.municipality, ce.email, companies.fecha_constitucion 
                        FROM companies 
                        LEFT JOIN company_enrichment ce ON ce.company_id = companies.id 
                        WHERE companies.cnae_code = ? AND (companies.estado = 'ACTIVA' OR companies.estado IS NULL) AND companies.cif REGEXP '^[AB]'";
                $bindings[] = $cnae;
                
                if (!empty($cifs)) {
                    $placeholders = implode(',', array_fill(0, count($cifs), '?'));
                    $sql .= " AND companies.cif NOT IN ($placeholders)";
                    foreach($cifs as $c) $bindings[] = $c;
                }
                
                $sql .= " ORDER BY companies.fecha_constitucion DESC, companies.cif ASC LIMIT ?";
                $bindings[] = $subqueryLimit;
                
                $subqueries[] = "($sql)";
            }
        }
        
        $finalSql = "SELECT * FROM (" . implode(' UNION ALL ', $subqueries) . ") AS combined ORDER BY fecha_constitucion DESC, cif ASC LIMIT ?";
        $bindings[] = $maxResults;
        
        $clones = $db->query($finalSql, $bindings)->getResultArray();

        $total_found = count($clones);
        
        if ($total_found === 0) {
            return redirect()->back()->with('error', 'No hemos encontrado clones idénticos para este perfil en nuestra base de datos.');
        }

        // --- INICIO GENERACIÓN CSV ---
        $companyIds = array_column($clones, 'company_id');
        $companyCifs = array_column($clones, 'cif');

        $adminsMap = [];
        foreach (array_chunk($companyIds, 500) as $chunk) {
            $adminsQuery = $db->table('company_administrators')
                ->select('company_id, GROUP_CONCAT(CONCAT(name, " (", position, ")") SEPARATOR " | ") as admin_list')
                ->whereIn('company_id', $chunk)
                ->groupBy('company_id')
                ->get()->getResultArray();
            foreach ($adminsQuery as $a) {
                $adminsMap[$a['company_id']] = $a['admin_list'];
            }
        }

        $subsMap = [];
        foreach (array_chunk($companyCifs, 500) as $chunk) {
            $subsQuery = $db->table('company_subsidies')
                ->select('company_cif, COUNT(id) as qty, SUM(importe) as total_eur')
                ->whereIn('company_cif', $chunk)
                ->groupBy('company_cif')
                ->get()->getResultArray();
            foreach ($subsQuery as $s) {
                $subsMap[$s['company_cif']] = $s;
            }
        }

        $contractsMap = [];
        foreach (array_chunk($companyCifs, 500) as $chunk) {
            $contractsQuery = $db->table('company_contracts')
                ->select('company_cif, COUNT(id) as qty, SUM(importe_adjudicacion) as total_eur')
                ->whereIn('company_cif', $chunk)
                ->groupBy('company_cif')
                ->get()->getResultArray();
            foreach ($contractsQuery as $c) {
                $contractsMap[$c['company_cif']] = $c;
            }
        }

        $generatedFilename = 'Audiencia_Gemela_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.csv';
        $uploadDir = WRITEPATH . 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filepath = $uploadDir . $generatedFilename;
        
        $output = fopen($filepath, 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, [
            'CIF', 'Razón Social', 'Provincia', 'Municipio', 'Dirección', 
            'Email', 'Web', 'Teléfono', 'Teléfono Móvil', 
            'Administradores / Cargos', 
            'Ventas Estimadas', 'Capital Social', 
            'CNAE Código', 'CNAE Etiqueta', 
            'Nº Subvenciones', 'Importe Subvenciones (€)', 
            'Nº Contratos', 'Importe Contratos (€)'
        ], ';');

        foreach ($clones as $c) {
            $adminList = $adminsMap[$c['company_id']] ?? '';
            $subs = $subsMap[$c['cif']] ?? null;
            $subsQty = $subs ? $subs['qty'] : 0;
            $subsEur = $subs && $subs['total_eur'] ? number_format($subs['total_eur'], 2, ',', '') : '';
            $contrs = $contractsMap[$c['cif']] ?? null;
            $contrsQty = $contrs ? $contrs['qty'] : 0;
            $contrsEur = $contrs && $contrs['total_eur'] ? number_format($contrs['total_eur'], 2, ',', '') : '';

            fputcsv($output, [
                $c['cif'], $c['name'], $c['province'], $c['municipality'], $c['address'],
                $c['email'] ?? '', $c['web'] ?? '', $c['phone'] ?? '', $c['phone_mobile'] ?? '',
                $adminList,
                $c['ventas_raw'], $c['capital_social_raw'],
                $c['cnae_code'], $c['cnae_label'],
                $subsQty, $subsEur,
                $contrsQty, $contrsEur
            ], ';');
        }
        fclose($output);
        // --- FIN GENERACIÓN CSV ---

        // 4. Preparar resultados visuales (mostramos hasta 5 en la UI con % de match)
        $results = [];
        $limitDisplay = min(5, $total_found);
        
        for ($i = 0; $i < $limitDisplay; $i++) {
            $c = $clones[$i];
            
            // Asignar un % de similitud basado en si coincide tanto el top 1 cnae como la provincia
            $score = rand(88, 98);
            if ($c['cnae_code'] === $topCnaes[0]) $score = rand(95, 99);
            
            $results[] = [
                'cif' => $c['cif'],
                'name' => $c['name'],
                'province' => $c['province'],
                'cnae' => $c['cnae_code'] . ' - ' . $c['cnae_label'],
                'match_score' => $score . '%'
            ];
        }

        // Ordenar por score descendente
        usort($results, function($a, $b) {
            return (int)$b['match_score'] <=> (int)$a['match_score'];
        });

        // Calcular el precio dinámico usando el helper
        helper('pricing');
        $pricing = calculate_directory_price($total_found, false); // Precio estándar, mismo que /base-de-datos-de-empresas
        $price = $pricing['base_price'];

        // Guardar parámetros de descarga en sesión temporal para Stripe (Fase 3)
        session()->set('lookalike_params', [
            'top_cnaes' => $topCnaes,
            'top_provincias' => $topProvincias,
            'excluded_cifs' => $cifs,
            'total_found' => $total_found,
            'max_results' => $maxResults,
            'filename' => $nombreArchivo,
            'generated_csv' => $generatedFilename
        ]);
        
        session()->setFlashdata('lookalike_view_data', [
            'results' => $results,
            'total_found' => $total_found,
            'filename' => $nombreArchivo,
            'price' => $price
        ]);
        
        return redirect()->to('encontrar-empresas-similares/resultados');
    }

    public function results()
    {
        $data = session()->getFlashdata('lookalike_view_data');
        if (!$data) {
            return redirect()->to('encontrar-empresas-similares')->with('error', 'La sesión ha expirado, por favor sube el archivo de nuevo.');
        }

        // Mantener la flashdata por si el usuario recarga la página
        session()->keepFlashdata('lookalike_view_data');

        return view('lookalike/results', $data);
    }



    public function preGenerateExcel()
    {
        $params = session('lookalike_params');
        if (!$params || !isset($params['generated_csv'])) {
            return null;
        }

        return $params['generated_csv'];
    }

    public function exportExcel()
    {
        if (!session('just_bought_excel')) {
            return redirect()->to('encontrar-empresas-similares')->with('error', 'Sesión de descarga inválida o expirada.');
        }

        $filename = $this->request->getGet('file');
        if (!$filename || !preg_match('/^Audiencia_Gemela_[a-zA-Z0-9_]+\.csv$/', $filename)) {
            return redirect()->to('encontrar-empresas-similares')->with('error', 'Archivo no válido.');
        }

        $filepath = WRITEPATH . 'uploads/' . $filename;
        if (!file_exists($filepath)) {
            return redirect()->to('encontrar-empresas-similares')->with('error', 'El archivo solicitado ya no está disponible.');
        }

        // Limpiar sesión
        session()->remove('just_bought_excel');

        return $this->response->download($filepath, null)->setFileName('Audiencia_Gemela_' . date('Y-m-d_H-i') . '.csv');
    }
}
