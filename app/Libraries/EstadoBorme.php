<?php

namespace App\Libraries;

/**
 * Deduce el estado registral de una empresa a partir de sus anuncios del BORME.
 *
 * Reglas:
 *  - Un acto solo cuenta si es un encabezado del anuncio ("Extinción.", "Disolución."),
 *    no una palabra suelta dentro del texto.
 *  - Los anuncios se agrupan por hoja registral. Dos hojas distintas son dos sociedades
 *    distintas (homónimas o mal enlazadas), salvo que la hoja nueva empiece con un
 *    cambio de domicilio (traslado a otro registro).
 *  - Solo se usa la entidad cuyo nombre coincide con el de la ficha.
 */
class EstadoBorme
{
    private const R_EXT   = '/(?:^|[.;]\s?)Extinci[oó]n\s?[.:]/iu';
    private const R_DIS   = '/(?:^|[.;]\s?)Disoluci[oó]n\s?[.:]/iu';
    private const R_ABS   = '/Fusi[oó]n por absorci[oó]n\.\s*Sociedades absorbidas/iu';
    private const R_FUS   = '/(?:^|[.;]\s?)Fusi[oó]n(?: por (?:absorci[oó]n|uni[oó]n))?\s?[.:]/iu';
    private const R_CONC  = '/(?:^|[.;]\s?)Situaci[oó]n concursal\s?[.:]|declaraci[oó]n de concurso/iu';
    private const R_CFIN  = '/conclusi[oó]n del concurso/iu';
    private const R_CREC  = '/cumplimiento del? convenio|pago a los acreedores|renuncia de los acreedores|desistimiento|satisfacci[oó]n de los acreedores/iu';
    private const R_CFINX = '/insuficiencia de (?:la )?masa|inexistencia de bienes|liquidaci[oó]n|extinci[oó]n|cierre (?:provisional )?de (?:la )?hoja/iu';
    private const R_REACT = '/(?:^|[.;]\s?)Reactivaci[oó]n/iu';
    private const R_VIVA  = '/(?:^|[.;]\s?)(?:Ampliaci[oó]n de capital|Reelecciones|Cambio de domicilio social|Cambio de objeto social|Fusi[oó]n por absorci[oó]n\. Sociedades absorbidas|Transformaci[oó]n de sociedad|Declaraci[oó]n de unipersonalidad|Cambio de denominaci[oó]n social)\s?[.:]/iu';
    private const R_NOM   = '/(?:^|[.;]\s?)Nombramientos\.\s*([^.:]{2,25})[.:]/iu';
    private const R_DOM   = '/(?:^|[.;]\s?)Cambio de domicilio social\s?[.:]/iu';
    private const R_HOJA  = '/\bH\s?([A-Z]{1,2})\s?(\d+)/u';

    /** Nombre sin forma jurídica, acentos ni signos, para comparar. */
    public static function normalizar(string $s): string
    {
        $s = mb_strtoupper($s, 'UTF-8');
        $s = strtr($s, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'À' => 'A', 'È' => 'E', 'Ò' => 'O', 'Ï' => 'I', 'Ç' => 'C']);
        $s = preg_replace('/\(R\.?M\.?.*$/u', '', $s) ?? $s;
        $s = preg_replace('/\b(SOCIEDAD DE RESPONSABILIDAD LIMITADA|SOCIEDAD LIMITADA|SOCIETAT LIMITADA|SOCIEDAD ANONIMA|SOCIETAT ANONIMA|S\.?L\.?U?\.?|S\.?A\.?U?\.?|S\.?L\.?L\.?|S\.?L\.?P\.?|S\.?A\.?D\.?|UNIPERSONAL|EN LIQUIDACION|LABORAL|PROFESIONAL)\b/u', '', $s) ?? $s;

