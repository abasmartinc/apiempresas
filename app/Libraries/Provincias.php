<?php

namespace App\Libraries;

/**
 * Las 52 provincias y las formas en que aparecen escritas en companies.registro_mercantil.
 *
 * Por qué existe (02-10-2026): el panel del Radar filtraba con strtoupper() del nombre del
 * desplegable ("VALENCIA", "ALICANTE", "BALEARES") y en la tabla hay "VALENCIA/VALÈNCIA",
 * "ALICANTE/ALACANT", "ILLES BALEARS", "BIZKAIA"…: varias provincias no devolvían nada.
 * La comparación en MySQL no distingue mayúsculas ni tildes, así que basta una variante
 * por forma de escribirla.
 */
class Provincias
{
    /**
     * nombre a mostrar => variantes en la base de datos. Los nombres son los mismos 52 que
     * usan los scripts (scrapers_v2/comun/provincias.py), para que coincidan con lo que
     * queda guardado tras normalizar.
     */
    private const MAPA = [
        'A Coruña'               => ['A Coruña', 'La Coruña', 'Coruña, A', 'Coruña'],
        'Álava'                  => ['Araba/Álava', 'Álava', 'Álava-Araba', 'Araba'],
        'Albacete'               => ['Albacete'],
        'Alicante'               => ['Alicante/Alacant', 'Alicante', 'Alacant'],
        'Almería'                => ['Almería'],
        'Asturias'               => ['Asturias'],
        'Ávila'                  => ['Ávila'],
        'Badajoz'                => ['Badajoz'],
        'Barcelona'              => ['Barcelona'],
        'Vizcaya'                => ['Vizcaya', 'Bizkaia', 'Vizcaya-Bizkaia'],
        'Burgos'                 => ['Burgos'],
        'Cáceres'                => ['Cáceres'],
        'Cádiz'                  => ['Cádiz'],
        'Cantabria'              => ['Cantabria'],
        'Castellón'              => ['Castellón/Castelló', 'Castellón', 'Castelló', 'Castellón/Castello'],
        'Ceuta'                  => ['Ceuta'],
        'Ciudad Real'            => ['Ciudad Real'],
        'Córdoba'                => ['Córdoba'],
        'Cuenca'                 => ['Cuenca'],
        'Guipúzcoa'              => ['Guipúzcoa', 'Gipuzkoa', 'Guipúzcoa-Gipuzkoa'],
        'Girona'                 => ['Girona', 'Gerona'],
        'Granada'                => ['Granada'],
        'Guadalajara'            => ['Guadalajara'],
        'Huelva'                 => ['Huelva'],
        'Huesca'                 => ['Huesca'],
        'Islas Baleares'         => ['Islas Baleares', 'Illes Balears', 'Baleares', 'Balears, Illes'],
        'Jaén'                   => ['Jaén'],
        'La Rioja'               => ['La Rioja', 'Rioja, La', 'Rioja'],
        'Las Palmas'             => ['Las Palmas', 'Palmas, Las'],
        'León'                   => ['León'],
        'Lleida'                 => ['Lleida', 'Lérida'],
        'Lugo'                   => ['Lugo'],
        'Madrid'                 => ['Madrid'],
        'Málaga'                 => ['Málaga'],
        'Melilla'                => ['Melilla'],
        'Murcia'                 => ['Murcia'],
        'Navarra'                => ['Navarra'],
        'Ourense'                => ['Ourense', 'Orense'],
        'Palencia'               => ['Palencia'],
        'Pontevedra'             => ['Pontevedra'],
        'Salamanca'              => ['Salamanca'],
        'Santa Cruz de Tenerife' => ['Santa Cruz de Tenerife', 'Sta. Cruz de Tenerife', 'Sta Cruz Tenerife', 'Tenerife'],
        'Segovia'                => ['Segovia'],
        'Sevilla'                => ['Sevilla'],
        'Soria'                  => ['Soria'],
        'Tarragona'              => ['Tarragona'],
        'Teruel'                 => ['Teruel'],
        'Toledo'                 => ['Toledo'],
        'Valencia'               => ['Valencia/València', 'Valencia', 'València'],
        'Valladolid'             => ['Valladolid'],
        'Zamora'                 => ['Zamora'],
        'Zaragoza'               => ['Zaragoza'],
    ];

    /** Nombres para un desplegable, en orden alfabético */
    public static function nombres(): array
    {
        return array_keys(self::MAPA);
    }

    private static function clave(string $texto): string
    {
        helper(['url', 'text']);
        $t = strtr(mb_strtolower(trim($texto), 'UTF-8'), [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'è' => 'e', 'à' => 'a', 'ò' => 'o', 'ñ' => 'n',
        ]);

        return trim((string) preg_replace('/[^a-z0-9]+/', '-', $t), '-');
    }

    /** Nombre canónico a partir de un nombre, una variante o un slug ("illes-balears") */
    public static function canonica(?string $texto): ?string
    {
        $clave = self::clave((string) $texto);
        if ($clave === '') {
            return null;
        }
        foreach (self::MAPA as $nombre => $variantes) {
            if (self::clave($nombre) === $clave) {
                return $nombre;
            }
            foreach ($variantes as $v) {
                if (self::clave($v) === $clave) {
                    return $nombre;
                }
            }
        }

        return null;
    }

    /**
     * Valores de registro_mercantil que corresponden a la provincia pedida. Si no se
     * reconoce, devuelve el texto tal cual (en mayúsculas, como hacía el panel).
     */
    public static function variantes(?string $texto): array
    {
        $nombre = self::canonica($texto);
        if ($nombre === null) {
            return [mb_strtoupper(str_replace('-', ' ', trim((string) $texto)), 'UTF-8')];
        }

        return array_values(array_unique(array_merge([$nombre], self::MAPA[$nombre])));
    }
}
