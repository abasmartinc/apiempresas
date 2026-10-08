<?php

namespace App\Libraries\Errors;

/**
 * El limpiador (RGPD) del registro de errores: todo lo que va a error_issues / error_events pasa por aqui antes.
 * Copia del de abasmart, adaptado a apiempresas (formatos espanoles: DNI/NIE, IBAN, moviles; los CIF de sociedades se dejan).
 *
 * La regla es guardar lo necesario para ARREGLAR el fallo (donde, que tipo de error, con que forma de datos) y nunca un dato
 * personal de un usuario. Por eso se quita de los textos todo lo que podria serlo -- lo que va entre comillas (nombres,
 * direcciones, valores de un INSERT), emails, telefonos, DNI/NIE, IBAN y numeros largos, y fechas --, y de la peticion solo
 * quedan los nombres de los campos y, como valor, los ids numericos cortos y los CIF de sociedad.
 *
 * Los CIF de sociedad (B12345678, A08000000...) NO son datos personales (son publicos en el BORME) y son lo que mas ayuda a
 * reproducir un fallo de la ficha o de la API, asi que se dejan. Los NIF de personas fisicas (DNI, NIE) si se tapan.
 *
 * Los numeros cortos se dejan en el mensaje (ids, lineas, codigos de error): ayudan a reproducir y no identifican a nadie.
 * Para agrupar (fingerprint) se usa normalize(), que ademas los quita, asi "empresa 123" y "empresa 456" son el mismo issue.
 */
final class ErrorScrubber
{
    private const MAX_TEXT = 2000;

    /** Un texto libre (mensaje de excepcion, de log_message, de JavaScript) sin datos personales. */
    public static function text(?string $s, int $max = self::MAX_TEXT): string
    {
        $s = (string) $s;

        if ($s === '') {
            return '';
        }

        $s = (string) preg_replace('/data:[a-z]+\/[a-z0-9.+-]+;base64,[A-Za-z0-9+\/=]+/i', '[data]', $s);
        $s = (string) preg_replace('/\b[A-Za-z0-9+\/]{120,}={0,2}/', '[blob]', $s);
        // La propiedad de un error de JavaScript ("Cannot read properties of null (reading 'removeChild')") es un nombre del CODIGO, no
        // un dato, y sin ella no hay forma de saber que fallo: se aparta antes de tapar las comillas y se devuelve despues.
        // Solo con la frase exacta del motor de JavaScript ("properties of null|undefined (reading|setting 'x')").
        $s = (string) preg_replace("/(properties of (?:null|undefined) \\((?:reading|setting) )'([A-Za-z_\$][A-Za-z0-9_\$]{0,63})'/", "\$1\x01\$2\x02", $s);
        // Entre comillas: nombres, direcciones, valores de un INSERT ("Duplicate entry 'Smith, John'"...). Se dejan los que tienen
        // forma de identificador tecnico (Unknown column 'ii_client_address', in 'where clause'): un nombre de persona no la tiene.
        $s = (string) preg_replace_callback("/'((?:[^'\\\\]|\\\\.)*)'/s", static fn (array $m): string => self::identifier($m[1]) ? "'" . $m[1] . "'" : "'?'", $s);
        $s = (string) preg_replace_callback('/"((?:[^"\\\\]|\\\\.)*)"/s', static fn (array $m): string => self::identifier($m[1]) ? '"' . $m[1] . '"' : '"?"', $s);
        $s = strtr($s, ["\x01" => "'", "\x02" => "'"]);
        $s = (string) preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[email]', $s);
        // Fechas (YYYY-MM-DD, MM/DD/YYYY...) y horas sueltas que acompañan a una fecha.
        $s = (string) preg_replace('/\b\d{4}[-\/.]\d{1,2}[-\/.]\d{1,2}(?:[ T]\d{1,2}:\d{2}(?::\d{2})?)?\b/', '[date]', $s);
        $s = (string) preg_replace('/\b\d{1,2}[-\/.]\d{1,2}[-\/.]\d{2,4}\b/', '[date]', $s);
        // Los CIF de sociedad se apartan antes de tapar numeros largos y se devuelven despues (ver la cabecera).
        $cifs = [];
        $s = (string) preg_replace_callback('/\b' . self::CIF . '\b/', static function (array $m) use (&$cifs): string {
            $cifs[] = $m[0];

            return "\x03" . (count($cifs) - 1) . "\x04";
        }, $s);
        // Telefonos espanoles (+34 600 123 456, 912 34 56 78...), IBAN, DNI/NIE, tarjetas y numeros largos.
        $s = (string) preg_replace('/(?:\+|00)\d{2}[ .-]?\d{2,3}(?:[ .-]?\d{2,3}){2,4}\b/', '[number]', $s);
        $s = (string) preg_replace('/\b[6789]\d{2}(?:[ .-]\d{2,3}){2,3}\b/', '[number]', $s);
        $s = (string) preg_replace('/\b[A-Z]{2}\d{2}(?:[ ]?[A-Z0-9]{4}){4,7}\b/', '[number]', $s);
        $s = (string) preg_replace('/\b\d{4}[ -]\d{4}[ -]\d{4}[ -]\d{4}\b/', '[number]', $s);
        $s = (string) preg_replace('/\b[A-Z]{0,4}\d{7,}[A-Z]{0,4}\b/i', '[number]', $s);
        $s = (string) preg_replace_callback("/\x03(\\d+)\x04/", static fn (array $m): string => $cifs[(int) $m[1]] ?? '[number]', $s);

        return mb_substr($s, 0, $max);
    }

