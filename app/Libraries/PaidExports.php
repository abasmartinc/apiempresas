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
 * Vive en la sesión: es el arreglo inmediato. El definitivo es una tabla de
 * compras con token (ver claude/auditoria-descarga-listados.md, C1/A4).
 */
class PaidExports
{
    private const KEY = 'paid_exports';
    private const TTL = 7 * 86400; // 7 días

    /** Tipos válidos → ruta de descarga */
    public const KINDS = ['excel', 'subsidies', 'contracts'];

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
