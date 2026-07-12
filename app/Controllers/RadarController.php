<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\UsersuscriptionsModel;

class RadarController extends BaseController
{
    protected $companyModel;
    protected $subscriptionModel;
    protected $radarService;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->subscriptionModel = new UsersuscriptionsModel();
        $this->radarService = new \App\Services\RadarService();
        helper(['company', 'pricing']);
    }

    public function index()
    {
        session_write_close();
        return $this->renderRadar('general');
    }

    public function today()
    {
        session_write_close();
        return $this->renderRadar('hoy');
    }

    public function sectorProvince($sectorSlug, $provinceSlug)
    {
        session_write_close();
        $province = $this->deSlugify($provinceSlug);
        $sector = $this->radarService->resolveCnaeCodes($sectorSlug);

        if (!$sector) {
            return redirect()->to(site_url("empresas-nuevas/{$provinceSlug}"));
        }

        return $this->renderRadar('general', $province, $sector);
    }



    public function week()
    {
        session_write_close();
        return $this->renderRadar('semana');
    }

    public function month()
    {
        session_write_close();
        return $this->renderRadar('mes');
    }

    public function sector($sectorSlug)
    {
        session_write_close();
        $sector = $this->radarService->resolveCnaeCodes($sectorSlug);
        if (!$sector) {
            return redirect()->to(site_url('empresas-nuevas'));
        }
        return $this->renderRadar('general', null, $sector);
    }

    public function newRadarLongTail($sectorSlug, $provinceSlug)
    {
        session_write_close();
        $sector = $this->radarService->resolveCnaeCodes($sectorSlug);
        $province = $this->deSlugify($provinceSlug);

        if (!$sector) {
            return redirect()->to(site_url('empresas-nuevas/' . $provinceSlug));
        }

        return $this->renderRadar('general', $province, $sector);
    }


    public function provinceCatalog($provinceSlug)
    {
        session_write_close();
        $province = $this->deSlugify($provinceSlug);
        $data = $this->getRadarData($province, null, 'mes');

        if (!$data || empty($data['total_context_count'])) {
            return redirect()->to(site_url('empresas-nuevas'));
        }

        $data['title'] = "Empresas en {$province} hoy | +120 oportunidades activas";
        $data['excerptText'] = "Descubre " . number_format($data['total_context_count'], 0, ',', '.') . " empresas en {$province} detectadas hoy. Oportunidades reales listas para contactar antes que tu competencia.";
        $data['meta_description'] = $data['excerptText'];
        $data['canonical'] = site_url(uri_string());

        // SEO Headings
        $data['heading_highlight'] = ucfirst(mb_strtolower($province, 'UTF-8'));
        $data['heading_title'] = "Empresas en " . $data['heading_highlight'];

        return view('seo/radar_companies_province', $data);
    }

    public function province($provinceSlug)
    {
        session_write_close();
        $province = $this->deSlugify($provinceSlug);
        return $this->renderRadar('mes', $province);
    }

    public function todayProvince($provinceSlug)
    {
        session_write_close();
        $province = $this->deSlugify($provinceSlug);
        return $this->renderRadar('hoy', $province);
    }

    public function weekProvince($provinceSlug)
    {
        session_write_close();
        $province = $this->deSlugify($provinceSlug);
        return $this->renderRadar('semana', $province);
    }

    public function monthProvince($provinceSlug)
    {
        session_write_close();
        $province = $this->deSlugify($provinceSlug);
        return $this->renderRadar('mes', $province);
    }

    private function renderRadar($period, $province = null, $sector = null)
    {
        $data = $this->getRadarData($province, $sector, $period);
        if (!$data)
            return redirect()->to(site_url('empresas-nuevas'));

        // Tracking (Runs every visit, even if data is cached)
        $this->logSeoVariant($data);

        if ($province && mb_strtolower($province, 'UTF-8') !== 'españa') {
            $viewFile = 'seo/radar_new_companies_province';
        } elseif ($sector) {
            $viewFile = 'seo/radar_new_companies_sector';
        } else {
            $viewFile = ($period === 'general' || $period === 'mes') ? 'seo/radar_new_companies' : 'seo/radar_new_companies_period';
        }
        return view($viewFile, $data);
    }

    private function logSeoVariant($data)
    {
        if (!isset($data['variant_id']))
            return;

        $db = \Config\Database::connect();
        try {
            $db->table('seo_variant_performance')->insert([
                'url' => uri_string(),
                'variant_id' => $data['variant_id'],
                'variant_title' => $data['title'] ?? '',
                'variant_meta' => $data['excerptText'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) { /* Fail silently */
        }
    }

    public function excel_preview()
    {
        $province = $this->request->getGet('provincia') ?? 'España';
        $sector = $this->request->getGet('sector') ?? '';
        $period = $this->request->getGet('period') ?? '30days';
        $cnae = $this->request->getGet('cnae') ?? '';

        $data = $this->getRadarData($province, $sector, $period, 15);
        if (!$data)
            return redirect()->to(site_url('empresas-nuevas'));

        $data['cnae'] = $cnae;
        // Deterministic Rotation for Excel
        $hash = crc32(uri_string());
        $tVariants = [
            "Descargar listado de clientes potenciales | Excel listo ahora",
            "Listado de empresas contratando en Excel | Descarga inmediata",
            "Oportunidades B2B en Excel: Descarga leads activos",
            "Descarga base de datos de empresas con necesidad activa"
        ];
        $mVariants = [
            "Descarga un listado de empresas activas que necesitan proveedores ahora mismo. Ideal para prospección comercial inmediata y generación de ventas.",
            "Listado completo de empresas de reciente creación en formato Excel. Empieza a captar clientes hoy con datos actualizados y reales.",
            "Accede a los datos de contacto de nuevas empresas en España. Descarga tu Excel y adelántate a tu competencia cerrando ventas.",
            "Bases de datos de empresas recién constituidas listas para tu CRM. Aumenta tus ventas con leads B2B de alta intención comercial."
        ];

        $data['title'] = $tVariants[$hash % count($tVariants)];
        $data['excerptText'] = $mVariants[$hash % count($mVariants)];
        $data['variant_id'] = 'excel-rotation-' . ($hash % count($tVariants));

        $this->logSeoVariant($data);

        return view('radar/excel_preview', $data);
    }

    public function excel_unlock()
    {
        $email = strtolower(trim((string) $this->request->getPost('email')));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Por favor, introduce un email válido.']);
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $email)->first();

        // Lógica de Registro Rápido / Login
        if ($user) {
            if (($user->is_admin ?? 0) == 1) {
                return $this->response->setJSON([
                    'status' => 'exists',
                    'message' => 'Por seguridad, inicia sesión con tu cuenta de administrador.',
                    'redirect' => site_url('enter?redirect=checkout/radar-export&' . http_build_query($this->request->getPost()))
                ]);
            }

            session()->regenerate();
            session()->set([
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'logged_in' => true,
            ]);
        } else {
            // Crear nuevo usuario
            $password = bin2hex(random_bytes(8));
            $token = bin2hex(random_bytes(32));
            $user_id = $userModel->insert([
                'name' => explode('@', $email)[0],
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'reset_token' => $token,
                'reset_expires' => date('Y-m-d H:i:s', strtotime('+48 hours')),
                'is_active' => 1,
                'source_app' => 'apiempresas',
                'preferred_product' => 'excel_single',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            session()->regenerate();
            session()->set([
                'user_id' => $user_id,
                'user_email' => $email,
                'user_name' => explode('@', $email)[0],
                'logged_in' => true,
            ]);
        }

        // Construir redirect al checkout final
        $params = $this->request->getPost();
        unset($params['email']);
        $params['type'] = 'single';

        return $this->response->setJSON([
            'status' => 'success',
            'redirect' => site_url('checkout/radar-export?' . http_build_query($params))
        ]);
    }


    public function getRadarData($province, $sectorInput, $period, $limit = 100)
    {
        $cache = \Config\Services::cache();
        $sectorSlug = is_array($sectorInput) ? null : ($sectorInput ? url_title($sectorInput, '-', true) : null);
        $sectorCacheKey = is_array($sectorInput) ? implode(',', $sectorInput['codes'] ?? []) : (string) $sectorInput;
        $cacheKey = 'radar_' . md5("{$period}_{$province}_{$limit}_{$sectorCacheKey}");

        $forceNoCache = service('request')->getGet('nocache') === '1';
        if ($forceNoCache) {
            $cache->delete($cacheKey);
        }

        $cached = $cache->get($cacheKey);
        if ($cached !== null && !$forceNoCache) {
            return $cached;
        }

        $sector = is_array($sectorInput) ? $sectorInput : ($sectorSlug ? $this->radarService->resolveCnaeCodes($sectorSlug) : null);
        $sectorLabel = $sector ? $sector['label'] : null;

        $companies = $this->radarService->getCompaniesList($province, $sector, $period, $limit);
        $stats = $this->radarService->getContextStats($province, $sector);
        $seoMeta = $this->radarService->getSeoMetadata($province, $sector, $period, $stats, uri_string());
        $sidebarData = $this->radarService->getTopSidebarLinks($province);

        $totalCount = $seoMeta['total_context_count'];
        $dynamicPriceData = calculate_radar_price($totalCount);
        $dynamicPrice = $dynamicPriceData['base_price'];
        $isLowResults = $totalCount === 0;

        $prices = [
            'hoy' => calculate_radar_price($stats['hoy'])['base_price'],
            'semana' => calculate_radar_price($stats['semana'])['base_price'],
            'mes' => calculate_radar_price($stats['mes'])['base_price'],
            '30days' => calculate_radar_price($stats['30days'])['base_price'],
        ];

        $nationalStats = $stats;
        $nationalPrices = $prices;

        if ($province && mb_strtolower($province, 'UTF-8') !== 'españa') {
            $nationalStats = $this->radarService->getContextStats(null, $sector);
            $nationalPrices = [
                'hoy' => calculate_radar_price($nationalStats['hoy'])['base_price'],
                'semana' => calculate_radar_price($nationalStats['semana'])['base_price'],
                'mes' => calculate_radar_price($nationalStats['mes'])['base_price'],
                '30days' => calculate_radar_price($nationalStats['30days'])['base_price'],
            ];
        }

        $data = array_merge($seoMeta, [
            'meta_description' => $seoMeta['excerptText'],
            'companies' => $companies,
            'stats' => $stats,
            'prices' => $prices,
            'national_stats' => $nationalStats,
            'national_prices' => $nationalPrices,
            'top_sectors' => $sidebarData['top_sectors'],
            'related_sectors' => $sidebarData['related_sectors'],
            'province' => $province,
            'sector' => $sector,
            'sector_label' => $sectorLabel,
            'potential_revenue_min' => number_format(($totalCount > 0 ? $totalCount : ($stats['30days'] ?? 100)) * 300, 0, ',', '.'),
            'potential_revenue_max' => number_format(($totalCount > 0 ? $totalCount : ($stats['30days'] ?? 100)) * 1500, 0, ',', '.'),
            'conversion_count' => $totalCount > 0 ? $totalCount : ($stats['30days'] ?? 100),
            'conversion_label' => $totalCount > 0 ? ($period === 'hoy' ? 'hoy' : 'en este periodo') : 'en el último mes',
            'dynamic_price' => $dynamicPriceData,
            'pricing'       => $dynamicPriceData,
            'period' => $period,
            'is_low_results' => $isLowResults,
            'robots' => $isLowResults ? 'noindex, follow' : 'index, follow',
            'canonical' => site_url(uri_string()),
            'paywall_level' => 'strong',
            'freeLimit' => get_free_plan_limit()
        ]);

        if ($isLowResults) {
            if ($province && $sectorLabel) {
                $data['national_sector_url'] = site_url("empresas-nuevas-sector/" . url_title($sectorLabel, '-', true));
                $data['general_directory_url'] = site_url("empresas-" . url_title($sectorLabel, '-', true) . "-en-" . ($province ? url_title($province, '-', true) : 'madrid'));
            } elseif ($province) {
                $data['general_directory_url'] = site_url("empresas/" . url_title($province, '-', true));
            }
        }

        $cache->save($cacheKey, $data, 82800);
        return $data;
    }

    /**
     * Called by the Python importer after each daily import to bust the radar cache.
     * Usage: GET /cron/radar-cache-clear/{token}
     */
    public function clearRadarCache($token)
    {
        $secretToken = env('RADAR_CACHE_TOKEN', 'radar_clear_2026');

        if ($token !== $secretToken) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Token inválido']);
        }

        $cache = \Config\Services::cache();
        $cache->clean(); // Clears all app cache

        log_message('info', '[RadarController] Cache purgado por importador Python - ' . date('Y-m-d H:i:s'));

        return $this->response->setJSON([
            'status' => 'ok',
            'message' => 'Cache de Radar eliminado correctamente',
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    private function deSlugify($slug)
    {
        $provinces = [
            'madrid' => 'MADRID',
            'barcelona' => 'BARCELONA',
            'valencia' => 'VALENCIA',
            'sevilla' => 'SEVILLA',
            'alicante' => 'ALICANTE',
            'alacant' => 'ALICANTE',
            'malaga' => 'MALAGA',
            'murcia' => 'MURCIA',
            'cadiz' => 'CADIZ',
            'vizcaya' => 'VIZCAYA',
            'coruna' => 'A CORUNA',
            'asturias' => 'ASTURIAS',
            'zaragoza' => 'ZARAGOZA',
            'pontevedra' => 'PONTEVEDRA',
            'granada' => 'GRANADA',
            'tarragona' => 'TARRAGONA',
            'cordoba' => 'CORDOBA',
            'girona' => 'GIRONA',
            'almeria' => 'ALMERIA',
            'toledo' => 'TOLEDO',
            'badajoz' => 'BADAJOZ',
            'navarra' => 'NAVARRA',
            'jaen' => 'JAEN',
            'cantabria' => 'CANTABRIA',
            'castellon' => 'CASTELLON',
            'huelva' => 'HUELVA',
            'valladolid' => 'VALLADOLID',
            'ciudad-real' => 'CIUDAD REAL',
            'leon' => 'LEON',
            'lleida' => 'LLEIDA',
            'caceres' => 'CACERES',
            'alava' => 'ALAVA',
            'lugo' => 'LUGO',
            'salamanca' => 'SALAMANCA',
            'burgos' => 'BURGOS',
            'albacete' => 'ALBACETE',
            'orense' => 'OURENSE',
            'ourense' => 'OURENSE',
            'larioja' => 'LA RIOJA',
            'rioja' => 'LA RIOJA',
            'guipuzcoa' => 'GUIPUZCOA',
            'huesca' => 'HUESCA',
            'cuenca' => 'CUENCA',
            'zamora' => 'ZAMORA',
            'palencia' => 'PALENCIA',
            'avila' => 'AVILA',
            'segovia' => 'SEGOVIA',
            'teruel' => 'TERUEL',
            'guadalajara' => 'GUADALAJARA',
            'soria' => 'SORIA',
            'islas-baleares' => 'BALEARES',
            'baleares' => 'BALEARES',
            'las-palmas' => 'LAS PALMAS',
            'santa-cruz-de-tenerife' => 'STA CRUZ TENERIFE',
            'tenerife' => 'STA CRUZ TENERIFE',
            'ceuta' => 'CEUTA',
            'melilla' => 'MELILLA',
        ];

        $key = strtolower($slug);
        return $provinces[$key] ?? strtoupper(str_replace('-', ' ', $slug));
    }



    /**
     * Webhook CRON (Sincronización Nocturna)
     */
    public function syncStatsWebhook($token)
    {
        $secretToken = 'sync_seo_api_2026';
        if ($token !== $secretToken) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Token inválido']);
        }

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

        // 2. Sincronizar CNAE has been removed due to deprecation of seo_stats_cnae table

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Sincronización SEO completada',
            'stats' => [
                'provinces_updated' => $provincesCount,
                'sectors_updated' => 0
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Genera un archivo CSV compatible con Excel para la descarga del listado comprado.
     */
    public function exportExcel()
    {
        $hasSimulatorToken = session('simulator_excel_token') !== null || session('just_bought_excel') !== null;
        if (!session('logged_in') && !$hasSimulatorToken) {
            return redirect()->to(site_url('enter'))->with('error', 'Debes iniciar sesión para descargar el listado.');
        }

        $params = $this->request->getGet();
        $filename = $this->getExportFilename($params);

        if (ob_get_length())
            ob_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        if (!empty($params['dl_token'])) {
            setcookie('dl_token', $params['dl_token'], time() + 120, '/');
        }

        $fp = fopen('php://output', 'w');
        $this->streamExportData($params, $fp);
        fclose($fp);
        exit();
    }

    public function sendExportEmail()
    {
        $hasExcelToken = session('simulator_excel_token') !== null || session('just_bought_excel') !== null;
        if (!session('logged_in') && !$hasExcelToken) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Debes iniciar sesión.']);
        }

        $email = $this->request->getPost('email');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Email no válido.']);
        }

        session()->set('last_export_email', $email);

        $params = $this->request->getGet();
        $filename = $this->getExportFilename($params);

        $tempFile = tempnam(sys_get_temp_dir(), 'export');
        $fp = fopen($tempFile, 'w');
        $this->streamExportData($params, $fp);
        fclose($fp);

        $emailService = \Config\Services::email();
        $emailService->setTo($email);
        $emailService->setSubject('Tu listado de empresas - Radar APIEmpresas');
        $emailService->setMessage('Adjunto encontrarás el listado de empresas solicitado en formato CSV.');
        $emailService->attach($tempFile, 'attachment', $filename, 'text/csv');

        if ($emailService->send()) {
            unlink($tempFile);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Email enviado correctamente a ' . $email]);
        } else {
            unlink($tempFile);
            return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo enviar el email.']);
        }
    }

    private function getExportData($params): array
    {
        $sector = $params['sector'] ?? '';
        $province = $params['provincia'] ?? 'España';
        $period = $params['period'] ?? $params['rango'] ?? '30days';
        $cnae = $params['cnae'] ?? '';
        $cnae_text = $params['cnae_text'] ?? '';
        $estado = $params['estado'] ?? '';

        $allowedPeriods = ['7', '30', '90', 'hoy', 'semana', 'mes', '30days', 'general'];
        if (($params['is_historical'] ?? '0') === '1') {
            $period = 'general';
        }
        if (!in_array($period, $allowedPeriods, true)) {
            $period = '30days';
        }

        $db = \Config\Database::connect();
        $builder = $db->table('companies');
        $builder->select('id, company_name as name, cif, fecha_constitucion, cnae_label, registro_mercantil, municipality, address, objeto_social, phone');

        if ($cnae !== '') {
            $builder->where('cnae_code LIKE', $cnae . '%');
        } elseif ($cnae_text !== '') {
            $builder->like('cnae_label', $cnae_text, 'both');
        }

        if ($estado !== '') {
            $builder->where('estado', $estado);
        }

        if ($province && mb_strtolower($province, 'UTF-8') !== 'españa' && $province !== $sector) {
            if (in_array(mb_strtolower($province, 'UTF-8'), ['alicante', 'alacant', 'alicante/alacant'])) {
                $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant', 'ALACANT']);
            } elseif (in_array(mb_strtolower($province, 'UTF-8'), ['araba/álava', 'álava', 'álava-araba', 'araba', 'alava'])) {
                $builder->whereIn('registro_mercantil', ['ÁLAVA', 'Álava-Araba', 'Araba/Álava', 'ALAVA']);
            } else {
                $builder->where('registro_mercantil', $province);
            }
        }

        if ($sector && mb_strtolower($sector, 'UTF-8') !== 'general') {
            $resolution = $this->radarService->resolveCnaeCodes(url_title($sector, '-', true));
            if ($resolution) {
                $codes = $resolution['codes'];
                if (count($codes) === 1)
                    $builder->where('cnae_code LIKE', $codes[0] . '%');
                else {
                    $builder->groupStart();
                    foreach ($codes as $code)
                        $builder->orLike('cnae_code', $code, 'after');
                    $builder->groupEnd();
                }
            }
        }

        // Filtro de fecha: 'general' o CNAE histórico sin period = sin límite de fecha
        if ($period === 'hoy') {
            $builder->where('fecha_constitucion', date('Y-m-d'));
        } elseif ($period === 'semana' || $period === '7') {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-7 days')));
            $builder->where('fecha_constitucion <=', date('Y-m-d'));
        } elseif ($period === '90') {
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')));
            $builder->where('fecha_constitucion <=', date('Y-m-d'));
        } elseif ($period === 'general') {
            // Histórico completo: sin filtro de fecha (para exportaciones de directorios CNAE y provincias)
            // No requerimos fecha_constitucion IS NOT NULL para no perder registros históricos.
        } else {
            // Default: últimos 90 días
            $builder->where('fecha_constitucion >=', date('Y-m-d', strtotime('-90 days')));
            $builder->where('fecha_constitucion <=', date('Y-m-d'));
        }

        $builder->orderBy('fecha_constitucion', 'DESC');

        $isHistorical = ($params['is_historical'] ?? '0') === '1' || $period === 'general';

        if (!$isHistorical) {
            $builder->limit($cnae !== '' ? 2000 : 5000);
        } else {
            // For historical province downloads, limit must be much higher or removed.
            // We set it to 500k to prevent OOM but allow full provinces.
            $builder->limit(500000);
        }

        $companies = $builder->get()->getResultArray();

        if (empty($companies)) {
            return [];
        }

        $companyIds = array_column($companies, 'id');

        $adminRows = [];
        $bormeRows = [];
        $chunks = array_chunk($companyIds, 5000);

        foreach ($chunks as $chunk) {
            $chunkAdmin = $db->table('company_administrators')
                ->select('company_id, position, name')
                ->whereIn('company_id', $chunk)
                ->get()->getResultArray();
            $adminRows = array_merge($adminRows, $chunkAdmin);

            $chunkBorme = $db->table('borme_posts')
                ->select('company_id, description')
                ->whereIn('company_id', $chunk)
                ->get()->getResultArray();
            $bormeRows = array_merge($bormeRows, $chunkBorme);
        }

        $adminsByCompany = [];
        foreach ($adminRows as $row) {
            $cid = $row['company_id'];
            $position = $row['position'] ?: 'Administrador';
            $adminsByCompany[$cid][] = $position . ': ' . $row['name'];
        }

        $bormeExtracted = [];
        foreach ($bormeRows as $row) {
            $cid = $row['company_id'];
            $desc = $row['description'] ?? '';

            if (!isset($bormeExtracted[$cid])) {
                $bormeExtracted[$cid] = ['capital' => '', 'socio_unico' => ''];
            }

            // Extract Capital
            if (empty($bormeExtracted[$cid]['capital']) && preg_match('/Capital:\s*([\d\.,]+\s*Euros?)/iu', $desc, $matches)) {
                $bormeExtracted[$cid]['capital'] = trim($matches[1]);
            }

            // Extract Socio unico
            if (empty($bormeExtracted[$cid]['socio_unico']) && preg_match('/Socio único:\s*([^.]+)\./iu', $desc, $matches)) {
                $bormeExtracted[$cid]['socio_unico'] = trim($matches[1]);
            }
        }

        foreach ($companies as &$c) {
            $cid = $c['id'];
            $c['administrators'] = isset($adminsByCompany[$cid]) ? implode(' | ', $adminsByCompany[$cid]) : '';
            $c['capital_social'] = $bormeExtracted[$cid]['capital'] ?? '';
            $c['socio_unico'] = $bormeExtracted[$cid]['socio_unico'] ?? '';
        }

        return $companies;
    }

    private function getExportFilename($params): string
    {
        $sector = $params['sector'] ?? '';
        $province = $params['provincia'] ?? 'España';
        $cnae = $params['cnae'] ?? '';
        $isHistorical = ($params['is_historical'] ?? '0') === '1';

        if ($cnae !== '') {
            return "Directorio_" . preg_replace('/[^A-Za-z0-9_]/', '_', $cnae) . "_" . str_replace(' ', '_', $province) . ".csv";
        }

        if ($isHistorical) {
            return "Directorio_Historico_" . str_replace(' ', '_', $province) . ".csv";
        }

        return "Listado_Nuevas_Empresas_" . str_replace(' ', '_', $sector) . "_" . str_replace(' ', '_', $province) . ".csv";
    }

    private function streamExportData($params, $fp)
    {
        // BOM for Excel compatibility with UTF-8
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $companies = $this->getExportData($params);

        // Headers
        fputcsv($fp, [
            'Empresa',
            'CIF',
            'Constitución',
            'Sector CNAE',
            'Municipio',
            'Provincia',
            'Teléfono',
            'Dirección',
            'Objeto Social',
            'Capital Social',
            'Socio Único',
            'Administradores'
        ]);

        foreach ($companies as $c) {
            fputcsv($fp, [
                $c['name'] ?? '',
                $c['cif'] ?? '',
                $c['fecha_constitucion'] ?? '',
                $c['cnae_label'] ?? '',
                $c['municipality'] ?? '',
                $c['registro_mercantil'] ?? '',
                $c['phone'] ?? '',
                $c['address'] ?? '',
                $c['objeto_social'] ?? '',
                $c['capital_social'] ?? '',
                $c['socio_unico'] ?? '',
                $c['administrators'] ?? ''
            ]);
        }
    }

    // REMOVED OLD METHODS:
    private function generateExcelHtml(array $companies): string
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
            $rawDate = $company['fecha_constitucion'] ?? '';
            $ts = $rawDate ? strtotime(str_replace('/', '-', $rawDate)) : false;
            $cleanDate = ($ts && $ts >= strtotime('1900-01-01') && $ts <= strtotime('2100-01-01')) ? date('d/m/Y', $ts) : '';

            echo '<tr>';
            echo '<td style="' . $tdStyle . '">' . esc($company['name'] ?? '') . '</td>';
            echo '<td style="' . $textStyle . '">' . esc($company['cif'] ?? '') . '</td>';
            echo '<td style="' . $tdStyle . '">' . esc($company['address'] ?? '') . '</td>';
            echo '<td style="' . $tdStyle . '">' . esc($company['municipality'] ?? $company['municipio'] ?? '') . '</td>';
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

    public function exportSubsidiesExcel()
    {
        $hasExcelToken = session('simulator_excel_token') !== null || session('just_bought_excel') !== null;
        if (!session('logged_in') && !$hasExcelToken) {
            return redirect()->to(site_url('enter'))->with('error', 'Debes iniciar sesión para descargar el listado.');
        }

        $params = $this->request->getGet();
        $convocatoria = $params['convocatoria'] ?? '';
        $year = $params['year'] ?? '';

        $filename = "Subvenciones";
        if ($convocatoria) $filename .= "_" . substr(preg_replace('/[^A-Za-z0-9_]/', '_', $convocatoria), 0, 30);
        if ($year) $filename .= "_" . $year;
        $filename .= ".csv";

        if (ob_get_length()) ob_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $fp = fopen('php://output', 'w');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

        fputcsv($fp, [
            'Empresa',
            'CIF',
            'Convocatoria',
            'Instrumento / Detalle',
            'Fecha Concesión',
            'Importe',
            'Teléfono',
            'Sector CNAE',
            'Provincia',
            'Dirección'
        ]);

        $db = \Config\Database::connect();
        $builder = $db->table('company_subsidies s');
        $builder->select('
            s.raw_beneficiario, 
            s.company_cif, 
            s.convocatoria, 
            s.instrumento, 
            s.fecha_concesion, 
            s.importe,
            c.phone,
            c.cnae_label,
            c.registro_mercantil,
            c.address,
            c.company_name
        ');
        $builder->join('companies c', 'c.cif = s.company_cif', 'left');

        if ($convocatoria !== '') {
            $billingService = new \App\Services\BillingService();
            $builder->where('s.convocatoria', $billingService->resolveSubsidiesConvocatoria($convocatoria));
        }
        if ($year !== '') {
            $builder->where('YEAR(s.fecha_concesion)', $year);
        }

        $query = $builder->get();
        foreach ($query->getResultArray() as $row) {
            $empresa = $row['company_name'] ?: $row['raw_beneficiario'] ?: $row['company_cif'];
            fputcsv($fp, [
                $empresa,
                $row['company_cif'],
                $row['convocatoria'],
                $row['instrumento'],
                $row['fecha_concesion'] ? date('d/m/Y', strtotime($row['fecha_concesion'])) : '',
                number_format((float)$row['importe'], 2, ',', ''),
                $row['phone'] ?? '',
                $row['cnae_label'] ?? '',
                $row['registro_mercantil'] ?? '',
                $row['address'] ?? ''
            ]);
        }

        fclose($fp);
        exit();
    }

    public function exportContractsExcel()
    {
        $hasExcelToken = session('simulator_excel_token') !== null || session('just_bought_excel') !== null;
        if (!session('logged_in') && !$hasExcelToken) {
            return redirect()->to(site_url('enter'))->with('error', 'Debes iniciar sesión para descargar el listado.');
        }

        $params = $this->request->getGet();
        $year = $params['year'] ?? '';
        $organo = $params['organo'] ?? '';

        $filename = "Contratos_Publicos";
        if ($organo) $filename .= "_" . substr(preg_replace('/[^A-Za-z0-9_]/', '_', $organo), 0, 30);
        if ($year) $filename .= "_" . $year;
        $filename .= ".csv";

        if (ob_get_length()) ob_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $fp = fopen('php://output', 'w');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

        fputcsv($fp, [
            'Empresa Adjudicataria',
            'CIF',
            'Órgano de Contratación',
            'Título del Contrato',
            'Fecha Adjudicación',
            'Importe Adjudicación',
            'Teléfono',
            'Sector CNAE',
            'Provincia',
            'Dirección'
        ]);

        $db = \Config\Database::connect();
        $builder = $db->table('company_contracts c_contr');
        $builder->select('
            c_contr.company_name, 
            c_contr.raw_adjudicatario, 
            c_contr.company_cif, 
            c_contr.organo_contratacion, 
            c_contr.titulo_contrato, 
            c_contr.fecha_adjudicacion, 
            c_contr.importe_adjudicacion,
            c.phone,
            c.cnae_label,
            c.registro_mercantil,
            c.address
        ');
        $builder->join('companies c', 'c.cif = c_contr.company_cif', 'left');

        if ($organo !== '') {
            $billingService = new \App\Services\BillingService();
            $builder->where('c_contr.organo_contratacion', $billingService->resolveContractsOrgano($organo));
        }
        if ($year !== '') {
            $builder->where('YEAR(c_contr.fecha_adjudicacion)', $year);
        }

        $query = $builder->get();
        foreach ($query->getResultArray() as $row) {
            $empresa = $row['company_name'] ?: $row['raw_adjudicatario'] ?: $row['company_cif'];
            fputcsv($fp, [
                $empresa,
                $row['company_cif'],
                $row['organo_contratacion'],
                $row['titulo_contrato'],
                $row['fecha_adjudicacion'] ? date('d/m/Y', strtotime($row['fecha_adjudicacion'])) : '',
                number_format((float)$row['importe_adjudicacion'], 2, ',', ''),
                $row['phone'] ?? '',
                $row['cnae_label'] ?? '',
                $row['registro_mercantil'] ?? '',
                $row['address'] ?? ''
            ]);
        }

        fclose($fp);
        exit();
    }
}
