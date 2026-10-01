<?php

namespace App\Libraries;

/**
 * Permisos de descarga de listados PAGADOS.
 *
 * Antes las rutas de exportación (billing/export-excel, export-subsidies,
 * export-contracts, checkout/radar-email) se abrían a cualquiera con sesión
 * iniciada o con la marca `just_bought_excel`, y los filtros salían del GET:
 * una cuenta gratuita descargaba toda España, y quien compraba un listado de
 * 9 € podía bajarse cualquier otro cambiando la URL.
 *
 * Ahora Billing::success, SOLO tras comprobar en Stripe que el pago está cobrado,
 * concede un permiso con los filtros congelados de lo que se pagó. La descarga
 * lleva el token (`?t=`) y el exportador usa los filtros guardados aquí, nunca
 * los de la URL.
 *
 * Dos formas de demostrar la compra:
 *  - `?t=`: permiso en la sesión PHP, concedido por la página de éxito (7 días).
 *  - `?session_id=cs_…`: el enlace del correo de descarga. Cada uso pregunta a
 *    Stripe si esa sesión de pago es de un listado y está cobrada, y toma los
 *    filtros de su metadata. Funciona sin sesión, desde cualquier dispositivo y
 *    también para compras como invitado (30 días desde el pago). Es el mismo
 *    patrón que los pedidos a medida (PedidoMedidaService::sesionCobrada).
 */
class PaidExports
{
    private const KEY = 'paid_exports';
    private const TTL = 7 * 86400; // 7 días

    /** Validez del enlace del correo, desde el pago */
    public const TTL_CORREO = 30 * 86400;

    /** Tipos válidos → ruta de descarga */
    public const KINDS = ['excel', 'subsidies', 'contracts'];

    /** Planes de Stripe que son listados descargables (lookalike va aparte: fichero en sesión) */
    public const PLANES = ['directory_single', 'radar_single', 'subsidies_single', 'contracts_single'];

    /** Tipos de contexto de exportación que se pueden descargar */
    public const TIPOS = ['excel', 'directory_excel', 'subsidies_excel', 'contracts_excel'];

    /** Ruta de descarga de cada tipo */
    public const RUTAS = [
        'excel'     => 'billing/export-excel',
        'subsidies' => 'billing/export-subsidies',
        'contracts' => 'billing/export-contracts',
    ];

    /**
     * Del contexto de la compra (metadata `export_context`) a [tipo, filtros].
     * Es la única traducción: la usan la página de éxito y el enlace del correo.
     *
     * @return array{0: string, 1: array}
     */
    public static function fromContext(array $ctx): array
    {
        $type  = (string) ($ctx['type'] ?? '');
        $isDir = $type === 'directory_excel';

        if ($type === 'subsidies_excel') {
            $params = ['convocatoria' => $ctx['convocatoria'] ?? '', 'year' => $ctx['year'] ?? ''];
        } elseif ($type === 'contracts_excel') {
            $params = ['year' => $ctx['year'] ?? '', 'organo' => $ctx['organo'] ?? ''];
        } else {
            $params = [
                'sector'        => $ctx['sector'] ?? 'General',
                'provincia'     => $ctx['provincia'] ?? 'España',
                'period'        => $isDir ? 'general' : ($ctx['period'] ?? '30days'),
                'is_historical' => $isDir ? '1' : '0',
            ];
        }
        // Filtros que entraron en el precio
        foreach (['cnae_text', 'estado', 'has_phone', 'date_min', 'date_max', 'municipio'] as $filtro) {
            if (!empty($ctx[$filtro])) {
                $params[$filtro] = $ctx[$filtro];
            }
        }
        if (!empty($ctx['cnae'])) {
            $params['cnae']          = $ctx['cnae'];
            $params['is_historical'] = '1';
            $params['period']        = 'general';
        }

        $kind = match ($type) {
            'subsidies_excel' => 'subsidies',
            'contracts_excel' => 'contracts',
            default           => 'excel',
        };

        return [$kind, $params];
    }

