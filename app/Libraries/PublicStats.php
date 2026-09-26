<?php

namespace App\Libraries;

/**
 * Cifras reales de la base de datos para las páginas públicas (home y
 * /api-empresas): empresas, actos del BORME y fecha del último BORME procesado.
 *
 * Los COUNT(*) sobre tablas de millones de filas tardan unos segundos, así que
 * se calculan como mucho una vez cada 12 horas y se guardan en caché. Si algo
 * falla devuelve [] y la página no muestra las cifras.
 */
class PublicStats
{
    public const CACHE_KEY = 'home_stats_v1';

    public static function get(): array
    {
        $cache = \Config\Services::cache();
        $stats = $cache->get(self::CACHE_KEY);
        if (is_array($stats)) {
            return $stats;
        }

        try {
            $db = \Config\Database::connect();

            $companies = (int) $db->query('SELECT COUNT(*) AS n FROM companies')->getRow()->n;
            $acts      = (int) $db->query('SELECT COUNT(*) AS n FROM borme_posts')->getRow()->n;
            $lastBorme = $db->query('SELECT MAX(borme_date) AS d FROM borme_posts')->getRow()->d;

            $stats = [
                'companies'  => $companies,
                'acts'       => $acts,
                'last_borme' => $lastBorme ? date('Y-m-d', strtotime((string) $lastBorme)) : null,
            ];
            $cache->save(self::CACHE_KEY, $stats, 12 * 3600);
        } catch (\Throwable $e) {
            log_message('error', 'PublicStats: ' . $e->getMessage());
            $stats = [];
            $cache->save(self::CACHE_KEY, $stats, 600); // reintentar en 10 minutos
        }

        return $stats;
    }

    /**
     * "Más de 4,7 millones" / "Más de 512.000" redondeando hacia abajo, para que
     * el "más de" sea siempre cierto.
     */
    public static function masDe(int $n): string
    {
        if ($n >= 1000000) {
            $m = floor($n / 100000) / 10;
            return 'Más de ' . number_format($m, $m == floor($m) ? 0 : 1, ',', '.') . ' millones';
        }
        if ($n >= 1000) {
            return 'Más de ' . number_format(floor($n / 1000) * 1000, 0, ',', '.');
        }
        return (string) $n;
    }

    /** "+4,7 M" para cifras destacadas. */
    public static function corto(int $n): string
    {
        if ($n >= 1000000) {
            $m = floor($n / 100000) / 10;
            return '+' . number_format($m, $m == floor($m) ? 0 : 1, ',', '.') . ' M';
        }
        if ($n >= 1000) {
            return '+' . number_format(floor($n / 1000), 0, ',', '.') . ' mil';
        }
        return (string) $n;
    }
}
