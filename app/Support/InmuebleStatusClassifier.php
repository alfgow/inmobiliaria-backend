<?php

namespace App\Support;

use App\Models\InmuebleStatus;
use Illuminate\Support\Str;

class InmuebleStatusClassifier
{
    /**
     * Keywords that indicate a closing / finalized status.
     *
     * @var array<int, string>
     */
    private const CLOSING_KEYWORDS = [
        'vendido',
        'rentado',
        'arrendado',
        'cerrado',
    ];

    /**
     * Normalized name that represents an available status.
     */
    private const AVAILABLE_STATUS_KEYWORD = 'disponible';

    /**
     * Cache of previously resolved closing status checks.
     *
     * @var array<int, bool>
     */
    private static array $closingStatusCache = [];

    /**
     * Cache of previously resolved available status checks.
     *
     * @var array<int, bool>
     */
    private static array $availableStatusCache = [];

    public static function isClosingStatusId(?int $statusId): bool
    {
        if (! $statusId) {
            return false;
        }

        if (array_key_exists($statusId, self::$closingStatusCache)) {
            return self::$closingStatusCache[$statusId];
        }

        $status = InmuebleStatus::query()->find($statusId);

        return self::$closingStatusCache[$statusId] = self::isClosingStatusName($status?->nombre);
    }

    public static function isClosingStatusName(?string $name): bool
    {
        $normalized = self::normalizeName($name);

        if ($normalized === null) {
            return false;
        }

        foreach (self::CLOSING_KEYWORDS as $keyword) {
            if (Str::contains($normalized, $keyword)) {
                return true;
            }
        }

        return false;
    }

    public static function isAvailableStatusId(?int $statusId): bool
    {
        if (! $statusId) {
            return false;
        }

        if (array_key_exists($statusId, self::$availableStatusCache)) {
            return self::$availableStatusCache[$statusId];
        }

        $status = InmuebleStatus::query()->find($statusId);

        return self::$availableStatusCache[$statusId] = self::isAvailableStatusName($status?->nombre);
    }

    public static function isAvailableStatusName(?string $name): bool
    {
        $normalized = self::normalizeName($name);

        if ($normalized === null) {
            return false;
        }

        return $normalized === self::AVAILABLE_STATUS_KEYWORD;
    }

    private static function normalizeName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        return Str::of($name)
            ->lower()
            ->squish()
            ->ascii()
            ->value();
    }
}
