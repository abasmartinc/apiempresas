<?php

namespace App\Libraries;

/**
 * Reglas comunes de las páginas y descargas de subvenciones y licitaciones.
 *
 * Por qué existe (02-10-2026): las tablas company_subsidies y company_contracts traen
 * también beneficiarios que son PERSONAS FÍSICAS (becas, ayudas a particulares,
 * autónomos). Se estaban publicando con nombre y DNI parcial, indexables en Google y
 * dentro del CSV de pago. Que el dato sea público en origen no permite republicarlo ni
 * venderlo: aquí solo se muestran y se venden registros de personas jurídicas y
 * entidades (identificador con formato de CIF).
 *
 * La misma condición se usa en páginas, rankings, sitemaps, recuento del precio y CSV,
 * para que lo que se cobra coincida con lo que se entrega.
 */
class FondosPublicos
{
    /** Letras con las que empieza el NIF de una persona jurídica o entidad */
    private const PATRON_CIF = '^[ABCDEFGHJNPQRSUVW][0-9]{7}[0-9A-J]$';

    private const TTL = 86400;

    /** Condición SQL: la columna contiene un CIF de persona jurídica o entidad */
    public static function soloJuridicas(string $columna): string
    {
        // Con "= 1" para que el constructor de consultas de CodeIgniter lo reconozca como
        // condición completa (sin operador conocido le añadiría "IS NULL").
        return '(' . $columna . " REGEXP '" . self::PATRON_CIF . "') = 1";
    }

    public static function esJuridica(?string $cif): bool
    {
        return (bool) preg_match('/' . self::PATRON_CIF . '/', strtoupper(trim((string) $cif)));
    }

    /**
     * Condición SQL por año como rango de fechas (YEAR(col) = ? no puede usar índice).
     * Con un año no válido devuelve una condición que no cumple ninguna fila.
     */
    public static function rangoAno(string $columna, $ano): string
    {
        $ano = (int) $ano;
        if (!self::anoPlausible($ano)) {
            return '1=0';
        }

        return "{$columna} >= '{$ano}-01-01' AND {$columna} < '" . ($ano + 1) . "-01-01'";
    }

    public static function anoPlausible(int $ano): bool
    {
        return $ano >= 1990 && $ano <= (int) date('Y') + 1;
    }

    /** slug => nombre de convocatoria (solo las que tienen alguna entidad) */
    public static function slugsConvocatorias(): array
    {
        return self::slugs('fp_slugs_convocatorias_v1', 'company_subsidies', 'convocatoria');
    }

    /** slug => nombre de órgano de contratación */
    public static function slugsOrganos(): array
    {
        return self::slugs('fp_slugs_organos_v1', 'company_contracts', 'organo_contratacion');
    }

    /** Slug de una convocatoria u órgano, igual que en las URL */
    public static function slug(string $nombre): string
    {
        helper(['url', 'text']);

        return url_title($nombre, '-', true);
    }

    private static function slugs(string $clave, string $tabla, string $columna): array
    {
        $cache = \Config\Services::cache();
        $mapa  = $cache->get($clave);
        if (is_array($mapa) && $mapa) {
            return $mapa;
        }

        $db    = \Config\Database::connect();
        $filas = $db->query("
            SELECT {$columna} AS nombre, COUNT(*) AS n
            FROM {$tabla}
            WHERE {$columna} IS NOT NULL AND {$columna} != '' AND " . self::soloJuridicas('company_cif') . "
            GROUP BY {$columna}
            ORDER BY n DESC
        ")->getResultArray();

        $mapa = [];
        foreach ($filas as $f) {
            $slug = self::slug((string) $f['nombre']);
            // Si dos nombres dan el mismo slug se queda el de más registros (van ordenados);
            // antes ganaba el último, al azar.
            if ($slug !== '' && !isset($mapa[$slug])) {
                $mapa[$slug] = $f['nombre'];
            }
        }
        if ($mapa) {
            $cache->save($clave, $mapa, self::TTL);
        }

        return $mapa;
    }

    /** Años con subvenciones a entidades: [año => registros], del más reciente al más antiguo */
    public static function anosSubvenciones(): array
    {
        return self::anos('fp_anos_subvenciones_v1', 'company_subsidies', 'fecha_concesion');
    }

    /** Años con contratos: [año => registros] */
    public static function anosContratos(): array
    {
        return self::anos('fp_anos_contratos_v1', 'company_contracts', 'fecha_adjudicacion');
    }

    private static function anos(string $clave, string $tabla, string $columna): array
    {
        $cache = \Config\Services::cache();
        $anos  = $cache->get($clave);
        if (is_array($anos) && $anos) {
            return $anos;
        }

        $db    = \Config\Database::connect();
        $filas = $db->query("
            SELECT YEAR({$columna}) AS ano, COUNT(*) AS n
            FROM {$tabla}
            WHERE {$columna} IS NOT NULL AND " . self::soloJuridicas('company_cif') . "
            GROUP BY YEAR({$columna})
            ORDER BY ano DESC
        ")->getResultArray();

        $anos = [];
        foreach ($filas as $f) {
            $ano = (int) $f['ano'];
            if (self::anoPlausible($ano) && (int) $f['n'] > 0) {
                $anos[$ano] = (int) $f['n'];
            }
        }
        if ($anos) {
            $cache->save($clave, $anos, self::TTL);
        }

        return $anos;
    }
}
