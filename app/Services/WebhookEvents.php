<?php

namespace App\Services;

class WebhookEvents
{
    public const COMPANY_CREATED  = 'company.created';
    public const EXPORT_COMPLETED = 'export.completed';
    public const TEST_PING        = 'test.ping';

    // Eventos de la vigilancia (28-09-2026): salen de las empresas de /watchlist.
    public const COMPANY_BORME_ACT          = 'company.borme_act';
    public const COMPANY_STATUS_CHANGED     = 'company.status_changed';
    public const COMPANY_RISK_LEVEL_CHANGED = 'company.risk_level_changed';
    /** Suscripción a los tres eventos de la vigilancia. */
    public const WATCHLIST_ALL = 'watchlist.*';

    /**
     * Map of legacy aliases to canonical event names.
     */
    protected static array $aliases = [
        'new_company' => self::COMPANY_CREATED,
        'watchlist'   => self::WATCHLIST_ALL,
    ];

    /** Tipo de evento de la vigilancia (ApiWatchlistService) → evento de webhook. */
    public const FROM_WATCHLIST = [
        'borme_act'         => self::COMPANY_BORME_ACT,
        'status_change'     => self::COMPANY_STATUS_CHANGED,
        'risk_level_change' => self::COMPANY_RISK_LEVEL_CHANGED,
    ];

    /**
     * List of all supported canonical event names.
     *
     * @return string[]
     */
    public static function all(): array
    {
        return [
            self::COMPANY_CREATED,
            self::EXPORT_COMPLETED,
            self::TEST_PING,
            self::COMPANY_BORME_ACT,
            self::COMPANY_STATUS_CHANGED,
            self::COMPANY_RISK_LEVEL_CHANGED,
            self::WATCHLIST_ALL,
        ];
    }

    /**
     * Check if the given event name is valid (canonical or supported legacy alias).
     *
     * @param string $event
     * @return bool
     */
    public static function isValid(string $event): bool
    {
        $normalized = self::normalize($event);
        return in_array($normalized, self::all(), true);
    }

    /**
     * Normalize an event name, resolving legacy aliases to canonical names.
     *
     * @param string $event
     * @return string
     */
    public static function normalize(string $event): string
    {
        $trimmed = strtolower(trim($event));
        return self::$aliases[$trimmed] ?? $trimmed;
    }

    /**
     * Tipos de la vigilancia que recibe un webhook suscrito a $event
     * (vacío si el evento no es de la vigilancia).
     *
     * @return string[]
     */
    public static function watchlistTypesFor(string $event): array
    {
        $e = self::normalize($event);
        if ($e === self::WATCHLIST_ALL) {
            return array_keys(self::FROM_WATCHLIST);
        }
        $tipo = array_search($e, self::FROM_WATCHLIST, true);
        return $tipo === false ? [] : [$tipo];
    }
}