    /**
     * Sesión de Stripe de un listado, cobrada y con menos de 30 días; o null.
     * Devuelve [sesión, contexto].
     */
    public static function sesionCobrada(?string $sessionId): ?array
    {
        $sessionId = (string) $sessionId;
        if (!preg_match('/^cs_(live|test)_[A-Za-z0-9]{10,}$/', $sessionId)) {
            return null;
        }

        try {
            $s = (new \Stripe\StripeClient(env('STRIPE_SECRET_KEY')))
                ->checkout->sessions->retrieve($sessionId);
        } catch (\Throwable $e) {
            log_message('error', '[PaidExports] No se pudo consultar la sesión ' . $sessionId . ': ' . $e->getMessage());
            return null;
        }

        if (!in_array((string) ($s->metadata->plan ?? ''), self::PLANES, true)
            || !in_array((string) ($s->payment_status ?? ''), ['paid', 'no_payment_required'], true)
            || ((int) ($s->created ?? 0)) < time() - self::TTL_CORREO) {
            return null;
        }

        $ctx = json_decode((string) ($s->metadata->export_context ?? ''), true);
        if (!is_array($ctx) || !in_array($ctx['type'] ?? '', self::TIPOS, true)) {
            return null;
        }

        return [$s, $ctx];
    }

    /**
     * Filtros pagados según el enlace del correo (`?session_id=`), o null.
     */
    public static function fromStripeSession(?string $sessionId, string $kind): ?array
    {
        $cobrada = self::sesionCobrada($sessionId);
        if ($cobrada === null) {
            return null;
        }
        [$k, $params] = self::fromContext($cobrada[1]);

        return $k === $kind ? $params : null;
    }

    /**
     * Comprobación común de las rutas de descarga: token de sesión o enlace del correo.
     */
    public static function autorizar(\CodeIgniter\HTTP\IncomingRequest $request, string $kind): ?array
    {
        $t = $request->getGet('t') ?? $request->getPost('t');
        if ($t) {
            return self::get((string) $t, $kind);
        }

        return self::fromStripeSession((string) ($request->getGet('session_id') ?? ''), $kind);
    }

    /**
     * Enlace permanente (30 días) de descarga para el correo.
     */
    public static function urlCorreo(string $sessionId, array $ctx): string
    {
        [$kind] = self::fromContext($ctx);

        return site_url(self::RUTAS[$kind]) . '?' . http_build_query(['session_id' => $sessionId]);
    }

    public static function grant(string $kind, array $params, string $stripeSessionId = ''): string
    {
        $all = self::purge((array) (session(self::KEY) ?? []));

        // Una misma sesión de Stripe reutiliza su token (recargas de la página de éxito)
        if ($stripeSessionId !== '') {
            foreach ($all as $token => $p) {
                if (($p['stripe'] ?? '') === $stripeSessionId && ($p['kind'] ?? '') === $kind) {
                    return (string) $token;
                }
            }
        }

        $token = bin2hex(random_bytes(16));
        $all[$token] = [
            'kind'    => $kind,
            'params'  => $params,
            'stripe'  => $stripeSessionId,
            'expires' => time() + self::TTL,
        ];
        session()->set(self::KEY, $all);

        return $token;
    }

    /**
     * Filtros pagados para ese token y tipo, o null si no hay permiso.
     */
    public static function get(?string $token, string $kind): ?array
    {
        $token = (string) $token;
        if ($token === '' || !preg_match('/^[a-f0-9]{32}$/', $token)) {
            return null;
        }

        $all = self::purge((array) (session(self::KEY) ?? []));
        $p   = $all[$token] ?? null;
        if (!$p || ($p['kind'] ?? '') !== $kind) {
            return null;
        }

        return (array) ($p['params'] ?? []);
    }

    private static function purge(array $all): array
    {
        $now = time();
        foreach ($all as $token => $p) {
            if ((int) ($p['expires'] ?? 0) < $now) {
                unset($all[$token]);
            }
        }

        return $all;
    }
}
