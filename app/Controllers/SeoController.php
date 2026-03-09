<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use CodeIgniter\Cache\CacheInterface;

class SeoController extends BaseController
{
    protected $companyModel;
    protected CacheInterface $cache;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->cache = \Config\Services::cache();
        helper(['form', 'url', 'pricing']); // Load pricing helper
    }

    /**
     * Tipo 1: Empresas por provincia
     * URL: /empresas/madrid
     */
    public function province($provinceSlug)
    {
        $province = $this->deSlugify($provinceSlug);

        $cacheKey = "seo_province_" . md5($province);
        $data = $this->cache->get($cacheKey);

        if (!$data) {
            $data = $this->getProvinceData($province);
            if ($data) {
                $this->cache->save($cacheKey, $data, 1296000); // 15 días
            }
        }

        // Si los datos vienen de la caché antigua, pueden faltar claves. Invalido y reintento si es necesario.
        if ($data && (!isset($data['total_companies']) || !isset($data['recent_companies']))) {
            $this->cache->delete($cacheKey);
            $data = $this->getProvinceData($province);
            if ($data) {
                $this->cache->save($cacheKey, $data, 1296000);
            }
        }

        if (!$data || empty($data['total'])) {
            return redirect()->to(site_url('directorio'))->with('error', 'Provincia no encontrada');
        }

        $data['title'] = "Empresas en {$province} | Listado y Estadísticas | APIEmpresas";
        $data['meta_description'] = "Descubre las {$data['total_formatted']} empresas en {$province}. Análisis de sectores, crecimiento del " . $data['growth_pct'] . "% y empresas de reciente creación.";
        $data['canonical'] = current_url();

        // Coherencia con la vista (Unificación Radar Hub)
        $data['total_companies'] = $data['total'];
        $data['recent_companies'] = $data['latest'];
        $data['companies'] = $data['latest'];
        $data['period'] = 'general';
        $data['active_menu'] = 'new_companies';
        $data['paywall_level'] = 'none';
        $data['total_context_count'] = $data['stats']['30dias'] ?? 0;

        // Split Heading logic (General Directory — NOT "Nuevas")
        $data['heading_prefix']    = "Empresas en ";
        $data['heading_suffix']    = "";
        $data['heading_highlight'] = ucfirst(strtolower($province));
        $data['heading_middle']    = "";   // evita que la plantilla añada ' en ' extra
        $data['heading_location']  = "";
        $data['heading_time']      = "";
        $data['heading_title']     = $data['heading_prefix'] . $data['heading_highlight'];


        $data['related_sectors'] = $data['related_sectors'] ?? [];
        $data['sector_label'] = null;

        return view('seo/new_province_v2', $data);
    }

    /**
     * Tipo 2: Empresas por sector (CNAE)
     * URL: /empresas-cnae/6201-programacion-informatica
     */
    public function cnae($slug)
    {
        // El slug suele ser código-nombre
        $parts = explode('-', $slug, 2);
        $cnaeCode = $parts[0];

        // Ensure we fall back to resolution logic if the slug lacks a numerical prefix
        if (!is_numeric($cnaeCode)) {
            $resolution = $this->resolveCnaeCodes($slug);
            if ($resolution && !empty($resolution['codes'])) {
                $cnaeCode = $resolution['codes'][0];
            } else {
                return redirect()->to(site_url('directorio'));
            }
        }

        $cacheKey = "seo_cnae_" . $cnaeCode;
        $data = $this->cache->get($cacheKey);

        if (!$data) {
            $data = $this->getCnaeData($cnaeCode);
            if ($data) {
                $this->cache->save($cacheKey, $data, 1296000);
            }
        }

        // Invalida caché antigua si faltan claves O el total es 0/inexistente por culpa de un fallo temporal previo
        if ($data && (!isset($data['cnae_code']) || !isset($data['label']) || empty($data['total']))) {
            $this->cache->delete($cacheKey);
            $data = $this->getCnaeData($cnaeCode);
            if ($data && !empty($data['total'])) {
                $this->cache->save($cacheKey, $data, 1296000);
            }
        }

        if (!$data || empty($data['total'])) {
            return redirect()->to(site_url('directorio'));
        }

        // Prefer shorter/alias label if we can resolve it from the slug
        $slugName = is_numeric($parts[0]) && isset($parts[1]) ? $parts[1] : $slug;
        $resolution = $this->resolveCnaeCodes($slugName);
        if ($resolution && !empty($resolution['label'])) {
            $data['label'] = $resolution['label'];
        }

        $data['title'] = "Empresas de {$data['label']} en España | Directorio CNAE {$cnaeCode}";
        $data['meta_description'] = "Listado completo de {$data['total_formatted']} empresas de {$data['label']}. Vea las provincias con más actividad y empresas destacadas del sector.";
        $data['canonical'] = current_url();

        // Coherencia con la vista
        $data['total_companies'] = $data['total'];
        $data['cnae_label'] = $data['label'];
        $data['paywall_level'] = 'none';
        // Default to empty array if companies wasn't set in older caches
        $data['companies'] = $data['companies'] ?? [];

        return view('seo/cnae', $data);
    }

    /**
     * Tipo 3: Sector + Provincia (El más potente)
     * URL: /empresas-programacion-en-madrid
     */
    public function sectorProvince($sectorSlug, $provinceSlug)
    {
        $province = $this->deSlugify($provinceSlug);

        $cacheKey = "seo_combined_" . md5($sectorSlug . $provinceSlug);
        $data = $this->cache->get($cacheKey);

        if (!$data) {
            $data = $this->getCombinedData($sectorSlug, $province);
            if ($data) {
                $this->cache->save($cacheKey, $data, 1296000);
            }
        }

        if (!$data || empty($data['total'])) {
            return redirect()->to(site_url("empresas/{$provinceSlug}"));
        }

        $data['title'] = "Empresas de {$data['sector_label']} en {$province} (Directorio completo)";
        $data['meta_description'] = "Encuentra las principales empresas de {$data['sector_label']} en {$province} y accede a datos comerciales listos para prospección B2B. Directorio actualizado.";
        $data['canonical'] = current_url();
        $data['total_companies'] = $data['total'];
        $data['paywall_level'] = 'soft';

        // Major hubs for national sector cluster
        $data['national_hubs'] = [
            ['name' => 'Madrid', 'url' => site_url("empresas-" . url_title($data['sector_label'], '-', true) . "-en-madrid")],
            ['name' => 'Barcelona', 'url' => site_url("empresas-" . url_title($data['sector_label'], '-', true) . "-en-barcelona")],
            ['name' => 'Sevilla', 'url' => site_url("empresas-" . url_title($data['sector_label'], '-', true) . "-en-sevilla")]
        ];

        return view('seo/combined', $data);
    }

    // --- RADAR CLUSTER (New Companies Strategy) ---

    public function newRadarHub()
    {
        // Hub central de España: Mostrar las más nuevas sin filtro
        return $this->renderRadarView(null, null, 'general');
    }

    public function newRadarTime($period)
    {
        // Hubs temporales: hoy, semana, mes (España)
        return $this->renderRadarView(null, null, $period);
    }

    public function newRadarLongTail($sectorSlug, $provinceSlug)
    {
        // Hub Long-Tail: Empresas de {Sector} nuevas en {Provincia}
        $province = $this->deSlugify($provinceSlug);
        $sectorName = str_replace('-', ' ', $sectorSlug);
        return $this->renderRadarView($province, $sectorName, 'general');
    }

    public function newRadarSector($sectorSlug)
    {
        // Hub National Sector: Empresas de {Sector} nuevas en España
        $sectorName = str_replace('-', ' ', $sectorSlug);
        return $this->renderRadarView(null, $sectorName, 'general');
    }

    public function newInProvince($provinceSlug)
    {
        // Hub Provincial: Empresas nuevas en {Provincia}
        $province = $this->deSlugify($provinceSlug);
        return $this->renderRadarView($province, null, 'general');
    }

    /**
     * Motor principal para renderizar la vista del Radar B2B (new_province.php)
     * Soporta filtros opcionales de provincia, sector y rango de tiempo.
     */
    private function renderRadarView($province = null, $sectorName = null, $period = 'general')
    {
        // Construir clave de caché
        $cacheKey = "seo_radar_" . md5(($province ?? 'all') . ($sectorName ?? 'all') . $period);
        $data = $this->cache->get($cacheKey);

        if (!$data) {
            $data = $this->getRadarData($province, $sectorName, $period, 40);
            if ($data) {
                // Cache radar for 6 hours (more volatile than directory)
                $this->cache->save($cacheKey, $data, 21600);
            }
        }

        if (!$data || empty($data['companies'])) {
            // Fallback si no hay data
            if ($province && !$sectorName)
                return redirect()->to(site_url("empresas/" . url_title($province, '-', true)));
            return redirect()->to(site_url("directorio"));
        }

        // Meta Titles dinámicos
        if ($province && $sectorName) {
            $data['title'] = "Nuevas Empresas de " . ucfirst($sectorName) . " en " . ucfirst($province) . " | Radar de Sociedades";
            $data['meta_description'] = "Listado de empresas recién constituidas del sector " . ucfirst($sectorName) . " en " . ucfirst($province) . ". Monitoreo diario del BORME.";
        } elseif ($province) {
            $data['title'] = "Nuevas Empresas en " . ucfirst($province) . " | Últimas Constituciones BORME";
            $data['meta_description'] = "Radar de las últimas sociedades creadas en " . ucfirst($province) . ". Encuentra nuevos leads y oportunidades de negocio antes que tu competencia.";
        } elseif ($period === 'hoy') {
            $data['title'] = "Empresas Nuevas Creadas Hoy en España | Constituciones Registradas";
            $data['meta_description'] = "Detecta nuevas empresas creadas hoy en España y accede a oportunidades comerciales antes que tu competencia. Listado actualizado del BORME.";
        } elseif ($period === 'semana') {
            $data['title'] = "Empresas Nuevas Creadas esta Semana en España | Registros BORME";
            $data['meta_description'] = "Detecta empresas creadas esta semana en España y accede a nuevas oportunidades comerciales antes que tu competencia. Rastreo total del BORME.";
        } elseif ($period === 'mes') {
            $data['title'] = "Empresas Nuevas Creadas este Mes en España | Registro Mercantil";
            $data['meta_description'] = "Detecta empresas creadas este mes en España y accede a nuevas oportunidades comerciales antes que tu competencia. Datos actualizados del BORME.";
        } else {
            $data['title'] = "Radar de Nuevas Empresas y Constituciones Societarias en España";
            $data['meta_description'] = "Accede al radar nacional de constituciones societarias. Las últimas empresas dadas de alta en España listas para contactar.";
        }

        $data['canonical'] = current_url();
        $data['active_menu'] = 'new_companies';
        $data['paywall_level'] = 'strong';

        return view('seo/new_province_v2', $data);
    }
    // --- END RADAR CLUSTER ---

    // --- Webhook CRON (Sincronización Nocturna) ---
    /**
     * Endpoint protegido para ser llamado por un CRON del servidor
     * URL: /cron/seo-sync/TU_TOKEN_SECRETO
     */
    public function syncStatsWebhook($token)
    {
        $secretToken = 'sync_seo_api_2026'; // Token de seguridad

        if ($token !== $secretToken) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Token inválido']);
        }

        // Aumentar tiempo límite de ejecución ya que puede tardar varios minutos
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $db = \Config\Database::connect();
        $provincesCount = 0;
        $sectorsCount = 0;

        // 1. Sincronizar Provincias
        $provinces = $db->query("SELECT DISTINCT registro_mercantil FROM companies WHERE registro_mercantil IS NOT NULL AND registro_mercantil != ''")->getResultArray();

        foreach ($provinces as $row) {
            $province = $row['registro_mercantil'];
            $totalRow = $db->query("SELECT COUNT(*) as total FROM companies WHERE registro_mercantil = ?", [$province])->getRow();
            $total = $totalRow->total;

            $oneYearAgo = date('Y-m-d', strtotime('-1 year'));
            $newRow = $db->query("SELECT COUNT(*) as total FROM companies WHERE registro_mercantil = ? AND fecha_constitucion >= ?", [$province, $oneYearAgo])->getRow();
            $newCount = $newRow->total;

            $growthPct = ($total > 0) ? round(($newCount / $total) * 100, 2) : 0;

            $topSectors = $db->query("
                SELECT cnae_code as cnae, cnae_label, COUNT(id) as total 
                FROM companies 
                WHERE registro_mercantil = ? AND cnae_label IS NOT NULL AND cnae_label != '' 
                AND fecha_constitucion >= ?
                GROUP BY cnae_code, cnae_label 
                ORDER BY total DESC 
                LIMIT 8
            ", [$province, date('Y-m-d', strtotime('-90 days'))])->getResultArray();

            $sql = "INSERT INTO seo_stats (province, total_companies, growth_pct, top_sectors) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                        total_companies = VALUES(total_companies), 
                        growth_pct = VALUES(growth_pct), 
                        top_sectors = VALUES(top_sectors)";
            $db->query($sql, [$province, $total, $growthPct, json_encode($topSectors)]);
            $provincesCount++;
        }

        // 2. Sincronizar CNAE
        $cnaes = $db->query("SELECT DISTINCT cnae_code, cnae_label FROM companies WHERE cnae_code IS NOT NULL AND cnae_label IS NOT NULL AND cnae_label != ''")->getResultArray();

        foreach ($cnaes as $row) {
            $cnaeCode = $row['cnae_code'];
            $cnaeLabel = $row['cnae_label'];

            if (empty($cnaeCode) || strlen($cnaeCode) > 6)
                continue;

            $totalRow = $db->query("SELECT COUNT(*) as total FROM companies WHERE cnae_code = ?", [$cnaeCode])->getRow();
            $total = $totalRow->total;

            if ($total == 0)
                continue;

            $topProvinces = $db->query("
                SELECT registro_mercantil as provincia, COUNT(id) as total 
                FROM companies 
                WHERE cnae_code = ? AND registro_mercantil IS NOT NULL AND registro_mercantil != ''
                AND fecha_constitucion >= ?
                GROUP BY registro_mercantil 
                ORDER BY total DESC 
                LIMIT 8
            ", [$cnaeCode, date('Y-m-d', strtotime('-90 days'))])->getResultArray();

            $sql = "INSERT INTO seo_stats_cnae (cnae_code, cnae_label, total_companies, top_provinces) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                        cnae_label = VALUES(cnae_label),
                        total_companies = VALUES(total_companies), 
                        top_provinces = VALUES(top_provinces)";
            $db->query($sql, [$cnaeCode, $cnaeLabel, $total, json_encode($topProvinces)]);
            $sectorsCount++;
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Sincronización SEO completada',
            'stats' => [
                'provinces_updated' => $provincesCount,
                'sectors_updated' => $sectorsCount
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    // --- Métodos Privados de Agregación ---

    private function getProvinceData($province)
    {
        $db = \Config\Database::connect();
        $row = $db->query("SELECT * FROM seo_stats WHERE province = ?", [$province])->getRowArray();

        if (!$row) {
            // Fallback si el cron no ha corrido: consulta pesada (no recomendada pero segura)
            $builder = $this->companyModel->builder();
            $total = $builder->where('registro_mercantil', $province)->countAllResults(false);
            if ($total === 0)
                return null;

            // No hay datos precalculados, pero podemos obtener las últimas empresas
            $latest = $this->companyModel->builder()
                ->select('id, company_name as name, cif, fecha_constitucion, cnae_label')
                ->where('registro_mercantil', $province)
                ->where('fecha_constitucion IS NOT NULL')
                ->orderBy('fecha_constitucion', 'DESC')
                ->limit(100)
                ->get()
                ->getResultArray();
        } else {
            // Obtener solo las 10 constituciones más recientes (una única consulta rápida indexada)
            $latest = $this->companyModel->builder()
                ->select('id, company_name as name, cif, fecha_constitucion, cnae_label')
                ->where('registro_mercantil', $province)
                ->where('fecha_constitucion IS NOT NULL')
                ->orderBy('fecha_constitucion', 'DESC')
                ->limit(100)
                ->get()
                ->getResultArray();
        }

        // Obtener estadísticas de crecimiento (Real-time counts para el hero premium)
        // Usamos el builder para que respete la normalización de Alicante si fuera necesario
        $baseBuilder = function () use ($province, $db) {
            $b = $db->table('companies');
            if (strtolower($province) === 'alicante') {
                $b->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
            } else {
                $b->where('registro_mercantil', $province);
            }
            $b->where('fecha_constitucion IS NOT NULL');
            return $b;
        };

        $docsToday = $baseBuilder()->where('fecha_constitucion >=', date('Y-m-d'))->countAllResults();
        $docsWeek = $baseBuilder()->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')))->countAllResults();
        $docsMonth = $baseBuilder()->where('fecha_constitucion >=', date('Y-m-01'))->countAllResults();
        $docs30days = $baseBuilder()->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')))->countAllResults();

        return [
            'province' => $row['province'] ?? $province,
            'total' => $row['total_companies'] ?? $total,
            'total_formatted' => number_format($row['total_companies'] ?? $total, 0, ',', '.'),
            'growth_pct' => $row['growth_pct'] ?? 0,
            'top_sectors' => json_decode($row['top_sectors'] ?? '[]', true),
            'latest' => $latest,
            'stats' => [
                'hoy' => $docsToday,
                'semana' => $docsWeek,
                'mes' => $docsMonth,
                '30dias' => $docs30days
            ]
        ];
    }

    private function getCnaeData($cnaeCode)
    {
        $db = \Config\Database::connect();
        $row = $db->query("SELECT * FROM seo_stats_cnae WHERE cnae_code = ?", [$cnaeCode])->getRowArray();

        if (!$row) {
            // Fallback pesado
            $builder = $this->companyModel->builder();
            $total = $builder->where('cnae_code', $cnaeCode)->countAllResults();

            if ($total === 0) {
                // Relajar el filtro a LIKE para cazar sub-niveles (e.g. 5610, 5611 desde la cabecera 561)
                $builder = $this->companyModel->builder();
                $total = $builder->where('cnae_code LIKE', $cnaeCode . '%')->countAllResults();
            }

            if ($total === 0)
                return null;

            $topProvincesFallback = $this->companyModel->builder()
                ->select('registro_mercantil as provincia, COUNT(*) as total')
                ->where('cnae_code LIKE', $cnaeCode . '%')
                ->where('registro_mercantil IS NOT NULL')
                ->where('registro_mercantil !=', '')
                ->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')))
                ->groupBy('registro_mercantil')
                ->orderBy('total', 'DESC')
                ->limit(8)
                ->get()->getResultArray();

            return [
                'cnae_code' => $cnaeCode,
                'label' => "Sector {$cnaeCode}",
                'total' => $total,
                'total_formatted' => number_format($total, 0, ',', '.'),
                'top_provinces' => $topProvincesFallback,
                'companies' => $this->getLatestCompaniesForCnae($cnaeCode)
            ];
        }

        return [
            'cnae_code' => $row['cnae_code'],
            'label' => $row['cnae_label'],
            'total' => $row['total_companies'],
            'total_formatted' => number_format($row['total_companies'], 0, ',', '.'),
            'top_provinces' => json_decode($row['top_provinces'] ?? '[]', true),
            'companies' => $this->getLatestCompaniesForCnae($cnaeCode)
        ];
    }

    private function getLatestCompaniesForCnae($cnaeCode)
    {
        return $this->companyModel->builder()
            ->select('id, company_name as name, cif, fecha_constitucion, cnae_label, registro_mercantil, municipality')
            ->where('cnae_code LIKE', $cnaeCode . '%')
            ->where('fecha_constitucion IS NOT NULL')
            ->orderBy('fecha_constitucion', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();
    }

    private function getCombinedData($sectorSlug, $province)
    {
        $db = \Config\Database::connect();

        // Unificación: Usar el motor de resolución híbrido
        $resolution = $this->resolveCnaeCodes($sectorSlug);
        if (!$resolution)
            return null;

        $codes = $resolution['codes'];
        $cnaeLabel = $resolution['label'];

        $builder = $this->companyModel->builder();

        // Normalización de provincia para Alicante (variantes en DB: 'Alicante' y 'Alicante/Alacant')
        if (strtolower($province) === 'alicante') {
            $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
        } else {
            $builder->where('registro_mercantil', $province);
        }

        if (count($codes) === 1) {
            $builder->where('cnae_code LIKE', $codes[0] . '%');
        } else {
            $builder->groupStart();
            foreach ($codes as $code) {
                $builder->orLike('cnae_code', $code, 'after');
            }
            $builder->groupEnd();
        }

        $total = $builder->countAllResults(false);
        if ($total === 0)
            return null;

        $companies = $builder->select('id, company_name as name, cif, address, municipality, cnae_code, fecha_constitucion')
            ->orderBy('fecha_constitucion', 'DESC')
            ->limit(60)
            ->get()
            ->getResultArray();

        return [
            'province' => $province,
            'sector_label' => $cnaeLabel,
            'sector_code' => $codes[0],
            'total' => $total,
            'total_formatted' => number_format($total, 0, ',', '.'),
            'companies' => $companies
        ];
    }

    public function getRadarData($province, $sectorName, $period, $limit = 40)
    {
        $db = \Config\Database::connect();
        $builder = $this->companyModel->builder();

        $builder->select('id, company_name as name, cif, fecha_constitucion, cnae_code as cnae, cnae_label, registro_mercantil, municipality, objeto_social');

        // Aplicar Filtro Provincial
        if ($province && strtolower($province) !== 'españa') {
            if (strtolower($province) === 'alicante') {
                $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
            } else {
                $builder->where('registro_mercantil', $province);
            }
        }

        // Aplicar Filtro Sectorial (Hybrid Logic: Alias + DB Search)
        if ($sectorName) {
            $slug = url_title($sectorName, '-', true);
            $resolution = $this->resolveCnaeCodes($slug);

            if (!$resolution)
                return null; // Sector invalido

            $codes = $resolution['codes'];
            if (count($codes) === 1) {
                // Usamos LIKE para capturar sub-niveles (ej: 56 -> 5611, etc)
                $builder->where('cnae_code LIKE', $codes[0] . '%');
            } else {
                $builder->groupStart();
                foreach ($codes as $code) {
                    $builder->orLike('cnae_code', $code, 'after');
                }
                $builder->groupEnd();
            }
            $finalSectorLabel = $resolution['label'];
        }

        // Aplicar Filtros Temporales Dinámicos (Solo si se pide un rango temporal)
        if ($period === 'hoy') {
            $lastDateRow = $db->query("SELECT MAX(fecha_constitucion) as last_date FROM companies WHERE fecha_constitucion IS NOT NULL AND fecha_constitucion <= CURDATE()")->getRowArray();
            $targetDate = $lastDateRow['last_date'] ?? date('Y-m-d');
            $builder->where('fecha_constitucion', $targetDate);
        } elseif ($period === 'semana') {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')))
                    ->where('fecha_constitucion <=', date('Y-m-d'));
        } elseif ($period === 'mes') {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')))
                    ->where('fecha_constitucion <=', date('Y-m-d'));
        } elseif ($period === '30days') {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')))
                    ->where('fecha_constitucion <=', date('Y-m-d'));
        } elseif ($period === 'general') {
            // Long-tail pages (sector+province): use 90-day window for broader results.
            // Province-only or sector-only pages also benefit from 90 days.
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')))
                    ->where('fecha_constitucion <=', date('Y-m-d'));
        }


        // Exigir siempre fechas y CNAE para presentar listas B2B de calidad
        $builder->where('fecha_constitucion IS NOT NULL');
        // $builder->where('cnae_label IS NOT NULL')->where('cnae_label !=', ''); // Opcional, relajar si caen leads

        $builder->orderBy('fecha_constitucion', 'DESC');
        $builder->limit($limit); // Suficiente para pintar el Paywall (10 libres + 30 blureadas) o Full Export

        $companies = $builder->get()->getResultArray();

        // -----------------------------------------
        // Calcular Estadísticas Reales (Full Dataset Counts)
        // Gracias al índice compuesto (registro, cnae, fecha), estos counts son instantáneos.
        // -----------------------------------------

        // Clonamos el builder base (filtros de registro y cnae) para los conteos
        $baseBuilder = function () use ($province, $sectorName, $db) {
            $b = $db->table('companies');
            if ($province && strtolower($province) !== 'españa') {
                if (strtolower($province) === 'alicante') {
                    $b->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
                } else {
                    $b->where('registro_mercantil', $province);
                }
            }
            if ($sectorName) {
                $slug = url_title($sectorName, '-', true);
                $res = $this->resolveCnaeCodes($slug);
                if ($res) {
                    $codes = $res['codes'];
                    if (count($codes) === 1)
                        $b->where('cnae_code LIKE', $codes[0] . '%');
                    else {
                        $b->groupStart();
                        foreach ($codes as $code)
                            $b->orLike('cnae_code', $code, 'after');
                        $b->groupEnd();
                    }
                }
            }
            $b->where('fecha_constitucion IS NOT NULL');
            return $b;
        };
        
        $lastDateRow = $db->query("SELECT MAX(fecha_constitucion) as last_date FROM companies WHERE fecha_constitucion IS NOT NULL AND fecha_constitucion <= CURDATE()")->getRowArray();
        $targetDate = $lastDateRow['last_date'] ?? date('Y-m-d');

        $docsToday = $baseBuilder()->where('fecha_constitucion', $targetDate)->countAllResults();
        $docsWeek  = $baseBuilder()->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')))
                                  ->where('fecha_constitucion <=', date('Y-m-d'))
                                  ->countAllResults();
        $docsMonth = $baseBuilder()->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')))
                                   ->where('fecha_constitucion <=', date('Y-m-d'))
                                   ->countAllResults();
        $docs30Days = $baseBuilder()->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')))
                                    ->where('fecha_constitucion <=', date('Y-m-d'))
                                    ->countAllResults();

        // Label Formateos — evitar "Empresas nuevas de empresas nuevas en Madrid"
        $headingLocation = $province ? ucfirst(mb_strtolower($province, 'UTF-8')) : "España";
        $headingTime = "";
        if ($period === 'hoy') $headingTime = " hoy";
        elseif ($period === 'semana') $headingTime = " esta semana";
        elseif ($period === 'mes') $headingTime = " este mes";

        if ($sectorName && isset($finalSectorLabel)) {
            // Sector + (opcionalmente) provincia: "Empresas nuevas de [sector] en [provincia]"
            $headingPrefix    = "Empresas nuevas";
            $headingSuffix    = " de ";
            $headingHighlight = mb_strtolower($finalSectorLabel, 'UTF-8');
            $headingMiddle    = " en ";
        } elseif ($province) {
            // Solo provincia: "Nuevas empresas en [provincia]"
            $headingPrefix    = "Nuevas empresas en ";
            $headingSuffix    = "";
            $headingHighlight = $headingLocation;
            $headingMiddle    = "";
            $headingLocation  = "";
        } else {
            // Nacional sin sector: "Empresas nuevas en España"
            $headingPrefix    = "Empresas nuevas";
            $headingSuffix    = "";
            $headingHighlight = "";
            $headingMiddle    = " en ";
        }

        // Final H1 Assembly
        $headingTitle = "Empresas nuevas";
        $headingPrefix_local = $headingPrefix; // alias para compatibilidad
        $fullHeading = trim($headingPrefix . $headingSuffix . $headingHighlight . $headingMiddle . $headingLocation . $headingTime);

        // Determinar qué mostrar en el Impacto Geográfico (Provincias o Sectores)
        $topSectors = [];
        if ($province) {
            $topB = $db->table('companies')
                ->select('cnae_code as cnae, cnae_label, COUNT(id) as total')
                ->where('cnae_code IS NOT NULL')
                ->where('cnae_code !=', '')
                ->where('cnae_label IS NOT NULL')
                ->where('cnae_label !=', '')
                ->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')))
                ->groupBy(['cnae_code', 'cnae_label'])
                ->orderBy('total', 'DESC')
                ->limit(8);
            if (strtolower($province) === 'alicante') {
                $topB->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
            } else {
                $topB->where('registro_mercantil', $province);
            }
            $topSectorsRaw = $topB->get()->getResultArray();

            foreach ($topSectorsRaw as $ts) {
                $topSectors[] = [
                    'cnae' => substr($ts['cnae_label'], 0, 2), 
                    'cnae_label' => ucfirst($ts['cnae_label']),
                    'total' => $ts['total'],
                    'url' => site_url("empresas-nuevas/" . url_title($ts['cnae_label'], '-', true) . "-en-" . url_title($province, '-', true))
                ];
            }
        } else {
            // Mostrar top provincias recientes si no hay provincia
            $topB = $db->table('companies')
                ->select('registro_mercantil as cnae_label, COUNT(id) as total')
                ->where('registro_mercantil IS NOT NULL')
                ->where('registro_mercantil !=', '')
                ->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')))
                ->groupBy('registro_mercantil')
                ->orderBy('total', 'DESC')
                ->limit(8);

            if ($sectorName && isset($res) && count($res['codes']) > 0) {
                // Filtrar por cnae a calcular provincias de un sector nacional
                $codes = $res['codes'];
                if (count($codes) === 1)
                    $topB->where('cnae_code LIKE', $codes[0] . '%');
                else {
                    $topB->groupStart();
                    foreach ($codes as $code)
                        $topB->orLike('cnae_code', $code, 'after');
                    $topB->groupEnd();
                }
            }
            $topSectorsRaw = $topB->get()->getResultArray();

            // Logic ordering for Top Provinces: ensure major hubs are always first
            $mainHubsOrder = ['Madrid', 'Barcelona', 'Valencia', 'Málaga', 'Sevilla', 'Alicante'];
            usort($topSectorsRaw, function ($a, $b) use ($mainHubsOrder) {
                $posA = array_search(ucfirst($a['cnae_label']), $mainHubsOrder);
                $posB = array_search(ucfirst($b['cnae_label']), $mainHubsOrder);

                if ($posA !== false && $posB !== false)
                    return $posA - $posB;
                if ($posA !== false)
                    return -1;
                if ($posB !== false)
                    return 1;

                return $b['total'] - $a['total']; // Fallback to volume
            });

            foreach ($topSectorsRaw as $ts) {
                $topSectors[] = [
                    'cnae' => substr($ts['cnae_label'], 0, 2), 
                    'cnae_label' => ucfirst($ts['cnae_label']),
                    'total' => $ts['total'],
                    'url' => ($sectorName) 
                        ? site_url("empresas-nuevas/" . url_title($sectorName, '-', true) . "-en-" . url_title($ts['cnae_label'], '-', true))
                        : site_url("empresas-nuevas/" . url_title($ts['cnae_label'], '-', true))
                ];
            }
        }

        // Calculate volume context matching the actual period shown
        if ($period === 'hoy') {
            $lastDateRow = $db->query("SELECT MAX(fecha_constitucion) as last_date FROM companies WHERE fecha_constitucion IS NOT NULL AND fecha_constitucion <= CURDATE()")->getRowArray();
            $targetDate = $lastDateRow['last_date'] ?? date('Y-m-d');
            
            // Debemos clonar de nuevo porque $baseBuilder ya fue consumida antes
            $totalContextCount = $baseBuilder()->where('fecha_constitucion', $targetDate)->countAllResults();
        } elseif ($period === 'semana') {
            $totalContextCount = $baseBuilder()->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')))
                                               ->where('fecha_constitucion <=', date('Y-m-d'))
                                               ->countAllResults();
        } elseif ($period === 'mes') {
            $totalContextCount = $baseBuilder()->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')))
                                               ->where('fecha_constitucion <=', date('Y-m-d'))
                                               ->countAllResults();
        } else {
            $totalContextCount = $baseBuilder()->where('fecha_constitucion >=', date('Y-m-d', strtotime('-30 days')))
                                               ->where('fecha_constitucion <=', date('Y-m-d'))
                                               ->countAllResults();
        }

        // Generate related sectors for internal linking (Join with cnae_2009_2025 for cleaner labels)
        $relatedSectorsRaw = $db->table('seo_stats_cnae')
            ->select('seo_stats_cnae.cnae_code, COALESCE(cnae_2009_2025.label_2009, seo_stats_cnae.cnae_label) as cnae_label')
            ->join('cnae_2009_2025', 'cnae_2009_2025.cnae_2009 = seo_stats_cnae.cnae_code', 'left')
            ->orderBy('total_companies', 'DESC')
            ->limit(10)
            ->groupBy(['seo_stats_cnae.cnae_code', 'cnae_2009_2025.label_2009', 'seo_stats_cnae.cnae_label']) // Evitar error only_full_group_by
            ->get()
            ->getResultArray();

        $relatedSectors = [];
        foreach ($relatedSectorsRaw as $rs) {
            $label = $rs['cnae_label'];

            // Sanitización intensiva
            $label = preg_replace('/INFORME COMERCIAL.*/i', '', $label);
            $label = preg_replace('/CNAE \d+ -/i', '', $label);
            $label = trim($label, " \t\n\r\0\x0B-");

            if (strtoupper($label) === $label) {
                $label = mb_convert_case(strtolower($label), MB_CASE_TITLE, "UTF-8");
            }

            if (empty($label))
                continue;

            $relatedSectors[] = [
                'label' => $label,
                'url' => $province
                    ? site_url("empresas-nuevas/" . url_title($label, '-', true) . "-en-" . url_title($province, '-', true))
                    : site_url("empresas-cnae/" . url_title($label, '-', true))
            ];
        }

        // H1 Override for special time-based national landings (overrides the heading parts set above)
        if ($period === 'hoy' && !$province && !$sectorName) {
            $headingPrefix    = "Empresas nuevas creadas hoy en ";
            $headingSuffix    = "";
            $headingHighlight = "España";
            $headingMiddle    = "";
            $headingLocation  = "";
            $headingTime      = "";
        } elseif ($period === 'semana' && !$province && !$sectorName) {
            $headingPrefix    = "Empresas nuevas esta semana en ";
            $headingSuffix    = "";
            $headingHighlight = "España";
            $headingMiddle    = "";
            $headingLocation  = "";
            $headingTime      = "";
        } elseif ($period === 'mes' && !$province && !$sectorName) {
            $headingPrefix    = "Empresas nuevas este mes en ";
            $headingSuffix    = "";
            $headingHighlight = "España";
            $headingMiddle    = "";
            $headingLocation  = "";
            $headingTime      = "";
        }

        // Calcula los precios dinámicos según el volumen para los CTA
        $dynamicPriceContext = calculate_radar_price($totalContextCount);
        $dynamicPrice30Days  = calculate_radar_price($docs30Days);

        return [
            'heading_title' => $fullHeading,

            'heading_prefix' => $headingPrefix,
            'heading_suffix' => $headingSuffix,
            'heading_highlight' => $headingHighlight,
            'heading_middle' => $headingMiddle,
            'heading_location' => $headingLocation,
            'heading_time' => $headingTime,
            'province' => $province,
            'sector_label' => $sectorName ? ($finalSectorLabel ?? ucfirst($sectorName)) : null,
            'period' => $period,
            'companies' => $companies,
            'top_sectors' => $topSectors,
            'related_sectors' => $relatedSectors,
            'total_context_count' => $totalContextCount,
            'dynamic_price' => $dynamicPriceContext,       // Precio para el context actual (e.g. 'hoy')
            'dynamic_price_30days' => $dynamicPrice30Days, // Precio garantizado para los botones de 30 días
            'stats' => [
                'hoy' => $docsToday,
                'semana' => $docsWeek,
                'mes' => $docsMonth,
                '30days' => $docs30Days
            ]
        ];
    }

    /**
     * Resuelve un slug de sector (ej: hosteleria, programacion) en códigos CNAE.
     * Utiliza un sistema híbrido: Alias manuales -> Tabla cnae_2009_2025 -> Tabla seo_stats_cnae.
     */
    private function resolveCnaeCodes($slug)
    {
        $db = \Config\Database::connect();

        // Prioridad 1: Alias Manuales (Optimizados para SEO y términos comunes que fallan en DB)
        $aliases = [
            'hosteleria' => [
                'codes' => ['55', '56'],
                'label' => 'Hostelería, Restaurantes y Catering'
            ],
            'restaurantes' => [
                'codes' => ['561'],
                'label' => 'Restaurantes y Puestos de Comida'
            ],
            'restaurantes-y-puestos-de-comida' => [
                'codes' => ['561'],
                'label' => 'Restaurantes y Puestos de Comida'
            ],
            'programacion' => [
                'codes' => ['62'],
                'label' => 'Programación Informática'
            ],
            'programacion-informatica' => [
                'codes' => ['62'],
                'label' => 'Programación Informática'
            ],
            'marketing' => [
                'codes' => ['731'],
                'label' => 'Marketing y Publicidad'
            ],
            'publicidad' => [
                'codes' => ['731'],
                'label' => 'Marketing y Publicidad'
            ],
            'construccion' => [
                'codes' => ['41', '42', '43'],
                'label' => 'Construcción e Inmobiliaria'
            ],
            'transporte' => [
                'codes' => ['49', '50', '51', '52', '53'],
                'label' => 'Transporte y Logística'
            ],
            'logistica' => [
                'codes' => ['52'],
                'label' => 'Logística y Almacenamiento'
            ],
            'finanzas' => [
                'codes' => ['64', '65', '66'],
                'label' => 'Seguros y Finanzas'
            ],
            'inmobiliaria' => [
                'codes' => ['68'],
                'label' => 'Actividades Inmobiliarias'
            ],
            'sanidad' => [
                'codes' => ['86'],
                'label' => 'Actividades Sanitarias'
            ],
        ];

        $normalizedSlug = strtr(mb_strtolower($slug), [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'à' => 'a',
            'è' => 'e',
            'ì' => 'i',
            'ò' => 'o',
            'ù' => 'u',
            'ä' => 'a',
            'ë' => 'e',
            'ï' => 'i',
            'ö' => 'o',
            'ü' => 'u',
            'ñ' => 'n'
        ]);

        if (isset($aliases[$normalizedSlug])) {
            return $aliases[$normalizedSlug];
        }

        // Prioridad 2: Búsqueda dinámica en la tabla maestra cnae_2009_2025
        $searchTerm = str_replace('-', ' ', $slug);

        $query = "SELECT cnae_2009, label_2009 FROM cnae_2009_2025 
                  WHERE label_2009 LIKE ? OR label_2025 LIKE ? 
                  ORDER BY 
                    (CASE 
                        WHEN label_2009 LIKE ? THEN 1 
                        WHEN label_2009 LIKE ? THEN 2
                        ELSE 3 
                    END), 
                    LENGTH(cnae_2009) ASC 
                  LIMIT 1";

        $row = $db->query($query, [
            '%' . $searchTerm . '%',
            '%' . $searchTerm . '%',
            $searchTerm . '%',
            '% ' . $searchTerm . '%'
        ])->getRowArray();

        if ($row) {
            return [
                'codes' => [$row['cnae_2009']],
                'label' => $this->normalizeLabel($row['label_2009'])
            ];
        }

        // Prioridad 3: Fallback a la tabla de estadísticas SEO
        $statRow = $db->query("SELECT cnae_code, cnae_label FROM seo_stats_cnae WHERE cnae_label LIKE ? LIMIT 1", ['%' . $searchTerm . '%'])->getRowArray();

        if ($statRow) {
            return [
                'codes' => [$statRow['cnae_code']],
                'label' => $this->normalizeLabel($statRow['cnae_label'])
            ];
        }

        return null;
    }

    /**
     * Limpia y normaliza etiquetas técnicas de CNAE para uso SEO.
     */
    private function normalizeLabel($label)
    {
        // 1. Quitar coletilla de informes comerciales de la tabla de stats
        $label = preg_replace('/INFORME COMERCIAL.*$/is', '', $label);

        // 2. Quitar descripciones largas que empiezan por ".- si entre las actividades..."
        $label = preg_replace('/\.- si entre las actividades.*$/is', '', $label);

        // 3. Quitar menciones a códigos CNAE dentro del texto (ej: "-cnae 6210-")
        $label = preg_replace('/-?cnae\s*[0-9]+-?/i', '', $label);

        // 4. Limpieza de puntuación sobrante al final
        $label = rtrim(trim($label), '.,; -');

        // 5. Corregir Capitalización (si está todo en Mayúsculas)
        if (mb_strtoupper($label) === $label) {
            $label = mb_convert_case($label, MB_CASE_TITLE, "UTF-8");
        }

        return $label;
    }

    private function deSlugify($slug)
    {
        // Simple map for provinces (could be more robust)
        $provinces = [
            'madrid' => 'MADRID',
            'barcelona' => 'BARCELONA',
            'valencia' => 'VALENCIA',
            'sevilla' => 'SEVILLA',
            'alicante' => 'ALICANTE',
            'alicant' => 'ALICANTE', // Fuzzy match for common typo
            'malaga' => 'MALAGA',
            'murcia' => 'MURCIA',
            'cadiz' => 'CADIZ',
            'vizcaya' => 'VIZCAYA',
            'coruna' => 'A CORUNA',
            // ... etc
        ];

        $key = strtolower($slug);
        return $provinces[$key] ?? strtoupper(str_replace('-', ' ', $slug));
    }

    /**
     * Genera un archivo CSV compatible con Excel para la descarga del listado comprado.
     */
    public function exportExcel()
    {
        // --- GUARD 1: Solo usuarios autenticados ---
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'))->with('error', 'Debes iniciar sesión para descargar el listado.');
        }

        $sector   = $this->request->getGet('sector')   ?: '';
        $province = $this->request->getGet('provincia') ?: 'España';
        $period   = $this->request->getGet('period')   ?: '30days';
        $cnae     = $this->request->getGet('cnae')     ?: '';  // param de combined.php

        // --- GUARD 2: Whitelist de periodos ---
        $allowedPeriods = ['hoy', 'semana', 'mes', '30days', 'general'];
        if (!in_array($period, $allowedPeriods, true)) {
            $period = '30days';
        }

        // --- MODO A: Directorio sector+provincia (combined.php) ---
        // Viene con ?cnae=CODE&provincia=X → sin filtro de fecha, todas las empresas
        if ($cnae !== '') {
            $db      = \Config\Database::connect();
            $builder = $db->table('companies');
            $builder->select('id, company_name as name, cif, fecha_constitucion, cnae_label, registro_mercantil, municipality, objeto_social');
            $builder->where('cnae_code LIKE', $cnae . '%');

            if ($province && strtolower($province) !== 'españa') {
                if (strtolower($province) === 'alicante') {
                    $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
                } else {
                    $builder->where('registro_mercantil', $province);
                }
            }
            $builder->orderBy('fecha_constitucion', 'DESC');
            $builder->limit(2000);
            $companies = $builder->get()->getResultArray();
            $filename  = "Directorio_" . preg_replace('/[^A-Za-z0-9_]/', '_', $cnae) . "_" . str_replace(' ', '_', $province) . ".csv";

        // --- MODO B: Radar nuevas empresas (new_province.php) ---
        // Viene con ?sector=LABEL&provincia=X&period=30days → con filtro de fecha
        } else {
            $data      = $this->getRadarData($province, $sector, $period, 100000);
            $companies = $data['companies'] ?? [];
            $filename  = "Listado_Nuevas_Empresas_" . str_replace(' ', '_', $sector) . "_" . str_replace(' ', '_', $province) . ".csv";
        }

        if (ob_get_length()) {
            ob_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM para que Excel reconozca los acentos
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Cabeceras
        fputcsv($output, ['Nombre de la Empresa', 'CIF', 'Sector CNAE', 'Provincia', 'Municipio', 'Fecha Registro', 'Objeto Social'], ';');

        // Datos
        foreach ($companies as $company) {
            $rawDate   = $company['fecha_constitucion'] ?? '';
            $ts        = $rawDate ? strtotime(str_replace('/', '-', $rawDate)) : false;
            $cleanDate = ($ts && $ts >= strtotime('1900-01-01') && $ts <= strtotime('2100-01-01'))
                ? date('d/m/Y', $ts)
                : '';

            fputcsv($output, [
                $company['name']               ?? '',
                $company['cif']                ?? '',
                $company['cnae_label']         ?? '',
                $company['registro_mercantil'] ?? '',
                $company['municipality']       ?? '',
                $cleanDate,
                $company['objeto_social']      ?? '',
            ], ';');
        }

        fclose($output);
        exit();
    }
}
