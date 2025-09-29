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
     * Cache of previously resolved closing status checks.
     *
     * @var array<int, bool>
     */
    private static array $closingStatusCache = [];

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
        if ($name === null) {
            return false;
        }

        $normalized = Str::of($name)
            ->lower()
            ->squish()
            ->ascii()
            ->value();

        foreach (self::CLOSING_KEYWORDS as $keyword) {
            if (Str::contains($normalized, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
