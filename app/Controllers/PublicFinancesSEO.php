<?php

namespace App\Controllers;

use App\Libraries\FondosPublicos;

class PublicFinancesSEO extends BaseController
{
    private const POR_PAGINA = 50;

    /** Página pedida (?page=N), mínimo 1 */
    private function pagina(): int
    {
        return max(1, (int) ($this->request->getGet('page') ?? 1));
    }

    private function noEncontrada(string $msg = 'Página no encontrada.')
    {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound($msg);
    }

    /** 404 si la página pedida no existe (antes respondía 200 con la tabla vacía) */
    private function validarPagina(int $page, int $total, string $q): void
    {
        if ($q !== '') {
            return;
        }
        if ($total === 0 || $page > (int) ceil($total / self::POR_PAGINA)) {
            $this->noEncontrada();
        }
    }

    /** Las URL antiguas /slug/2 mostraban la página 1: se redirigen a ?page=2 */
    private function redirigirSegmento(string $base, $segmento)
    {
        $n = (int) $segmento;
        return redirect()->to($base . ($n > 1 ? '?page=' . $n : ''), 301);
    }

    /**
     * Filas y totales de un listado de subvenciones o contratos.
     * Solo personas jurídicas (ver Libraries/FondosPublicos). El nombre de la empresa
     * se saca con subconsulta: con LEFT JOIN, un CIF repetido en companies duplicaba
     * filas e inflaba el total.
     */
    private function listado(string $tabla, string $cond, array $params, string $orden, string $colImporte, string $nombreOrigen, string $q, int $page, string $claveTotal): array
    {
        $db    = \Config\Database::connect();
        $where = $cond . ' AND ' . FondosPublicos::soloJuridicas('t.company_cif');
        if ($q !== '') {
            $where   .= ' AND (t.company_cif LIKE ? OR EXISTS (SELECT 1 FROM companies comp WHERE comp.cif = t.company_cif AND comp.company_name LIKE ?))';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        $cache  = \Config\Services::cache();
        $totales = $q === '' ? $cache->get($claveTotal) : null;
        if (!is_array($totales)) {
            $r = $db->query("SELECT COUNT(*) AS total, SUM(t.{$colImporte}) AS importe FROM {$tabla} t WHERE {$where}", $params)->getRow();
            $totales = ['total' => (int) ($r->total ?? 0), 'importe' => (float) ($r->importe ?? 0)];
            if ($q === '' && $totales['total'] > 0) {
                $cache->save($claveTotal, $totales, 21600);
            }
        }

        $this->validarPagina($page, $totales['total'], $q);

        $filas = $db->query("
            SELECT t.*, {$nombreOrigen} AS nombre_origen,
                   (SELECT comp.company_name FROM companies comp WHERE comp.cif = t.company_cif LIMIT 1) AS company_name
            FROM {$tabla} t
            WHERE {$where}
            ORDER BY {$orden}
            LIMIT ? OFFSET ?
        ", array_merge($params, [self::POR_PAGINA, ($page - 1) * self::POR_PAGINA]))->getResultArray();

        return [$filas, $totales['total'], $totales['importe']];
    }

    private function enlaces(int $page, int $total): string
    {
        return \Config\Services::pager()->makeLinks($page, self::POR_PAGINA, $total, 'seo_es');
    }

    private const NOMBRE_CONTRATO   = "COALESCE(NULLIF(t.company_name, ''), t.raw_adjudicatario)";
    private const NOMBRE_SUBVENCION = 't.raw_beneficiario';

    // ── LICITACIONES ─────────────────────────────────────────────────────────
    public function contractsHub()
    {
        $page = $this->pagina();
        $q    = trim((string) ($this->request->getGet('q') ?? ''));
        $db   = \Config\Database::connect();

        $whereClause = "organo_contratacion IS NOT NULL AND organo_contratacion != '' AND " . FondosPublicos::soloJuridicas('company_cif');
        $params = [];
        if ($q !== '') {
            $whereClause .= ' AND organo_contratacion LIKE ?';
            $params[] = '%' . $q . '%';
        }

        $cache    = \Config\Services::cache();
        $cacheKey = 'seo_contracts_hub_page1_v3';
        $data     = ($page === 1 && $q === '') ? $cache->get($cacheKey) : null;

        if (!$data) {
            $organsData = $db->query("
                SELECT organo_contratacion as name, COUNT(id) as total_contracts, SUM(importe_adjudicacion) as total_amount, COUNT(DISTINCT company_cif) as total_companies
                FROM company_contracts
                WHERE $whereClause
                GROUP BY organo_contratacion
                ORDER BY total_contracts DESC
                LIMIT ? OFFSET ?
            ", array_merge($params, [self::POR_PAGINA, ($page - 1) * self::POR_PAGINA]))->getResultArray();

            $organs = [];
            foreach ($organsData as $org) {
                $org['slug'] = FondosPublicos::slug($org['name']);
                if ($org['slug'] !== '') {
                    $organs[] = $org;
                }
            }

            $statsRow = $db->query("SELECT COUNT(DISTINCT organo_contratacion) as total, COUNT(id) as total_c, SUM(importe_adjudicacion) as total_a FROM company_contracts WHERE $whereClause", $params)->getRow();
            $maxRow   = $db->query("SELECT COUNT(id) as c FROM company_contracts WHERE organo_contratacion IS NOT NULL AND organo_contratacion != '' AND " . FondosPublicos::soloJuridicas('company_cif') . " GROUP BY organo_contratacion ORDER BY c DESC LIMIT 1")->getRow();

            $data = [
                'organs'           => $organs,
                'total'            => (int) ($statsRow->total ?? 0),
                'max_contracts'    => $maxRow->c ?? 1,
                'global_contracts' => $statsRow->total_c ?? 0,
                'global_amount'    => $statsRow->total_a ?? 0,
            ];

            if ($page === 1 && $q === '' && $organs) {
                $cache->save($cacheKey, $data, 86400);
            }
        }

        $this->validarPagina($page, (int) $data['total'], $q);

        return view('seo/hub_contratos', [
            'organs' => $data['organs'],
            'total_organs' => $data['total'],
            'global_contracts' => $data['global_contracts'],
            'global_amount' => $data['global_amount'],
            'max_contracts' => $data['max_contracts'],
            'anos' => array_keys(FondosPublicos::anosContratos()),
            'pager' => $this->enlaces($page, (int) $data['total']),
            'currentPage' => $page,
            'searchQuery' => $q,
            'title' => "Licitaciones públicas: buscador de empresas adjudicatarias",
            'meta_description' => "Qué empresas ganan las licitaciones públicas en España, por órgano de contratación. Adjudicaciones, importes y contratistas, a partir de datos públicos.",
            'canonical' => site_url('licitaciones-del-estado') . ($page > 1 ? '?page=' . $page : '')
        ]);
    }

    public function contractsByOrgan($slug, $segmento = null)
    {
        $base = site_url('licitaciones-del-estado/organo-' . $slug);
        if ($segmento !== null) {
            return $this->redirigirSegmento($base, $segmento);
        }

        $page = $this->pagina();
        $q    = trim((string) ($this->request->getGet('q') ?? ''));

        $organName = FondosPublicos::slugsOrganos()[$slug] ?? null;
        if (!$organName) {
            $this->noEncontrada('Órgano no encontrado.');
        }

        [$contracts, $total] = $this->listado(
            'company_contracts', 't.organo_contratacion = ?', [$organName],
            't.fecha_adjudicacion DESC', 'importe_adjudicacion', self::NOMBRE_CONTRATO,
            $q, $page, 'fp_total_organo_' . md5($organName)
        );

        $organTitle = mb_convert_case($organName, MB_CASE_TITLE, "UTF-8");

        return view('seo/listado_organo', [
            'organName'   => $organName,
            'organTitle'  => $organTitle,
            'contracts'   => $contracts,
            'pager'       => $this->enlaces($page, $total),
            'currentPage' => $page,
            'total'       => $total,
            'searchQuery' => $q,
            'slug'        => $slug,
            'title'       => "Licitaciones y contratos de {$organTitle} | Empresas adjudicatarias" . ($page > 1 ? " · Página {$page}" : ''),
            'meta_description' => "Empresas que han ganado contratos públicos de {$organTitle}. Importes, fechas e historial de adjudicaciones, a partir de datos públicos.",
            'canonical'   => $base . ($page > 1 ? '?page=' . $page : '')
        ]);
    }

    // ── SUBVENCIONES ─────────────────────────────────────────────────────────
    public function subsidiesHub()
    {
        $page = $this->pagina();
        $q    = trim((string) ($this->request->getGet('q') ?? ''));
        $db   = \Config\Database::connect();

        $cache    = \Config\Services::cache();
        $cacheKey = 'seo_subsidies_hub_page1_v3';
        $data     = ($page === 1 && $q === '') ? $cache->get($cacheKey) : null;

        if (!$data) {
            // Se calcula desde company_subsidies (y no desde la tabla resumen
            // seo_hub_subvenciones) para poder dejar fuera a las personas físicas.
            $where  = "convocatoria IS NOT NULL AND convocatoria != '' AND " . FondosPublicos::soloJuridicas('company_cif');
            $params = [];
            if ($q !== '') {
                $where   .= ' AND convocatoria LIKE ?';
                $params[] = '%' . $q . '%';
            }

            $filas = $db->query("
                SELECT convocatoria, COUNT(*) AS total_subsidies, COUNT(DISTINCT company_cif) AS total_companies, SUM(importe) AS total_amount
                FROM company_subsidies
                WHERE $where
                GROUP BY convocatoria
                ORDER BY total_subsidies DESC
                LIMIT ? OFFSET ?
            ", array_merge($params, [self::POR_PAGINA, ($page - 1) * self::POR_PAGINA]))->getResultArray();

            $convocatorias = [];
            foreach ($filas as $conv) {
                $conv['name'] = $conv['convocatoria'];
                $conv['slug'] = FondosPublicos::slug($conv['convocatoria']);
                if ($conv['slug'] !== '') {
                    $convocatorias[] = $conv;
                }
            }

            $statsRow = $db->query("SELECT COUNT(DISTINCT convocatoria) AS total_convocatorias, COUNT(*) AS total_s, SUM(importe) AS total_a FROM company_subsidies WHERE $where", $params)->getRow();
            $maxRow   = $db->query("SELECT COUNT(*) AS c FROM company_subsidies WHERE convocatoria IS NOT NULL AND convocatoria != '' AND " . FondosPublicos::soloJuridicas('company_cif') . " GROUP BY convocatoria ORDER BY c DESC LIMIT 1")->getRow();

            $data = [
                'convocatorias'    => $convocatorias,
                'total'            => (int) ($statsRow->total_convocatorias ?? 0),
                'max_subsidies'    => $maxRow->c ?? 1,
                'global_subsidies' => $statsRow->total_s ?? 0,
                'global_amount'    => $statsRow->total_a ?? 0,
            ];

            if ($page === 1 && $q === '' && $convocatorias) {
                $cache->save($cacheKey, $data, 86400);
            }
        }

        $this->validarPagina($page, (int) $data['total'], $q);

        return view('seo/hub_subvenciones', [
            'convocatorias'    => $data['convocatorias'],
            'total_convocatorias' => $data['total'],
            'global_subsidies' => $data['global_subsidies'],
            'global_amount'    => $data['global_amount'],
            'max_subsidies'    => $data['max_subsidies'],
            'anos'             => array_keys(FondosPublicos::anosSubvenciones()),
            'pager'            => $this->enlaces($page, (int) $data['total']),
            'currentPage'      => $page,
            'searchQuery'      => $q,
            'title'            => "Subvenciones a empresas y entidades | Buscador de convocatorias",
            'meta_description' => "Convocatorias de subvenciones y ayudas públicas en España y las empresas y entidades que las han recibido. Importes y fechas, a partir de datos públicos.",
            'canonical'        => site_url('subvenciones-empresas') . ($page > 1 ? '?page=' . $page : '')
        ]);
    }

    public function subsidiesByConvocatoria($slug, $segmento = null)
    {
        $base = site_url('subvenciones-empresas/convocatoria-' . $slug);
        if ($segmento !== null) {
            return $this->redirigirSegmento($base, $segmento);
        }

        $page = $this->pagina();
        $q    = trim((string) ($this->request->getGet('q') ?? ''));

        $convName = FondosPublicos::slugsConvocatorias()[$slug] ?? null;
        if (!$convName) {
            $this->noEncontrada('Convocatoria no encontrada.');
        }

        [$subsidies, $total] = $this->listado(
            'company_subsidies', 't.convocatoria = ?', [$convName],
            't.fecha_concesion DESC', 'importe', self::NOMBRE_SUBVENCION,
            $q, $page, 'fp_total_conv_' . md5($convName)
        );

        $convTitle = mb_convert_case($convName, MB_CASE_TITLE, "UTF-8");

        return view('seo/listado_convocatoria', [
            'convocatoriaName' => $convName,
            'convTitle'        => $convTitle,
            'subsidies'        => $subsidies,
            'pager'            => $this->enlaces($page, $total),
            'currentPage'      => $page,
            'total'            => $total,
            'searchQuery'      => $q,
            'slug'             => $slug,
            'title'            => "Entidades beneficiarias: {$convTitle}" . ($page > 1 ? " · Página {$page}" : ''),
            'meta_description' => "Empresas y entidades que han recibido la subvención {$convTitle}. Importes y fechas de concesión, a partir de datos públicos.",
            'canonical'        => $base . ($page > 1 ? '?page=' . $page : '')
        ]);
    }

    // ── RANKINGS ─────────────────────────────────────────────────────────────
    private function ranking(string $tabla, string $colRegistros, string $cacheKey, int $page, string $q): array
    {
        $db    = \Config\Database::connect();
        $cache = \Config\Services::cache();
        $datos = ($page === 1 && $q === '') ? $cache->get($cacheKey) : null;

        if (!$datos) {
            $base   = FondosPublicos::soloJuridicas('company_cif');
            $where  = $base;
            $params = [];
            if ($q !== '') {
                $where   .= ' AND (company_cif LIKE ? OR company_name LIKE ?)';
                $params[] = '%' . $q . '%';
                $params[] = '%' . $q . '%';
            }

            $rows = $db->query("SELECT * FROM {$tabla} WHERE $where ORDER BY total_amount DESC LIMIT ? OFFSET ?",
                array_merge($params, [self::POR_PAGINA, ($page - 1) * self::POR_PAGINA]))->getResultArray();
            $total = (int) ($db->query("SELECT COUNT(*) as total FROM {$tabla} WHERE $where", $params)->getRow()->total ?? 0);
            $stats = $db->query("SELECT SUM(total_amount) as total_a, SUM({$colRegistros}) as total_n FROM {$tabla} WHERE $base")->getRow();

            $datos = [
                'rows'    => $rows,
                'total'   => $total,
                'total_a' => $stats->total_a ?? 0,
                'total_n' => $stats->total_n ?? 0,
            ];
            if ($page === 1 && $q === '' && $rows) {
                $cache->save($cacheKey, $datos, 86400);
            }
        }

        $this->validarPagina($page, (int) $datos['total'], $q);

        return $datos;
    }

    public function topContractors()
    {
        $page = $this->pagina();
        $q    = trim((string) ($this->request->getGet('q') ?? ''));
        $d    = $this->ranking('seo_ranking_contratos', 'total_contracts', 'seo_top_contractors_p1_v2', $page, $q);

        return view('seo/ranking_contratistas', [
            'companies'       => $d['rows'],
            'total'           => $d['total'],
            'global_amount'   => $d['total_a'],
            'global_contracts'=> $d['total_n'],
            'pager'           => $this->enlaces($page, (int) $d['total']),
            'currentPage'     => $page,
            'searchQuery'     => $q,
            'title'           => 'Mayores empresas contratistas del sector público | Ranking',
            'meta_description'=> 'Ranking de las empresas que más contratos públicos acumulan en España: volumen adjudicado y número de contratos, a partir de datos públicos.',
            'canonical'       => site_url('mayores-empresas-contratistas-del-estado') . ($page > 1 ? '?page=' . $page : ''),
        ]);
    }

    public function topSubsidyRecipients()
    {
        $page = $this->pagina();
        $q    = trim((string) ($this->request->getGet('q') ?? ''));
        $d    = $this->ranking('seo_ranking_subvenciones', 'total_subsidies', 'seo_top_subsidies_p1_v2', $page, $q);

        return view('seo/ranking_subvencionadas', [
            'companies'        => $d['rows'],
            'total'            => $d['total'],
            'global_amount'    => $d['total_a'],
            'global_subsidies' => $d['total_n'],
            'pager'            => $this->enlaces($page, (int) $d['total']),
            'currentPage'      => $page,
            'searchQuery'      => $q,
            'title'            => 'Empresas y entidades más subvencionadas de España | Ranking',
            'meta_description' => 'Qué empresas y entidades han recibido más subvenciones públicas en España. Ranking por importe total concedido, a partir de datos públicos.',
            'canonical'        => site_url('empresas-mas-subvencionadas-espana') . ($page > 1 ? '?page=' . $page : ''),
        ]);
    }

    // ── POR AÑO ──────────────────────────────────────────────────────────────
    public function contractsByYear($year)
    {
        $year = (int) $year;
        $page = $this->pagina();
        $q    = trim((string) ($this->request->getGet('q') ?? ''));

        // Solo años con datos: antes cualquier año (ano-1990) respondía 200 vacío.
        if (!isset(FondosPublicos::anosContratos()[$year])) {
            $this->noEncontrada();
        }

        [$contracts, $total, $total_amount] = $this->listado(
            'company_contracts', FondosPublicos::rangoAno('t.fecha_adjudicacion', $year), [],
            't.importe_adjudicacion DESC', 'importe_adjudicacion', self::NOMBRE_CONTRATO,
            $q, $page, 'fp_total_contratos_ano_' . $year
        );

        return view('seo/listado_ano_contratos', [
            'year'         => $year,
            'contracts'    => $contracts,
            'total'        => $total,
            'total_amount' => $total_amount,
            'pager'        => $this->enlaces($page, $total),
            'currentPage'  => $page,
            'searchQuery'  => $q,
            'title'        => "Contratos públicos adjudicados en {$year} | Licitaciones" . ($page > 1 ? " · Página {$page}" : ''),
            'meta_description' => "Contratos públicos adjudicados en {$year}: empresas adjudicatarias, importes y órganos de contratación, a partir de datos públicos.",
            'canonical'    => site_url("licitaciones-del-estado/ano-{$year}") . ($page > 1 ? '?page=' . $page : ''),
        ]);
    }

    public function subsidiesByYear($year)
    {
        $year = (int) $year;
        $page = $this->pagina();
        $q    = trim((string) ($this->request->getGet('q') ?? ''));

        if (!isset(FondosPublicos::anosSubvenciones()[$year])) {
            $this->noEncontrada();
        }

        [$subsidies, $total, $total_amount] = $this->listado(
            'company_subsidies', FondosPublicos::rangoAno('t.fecha_concesion', $year), [],
            't.importe DESC', 'importe', self::NOMBRE_SUBVENCION,
            $q, $page, 'fp_total_subvenciones_ano_' . $year
        );

        return view('seo/listado_ano_subvenciones', [
            'year'         => $year,
            'subsidies'    => $subsidies,
            'total'        => $total,
            'total_amount' => $total_amount,
            'pager'        => $this->enlaces($page, $total),
            'currentPage'  => $page,
            'searchQuery'  => $q,
            'title'        => "Subvenciones concedidas en {$year} a empresas y entidades" . ($page > 1 ? " · Página {$page}" : ''),
            'meta_description' => "Subvenciones concedidas en {$year} a empresas y entidades: beneficiarios, importes y convocatorias, a partir de datos públicos.",
            'canonical'    => site_url("subvenciones-empresas/ano-{$year}") . ($page > 1 ? '?page=' . $page : ''),
        ]);
    }
}
