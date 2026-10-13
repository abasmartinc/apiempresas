<?php

declare(strict_types=1);

namespace ApiEmpresas\ValidarCif;

/**
 * validar-cif: valida CIF, DNI y NIE españoles (formato y dígito de control).
 * Sin dependencias. https://apiempresas.es/validar-cif-php-python
 *
 * Ojo: un CIF bien formado no garantiza que la empresa exista. Para saber si existe,
 * su razón social y si sigue activa: https://apiempresas.es (API de empresas).
 */
final class Validador
{
    private const LETRAS_DNI = 'TRWAGMYFPDXBNJZSQVHLCKE';
    private const LETRAS_CONTROL_CIF = 'JABCDEFGHI';

    /** Forma jurídica de cada letra inicial del CIF. */
    public const TIPOS_ENTIDAD = [
        'A' => 'Sociedad anónima',
        'B' => 'Sociedad de responsabilidad limitada',
        'C' => 'Sociedad colectiva',
        'D' => 'Sociedad comanditaria',
        'E' => 'Comunidad de bienes, herencia yacente y otras entidades sin personalidad jurídica',
        'F' => 'Sociedad cooperativa',
        'G' => 'Asociación',
        'H' => 'Comunidad de propietarios en régimen de propiedad horizontal',
        'J' => 'Sociedad civil, con o sin personalidad jurídica',
        'N' => 'Entidad extranjera',
        'P' => 'Corporación local',
        'Q' => 'Organismo público',
        'R' => 'Congregación o institución religiosa',
        'S' => 'Órgano de la Administración del Estado y de las comunidades autónomas',
        'U' => 'Unión temporal de empresas',
        'V' => 'Otros tipos no definidos',
        'W' => 'Establecimiento permanente de entidad no residente en España',
    ];

    /** Quita espacios, guiones, puntos y barras, y pasa a mayúsculas. */
    public static function limpiar(?string $valor): string
    {
        return strtoupper((string) preg_replace('/[\s.\-\/]/', '', (string) $valor));
    }

    /** true si el CIF (empresa) está bien formado y el carácter de control cuadra. */
    public static function cif(?string $valor): bool
    {
        if (!preg_match('/^([ABCDEFGHJNPQRSUVW])(\d{7})([0-9A-J])$/', self::limpiar($valor), $m)) {
            return false;
        }
        [, $letra, $numeros, $control] = $m;

        $suma = 0;
        for ($i = 0; $i < 7; $i++) {
            $n = (int) $numeros[$i];
            if ($i % 2 === 0) {          // posiciones impares: se dobla y se suman sus cifras
                $n *= 2;
                $n = $n > 9 ? $n - 9 : $n;
            }
            $suma += $n;
        }
        $digito = (10 - $suma % 10) % 10;
        $letraControl = self::LETRAS_CONTROL_CIF[$digito];

        if (strpos('ABEH', $letra) !== false) {
            return $control === (string) $digito;      // control numérico
        }
        if (strpos('NPQRSW', $letra) !== false) {
            return $control === $letraControl;         // control con letra
        }
        return $control === (string) $digito || $control === $letraControl;
    }

    /** true si el DNI (8 números y letra) es correcto. */
    public static function dni(?string $valor): bool
    {
        return (bool) preg_match('/^(\d{8})([A-Z])$/', self::limpiar($valor), $m)
            && self::LETRAS_DNI[((int) $m[1]) % 23] === $m[2];
    }

    /** true si el NIE (X, Y o Z, 7 números y letra) es correcto. */
    public static function nie(?string $valor): bool
    {
        if (!preg_match('/^([XYZ])(\d{7})([A-Z])$/', self::limpiar($valor), $m)) {
            return false;
        }
        $numero = (int) (strpos('XYZ', $m[1]) . $m[2]);
        return self::LETRAS_DNI[$numero % 23] === $m[3];
    }

    /** true si es un CIF, DNI o NIE válido. */
    public static function nif(?string $valor): bool
    {
        return self::cif($valor) || self::dni($valor) || self::nie($valor);
    }

    /** 'cif', 'dni', 'nie' o null si no es válido. */
    public static function tipo(?string $valor): ?string
    {
        if (self::cif($valor)) {
            return 'cif';
        }
        if (self::dni($valor)) {
            return 'dni';
        }
        if (self::nie($valor)) {
            return 'nie';
        }
        return null;
    }

    /** Forma jurídica según la letra inicial del CIF, o null. */
    public static function tipoEntidad(?string $valor): ?string
    {
        $cif = self::limpiar($valor);
        return self::cif($cif) ? self::TIPOS_ENTIDAD[$cif[0]] : null;
    }
}