    /** Un CIF de sociedad: letra de persona juridica + 7 cifras + control. Los de persona fisica (K, L, M, X, Y, Z) no entran. */
    private const CIF = '[ABCDEFGHJNPQRSUVW]\d{7}[0-9A-J]';

    public static function isCompanyCif(string $s): bool
    {
        return preg_match('/^' . self::CIF . '$/', strtoupper(trim($s))) === 1;
    }

    /**
     * Si un texto entre comillas es un identificador tecnico (columna, tabla, clave de array, clausula SQL) y no un dato: con guion
     * bajo (`ii_client_address`, `abasmart_palomohc.visits`), o una de las clausulas que nombra MySQL. Con solo un punto NO: "john.smith".
     */
    private static function identifier(string $s): bool
    {
        if (in_array(strtolower($s), ['where clause', 'where', 'field list', 'on clause', 'on', 'order clause', 'order by', 'group statement', 'group by', 'having clause', 'having', 'set', 'primary'], true)) {
            return true;
        }

        return strlen($s) <= 64 && str_contains($s, '_') && preg_match('/^[A-Za-z][A-Za-z0-9]*(?:[_.][A-Za-z0-9]+)+$/', $s) === 1;
    }

    /** Para agrupar: el texto limpio y ademas sin ningun numero, en minusculas y con los espacios plegados. */
    public static function normalize(string $s): string
    {
        $s = self::text($s, 500);
        $s = (string) preg_replace('/\d+/', '#', $s);
        $s = (string) preg_replace('/\s+/', ' ', $s);

        return strtolower(trim($s));
    }

    /** Una consulta SQL sin ningun valor: cadenas y numeros pasan a `?`. Se queda la forma de la consulta. */
    public static function sql(?string $sql): string
    {
        $sql = (string) $sql;

        if ($sql === '') {
            return '';
        }

        $sql = (string) preg_replace("/'(?:[^'\\\\]|\\\\.)*'/s", '?', $sql);
        $sql = (string) preg_replace('/"(?:[^"\\\\]|\\\\.)*"/s', '?', $sql);
        $sql = (string) preg_replace('/\b\d+(?:\.\d+)?\b/', '?', $sql);
        $sql = (string) preg_replace('/\s+/', ' ', $sql);

        return mb_substr(trim($sql), 0, 4000);
    }

    /**
     * Los datos de la peticion (GET/POST): todos los NOMBRES, y como valor solo un id numerico corto. El resto se cambia por
     * su tipo y tamaño ("[text 42]"), que suele bastar para reproducir ("llego vacio", "llego un array").
     *
     * @param array<mixed> $datos
     * @return array<string, mixed>
     */
    public static function params(array $datos, int $nivel = 0): array
    {
        $out = [];
        $n = 0;

        foreach ($datos as $clave => $valor) {
            if (++$n > 100) {
                $out['…'] = '[' . (count($datos) - 100) . ' more]';
                break;
            }

            $clave = mb_substr((string) $clave, 0, 80);

            if (is_array($valor)) {
                $out[$clave] = $nivel >= 3 ? '[array ' . count($valor) . ']' : self::params($valor, $nivel + 1);
                continue;
            }

            $out[$clave] = self::value($clave, $valor);
        }

        return $out;
    }

    private static function value(string $clave, mixed $valor): string|int|null
    {
        if ($valor === null) {
            return null;
        }

        if (! is_scalar($valor)) {
            return '[' . get_debug_type($valor) . ']';
        }

        $texto = (string) $valor;

        if ($texto === '') {
            return '';
        }

        $sensible = preg_match('/pass|token|secret|key|auth|dni|nie|nif_persona|tel|phone|movil|mobile|iban|card|tarjeta|cvv|cvc|pin|code|codigo|^cp$|postal|zip|birth|nacim/i', $clave) === 1;

        // Ids: numeros cortos y nunca en un campo que por su nombre pueda llevar un dato personal o un secreto.
        if (preg_match('/^\d{1,9}$/', $texto) && ! $sensible) {
            return (int) $texto;
        }

        // CIF de sociedad (dato publico): ayuda a reproducir el fallo de una ficha o de la API.
        if (! $sensible && self::isCompanyCif($texto)) {
            return strtoupper(trim($texto));
        }

        return '[text ' . mb_strlen($texto) . ']';
    }

    /** Solo los NOMBRES de los parametros de un query string (?client=..&search=..) -> "client, search". */
    public static function queryKeys(string $query): string
    {
        parse_str($query, $q);

        return implode(', ', array_slice(array_map('strval', array_keys($q)), 0, 30));
    }

    /**
     * La traza sin argumentos (los argumentos son los datos con los que se llamo: pueden ser datos personales).
     *
     * @param list<array<string, mixed>> $trace
     * @return list<array{file: string, line: int, function: string}>
     */
    public static function trace(array $trace, int $max = 60): array
    {
        $out = [];

        foreach (array_slice($trace, 0, $max) as $f) {
            $out[] = [
                'file' => isset($f['file']) ? self::path((string) $f['file']) : '[internal]',
                'line' => (int) ($f['line'] ?? 0),
                'function' => (isset($f['class']) ? $f['class'] . ($f['type'] ?? '::') : '') . ($f['function'] ?? ''),
            ];
        }

        return $out;
    }

    /** La ruta de un fichero relativa al proyecto (sin /home/.../public_html/...). */
    public static function path(string $file): string
    {
        $file = str_replace('\\', '/', $file);
        $raiz = rtrim(str_replace('\\', '/', (string) (realpath(ROOTPATH) ?: ROOTPATH)), '/') . '/';

        return stripos($file, $raiz) === 0 ? substr($file, strlen($raiz)) : $file;
    }
}
