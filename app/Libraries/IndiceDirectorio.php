<?php

namespace App\Libraries;

/**
 * Datos de /listado-de-empresas: empresas por provincia y por sector, y las últimas altas.
 *
 * Antes vivían dentro de Directory::index. Los recuentos son dos GROUP BY sobre las 4,2 M
 * de empresas y la primera visita tras caducar la caché esperaba 8,8 s (01-10-2026).
 * Ahora los recalcula cada noche `php spark directorio:calentar` (sobrescribe la caché,
 * así nadie la encuentra vacía) y el mapa reutiliza las provincias para su estado inicial.
 */
class IndiceDirectorio
{
    /** v6 (01-10-2026): nombres de la CNAE-2025 y sin códigos que no existen */
    public const CLAVE = 'directory_index_data_v6';

    public const CLAVE_ULTIMAS = 'directory_latest_v1';

    /**
     * Caché de 2 días: el comando la renueva cada noche; si un día no corre, se recalcula
     * en la visita (como antes) en vez de enseñar datos de dos semanas.
     */
    private const TTL = 172800;

    private const NOMBRES_NO_VALIDOS = [
        '', ' ', '  ', '-', '.', '..', '...', '8', 'N/A', 'NULL', 'UNDEFINED',
        '00 DESCONOCIDA', 'desconocido', 'desconocida', 'no disponible', 'n/a', 'unknown', 'sin especificar',
        'ÍNDICE ALFABÉTICO DE SOCIEDADES', 'No Detectado',
    ];

    /** ['provinces' => [[name, total]…] (de más a menos), 'cnaes' => [[cnae, total, name]…]] */
    public static function datos(bool $recalcular = false): array
    {
        $cache = \Config\Services::cache();
        $data  = $recalcular ? null : $cache->get(self::CLAVE);
        if (is_array($data) && isset($data['provinces'], $data['cnaes'])) {
            return $data;
        }

        $db = \Config\Database::connect();

        $provinces = $db->table('companies')
            ->select('registro_mercantil as name, COUNT(id) as total')
            ->where('registro_mercantil IS NOT NULL')
            ->where('registro_mercantil >=', 'A')
            ->whereNotIn('registro_mercantil', self::NOMBRES_NO_VALIDOS)
            ->groupBy('registro_mercantil')
            ->get()
            ->getResultArray();
        usort($provinces, static fn ($a, $b) => $b['total'] <=> $a['total']);

        $cnaes = $db->table('companies')
            ->select('cnae_code as cnae, COUNT(id) as total')
            ->where('cnae_code IS NOT NULL')
            ->where('cnae_code >=', '0100')
            ->groupBy('cnae_code')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();

        // Nombres de sector: CNAE-2009 y, si el código es de la CNAE-2025 (las altas
        // nuevas), su nombre de 2025 (Sectores). Solo se listan, y se cuentan en
        // "Sectores CNAE", los códigos que existen en alguna de las dos clasificaciones:
        // los demás eran códigos sueltos y erróneos que inflaban el KPI hasta 1.222.
        $nombres = Sectores::nombres();
        $cnaes   = array_values(array_filter($cnaes, static fn ($c) => isset($nombres[(string) $c['cnae']])));
        foreach ($cnaes as &$cnae) {
            $cnae['name'] = $nombres[(string) $cnae['cnae']];
        }
        unset($cnae);

        $data = ['provinces' => $provinces, 'cnaes' => $cnaes];
        $cache->save(self::CLAVE, $data, self::TTL);

        return $data;
    }

    /** Lo que haya en caché, sin calcular nunca (para páginas que no deben esperar 8 s) */
    public static function enCache(): ?array
    {
        $data = \Config\Services::cache()->get(self::CLAVE);

        return is_array($data) && isset($data['provinces']) ? $data : null;
    }

    /** "Últimas empresas registradas": caché propia de 1 hora */
    public static function ultimas(bool $recalcular = false): array
    {
        $cache  = \Config\Services::cache();
        $latest = $recalcular ? null : $cache->get(self::CLAVE_ULTIMAS);
        if (is_array($latest)) {
            return $latest;
        }

        $latest = \Config\Database::connect()->table('companies')
            ->select('id, cif, company_name as name, fecha_constitucion as founded, cnae_label, registro_mercantil as province')
            ->where('fecha_constitucion IS NOT NULL')
            ->where('fecha_constitucion <=', date('Y-m-d'))
            ->orderBy('fecha_constitucion', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();
        $cache->save(self::CLAVE_ULTIMAS, $latest, 3600);

        return $latest;
    }
}
