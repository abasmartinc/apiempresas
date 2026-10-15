<?php

namespace App\Services;

use Config\Services;

/**
 * Aportaciones de datos de contacto desde la ficha: "Añadir teléfono / web / email" (15-10-2026).
 *
 * Como en Empresite: donde la ficha no tiene teléfono, web o email, un enlace abre un
 * formulario y cualquiera puede aportarlo. Se guarda en company_contributions y NO se
 * publica solo (misma regla que los avisos de datos desde el 29-09-2026: cualquiera puede
 * rellenarlo, y un competidor podría poner su teléfono o una web de phishing).
 *
 * Qué hace que un dato se dé por bueno (estado "verificado"):
 *
 *   cif_en_web      La web aportada lleva el CIF de la empresa (aviso legal, pie, contacto...).
 *                   La LSSI (art. 10) obliga a las webs de empresa a publicar su NIF.
 *   en_web_empresa  El teléfono o el email aparece en una web de la empresa ya comprobada así.
 *   email_dominio   Quien aporta dice ser de la empresa, su email es del dominio de esa web
 *                   comprobada y ha pulsado el enlace que le mandamos: todo su envío vale.
 *
 * Lo comprueba el comando `datos:verificar-aportaciones` (descarga la web fuera de la
 * petición del usuario). Con --aplicar, publica lo verificado SOLO si la ficha sigue sin ese
 * dato. Lo demás espera en /admin/aportaciones, donde se aprueba o rechaza a mano.
 *
 * Teléfonos: solo fijos (8/9). Desde el 15-10-2026 no se guardan móviles.
 */
class ContributionService
{
    public const CAMPOS     = ['telefono', 'web', 'correo'];
    public const RELACIONES = ['empresa', 'cliente', 'otro'];

    /** Envíos por IP y hora. */
    public const MAX_IP_HORA = 10;

    /** Páginas de una web que se descargan como mucho para buscar el CIF. */
    private const MAX_PAGINAS = 6;
    private const MAX_BYTES   = 1500000;

    /** Correos gratuitos: no prueban que la persona sea de la empresa. */
    private const CORREOS_GRATUITOS = [
        'gmail.com', 'googlemail.com', 'hotmail.com', 'hotmail.es', 'outlook.com', 'outlook.es', 'live.com', 'live.es',
        'msn.com', 'yahoo.com', 'yahoo.es', 'ymail.com', 'icloud.com', 'me.com', 'mac.com', 'aol.com', 'gmx.com',
        'gmx.es', 'gmx.net', 'protonmail.com', 'proton.me', 'mail.com', 'zoho.com', 'yandex.com', 'telefonica.net',
        'movistar.es', 'ono.com', 'orange.es', 'vodafone.es', 'terra.es', 'wanadoo.es', 'jazztel.es',
    ];

    /** No son la web de la empresa: redes, directorios, buscadores, acortadores. */
    private const WEBS_NO_VALIDAS = [
        'facebook.com', 'instagram.com', 'linkedin.com', 'twitter.com', 'x.com', 'tiktok.com', 'youtube.com',
        'google.com', 'google.es', 'goo.gl', 'g.page', 'maps.app.goo.gl', 'bit.ly', 'linktr.ee', 'wa.me', 'whatsapp.com',
        'empresite.eleconomista.es', 'eleconomista.es', 'einforma.com', 'infoempresa.com', 'axesor.es', 'infocif.es',
        'informa.es', 'paginasamarillas.es', 'qdq.com', 'cylex.es', 'infobel.com', 'apiempresas.es', 'empresia.es',
        'libreborme.net', 'datoscif.es', 'iberinform.es', 'expansion.com', 'boe.es', 'registradores.org',
    ];

    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    // ================================================================ normalizar

