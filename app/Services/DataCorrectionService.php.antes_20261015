<?php

namespace App\Services;

/**
 * Revisión de los avisos de datos de la ficha (29-09-2026).
 *
 * DESDE EL 29-09-2026 (tarde) NADA SE APLICA SOLO desde la web: cualquiera puede
 * rellenar el formulario, sea o no de la empresa, y un competidor podría poner su
 * teléfono o una web de phishing. La web llama a revisar(), que valida y normaliza
 * cada dato, lo compara con lo que hay en la ficha y devuelve la propuesta; el
 * administrador la recibe por correo y la cambia a mano. aplicar() queda para el
 * comando datos:aplicar-avisos, que solo se lanza a mano.
 *
 * Lo que sigue describe cómo se valida y dónde iría cada dato.
 *
 * Cuando alguien contesta "No, hay un error" en "¿Son correctos estos datos?" y
 * deja el valor correcto, este servicio lo valida y lo escribe en la ficha, para no
 * tener que hacerlo a mano desde /admin/avisos-datos.
 *
 *   Campo            Dónde se escribe
 *   ---------------  ---------------------------------------------------------------
 *   telefono         companies.phone (en avisos antiguos, un móvil va a phone_mobile)
 *   movil            companies.phone_mobile
 *   direccion        companies.address (y lat/lng a NULL para que se vuelva a geocodificar)
 *   actividad        companies.cnae_code + companies.cnae_label (si trae un código CNAE)
 *   web              company_enrichment.website_official
 *   correo           company_enrichment.email
 *   otro             se deduce del valor: email → correo, URL → web, teléfono → telefono
 *   estado           NO se aplica solo: sale del Registro/BORME y del motor de riesgo,
 *   administradores  y un cambio anónimo aquí puede hacer daño a una empresa real.
 *
 * Cada aviso que se aplica deja en company_ratings.feedback el valor anterior
 * (" | Aplicado: tabla.columna | Anterior: ..."), así que se puede deshacer.
 * Desde el 29-09-2026 la ficha manda varios campos a la vez (aplicarVarios): el
 * formulario sale relleno con los datos actuales y solo llegan los que el usuario ha
 * cambiado, con el valor que veía (original). Si la base de datos ya no tiene ese
 * valor (la página estaba cacheada y el dato cambió después), no se pisa: a mano.
 *
 * Tope de fichas corregidas automáticamente por IP y día para que nadie reescriba fichas en serie.
 * Tras escribir se vacía la caché de Cloudflare de la ficha (CloudflareCacheService).
 */
class DataCorrectionService
{
    /** Fichas distintas corregidas automáticamente por IP en 24 h. A partir de ahí, a mano. */
    public const MAX_POR_IP_DIA = 5;

    /** Campos que se corrigen solos. */
    public const AUTOMATICOS = ['telefono', 'movil', 'direccion', 'actividad', 'web', 'correo', 'otro'];

    /**
     * Valida los cambios de un envío SIN escribir nada. Por cada campo devuelve lo de
     * aplicar() más 'valido' (true = formato correcto y distinto de lo que hay: listo
     * para que el administrador lo copie) y deja 'aplicado' siempre en false.
     *
     * @return array<string,array>
     */
    public function revisar(int $companyId, array $cambios, array $originales): array
    {
        $out = [];
        foreach ($cambios as $campo => $valor) {
            $r = $this->aplicar(
                $companyId, (string) $campo, (string) $valor, '', true, false,
                array_key_exists($campo, $originales) ? (string) $originales[$campo] : null
            );
            $r['valido']   = $r['aplicado'];
            $r['aplicado'] = false;
            if ($r['valido']) {
                $r['motivo'] = '';
            }
            $out[$campo] = $r;
        }
        return $out;
    }

