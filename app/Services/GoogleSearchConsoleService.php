<?php

namespace App\Services;

use Google\Client;
use Google\Service\SearchConsole;
use Google\Service\Exception as GoogleServiceException;

class GoogleSearchConsoleService
{
    protected string $credentialsPath;
    protected string $siteUrl;

    public function __construct()
    {
        // Ruta al archivo JSON de credenciales
        $this->credentialsPath = WRITEPATH . 'credentials/google-service-account.json';
        
        // El sitio exactamente como está configurado en Search Console (con https:// y / al final si es propiedad de prefijo de URL)
        // O si es propiedad de dominio "sc-domain:apiempresas.es"
        $this->siteUrl = 'https://apiempresas.es/'; 
    }

    /**
     * Inicializa el cliente de Google
     */
    protected function getClient(): Client
    {
        if (!file_exists($this->credentialsPath)) {
            throw new \Exception("El archivo de credenciales de Google Service Account no se encontró en: {$this->credentialsPath}");
        }

        $client = new Client();
        $client->setAuthConfig($this->credentialsPath);
        $client->addScope(SearchConsole::WEBMASTERS_READONLY);
        $client->setAccessType('offline');
        
        return $client;
    }

    /**
     * Obtiene los KPIs principales (Clics, Impresiones, CTR, Posición) de un periodo dado.
     * 
     * @param string $startDate YYYY-MM-DD
     * @param string $endDate YYYY-MM-DD
     * @return array
     */
    public function getPerformanceMetrics(string $startDate, string $endDate): array
    {
        // Comprobar caché primero
        $cacheKey = "gsc_metrics_{$startDate}_{$endDate}";
        $cache = \Config\Services::cache();
        
        if ($cachedData = $cache->get($cacheKey)) {
            return $cachedData;
        }

        $client = $this->getClient();
        $service = new SearchConsole($client);

        $request = new \Google\Service\SearchConsole\SearchAnalyticsQueryRequest();
        $request->setStartDate($startDate);
        $request->setEndDate($endDate);
        // Sin dimensiones agrupamos por todo el sitio
        // $request->setDimensions(['date']); 
        
        try {
            $response = $service->searchanalytics->query($this->siteUrl, $request);
            $rows = $response->getRows();
            
            $metrics = [
                'clicks'      => 0,
                'impressions' => 0,
                'ctr'         => 0,
                'position'    => 0
            ];

            if (!empty($rows)) {
                $row = $rows[0]; // Como no agrupamos por dimensiones, solo hay 1 fila con los totales
                $metrics['clicks']      = $row->getClicks();
                $metrics['impressions'] = $row->getImpressions();
                $metrics['ctr']         = round($row->getCtr() * 100, 2); // CTR viene como 0.x
                $metrics['position']    = round($row->getPosition(), 2);
            }

            // Guardar en caché por 6 horas (21600 segundos)
            $cache->save($cacheKey, $metrics, 21600);
            
            return $metrics;

        } catch (\Google\Service\Exception $e) {
            $errorMsg = json_decode($e->getMessage(), true) ?? $e->getMessage();
            throw new \Exception("Error API Google: " . ($errorMsg['error']['message'] ?? json_encode($errorMsg)));
        } catch (\Exception $e) {
            throw new \Exception("Error al consultar Search Console: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene los datos top (Búsquedas, Páginas) de un periodo dado.
     */
    public function getTopData(string $startDate, string $endDate, string $dimension, int $limit = 10): array
    {
        $cacheKey = "gsc_top_{$dimension}_{$startDate}_{$endDate}_{$limit}";
        $cache = \Config\Services::cache();
        
        if ($cachedData = $cache->get($cacheKey)) {
            return $cachedData;
        }

        $client = $this->getClient();
        $service = new SearchConsole($client);

        $request = new \Google\Service\SearchConsole\SearchAnalyticsQueryRequest();
        $request->setStartDate($startDate);
        $request->setEndDate($endDate);
        $request->setDimensions([$dimension]);
        $request->setRowLimit($limit);
        
        try {
            $response = $service->searchanalytics->query($this->siteUrl, $request);
            $rows = $response->getRows();
            
            $results = [];
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $keys = $row->getKeys();
                    $key = !empty($keys) ? $keys[0] : 'Desconocido';
                    
                    $results[] = [
                        'key'         => $key,
                        'clicks'      => $row->getClicks() ?? 0,
                        'impressions' => $row->getImpressions() ?? 0,
                        'ctr'         => round(($row->getCtr() ?? 0) * 100, 2),
                        'position'    => round($row->getPosition() ?? 0, 2)
                    ];
                }
            }

            $cache->save($cacheKey, $results, 21600); // 6 horas
            
            return $results;

        } catch (\Google\Service\Exception $e) {
            $errorMsg = json_decode($e->getMessage(), true) ?? $e->getMessage();
            throw new \Exception("Error API Google: " . ($errorMsg['error']['message'] ?? json_encode($errorMsg)));
        } catch (\Exception $e) {
            throw new \Exception("Error al consultar Search Console dimensiones: " . $e->getMessage());
        }
    }
    
    /**
     * Múltiple Periodo: Obtener Actual y Anterior
     */
    public function getKpisWithComparison(): array
    {
        // Últimos 28 días disponibles (GSC suele tener 2-3 días de retraso)
        $endDate = date('Y-m-d', strtotime('-3 days'));
        $startDate = date('Y-m-d', strtotime('-30 days'));
        
        // Periodo anterior para porcentaje (28 días previos)
        $prevEndDate = date('Y-m-d', strtotime('-31 days'));
        $prevStartDate = date('Y-m-d', strtotime('-58 days'));

        try {
            $currentData = $this->getPerformanceMetrics($startDate, $endDate);
            $previousData = $this->getPerformanceMetrics($prevStartDate, $prevEndDate);
            
            $topQueries = $this->getTopData($startDate, $endDate, 'query', 15);
            $topPages = $this->getTopData($startDate, $endDate, 'page', 10);
            
            // Nuevas dimensiones
            $topCountries = $this->getTopData($startDate, $endDate, 'country', 10);
            $topDevices   = $this->getTopData($startDate, $endDate, 'device', 3); // Desktop, Mobile, Tablet
            $dailyMetrics = $this->getTopData($startDate, $endDate, 'date', 35); // 30 días aprox + colchón
            
            // Ordenamos dailyMetrics cronológicamente por la key (que es la fecha YYYY-MM-DD)
            usort($dailyMetrics, function($a, $b) {
                return strcmp($a['key'], $b['key']);
            });

            return [
                'status'   => 'success',
                'current'  => $currentData,
                'previous' => $previousData,
                'queries'  => $topQueries,
                'pages'    => $topPages,
                'countries'=> $topCountries,
                'devices'  => $topDevices,
                'daily'    => $dailyMetrics,
                'period'   => [
                    'start' => $startDate,
                    'end'   => $endDate
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message'=> $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene la lista de sitemaps registrados en la propiedad.
     */
    public function getSitemaps(): array
    {
        $client = $this->getClient();
        $service = new SearchConsole($client);

        try {
            $response = $service->sitemaps->listSitemaps($this->siteUrl);
            $sitemaps = $response->getSitemap();
            
            $results = [];
            if ($sitemaps) {
                foreach ($sitemaps as $sitemap) {
                    $contents = $sitemap->getContents();
                    $indexed = 0;
                    $submitted = 0;
                    
                    if ($contents) {
                        foreach ($contents as $content) {
                            $indexed += $content->getIndexed();
                            $submitted += $content->getSubmitted();
                        }
                    }

                    $results[] = [
                        'path'            => $sitemap->getPath(),
                        'lastDownloaded'  => $sitemap->getLastDownloaded(),
                        'lastSubmitted'   => $sitemap->getLastSubmitted(),
                        'errors'          => $sitemap->getErrors(),
                        'warnings'        => $sitemap->getWarnings(),
                        'type'            => $sitemap->getType(),
                        'submitted'       => $submitted,
                        'indexed'         => $indexed,
                        'indexRate'       => ($submitted > 0) ? round(($indexed / $submitted) * 100, 1) : 0
                    ];
                }
            }
            
            return [
                'status'   => 'success',
                'sitemaps' => $results
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Inspecciona una URL específica para ver su estado de indexación.
     */
    public function inspectUrl(string $url): array
    {
        $client = $this->getClient();
        $service = new SearchConsole($client);

        $request = new \Google\Service\SearchConsole\InspectUrlIndexRequest();
        $request->setSiteUrl($this->siteUrl);
        $request->setInspectionUrl($url);
        $request->setLanguageCode('es');

        try {
            $inspection = $service->urlInspection_index->inspect($request);
            $result = $inspection->getInspectionResult();
            
            $indexStatus = $result->getIndexStatusResult();
            $mobileResult = $result->getMobileUsabilityResult();
            
            return [
                'status' => 'success',
                'result' => [
                    'verdict'   => $indexStatus->getVerdict(),
                    'coverage'  => $indexStatus->getCoverageState(),
                    'robots'    => $indexStatus->getRobotsTxtState(),
                    'indexing'  => $indexStatus->getIndexingState(),
                    'lastCrawl' => $indexStatus->getLastCrawlTime(),
                    'crawledAs' => $indexStatus->getCrawledAs(),
                    'mobile'    => $mobileResult ? $mobileResult->getVerdict() : 'N/A'
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