        return preg_replace('/[^A-Z0-9Ñ]/u', '', $s) ?? $s;
    }

    /** Hoja registral del anuncio: ['M', '5418'] o null. */
    public static function hoja(string $desc): ?string
    {
        $i = mb_strrpos($desc, 'Datos registrales');
        if ($i === false) {
            return null;
        }
        if (! preg_match(self::R_HOJA, mb_substr($desc, $i), $m)) {
            return null;
        }

        return $m[1] . ' ' . ltrim($m[2], '0');
    }

    private static function actoVivo(string $desc): bool
    {
        if (preg_match(self::R_VIVA, $desc)) {
            return true;
        }
        if (preg_match_all(self::R_NOM, $desc, $mm)) {
            foreach ($mm[1] as $cargo) {
                if (! preg_match('/liqui|concursal/iu', $cargo)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Estado de una serie de anuncios (ya ordenados por fecha) de UNA entidad.
     *
     * @param list<array{0:string,1:string,2:string}> $actos [fecha, nombre, descripcion]
     * @return array{estado:?string,fecha:?string,vivos_despues:int,ultimo_vivo:?string,vivos_recientes:int}
     */
    public static function estadoDe(array $actos, string $hoy, string $nombre = ''): array
    {
        $st = null; $sd = null; $despues = 0; $ultimo = null; $recientes = 0;
        $corte = date('Y-m-d', strtotime($hoy . ' -24 months'));

        foreach ($actos as [$f, $an, $d]) {
            $ext  = (bool) preg_match(self::R_EXT, $d);
            $dis  = (bool) preg_match(self::R_DIS, $d);
            $abs  = (bool) preg_match(self::R_ABS, $d);
            $fus  = ! $abs && preg_match(self::R_FUS, $d);
            $cfin = (bool) preg_match(self::R_CFIN, $d);
            $conc = ! $cfin && preg_match(self::R_CONC, $d);

            if ($ext) {
                $st = $fus ? 'Fusión' : 'Extinción'; $sd = $f; $despues = 0;
            } elseif ($cfin) {
                if (preg_match(self::R_CREC, $d)) {
                    if ($st === 'Concurso') { $st = null; $sd = $f; }
                } elseif (preg_match(self::R_CFINX, $d)) {
                    $st = 'Extinción'; $sd = $f; $despues = 0;
                }
            } elseif ($conc) {
                if ($st !== 'Extinción' && $st !== 'Fusión') { $st = 'Concurso'; $sd = $f; $despues = 0; }
            } elseif ($dis) {
                if ($st !== 'Extinción' && $st !== 'Fusión') { $st = 'Disolución'; $sd = $f; $despues = 0; }
            } elseif (preg_match(self::R_REACT, $d)) {
                $st = null; $sd = $f;
            }

            // Solo cuentan como "empresa viva" los actos publicados con el nombre de la ficha:
            // los de otro nombre pueden ser de otra sociedad mal enlazada.
            if (self::actoVivo($d) && ($nombre === '' || self::normalizar($an) === $nombre)) {
                $ultimo = $f;
                if ($f >= $corte) { $recientes++; }
                if ($st !== null && $st !== 'Concurso' && $sd !== null
                    && (strtotime($f) - strtotime($sd)) > 31 * 86400) {
                    $despues++;
                }
            }
        }

        return ['estado' => $st, 'fecha' => $st ? $sd : null, 'vivos_despues' => $despues,
                'ultimo_vivo' => $ultimo, 'vivos_recientes' => $recientes];
    }

    /**
     * Estado deducido para una ficha.
     *
     * @param list<array{0:string,1:string,2:string}> $actos ordenados por fecha
     * @return array{estado:?string,fecha:?string,vivos_despues:int,ultimo_vivo:?string,vivos_recientes:int,entidades:int,otra_viva:bool}
     */
    public static function deducir(string $nombreFicha, array $actos, string $hoy): array
    {
        $nombre = self::normalizar($nombreFicha);
        $porHoja = []; $sinHoja = [];
        foreach ($actos as $a) {
            $h = self::hoja($a[2]);
            if ($h === null) { $sinHoja[] = $a; continue; }
            $porHoja[$h][] = $a;
        }

        // Traslado de domicilio: la hoja nueva empieza con un cambio de domicilio.
        $claves = array_keys($porHoja); $padre = [];
        foreach ($claves as $i => $k) {
            $padre[$k] = $k;
            if ($i === 0 || ! preg_match(self::R_DOM, $porHoja[$k][0][2])) {
                continue;
            }
            $inicio = $porHoja[$k][0][0]; $mejor = null; $mejorF = '';
            for ($j = 0; $j < $i; $j++) {
                $q = $claves[$j]; $uf = $porHoja[$q][count($porHoja[$q]) - 1][0];
                $uf = $uf <= $inicio ? $uf : '';
                if ($mejor === null || $uf > $mejorF) { $mejor = $q; $mejorF = $uf; }
            }
            $padre[$k] = $padre[$mejor];
        }
        $grupos = [];
        foreach ($claves as $k) {
            foreach ($porHoja[$k] as $a) { $grupos[$padre[$k]][] = $a; }
        }

        if (! $grupos) {
            return self::estadoDe($sinHoja, $hoy, $nombre) + ['entidades' => 0, 'otra_viva' => false];
        }

        $mejor = null; $mejorP = null;
        foreach ($grupos as $k => $g) {
            $ex = 0;
            foreach ($g as $a) { if (self::normalizar($a[1]) === $nombre) { $ex++; } }
            $p = [$ex > 0 ? 1 : 0, $ex, count($g)];
            if ($mejorP === null || $p > $mejorP) { $mejor = $k; $mejorP = $p; }
        }
        $principal = $grupos[$mejor];
        if (count($grupos) === 1) { $principal = array_merge($principal, $sinHoja); }
        usort($principal, static fn ($a, $b) => strcmp($a[0], $b[0]));

        $r = self::estadoDe($principal, $hoy, $nombre);
        $otraViva = false;
        foreach ($grupos as $k => $g) {
            if ($k === $mejor) { continue; }
            usort($g, static fn ($a, $b) => strcmp($a[0], $b[0]));
            $o = self::estadoDe($g, $hoy);
            if ($o['estado'] === null && $o['ultimo_vivo'] !== null
                && ($r['fecha'] === null || $o['ultimo_vivo'] > $r['fecha'])) { $otraViva = true; }
        }

        return $r + ['entidades' => count($grupos), 'otra_viva' => $otraViva];
    }

    /** Grupo del estado actual de la ficha. */
    public static function grupo(?string $estado): string
    {
        $e = trim((string) $estado);
        if ($e === '') { return 'NULL'; }
        $u = mb_strtoupper($e, 'UTF-8');
        if (str_starts_with($u, 'ACTIVA')) { return 'ACTIVA'; }
        if (str_starts_with($u, 'EXTINGUIDA')) { return 'EXTINGUIDA'; }
        if (in_array($e, ['Disolución', 'Extinción', 'Concurso', 'Fusión'], true)) { return $e; }

        return $u; // DISUELTA, INACTIVA, CIERRE HOJA REGISTRAL...
    }

    /**
     * Decide el cambio. Devuelve null si no hay que tocar nada, o
     * ['estado'=>..., 'fecha'=>..., 'motivo'=>...]; motivo que empieza por "REVISAR" no se aplica.
     */
    public static function decidir(?string $estado, ?string $fecha, array $d, string $hoy): ?array
    {
        $g = self::grupo($estado); $n = $d['estado']; $nf = $d['fecha'];
        $borme = in_array($g, ['Disolución', 'Extinción', 'Concurso', 'Fusión'], true);

        // Concurso antiguo sin cierre publicado y con vida reciente: no se afirma.
        if ($n === 'Concurso' && $nf < date('Y-m-d', strtotime($hoy . ' -8 years')) && $d['vivos_recientes'] >= 1) {
            $n = null; $nf = null;
            if ($g === 'Concurso') { return null; }
        }
        // Estado adverso contradicho por actos de empresa viva posteriores.
        if ($n !== null && $d['vivos_despues'] >= 2) {
            return ['estado' => $estado, 'fecha' => $fecha, 'motivo' => 'REVISAR: ' . $n . ' con actos de empresa viva posteriores'];
        }
        if ($n !== null && $d['otra_viva'] && $g !== 'EXTINGUIDA' && ! ($borme && self::peso($n) <= self::peso($g))) {
            return ['estado' => $estado, 'fecha' => $fecha, 'motivo' => 'REVISAR: ' . $n . ' pero hay otra hoja registral viva enlazada'];
        }

        if ($borme) {
            if ($n !== null) {
                if ($n === $g && $nf === $fecha) { return null; }
                if (self::peso($n) < self::peso($g)) { return null; } // nunca se rebaja una extinción
                if ($n === $g) { return ['estado' => $n, 'fecha' => $nf, 'motivo' => 'fecha corregida']; }
                return ['estado' => $n, 'fecha' => $nf, 'motivo' => $g . ' -> ' . $n . ' según el BORME'];
            }
            if ($d['ultimo_vivo'] !== null && ($fecha === null || $d['ultimo_vivo'] > $fecha)) {
                return ['estado' => 'ACTIVA', 'fecha' => null, 'motivo' => $g . ' sin acto que lo respalde y con actos de empresa viva posteriores'];
            }
            return null;
        }

        if ($g === 'ACTIVA' || $g === 'NULL') {
            if ($n === null) { return null; }
            return ['estado' => $n, 'fecha' => $nf, 'motivo' => $g . ' -> ' . $n . ' según el BORME'];
        }

        if ($g === 'EXTINGUIDA') {
            $limpio = 'EXTINGUIDA'; $f = $fecha;
            if ($f === null && preg_match('#\((\d{2})/(\d{2})/(\d{4})\)#', (string) $estado, $m)) { $f = "$m[3]-$m[2]-$m[1]"; }
            if ($f === null && ($n === 'Extinción' || $n === 'Fusión')) { $f = $nf; }
            if ($n === null && $d['vivos_recientes'] >= 2 && $d['entidades'] <= 1) {
                return ['estado' => 'ACTIVA', 'fecha' => null, 'motivo' => 'EXTINGUIDA sin acto de extinción y con actos de empresa viva recientes'];
            }
            if ($limpio !== $estado || $f !== $fecha) {
                return ['estado' => $limpio, 'fecha' => $f, 'motivo' => 'EXTINGUIDA: formato o fecha'];
            }
            return null;
        }

        // DISUELTA, INACTIVA, CIERRE HOJA REGISTRAL y otros de la ficha.
        if ($n === 'Extinción' || $n === 'Fusión') {
            return ['estado' => $n, 'fecha' => $nf, 'motivo' => $g . ' -> ' . $n . ' según el BORME'];
        }
        if ($n === null && $d['vivos_recientes'] >= 2 && $d['entidades'] <= 1) {
            return ['estado' => 'ACTIVA', 'fecha' => null, 'motivo' => $g . ' con actos de empresa viva recientes'];
        }

        return null;
    }

    private static function peso(string $e): int
    {
        return ['Concurso' => 1, 'Disolución' => 2, 'Fusión' => 3, 'Extinción' => 3][$e] ?? 0;
    }
}