    /**
     * Valida y normaliza un dato.
     *
     * @return array{valor?:string, error?:string}
     */
    public static function normalizar(string $campo, string $v, bool $en = false): array
    {
        $t = static fn (string $es, string $enTxt) => $en ? $enTxt : $es;
        $v = trim(preg_replace('/\s+/u', ' ', $v));
        if ($v === '') {
            return ['error' => $t('Está vacío.', 'It is empty.')];
        }

        switch ($campo) {
            case 'telefono':
                $d = preg_replace('/\D/', '', $v);
                if (strlen($d) === 13 && str_starts_with($d, '0034')) {
                    $d = substr($d, 4);
                } elseif (strlen($d) === 11 && str_starts_with($d, '34')) {
                    $d = substr($d, 2);
                }
                if (preg_match('/^[67]\d{8}$/', $d)) {
                    return ['error' => $t('Solo publicamos teléfonos fijos (empiezan por 8 o 9).', 'We only publish landline numbers (starting with 8 or 9).')];
                }
                if (!preg_match('/^[89]\d{8}$/', $d)) {
                    return ['error' => $t('No parece un teléfono fijo español (9 cifras que empiezan por 8 o 9).', 'This does not look like a Spanish landline (9 digits starting with 8 or 9).')];
                }
                // Tarificación adicional (803/806/807/905): no es un teléfono de contacto.
                if (preg_match('/^(80[3-7]|905)/', $d)) {
                    return ['error' => $t('Los números de tarificación adicional (803, 806, 807, 905) no se publican.', 'Premium-rate numbers (803, 806, 807, 905) are not published.')];
                }
                return ['valor' => $d];

            case 'web':
                if (str_contains($v, '@') || str_contains($v, ' ')) {
                    return ['error' => $t('No parece una dirección web.', 'This does not look like a website address.')];
                }
                $url = preg_match('~^https?://~i', $v) ? $v : 'https://' . ltrim($v, '/');
                $host = mb_strtolower((string) parse_url($url, PHP_URL_HOST));
                if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('/^([a-z0-9-]+\.)+[a-z]{2,}$/', $host)) {
                    return ['error' => $t('No parece una dirección web.', 'This does not look like a website address.')];
                }
                foreach (self::WEBS_NO_VALIDAS as $no) {
                    if ($host === $no || str_ends_with($host, '.' . $no)) {
                        return ['error' => $t('Indica la web propia de la empresa, no un perfil en redes sociales o en un directorio.', 'Enter the company\'s own website, not a social media or directory profile.')];
                    }
                }
                // Solo la portada: la web de la empresa, no una página concreta.
                $esquema = mb_strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'http' ? 'http' : 'https';
                return ['valor' => $esquema . '://' . $host];

            case 'correo':
                $email = mb_strtolower($v);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
                    return ['error' => $t('No parece un email válido.', 'This does not look like a valid email.')];
                }
                return ['valor' => $email];
        }

        return ['error' => $t('Dato desconocido.', 'Unknown field.')];
    }

    /** "https://www.empresa.es" o "info@empresa.es" → "empresa.es". */
    public static function dominio(string $v): string
    {
        $v = mb_strtolower(trim($v));
        $host = str_contains($v, '@') ? substr(strrchr($v, '@'), 1) : (string) parse_url(preg_match('~^https?://~', $v) ? $v : 'https://' . $v, PHP_URL_HOST);
        return preg_replace('/^www\./', '', $host);
    }

    /** ¿Mismo dominio? (o uno es subdominio del otro: tienda.empresa.es / empresa.es) */
    public static function mismoDominio(string $a, string $b): bool
    {
        $a = self::dominio($a);
        $b = self::dominio($b);
        if ($a === '' || $b === '') {
            return false;
        }
        return $a === $b || str_ends_with($a, '.' . $b) || str_ends_with($b, '.' . $a);
    }

    public static function correoGratuito(string $email): bool
    {
        return in_array(self::dominio($email), self::CORREOS_GRATUITOS, true);
    }

    // ================================================================ registrar (desde la ficha)

    /**
     * Guarda un envío del formulario. No escribe nada en la ficha.
     *
     * @param array<string,string> $datos campo => valor escrito
     * @return array{ok:bool, mensaje:string, errores:array<string,string>, guardados:int, confirmacion:bool}
     */
    public function registrar(int $companyId, array $datos, string $relacion, string $emailContacto, string $ip, string $ua, bool $en = false): array
    {
        $t = static fn (string $es, string $enTxt) => $en ? $enTxt : $es;
        $out = ['ok' => false, 'mensaje' => '', 'errores' => [], 'guardados' => 0, 'confirmacion' => false];

        $empresa = $this->empresa($companyId);
        if (!$empresa) {
            $out['mensaje'] = $t('La empresa no existe.', 'The company does not exist.');
            return $out;
        }

        $relacion = in_array($relacion, self::RELACIONES, true) ? $relacion : 'otro';
        $emailContacto = mb_strtolower(trim($emailContacto));
        if ($emailContacto !== '' && (!filter_var($emailContacto, FILTER_VALIDATE_EMAIL) || mb_strlen($emailContacto) > 190)) {
            $out['errores']['email_contacto'] = $t('Tu email no parece válido.', 'Your email does not look valid.');
        }
        if ($relacion === 'empresa' && $emailContacto === '') {
            $out['errores']['email_contacto'] = $t('Si eres de la empresa, déjanos tu email para confirmarlo.', 'If you work there, leave your email so we can confirm it.');
        }

        $recientes = $this->db->table('company_contributions')
            ->select('COUNT(DISTINCT envio) AS n')
            ->where('ip', $ip)
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-1 hour')))
            ->get()->getRowArray();
        if ((int) ($recientes['n'] ?? 0) >= self::MAX_IP_HORA) {
            $out['mensaje'] = $t('Has enviado muchos datos seguidos. Inténtalo más tarde.', 'You have sent a lot in a short time. Please try again later.');
            return $out;
        }

        $actual = [
            'telefono' => (string) ($empresa['phone'] ?? ''),
            'web'      => (string) ($empresa['website_official'] ?? ''),
            'correo'   => (string) ($empresa['email'] ?? ''),
        ];

        $filas = [];
        $yaEstaba = 0;
        foreach (self::CAMPOS as $campo) {
            $escrito = trim((string) ($datos[$campo] ?? ''));
            if ($escrito === '') {
                continue;
            }
            $n = self::normalizar($campo, $escrito, $en);
            if (isset($n['error'])) {
                $out['errores'][$campo] = $n['error'];
                continue;
            }
            if ($this->yaLoTiene($campo, $actual[$campo], $n['valor'])) {
                $yaEstaba++;
                continue;
            }
            $filas[$campo] = ['valor' => $n['valor'], 'escrito' => mb_substr($escrito, 0, 500)];
        }

        if ($out['errores']) {
            $out['mensaje'] = $t('Revisa los datos marcados.', 'Please check the highlighted fields.');
            return $out;
        }
        if (!$filas) {
            $out['mensaje'] = $yaEstaba ? $t('Ese dato ya lo tenemos. ¡Gracias!', 'We already have that. Thank you!') : $t('Escribe al menos un dato.', 'Enter at least one item.');
            $out['ok'] = $yaEstaba > 0;
            return $out;
        }

        $envio = bin2hex(random_bytes(16));
        $ahora = date('Y-m-d H:i:s');
        foreach ($filas as $campo => $f) {
            // El mismo dato ya aportado y pendiente: no se duplica.
            $dup = $this->db->table('company_contributions')
                ->where('company_id', $companyId)->where('campo', $campo)->where('valor', $f['valor'])
                ->whereIn('estado', ['pendiente', 'verificado'])
                ->countAllResults();
            if ($dup > 0) {
                continue;
            }
            $this->db->table('company_contributions')->insert([
                'company_id'     => $companyId,
                'envio'          => $envio,
                'campo'          => $campo,
                'valor'          => $f['valor'],
                'valor_escrito'  => $f['escrito'],
                'relacion'       => $relacion,
                'email_contacto' => $emailContacto !== '' ? $emailContacto : null,
                'estado'         => 'pendiente',
                'ip'             => mb_substr($ip, 0, 45),
                'user_agent'     => mb_substr($ua, 0, 255),
                'created_at'     => $ahora,
            ]);
            $out['guardados']++;
        }

        $out['ok'] = true;
        if ($out['guardados'] === 0) {
            $out['mensaje'] = $t('Ya nos habían enviado ese dato y lo estamos revisando. ¡Gracias!', 'Someone already sent us that and we are reviewing it. Thank you!');
            return $out;
        }

        // Enlace de confirmación: solo a un email del dominio de la web de la empresa
        // (la que ya tiene la ficha o la que aportan ahora). Así no mandamos correos a
        // direcciones cualquiera y el clic sirve para algo: prueba que tiene ese buzón.
        $webs = array_filter([$actual['web'], $filas['web']['valor'] ?? '']);
        $dominioOk = $emailContacto !== '' && !self::correoGratuito($emailContacto)
            && array_filter($webs, static fn ($w) => self::mismoDominio($emailContacto, $w));
        if ($relacion === 'empresa' && $dominioOk) {
            $out['confirmacion'] = $this->enviarConfirmacion($envio, $emailContacto, $empresa, array_keys($filas));
        }

        $this->avisarAdmin($empresa, $filas, $relacion, $emailContacto, $ip, $out['confirmacion']);

        $out['mensaje'] = $out['confirmacion']
            ? $t('Gracias. Te hemos enviado un correo a ' . $emailContacto . ': pulsa el enlace para confirmar que eres de la empresa y lo publicaremos antes.',
                 'Thank you. We have sent an email to ' . $emailContacto . ': click the link to confirm you work there and we will publish it sooner.')
            : $t('Gracias. Comprobaremos el dato antes de publicarlo en la ficha.', 'Thank you. We will check it before publishing it on this page.');
        return $out;
    }

    private function yaLoTiene(string $campo, string $actual, string $nuevo): bool
    {
        $actual = trim($actual);
        if ($actual === '' || $actual === '-') {
            return false;
        }
        if ($campo === 'telefono') {
            return str_contains(preg_replace('/\D/', '', $actual), $nuevo);
        }
        if ($campo === 'web') {
            return self::dominio($actual) === self::dominio($nuevo);
        }
        return mb_strtolower($actual) === $nuevo;
    }

    /** companies + company_enrichment de una empresa. */
    public function empresa(int $companyId): ?array
    {
        $row = $this->db->table('companies c')
            ->select('c.id, c.company_name, c.cif, c.phone, e.website_official, e.email')
            ->join('company_enrichment e', 'e.company_id = c.id', 'left')
            ->where('c.id', $companyId)
            ->get()->getRowArray();
        return $row ?: null;
    }

    // ================================================================ confirmar (enlace del correo)

    /**
     * Quien aporta pulsa el enlace. Solo anota la fecha: la decisión la toma verificar(),
     * que comprueba también que la web de ese dominio es de la empresa.
     *
     * @return array|null empresa del envío, o null si el enlace no vale
     */
    public function confirmar(string $envio): ?array
    {
        $empresa = $this->envioValido($envio);
        if ($empresa === null) {
            return null;
        }
        $this->db->table('company_contributions')->where('envio', $envio)->where('email_confirmado_at IS NULL', null, false)
            ->update(['email_confirmado_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);

        return $empresa;
    }

    /** Empresa del envío si el enlace existe y no ha caducado (14 días). */
    public function envioValido(string $envio): ?array
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $envio)) {
            return null;
        }
        $fila = $this->db->table('company_contributions')->select('company_id, created_at, email_contacto')
            ->where('envio', $envio)->orderBy('id', 'ASC')->limit(1)->get()->getRowArray();
        if (!$fila || strtotime($fila['created_at']) < strtotime('-14 days')) {
            return null;
        }
        $empresa = $this->empresa((int) $fila['company_id']);
        if ($empresa) {
            $empresa['email_contacto'] = (string) ($fila['email_contacto'] ?? '');
        }
        return $empresa;
    }

    // ================================================================ registro opcional

    /**
     * Enlace al alta rápida para quien dice ser de la empresa (15-10-2026): no se exige
     * registro para aportar, pero se ofrece justo después. Mismo flujo que "Avísame si
     * cambia" de la ficha (intent=view_risk_profile + redirect con ?vigilar=1): al volver,
     * la ficha pone su empresa en vigilancia. source=aportacion_ficha para medirlo.
     */
    public static function urlRegistro(array $empresa, string $email = ''): string
    {
        helper('url');
        $slug = url_title((string) ($empresa['company_name'] ?? ''), '-', true);
        $redirect = 'empresa/' . (int) ($empresa['id'] ?? 0) . ($slug !== '' ? '-' . $slug : '') . '?vigilar=1';
        return site_url('register/quick') . '?' . http_build_query(array_filter([
            'intent'   => 'view_risk_profile',
            'cif'      => (string) ($empresa['cif'] ?? ''),
            'redirect' => $redirect,
            'source'   => 'aportacion_ficha',
            'email'    => filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '',
        ]));
    }

    // ================================================================ verificar (comando)

    /**
     * Comprueba en la web de la empresa los datos pendientes de una empresa.
     * Escribe el resultado (estado/verificacion/detalle) salvo con $simular.
     *
     * @param array $filas filas pendientes de company_contributions de UNA empresa
     * @return array<int,array{verificado:bool, verificacion:string, detalle:string}> por id
     */
    public function verificarEmpresa(int $companyId, array $filas, bool $simular = false): array
    {
        $empresa = $this->empresa($companyId);
        $cif = strtoupper(trim((string) ($empresa['cif'] ?? '')));
        $out = [];

        // Webs candidatas: la de la ficha y las aportadas.
        $webs = [];
        if (!empty($empresa['website_official'])) {
            $webs[] = (string) $empresa['website_official'];
        }
        foreach ($filas as $f) {
            if ($f['campo'] === 'web') {
                $webs[] = $f['valor'];
            }
        }
        // Y las webs ya aportadas y comprobadas que aún no están en la ficha.
        foreach ($this->db->table('company_contributions')->select('valor')
            ->where('company_id', $companyId)->where('campo', 'web')->whereIn('estado', ['verificado', 'aprobado'])
            ->get()->getResultArray() as $w) {
            $webs[] = $w['valor'];
        }
        $webs = array_values(array_unique($webs));

        // Descargar cada web y buscar el CIF.
        $webOk = [];      // url => ['paginas' => [...], 'donde' => url con el CIF]
        $notasWeb = [];   // url => motivo
        foreach ($webs as $w) {
            if ($cif === '' || !preg_match('/^[A-Z]\d{7}[0-9A-J]$/', $cif)) {
                $notasWeb[$w] = 'La empresa no tiene un CIF válido con el que comprobar la web.';
                continue;
            }
            $r = $this->descargarWeb($w);
            if (!$r['paginas']) {
                $notasWeb[$w] = 'No se ha podido abrir la web: ' . $r['error'];
                continue;
            }
            $donde = null;
            foreach ($r['paginas'] as $p) {
                if (self::contieneCif($p['texto'], $cif)) {
                    $donde = $p['url'];
                    break;
                }
            }
            if ($donde !== null) {
                $webOk[$w] = ['paginas' => $r['paginas'], 'donde' => $donde];
            } else {
                $notasWeb[$w] = 'El CIF ' . $cif . ' no aparece en la web (' . count($r['paginas']) . ' páginas miradas).';
            }
        }

        foreach ($filas as $f) {
            $res = ['verificado' => false, 'verificacion' => '', 'detalle' => ''];

            if ($f['campo'] === 'web') {
                if (isset($webOk[$f['valor']])) {
                    $res = ['verificado' => true, 'verificacion' => 'cif_en_web', 'detalle' => 'CIF encontrado en ' . $webOk[$f['valor']]['donde']];
                } else {
                    $res['detalle'] = $notasWeb[$f['valor']] ?? 'Sin comprobar.';
                }
            } else {
                foreach ($webOk as $w => $info) {
                    foreach ($info['paginas'] as $p) {
                        $esta = $f['campo'] === 'telefono'
                            ? self::contieneTelefono($p['texto'], $f['valor'])
                            : str_contains(mb_strtolower($p['texto']), $f['valor']);
                        if ($esta) {
                            $res = ['verificado' => true, 'verificacion' => 'en_web_empresa', 'detalle' => 'Aparece en ' . $p['url'] . ' (web con el CIF de la empresa)'];
                            break 2;
                        }
                    }
                }
                if (!$res['verificado']) {
                    $res['detalle'] = $webOk
                        ? 'No aparece en la web de la empresa (' . implode(', ', array_keys($webOk)) . ').'
                        : ($webs ? 'No hay una web comprobada de la empresa donde buscarlo. ' . implode(' ', $notasWeb) : 'La empresa no tiene web donde buscarlo.');
                }
            }

            // Email confirmado de un dominio cuya web lleva el CIF: vale todo el envío.
            if (!$res['verificado'] && $f['relacion'] === 'empresa' && !empty($f['email_confirmado_at']) && !empty($f['email_contacto'])) {
                foreach (array_keys($webOk) as $w) {
                    if (self::mismoDominio($f['email_contacto'], $w)) {
                        $res = ['verificado' => true, 'verificacion' => 'email_dominio', 'detalle' => 'Lo envía ' . $f['email_contacto'] . ' (confirmado por correo), del dominio de ' . $w . ', que lleva el CIF.'];
                        break;
                    }
                }
            }

            $out[(int) $f['id']] = $res;

            if (!$simular) {
                $upd = [
                    'verificado_at'        => date('Y-m-d H:i:s'),
                    'verificacion_detalle' => mb_substr($res['detalle'], 0, 500),
                    'updated_at'           => date('Y-m-d H:i:s'),
                ];
                if ($res['verificado']) {
                    $upd['estado'] = 'verificado';
                    $upd['verificacion'] = $res['verificacion'];
                }
                $this->db->table('company_contributions')->where('id', $f['id'])->where('estado', 'pendiente')->update($upd);
            }
        }

        return $out;
    }

    /** El CIF en el texto, con o sin separadores ("B-86.854.536", "ES B86854536"). */
    public static function contieneCif(string $texto, string $cif): bool
    {
        // Cada carácter del CIF puede ir separado por espacio, punto, guion o barra.
        $patron = implode('[\\s.\\-\\/]?', array_map(static fn ($c) => preg_quote($c, '/'), str_split($cif)));
        return preg_match('/(?<![A-Z0-9])(?:ES[\\s\\-]?)?' . $patron . '(?![0-9])/u', mb_strtoupper($texto)) === 1;
    }

    /** El teléfono (9 cifras) en el texto, con espacios, puntos, guiones o +34 delante. */
    public static function contieneTelefono(string $texto, string $num): bool
    {
        $compacto = preg_replace('/(?<=\d)[\s.\-()]+(?=\d)/u', '', $texto);
        return preg_match('/(?<!\d)(?:\+?34|0034)?' . preg_quote($num, '/') . '(?!\d)/', $compacto) === 1;
    }

    // ================================================================ aplicar

    /**
     * Publica una aportación en la ficha con DataCorrectionService (misma validación,
     * mismas columnas, vacía la caché de Cloudflare).
     *
     * @param bool $soloSiVacio true (comando): no pisa un dato que la ficha ya tenga.
     *                          false (admin): sustituye lo que haya; el admin lo ha visto.
     */
    public function aplicar(array $fila, bool $soloSiVacio, string $nota = ''): array
    {
        $empresa = $this->empresa((int) $fila['company_id']);
        if (!$empresa) {
            return ['aplicado' => false, 'motivo' => 'La empresa no existe.'];
        }
        $col = ['telefono' => 'phone', 'web' => 'website_official', 'correo' => 'email'][$fila['campo']] ?? '';
        $actual = trim((string) ($empresa[$col] ?? ''));
        if ($soloSiVacio && $actual !== '' && $actual !== '-') {
            return ['aplicado' => false, 'motivo' => 'La ficha ya tiene ' . $fila['campo'] . ' (' . $actual . '): decide en el panel.'];
        }

        // original '' = "la ficha no tenía nada": DataCorrectionService no pisa si ha cambiado.
        // original null = el admin decide sustituir.
        $r = (new DataCorrectionService($this->db))->aplicar(
            (int) $fila['company_id'], $fila['campo'], $fila['valor'], '', false, true, $soloSiVacio ? '' : null
        );

        if ($r['aplicado']) {
            $this->db->table('company_contributions')->where('id', $fila['id'])->update([
                'estado'      => 'aprobado',
                'anterior'    => mb_substr((string) $r['anterior'], 0, 500),
                'revisado_at' => date('Y-m-d H:i:s'),
                'nota_admin'  => $nota !== '' ? mb_substr($nota, 0, 255) : null,
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        } elseif (($r['motivo'] ?? '') === 'La ficha ya tenía ese valor.') {
            $this->db->table('company_contributions')->where('id', $fila['id'])->update([
                'estado' => 'aprobado', 'revisado_at' => date('Y-m-d H:i:s'),
                'nota_admin' => 'La ficha ya tenía ese valor.', 'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return $r;
    }

    public function rechazar(int $id, string $nota = ''): void
    {
        $this->db->table('company_contributions')->where('id', $id)->update([
            'estado'      => 'rechazado',
            'revisado_at' => date('Y-m-d H:i:s'),
            'nota_admin'  => $nota !== '' ? mb_substr($nota, 0, 255) : null,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    // ================================================================ descargar una web (con cuidado)

    /**
     * Descarga la portada y las páginas legales/de contacto de una web.
     * La URL la escribe un usuario: solo http(s), puertos 80/443, nunca IPs privadas o
     * reservadas (también tras cada redirección), con la IP fijada para que no cambie entre
     * la comprobación y la conexión, tamaño y tiempo limitados.
     *
     * @return array{paginas: array<int,array{url:string,texto:string}>, error:string}
     */
    public function descargarWeb(string $url): array
    {
        $paginas = [];
        $portada = $this->get($url);
        if ($portada['html'] === null) {
            return ['paginas' => [], 'error' => $portada['error']];
        }
        $base = $portada['url'];
        $paginas[] = ['url' => $base, 'texto' => self::texto($portada['html'])];

        // Enlaces del mismo sitio a aviso legal, privacidad, contacto...
        $candidatas = [];
        if (preg_match_all('~<a\b[^>]*href\s*=\s*["\']([^"\'#]+)["\'][^>]*>(.*?)</a>~is', $portada['html'], $m, PREG_SET_ORDER)) {
            foreach ($m as $a) {
                $href = html_entity_decode(trim($a[1]), ENT_QUOTES);
                $txt  = mb_strtolower(strip_tags($a[2]) . ' ' . $href);
                if (!preg_match('/aviso|legal|privacidad|privacy|contact|cookies|quienes|qui[eé]nes|empresa|condiciones|imprint|about/u', $txt)) {
                    continue;
                }
                $abs = self::absoluta($base, $href);
                if ($abs !== null && self::mismoDominio($abs, $base)) {
                    $candidatas[$abs] = preg_match('/aviso|legal/u', $txt) ? 0 : 1;   // primero el aviso legal
                }
            }
        }
        asort($candidatas);
        $candidatas = array_keys($candidatas);
        if (!$candidatas) {
            $raiz = rtrim($base, '/');
            $candidatas = [$raiz . '/aviso-legal', $raiz . '/contacto'];
        }

        foreach (array_slice($candidatas, 0, self::MAX_PAGINAS - 1) as $u) {
            $r = $this->get($u);
            if ($r['html'] !== null) {
                $paginas[] = ['url' => $r['url'], 'texto' => self::texto($r['html'])];
            }
        }

        return ['paginas' => $paginas, 'error' => ''];
    }

    /** Texto visible + destinos de mailto:/tel: (a veces el dato solo está en el enlace). */
    public static function texto(string $html): string
    {
        $extra = '';
        if (preg_match_all('~href\s*=\s*["\'](?:mailto|tel):([^"\'?]+)~i', $html, $m)) {
            $extra = ' ' . implode(' ', array_map('rawurldecode', $m[1]));
        }
        $html = preg_replace('~<(script|style|noscript|svg)\b.*?</\1>~is', ' ', $html);
        $html = preg_replace('~<br\s*/?>|</(p|div|li|td|tr|h\d|span|a)>~i', ' ', $html);
        $t = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim(preg_replace('/\s+/u', ' ', $t . $extra));
    }

    private static function absoluta(string $base, string $href): ?string
    {
        if (preg_match('~^(mailto|tel|javascript|data):~i', $href)) {
            return null;
        }
        if (preg_match('~^https?://~i', $href)) {
            return $href;
        }
        $p = parse_url($base);
        $raiz = ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? '');
        if (str_starts_with($href, '//')) {
            return ($p['scheme'] ?? 'https') . ':' . $href;
        }
        if (str_starts_with($href, '/')) {
            return $raiz . $href;
        }
        $dir = isset($p['path']) ? preg_replace('~/[^/]*$~', '/', $p['path']) : '/';
        return $raiz . $dir . $href;
    }

    /** IP pública (ni privada, ni reservada, ni local). */
    public static function ipPublica(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /**
     * GET con las comprobaciones de seguridad, siguiendo hasta 4 redirecciones a mano.
     *
     * @return array{html:?string, url:string, error:string}
     */
    private function get(string $url): array
    {
        for ($salto = 0; $salto < 5; $salto++) {
            $p = parse_url($url);
            $esquema = strtolower($p['scheme'] ?? '');
            $host = strtolower($p['host'] ?? '');
            $puerto = $p['port'] ?? ($esquema === 'http' ? 80 : 443);
            if (!in_array($esquema, ['http', 'https'], true) || $host === '' || !in_array((int) $puerto, [80, 443], true)) {
                return ['html' => null, 'url' => $url, 'error' => 'dirección no admitida'];
            }
            if (filter_var($host, FILTER_VALIDATE_IP)) {
                return ['html' => null, 'url' => $url, 'error' => 'la dirección es una IP'];
            }
            $ips = @gethostbynamel($host) ?: [];
            if (!$ips) {
                return ['html' => null, 'url' => $url, 'error' => 'el dominio no existe'];
            }
            foreach ($ips as $ip) {
                if (!self::ipPublica($ip)) {
                    return ['html' => null, 'url' => $url, 'error' => 'el dominio apunta a una IP no pública'];
                }
            }

            $cuerpo = '';
            $demasiado = false;
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RESOLVE         => [$host . ':' . $puerto . ':' . $ips[0]],
                CURLOPT_FOLLOWLOCATION  => false,
                CURLOPT_PROTOCOLS       => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_CONNECTTIMEOUT  => 5,
                CURLOPT_TIMEOUT         => 10,
                CURLOPT_SSL_VERIFYPEER  => false,   // muchas webs pequeñas tienen el certificado caducado; solo leemos
                CURLOPT_SSL_VERIFYHOST  => 0,
                CURLOPT_USERAGENT       => 'Mozilla/5.0 (compatible; APIEmpresasBot/1.0; +https://apiempresas.es/fuentes-de-datos)',
                CURLOPT_HTTPHEADER      => ['Accept: text/html,application/xhtml+xml', 'Accept-Language: es-ES,es;q=0.9'],
                CURLOPT_ENCODING        => '',
                CURLOPT_HEADER          => false,
                CURLOPT_WRITEFUNCTION   => static function ($ch, $trozo) use (&$cuerpo, &$demasiado) {
                    $cuerpo .= $trozo;
                    if (strlen($cuerpo) > self::MAX_BYTES) {
                        $demasiado = true;
                        return 0;   // corta la descarga
                    }
                    return strlen($trozo);
                },
            ]);
            curl_exec($ch);
            $codigo = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $tipo = (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $destino = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
            $err = curl_error($ch);
            curl_close($ch);

            if ($codigo >= 300 && $codigo < 400 && $destino !== '') {
                $url = $destino;
                continue;
            }
            if ($codigo === 0) {
                return ['html' => null, 'url' => $url, 'error' => $demasiado ? 'página demasiado grande' : ($err ?: 'sin respuesta')];
            }
            if ($codigo >= 400) {
                return ['html' => null, 'url' => $url, 'error' => 'HTTP ' . $codigo];
            }
            if ($tipo !== '' && !preg_match('~text/html|application/xhtml~i', $tipo)) {
                return ['html' => null, 'url' => $url, 'error' => 'no es una página web (' . $tipo . ')'];
            }
            if (!mb_check_encoding($cuerpo, 'UTF-8')) {
                $cuerpo = mb_convert_encoding($cuerpo, 'UTF-8', 'ISO-8859-1');
            }
            return ['html' => $cuerpo, 'url' => $url, 'error' => ''];
        }
        return ['html' => null, 'url' => $url, 'error' => 'demasiadas redirecciones'];
    }

    // ================================================================ correos

    private function enviarConfirmacion(string $envio, string $to, array $empresa, array $campos): bool
    {
        $e = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $nombres = ['telefono' => 'el teléfono', 'web' => 'la web', 'correo' => 'el email'];
        $que = implode(', ', array_map(static fn ($c) => $nombres[$c] ?? $c, $campos));
        $link = site_url('aportacion/confirmar/' . $envio);

        $html = '<div style="font-family:-apple-system,Segoe UI,Roboto,sans-serif;max-width:560px;margin:0 auto;color:#334155;line-height:1.6">'
            . '<h2 style="color:#0f172a;font-size:18px">Confirma los datos de ' . $e($empresa['company_name'] ?? '') . '</h2>'
            . '<p>Hemos recibido ' . $e($que) . ' de <b>' . $e($empresa['company_name'] ?? '') . '</b> (' . $e($empresa['cif'] ?? '') . ') para su ficha en APIEmpresas.</p>'
            . '<p>Si lo has enviado tú, pulsa el botón para confirmar que eres de la empresa. Así lo publicamos antes.</p>'
            . '<p><a href="' . $e($link) . '" style="display:inline-block;background:#0f172a;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:700">Confirmar los datos</a></p>'
            . '<p style="font-size:13px;color:#64748b">Si no has sido tú, ignora este correo: no publicaremos nada sin comprobarlo.</p>'
            . '</div>';

        try {
            $email = Services::email();
            $email->clear(true);
            $email->setFrom(env('email.fromEmail', 'soporte@apiempresas.es'), env('email.fromName', 'APIEmpresas.es'));
            $email->setTo($to);
            $email->setSubject('Confirma los datos de ' . ($empresa['company_name'] ?? 'tu empresa'));
            $email->setMailType('html');
            $email->setMessage($html);
            if (!$email->send(false)) {
                log_message('error', '[Aportaciones] No se pudo enviar la confirmación: ' . $email->printDebugger(['headers']));
                return false;
            }
            return true;
        } catch (\Throwable $ex) {
            log_message('error', '[Aportaciones] Error enviando la confirmación: ' . $ex->getMessage());
            return false;
        }
    }

    private function avisarAdmin(array $empresa, array $filas, string $relacion, string $emailContacto, string $ip, bool $confirmacion): void
    {
        helper('company');
        $e = static fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $nombres = ['telefono' => 'Teléfono', 'web' => 'Web', 'correo' => 'Email'];
        $relaciones = ['empresa' => 'dice ser de la empresa', 'cliente' => 'cliente o proveedor', 'otro' => 'otro'];
        $ficha = function_exists('company_url') ? company_url(['cif' => $empresa['cif'] ?? '', 'name' => $empresa['company_name'] ?? '']) : '';

        $lineas = '';
        foreach ($filas as $c => $f) {
            $lineas .= '<li><b>' . $e($nombres[$c] ?? $c) . ':</b> ' . $e($f['valor']) . '</li>';
        }
        $html = '<div style="font-family:-apple-system,Segoe UI,Roboto,sans-serif;max-width:620px;color:#334155">'
            . '<h2 style="color:#0f172a;font-size:18px;margin-bottom:4px">Datos aportados</h2>'
            . '<p style="margin-top:0"><a href="' . $e($ficha) . '">' . $e($empresa['company_name'] ?? '') . '</a> · ' . $e($empresa['cif'] ?? '') . '</p>'
            . '<ul>' . $lineas . '</ul>'
            . '<p>Quién: ' . ($emailContacto !== '' ? $e($emailContacto) : '<em>anónimo</em>') . ' (' . $e($relaciones[$relacion] ?? $relacion) . ') · IP ' . $e($ip)
            . ($confirmacion ? ' · <b>le hemos mandado el enlace de confirmación</b>' : '') . '</p>'
            . '<p style="color:#64748b;font-size:13px">No se ha publicado nada. <code>php spark datos:verificar-aportaciones</code> lo busca en la web de la empresa.</p>'
            . '<p><a href="' . $e(site_url('admin/aportaciones')) . '" style="display:inline-block;background:#0f172a;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;font-weight:700">Revisar aportaciones</a></p>'
            . '</div>';

        try {
            $email = Services::email();
            $email->clear(true);
            $email->setFrom(env('email.fromEmail', 'soporte@apiempresas.es'), env('email.fromName', 'APIEmpresas.es'));
            $email->setTo(env('ADMIN_NOTIFY_EMAIL', 'papelo.amh@gmail.com'));
            if ($emailContacto !== '') {
                $email->setReplyTo($emailContacto);
            }
            $email->setSubject('➕ Datos aportados: ' . ($empresa['company_name'] ?? '') . ' (' . implode(', ', array_map(static fn ($c) => $nombres[$c] ?? $c, array_keys($filas))) . ')');
            $email->setMailType('html');
            $email->setMessage($html);
            $email->send(false);
        } catch (\Throwable $ex) {
            log_message('error', '[Aportaciones] Error avisando al admin: ' . $ex->getMessage());
        }
    }
}
