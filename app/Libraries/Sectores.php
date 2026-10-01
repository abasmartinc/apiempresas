<?php

namespace App\Libraries;

/**
 * Nombre y URL de cada sector CNAE, iguales en todas partes.
 *
 * Antes cada sitio sacaba el nombre de un lado: el índice de la tabla CNAE, la página
 * de sector del nombre más repetido entre las empresas, las píldoras de "Principales
 * sectores" del nombre de la empresa y el sitemap solo de la CNAE-2009. El slug salía
 * distinto y 7 de cada 40 enlaces del índice pasaban por un 301 (01-10-2026).
 *
 * Regla: nombre de la CNAE-2009 y, si el código solo existe en la CNAE-2025, su nombre
 * de 2025 (la misma que ya usaba el índice).
 */
class Sectores
{
    private const CLAVE_CACHE = 'sectores_nombres_v1';

    private static ?array $nombres = null;

    /** código => nombre */
    public static function nombres(): array
    {
        if (self::$nombres !== null) {
            return self::$nombres;
        }

        $cache = \Config\Services::cache();
        $mapa = $cache->get(self::CLAVE_CACHE);
        if (!is_array($mapa)) {
            $db = \Config\Database::connect();
            $mapa = [];
            foreach ($db->table('cnae_2009_2025')->select('cnae_2009 as cnae, label_2009 as label')->get()->getResultArray() as $row) {
                $code = (string) ($row['cnae'] ?? '');
                if ($code !== '' && !empty($row['label'])) {
                    $mapa[$code] = $row['label'];
                }
            }
            foreach ($db->table('cnae_2009_2025')->select('cnae_2025 as cnae, label_2025 as label')->get()->getResultArray() as $row) {
                $code = (string) ($row['cnae'] ?? '');
                if ($code !== '' && !empty($row['label']) && !isset($mapa[$code])) {
                    $mapa[$code] = $row['label'];
                }
            }
            $cache->save(self::CLAVE_CACHE, $mapa, 1296000); // 15 días
        }

        return self::$nombres = $mapa;
    }

    /** Nombre del sector, o null si el código no está en ninguna de las dos CNAE */
    public static function nombre(string $codigo): ?string
    {
        return self::nombres()[$codigo] ?? null;
    }

    public static function slug(string $codigo, ?string $respaldo = null): string
    {
        helper('text');

        return url_title(self::nombre($codigo) ?? ($respaldo ?: "CNAE {$codigo}"), '-', true);
    }

    /** /listado-de-empresas/sector-{código}/{slug} */
    public static function url(string $codigo, ?string $respaldo = null): string
    {
        return site_url('listado-de-empresas/sector-' . $codigo . '/' . self::slug($codigo, $respaldo));
    }
}
