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
        helper(['company', 'pricing', 'url']);
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
                'signup_intent' => 'radar',
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
     * Genera un archivo CSV compatible con Excel para la descarga del listado comprado.
     */
    public function exportExcel()
    {
        // Solo con una compra verificada: los filtros salen del permiso que concede
        // Billing::success tras cobrar, nunca de la URL (ver App\Libraries\PaidExports).
        $params = \App\Libraries\PaidExports::autorizar($this->request, 'excel');
        if ($params === null) {
            return $this->descargaNoAutorizada();
        }
        $filename = $this->getExportFilename($params);
        $params['dl_token'] = (string) ($this->request->getGet('dl_token') ?? '');

        // Listados grandes: se escriben por lotes y pueden tardar minutos. Sin límite
        // de tiempo, sin búferes (cada lote sale al navegador según se escribe) y
        // soltando la sesión para no bloquear al usuario en otras pestañas.
        @set_time_limit(0);
        session_write_close();
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

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
        $params = \App\Libraries\PaidExports::autorizar($this->request, 'excel');
        if ($params === null) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'No hay ninguna compra asociada a esta descarga.']);
        }

        $email = $this->request->getPost('email');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Email no válido.']);
        }

        session()->set('last_export_email', $email);

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

    /**
     * Respuesta común cuando se pide una descarga sin compra verificada.
     */
    private function descargaNoAutorizada()
    {
        // Página propia y directa: redirigir a /listado-de-empresas era lento (su
        // índice agrupa toda la tabla de empresas si la caché está fría) y esa
        // página no muestra mensajes flash, así que el aviso no se veía.
        return $this->response
            ->setStatusCode(403)
            ->setBody(view('billing/download_denied'));
    }

    /**
     * Consulta de la exportación con los filtros de la compra (sin orden ni límite).
     *
     * @return array{0: \CodeIgniter\Database\BaseBuilder, 1: bool} [builder, ¿histórico?]
     */
    private function buildExportQuery($db, array $params): array
    {
        $sector = $params['sector'] ?? '';
        $province = $params['provincia'] ?? 'España';
        $period = $params['period'] ?? $params['rango'] ?? '30days';
        $cnae = $params['cnae'] ?? '';
        $cnae_text = $params['cnae_text'] ?? '';
        $estado = $params['estado'] ?? '';
        $has_phone = $params['has_phone'] ?? '';

        $allowedPeriods = ['7', '30', '90', 'hoy', 'semana', 'mes', '30days', 'general'];
        if (($params['is_historical'] ?? '0') === '1' || $cnae !== '') {
            $period = 'general';
        }
        if (!in_array($period, $allowedPeriods, true)) {
            $period = $cnae !== '' ? 'general' : '30days';
        }

        $builder = $db->table('companies');
        // phone_mobile y estado (02-10-2026): el filtro "con teléfono" cuenta fijo O móvil,
        // pero el CSV solo traía el fijo (una empresa con solo móvil salía con el teléfono
        // vacío), y el mapa prometía la columna "Estado", que no existía.
        $builder->select('id, company_name as name, cif, fecha_constitucion, cnae_label, registro_mercantil, municipality, address, objeto_social, phone, phone_mobile, estado');

        // Prioridad de filtrado CNAE / Sector: si viene código CNAE explícito, no resolver sector para evitar conflicto WHERE
        if ($cnae !== '') {
            $builder->where('cnae_code LIKE', $cnae . '%');
        } elseif ($cnae_text !== '') {
            $builder->like('cnae_label', $cnae_text, 'both');
        } elseif ($sector && mb_strtolower($sector, 'UTF-8') !== 'general') {
            $resolution = $this->radarService->resolveCnaeCodes(url_title($sector, '-', true));
            if ($resolution) {
                $codes = $resolution['codes'];
                if (count($codes) === 1) {
                    $builder->where('cnae_code LIKE', $codes[0] . '%');
                } else {
                    $builder->groupStart();
                    foreach ($codes as $code) {
                        $builder->orLike('cnae_code', $code, 'after');
                    }
                    $builder->groupEnd();
                }
            }
        }

        if ($estado !== '') {
            $builder->where('estado', $estado);
        }

        if ($has_phone == '1') {
            $builder->groupStart()
                    ->groupStart()->where('phone IS NOT NULL', null, false)->where('phone !=', '')->groupEnd()
                    ->orGroupStart()->where('phone_mobile IS NOT NULL', null, false)->where('phone_mobile !=', '')->groupEnd()
                    ->groupEnd();
        }

        // Mismo filtro de fechas que BillingService::countDirectoryCompanies (lo que se cobró)
        if (!empty($params['date_min'])) {
            $builder->where('estado_fecha >=', $params['date_min']);
        }
        if (!empty($params['date_max'])) {
            $builder->where('estado_fecha <=', $params['date_max']);
        }

        // Municipio del mapa: mismo filtro que el recuento que se cobró
        if (!empty($params['municipio'])) {
            $builder->like('address', (string) $params['municipio'], 'both');
        }

        // Provincia. En los listados del directorio (históricos) se aplica SIEMPRE,
        // igual que en el recuento que se cobra (BillingService::filtrarProvincia).
        // Antes se saltaba si la provincia coincidía con el sector: el mapa manda
        // `sector=<provincia>` cuando no hay texto de CNAE, y se entregaba ese CNAE
        // de toda España habiendo cobrado solo la provincia. La excepción se queda
        // solo para el Radar, que es de donde venía.
        $esDirectorio = ($params['is_historical'] ?? '0') === '1';
        if ($esDirectorio
            || ($province && mb_strtolower($province, 'UTF-8') !== mb_strtolower($sector, 'UTF-8') && $province !== $cnae_text)) {
            \App\Services\BillingService::filtrarProvincia($builder, (string) $province);
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

        $isHistorical = ($params['is_historical'] ?? '0') === '1' || $period === 'general';

        return [$builder, $isHistorical];
    }

    /**
     * Añade administradores, capital y socio único a un lote de empresas.
     * Solo carga los del lote: la memoria no crece con el tamaño del listado.
     */
    private function enrichExportBatch($db, array $companies): array
    {
        if (empty($companies)) {
            return [];
        }
        $ids = array_column($companies, 'id');

        $adminsByCompany = [];
        foreach ($db->table('company_administrators')
                    ->select('company_id, position, name')
                    ->whereIn('company_id', $ids)
                    ->get()->getResultArray() as $row) {
            $position = $row['position'] ?: 'Administrador';
            $adminsByCompany[$row['company_id']][] = $position . ': ' . $row['name'];
        }

        $bormeExtracted = [];
        foreach ($db->table('borme_posts')
                    ->select('company_id, description')
                    ->whereIn('company_id', $ids)
                    ->get()->getResultArray() as $row) {
            $cid = $row['company_id'];
            $desc = $row['description'] ?? '';
            if (!isset($bormeExtracted[$cid])) {
                $bormeExtracted[$cid] = ['capital' => '', 'socio_unico' => ''];
            }
            if ($bormeExtracted[$cid]['capital'] === '' && preg_match('/Capital:\s*([\d\.,]+\s*Euros?)/iu', $desc, $m)) {
                $bormeExtracted[$cid]['capital'] = trim($m[1]);
            }
            if ($bormeExtracted[$cid]['socio_unico'] === '' && preg_match('/Socio único:\s*([^.]+)\./iu', $desc, $m)) {
                $bormeExtracted[$cid]['socio_unico'] = trim($m[1]);
            }
        }

        foreach ($companies as &$c) {
            $cid = $c['id'];
            $c['administrators'] = isset($adminsByCompany[$cid]) ? implode(' | ', $adminsByCompany[$cid]) : '';
            $c['capital_social'] = $bormeExtracted[$cid]['capital'] ?? '';
            $c['socio_unico'] = $bormeExtracted[$cid]['socio_unico'] ?? '';
        }
        unset($c);

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

    /**
     * Escribe el CSV por lotes.
     *
     * Antes se cargaba todo el listado (hasta 500.000 empresas, más sus
     * administradores y anuncios del BORME) en memoria antes de escribir la primera
     * línea: con Madrid, Barcelona o España se agotaba la memoria o el tiempo y el
     * cliente pagaba y recibía un error. Y el tope de 500.000 cortaba en silencio
     * listados que se cobraban enteros.
     *
     * Ahora, en los históricos, se leen lotes de EXPORT_BATCH empresas por id
     * (de más reciente a más antigua), se enriquecen solo esas y se escriben y
     * envían al momento. La memoria es la de un lote y no hay tope de filas.
     * Los listados del Radar (últimos días, máx. 5.000) siguen en una consulta.
     */
    private const EXPORT_BATCH = 2000;

    private function streamExportData($params, $fp)
    {
        // BOM for Excel compatibility with UTF-8
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

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
            'Administradores',
            'Estado'
        ]);

        $db = \Config\Database::connect();
        [$builder, $isHistorical] = $this->buildExportQuery($db, $params);

        if (!$isHistorical) {
            $cnae = $params['cnae'] ?? '';
            $rows = $builder->orderBy('fecha_constitucion', 'DESC')
                ->limit($cnae !== '' ? 2000 : 5000)
                ->get()->getResultArray();
            $this->writeExportRows($fp, $this->enrichExportBatch($db, $rows));
            return;
        }

        $lastId = null;
        do {
            [$builder] = $this->buildExportQuery($db, $params);
            if ($lastId !== null) {
                $builder->where('id <', $lastId);
            }
            $rows = $builder->orderBy('id', 'DESC')
                ->limit(self::EXPORT_BATCH)
                ->get()->getResultArray();

            if (empty($rows)) {
                break;
            }
            $lastId = (int) end($rows)['id'];

            $this->writeExportRows($fp, $this->enrichExportBatch($db, $rows));
            fflush($fp);
            if (function_exists('flush')) {
                flush();
            }
            unset($rows);
        } while (true);
    }

    /** Fijo y móvil en la columna "Teléfono" (sin repetir si son el mismo) */
    private static function telefonos(array $c): string
    {
        $t = array_filter(array_map('trim', [(string) ($c['phone'] ?? ''), (string) ($c['phone_mobile'] ?? '')]), 'strlen');

        return implode(' / ', array_unique($t));
    }

    /**
     * Vista previa del listado antes de pagar: filas del MISMO listado que se descargará
     * (misma consulta y mismos filtros que streamExportData), con las mismas columnas. Los datos de contacto y los nombres de personas van tapados: se ve que
     * están, no lo que dicen.
     *
     * @return array<int, array<string, string>> filas con las cabeceras del CSV como clave
     */
    public function previewExport(array $params, int $n = 5): array
    {
        $params['is_historical'] = '1';
        $n  = max(1, min(10, $n));
        $db = \Config\Database::connect();

        // Filas repartidas por todo el listado, no las N más recientes: las altas de los
        // últimos días aún no tienen CIF, sector ni teléfono (en Soria, 4 de 5 sin CIF) y
        // daban una imagen peor que la del archivo. Tampoco se eligen las más completas:
        // se toma la empresa que cae en el 10 %, 30 %, 50 %, 70 % y 90 % del rango de ids.
        $extremo = function (string $dir) use ($db, $params): int {
            [$b] = $this->buildExportQuery($db, $params);
            $r = $b->orderBy('id', $dir)->limit(1)->get()->getRowArray();

            return (int) ($r['id'] ?? 0);
        };
        $max = $extremo('DESC');
        $min = $extremo('ASC');
        if ($max <= 0) {
            return [];
        }

        $rows = [];
        for ($i = 0; $i < $n; $i++) {
            $umbral = (int) round($max - ($max - $min) * (($i + 0.5) / $n));
            [$b] = $this->buildExportQuery($db, $params);
            $r = $b->where('id <=', $umbral)->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();
            if ($r && !isset($rows[$r['id']])) {
                $rows[$r['id']] = $r;
            }
        }
        $rows = $this->enrichExportBatch($db, array_values($rows));

        $out = [];
        foreach ($rows as $c) {
            $out[] = [
                'Empresa'         => (string) ($c['name'] ?? ''),
                'CIF'             => (string) ($c['cif'] ?? ''),
                'Constitución'    => (string) ($c['fecha_constitucion'] ?? ''),
                'Sector CNAE'     => (string) ($c['cnae_label'] ?? ''),
                'Municipio'       => (string) ($c['municipality'] ?? ''),
                'Provincia'       => (string) ($c['registro_mercantil'] ?? ''),
                'Teléfono'        => self::taparTelefonos(self::telefonos($c)),
                'Dirección'       => self::taparFinal((string) ($c['address'] ?? ''), 14),
                'Objeto Social'   => (string) ($c['objeto_social'] ?? ''),
                'Capital Social'  => (string) ($c['capital_social'] ?? ''),
                'Socio Único'     => self::taparNombres((string) ($c['socio_unico'] ?? '')),
                'Administradores' => self::taparNombres((string) ($c['administrators'] ?? '')),
                'Estado'          => (string) ($c['estado'] ?? ''),
            ];
        }

        return $out;
    }

    /** "912345678 / 600111222" → "912 34• ••• / 600 11• •••" */
    private static function taparTelefonos(string $t): string
    {
        if ($t === '') {
            return '';
        }
        $partes = [];
        foreach (explode(' / ', $t) as $tel) {
            $d = preg_replace('/\D/', '', $tel) ?? '';
            $d = strlen($d) > 9 ? substr($d, -9) : $d;
            $partes[] = strlen($d) < 6 ? '••• ••• •••' : substr($d, 0, 3) . ' ' . substr($d, 3, 2) . '• •••';
        }

        return implode(' / ', $partes);
    }

    /** "Calle Mayor 12, 3º B, Madrid" → "Calle Mayor 12•••" */
    private static function taparFinal(string $s, int $visibles): string
    {
        return mb_strlen($s, 'UTF-8') <= $visibles ? $s : mb_substr($s, 0, $visibles, 'UTF-8') . '•••';
    }

    /** "Adm. Único: PEREZ GOMEZ JUAN | Apoderado: …" → "Adm. Único: P•••• G•••• J••• | Apoderado: …" */
    private static function taparNombres(string $s): string
    {
        if ($s === '') {
            return '';
        }
        $partes = [];
        foreach (explode(' | ', $s) as $parte) {
            $cargo  = '';
            $nombre = $parte;
            if (str_contains($parte, ': ')) {
                [$cargo, $nombre] = explode(': ', $parte, 2);
                $cargo .= ': ';
            }
            $nombre = preg_replace_callback('/[\p{L}\p{N}]+/u', static function ($m) {
                $w = $m[0];

                return mb_substr($w, 0, 1, 'UTF-8') . str_repeat('•', min(5, max(1, mb_strlen($w, 'UTF-8') - 1)));
            }, $nombre) ?? '•••';
            $partes[] = $cargo . $nombre;
        }

        return implode(' | ', $partes);
    }

    private function writeExportRows($fp, array $companies): void
    {
        foreach ($companies as $c) {
            fputcsv($fp, [
                $c['name'] ?? '',
                $c['cif'] ?? '',
                $c['fecha_constitucion'] ?? '',
                $c['cnae_label'] ?? '',
                $c['municipality'] ?? '',
                $c['registro_mercantil'] ?? '',
                self::telefonos($c),
                $c['address'] ?? '',
                $c['objeto_social'] ?? '',
                $c['capital_social'] ?? '',
                $c['socio_unico'] ?? '',
                $c['administrators'] ?? '',
                $c['estado'] ?? ''
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
        $params = \App\Libraries\PaidExports::autorizar($this->request, 'subsidies');
        if ($params === null) {
            return $this->descargaNoAutorizada();
        }
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
        $params = \App\Libraries\PaidExports::autorizar($this->request, 'contracts');
        if ($params === null) {
            return $this->descargaNoAutorizada();
        }
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