    /**
     * Texto que se añade al feedback de un aviso revisado (no aplicado).
     */
    public static function sufijoRevision(array $r): string
    {
        $limpio = static fn (string $v, int $max) => mb_substr(str_replace(['|', "\r", "\n"], ['/', ' ', ' '], $v), 0, $max);

        if (!empty($r['valido'])) {
            return ' | Pendiente: ' . $r['tabla'] . '.' . $r['columna']
                . ' | Actual: ' . (($r['anterior'] ?? '') !== '' ? $limpio($r['anterior'], 300) : '(vacío)');
        }
        return ' | Revisar: ' . $limpio((string) ($r['motivo'] ?? ''), 200);
    }

    /**
     * Aplica varios cambios de un mismo envío y vacía la caché de la ficha una sola vez.
     *
     * @param array<string,string> $cambios    campo => valor nuevo
     * @param array<string,string> $originales campo => valor que enseñaba la ficha
     * @return array<string,array> campo => resultado de aplicar()
     */
    public function aplicarVarios(int $companyId, array $cambios, array $originales, string $ip = ''): array
    {
        $resultados = [];
        foreach ($cambios as $campo => $valor) {
            $resultados[$campo] = $this->aplicar(
                $companyId, (string) $campo, (string) $valor, $ip, false, false,
                array_key_exists($campo, $originales) ? (string) $originales[$campo] : null
            );
        }

        $aplicados = array_filter($resultados, static fn ($r) => $r['aplicado']);
        if ($aplicados) {
            $cache = $this->purgar($companyId);
            foreach (array_keys($aplicados) as $campo) {
                $resultados[$campo]['cache'] = $cache;
            }
        }
        return $resultados;
    }

    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /**
     * Valida y, si procede, aplica la corrección.
     *
     * @param bool        $simular  true = solo calcula qué haría, sin escribir (para el comando de repaso).
     * @param bool        $purgar   vaciar la caché de Cloudflare de la ficha si se aplica.
     * @param string|null $original valor que enseñaba la ficha al usuario (formulario relleno);
     *                              null en los avisos antiguos de un solo campo.
     * @return array{aplicado:bool, campo:string, tabla:string, columna:string, anterior:string, nuevo:string, motivo:string}
     */
    public function aplicar(int $companyId, string $campo, string $valor, string $ip = '', bool $simular = false, bool $purgar = true, ?string $original = null): array
    {
        $r = [
            'aplicado' => false,
            'campo'    => $campo,
            'tabla'    => '',
            'columna'  => '',
            'anterior' => '',
            'nuevo'    => '',
            'detalle'  => '',
            'motivo'   => '',
            'cache'    => null,   // true/false: si se ha vaciado la caché de Cloudflare
        ];

        $valor = trim(preg_replace('/\s+/u', ' ', $valor));
        if ($valor === '') {
            $r['motivo'] = ($original !== null && trim($original) !== '')
                ? 'Han borrado el dato sin poner otro: no se borra solo, revísalo a mano.'
                : 'No han indicado el valor correcto.';
            return $r;
        }

        if (in_array($campo, ['estado', 'administradores'], true)) {
            $r['motivo'] = 'El ' . ($campo === 'estado' ? 'estado' : 'órgano de administración')
                . ' sale del Registro Mercantil: no se cambia solo, revísalo a mano.';
            return $r;
        }

        if ($campo === 'otro') {
            $campo = $this->deducirCampo($valor);
            $r['campo'] = $campo;
            if ($campo === 'otro') {
                $r['motivo'] = 'Campo "Otro dato" y el valor no es un teléfono, una web ni un email: revísalo a mano.';
                return $r;
            }
        }

        // Qué se escribe y dónde.
        $cambio = match ($campo) {
            'telefono'  => $this->prepararTelefono($valor, $original !== null ? 'phone' : null),
            'movil'     => $this->prepararTelefono($valor, 'phone_mobile'),
            'direccion' => $this->prepararDireccion($valor),
            'actividad' => $this->prepararActividad($valor),
            'web'       => $this->prepararWeb($valor),
            'correo'    => $this->prepararCorreo($valor),
            default     => ['error' => 'Campo desconocido: ' . $campo],
        };

        if (isset($cambio['error'])) {
            $r['motivo'] = $cambio['error'];
            return $r;
        }

        $r['tabla']   = $cambio['tabla'];
        $r['columna'] = $cambio['columna'];
        $r['nuevo']   = $cambio['nuevo'];
        $r['detalle'] = $cambio['label'] ?? '';

        // Valor actual.
        $actual = $this->db->table($cambio['tabla'])
            ->select($cambio['columna'])
            ->where($cambio['tabla'] === 'companies' ? 'id' : 'company_id', $companyId)
            ->get()->getRowArray();

        if ($cambio['tabla'] === 'companies' && !$actual) {
            $r['motivo'] = 'La empresa no existe.';
            return $r;
        }
        $r['anterior'] = trim((string) ($actual[$cambio['columna']] ?? ''));

        if ($this->iguales($r['anterior'], $r['nuevo'], $campo, $original === null)) {
            $r['motivo'] = 'La ficha ya tenía ese valor.';
            return $r;
        }

        // La ficha estaba cacheada y el dato ha cambiado desde entonces: no se pisa a ciegas.
        // (El email no se enseña en la ficha, así que su original siempre llega vacío.)
        if ($original !== null && $campo !== 'correo'
            && $this->clave($campo, $original) !== $this->clave($campo, $r['anterior'])) {
            $r['motivo'] = 'El dato ha cambiado desde que el usuario vio la ficha (veía "' . $original
                . '"): revísalo a mano.';
            return $r;
        }

        // Tope por IP: fichas distintas con algún cambio aplicado en las últimas 24 h.
        // La ficha que se está corrigiendo no cuenta (un envío con varios campos es una ficha).
        if ($ip !== '' && !$simular) {
            $fila = $this->db->table('company_ratings')
                ->select('COUNT(DISTINCT company_id) AS n')
                ->where('ip_address', $ip)
                ->where('company_id !=', $companyId)
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                ->like('feedback', '| Aplicado:', 'both')
                ->get()->getRowArray();
            $fichasIp = (int) ($fila['n'] ?? 0);
            if ($fichasIp >= self::MAX_POR_IP_DIA) {
                $r['motivo'] = 'Esta IP ya ha corregido ' . $fichasIp . ' fichas hoy: se deja para revisar a mano.';
                return $r;
            }
        }

        if ($simular) {
            $r['aplicado'] = true;
            $r['motivo']   = '(simulación, no se ha escrito nada)';
            return $r;
        }

        try {
            $this->escribir($companyId, $cambio);
        } catch (\Throwable $e) {
            log_message('error', '[DataCorrection] ' . $companyId . ' ' . $campo . ': ' . $e->getMessage());
            $r['motivo'] = 'Error al guardar: ' . $e->getMessage();
            return $r;
        }

        $r['aplicado'] = true;

        if ($purgar) {
            $r['cache'] = $this->purgar($companyId);
        }

        log_message('info', "[DataCorrection] Empresa {$companyId}: {$cambio['tabla']}.{$cambio['columna']} "
            . "'{$r['anterior']}' → '{$r['nuevo']}' (IP {$ip})");

        return $r;
    }

