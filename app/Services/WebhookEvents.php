<?php

namespace App\Services;

class WebhookEvents
{
    public const COMPANY_CREATED  = 'company.created';
    public const EXPORT_COMPLETED = 'export.completed';
    public const TEST_PING        = 'test.ping';

    /**
     * Map of legacy aliases to canonical event names.
     */
    protected static array $aliases = [
        'new_company' => self::COMPANY_CREATED,
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
}