    /** Que la ficha pública enseñe ya el dato corregido (Cloudflare la cachea 24 h). */
    private function purgar(int $companyId): bool
    {
        try {
            return (new CloudflareCacheService())->purgeCompany($companyId);
        } catch (\Throwable $e) {
            log_message('error', '[DataCorrection] Cloudflare ' . $companyId . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Texto que se añade al feedback del aviso para dejar constancia.
     */
    public static function sufijoFeedback(array $r): string
    {
        $limpio = static fn (string $v, int $max) => mb_substr(str_replace(['|', "\r", "\n"], ['/', ' ', ' '], $v), 0, $max);

        if ($r['aplicado']) {
            return ' | Aplicado: ' . $r['tabla'] . '.' . $r['columna']
                . ' | Anterior: ' . ($r['anterior'] !== '' ? $limpio($r['anterior'], 300) : '(vacío)');
        }
        return ' | No aplicado: ' . $limpio($r['motivo'], 200);
    }

    // ------------------------------------------------------------------ preparar

    /**
     * Uno o varios teléfonos ("910 80 54 44 / 912 22 33 44"). Se guardan solo con cifras,
     * separados por " / ", que es como los parte la ficha.
     *
     * @param string|null $columna phone | phone_mobile; null = aviso antiguo de un solo
     *                             número: los móviles (6/7) van a phone_mobile.
     */
    private function prepararTelefono(string $v, ?string $columna): array
    {
        $nums = $this->telefonos($v);
        if ($nums === null) {
            return ['error' => 'No parece un teléfono español válido (9 cifras que empiezan por 6, 7, 8 o 9).'];
        }
        if ($columna === null) {
            $columna = (count($nums) === 1 && in_array($nums[0][0], ['6', '7'], true)) ? 'phone_mobile' : 'phone';
        }
        return [
            'tabla'   => 'companies',
            'columna' => $columna,
            'nuevo'   => implode(' / ', $nums),
        ];
    }

    /** Lista de teléfonos válidos, o null si alguna parte no lo es. */
    private function telefonos(string $v): ?array
    {
        $out = [];
        foreach (preg_split('/[,;\/|]+|\s+(?:y|o|and|or)\s+/iu', $v) as $parte) {
            if (trim($parte) === '') {
                continue;
            }
            $d = preg_replace('/\D/', '', $parte);
            // Dos números seguidos separados solo por espacios: "910805444 912223344".
            $trozos = (strlen($d) === 18) ? str_split($d, 9) : [$d];
            foreach ($trozos as $t) {
                $n = $this->normalizarTelefono($t);
                if ($n === null) {
                    return null;
                }
                $out[] = $n;
            }
        }
        $out = array_values(array_unique($out));
        return $out ?: null;
    }

    private function prepararDireccion(string $v): array
    {
        $v = preg_replace('/\s+/u', ' ', $v);
        if (mb_strlen($v) < 8 || !preg_match('/\p{L}{3,}/u', $v)) {
            return ['error' => 'La dirección es demasiado corta para ser una dirección.'];
        }
        if (mb_strlen($v) > 255) {
            return ['error' => 'La dirección es demasiado larga.'];
        }
        if (preg_match('~https?://|www\.|@~i', $v)) {
            return ['error' => 'La dirección lleva un enlace o un email: parece spam.'];
        }
        return [
            'tabla'   => 'companies',
            'columna' => 'address',
            'nuevo'   => $v,
            // Coordenadas a NULL: GeocodeCompanies las recalcula con la dirección nueva.
            'extra'   => ['lat_num' => null, 'lng_num' => null],
        ];
    }

    private function prepararActividad(string $v): array
    {
        // Solo si traen el código: "6920", "69.20", "6920 - Actividades de contabilidad".
        if (!preg_match('/\b(\d{2})\.?(\d{2})\b/', $v, $m)) {
            return ['error' => 'La actividad no trae un código CNAE (4 cifras): revísala a mano.'];
        }
        $code = $m[1] . $m[2];

        // La etiqueta, de otra empresa con ese mismo código (misma fuente que el resto de fichas).
        $fila = $this->db->table('companies')
            ->select('cnae_label')
            ->where('cnae_code', $code)
            ->where('cnae_label IS NOT NULL', null, false)
            ->where('cnae_label !=', '')
            ->limit(1)
            ->get()->getRowArray();

        $label = trim((string) ($fila['cnae_label'] ?? ''));
        if ($label === '') {
            // Si no hay ninguna empresa con ese código, el código no existe (o no lo tenemos).
            return ['error' => "El código CNAE {$code} no aparece en ninguna otra empresa: revísalo a mano."];
        }

        return [
            'tabla'   => 'companies',
            'columna' => 'cnae_code',
            'nuevo'   => $code,
            'extra'   => ['cnae_label' => $label],
            'label'   => $label,
        ];
    }

    private function prepararWeb(string $v): array
    {
        $url = $this->normalizarWeb($v);
        if ($url === null) {
            return ['error' => 'No parece una dirección web válida.'];
        }
        return ['tabla' => 'company_enrichment', 'columna' => 'website_official', 'nuevo' => $url];
    }

    private function prepararCorreo(string $v): array
    {
        $email = mb_strtolower(trim($v));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
            return ['error' => 'No parece un email válido.'];
        }
        return ['tabla' => 'company_enrichment', 'columna' => 'email', 'nuevo' => $email];
    }

    // ------------------------------------------------------------------ utilidades

    private function deducirCampo(string $v): string
    {
        if (filter_var(trim($v), FILTER_VALIDATE_EMAIL)) {
            return 'correo';
        }
        if ($this->normalizarTelefono($v) !== null && !preg_match('/\p{L}{3,}/u', $v)) {
            return 'telefono';
        }
        if (!str_contains($v, ' ') && $this->normalizarWeb($v) !== null) {
            return 'web';
        }
        return 'otro';
    }

    /** "+34 910 80 54 44" → "910805444". Null si no es un número español. */
    private function normalizarTelefono(string $v): ?string
    {
        $d = preg_replace('/\D/', '', $v);
        if (strlen($d) === 13 && str_starts_with($d, '0034')) {
            $d = substr($d, 4);
        } elseif (strlen($d) === 11 && str_starts_with($d, '34')) {
            $d = substr($d, 2);
        }
        return preg_match('/^[6789]\d{8}$/', $d) ? $d : null;
    }

    /** "www.empresa.es/" → "https://www.empresa.es". Null si no es una web. */
    private function normalizarWeb(string $v): ?string
    {
        $v = trim($v);
        if ($v === '' || str_contains($v, '@') || preg_match('/\s/', $v)) {
            return null;
        }
        if (!preg_match('~^https?://~i', $v)) {
            $v = 'https://' . ltrim($v, '/');
        }
        if (!filter_var($v, FILTER_VALIDATE_URL)) {
            return null;
        }
        $host = (string) parse_url($v, PHP_URL_HOST);
        if (!preg_match('/^([a-z0-9-]+\.)+[a-z]{2,}$/i', $host)) {
            return null;
        }
        return rtrim($v, '/');
    }

    /**
     * @param bool $contiene aviso antiguo de un solo teléfono: basta con que ya esté entre los que hay.
     */
    private function iguales(string $antes, string $nuevo, string $campo, bool $contiene = false): bool
    {
        if (($campo === 'telefono' || $campo === 'movil') && $contiene) {
            $hay = $this->telefonos($antes) ?? [];
            return array_diff($this->telefonos($nuevo) ?? [], $hay) === [];
        }
        return $this->clave($campo, $antes) === $this->clave($campo, $nuevo);
    }

    /** Forma normalizada de un valor para comparar ("¿es el mismo dato?"). */
    private function clave(string $campo, string $v): string
    {
        $v = trim(preg_replace('/\s+/u', ' ', $v));
        switch ($campo) {
            case 'telefono':
            case 'movil':
                $n = $this->telefonos($v);
                if ($n === null) {
                    return preg_replace('/\D/', '', $v);
                }
                sort($n);
                return implode(',', $n);
            case 'actividad':
                return preg_match('/(\d{2})\.?(\d{2})/', $v, $m) ? $m[1] . $m[2] : mb_strtolower($v);
            case 'web':
                return mb_strtolower(rtrim(preg_replace('~^(https?://)?(www\.)?~i', '', $v), '/'));
            default:
                return mb_strtolower($v);
        }
    }

    private function escribir(int $companyId, array $cambio): void
    {
        $datos = [$cambio['columna'] => $cambio['nuevo']] + ($cambio['extra'] ?? []);

        if ($cambio['tabla'] === 'companies') {
            $this->db->table('companies')->where('id', $companyId)->update($datos);
            return;
        }

        // company_enrichment: puede que la empresa aún no tenga fila.
        $existe = $this->db->table('company_enrichment')->where('company_id', $companyId)->countAllResults() > 0;
        if ($existe) {
            $this->db->table('company_enrichment')->where('company_id', $companyId)->update($datos);
        } else {
            $this->db->table('company_enrichment')->insert(['company_id' => $companyId] + $datos);
        }
    }
}
