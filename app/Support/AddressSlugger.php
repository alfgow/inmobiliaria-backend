<?php

namespace App\Support;

use App\Models\Inmueble;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AddressSlugger
{
    public static function forInmueble(Inmueble $inmueble): string
    {
        $components = Arr::where([
            $inmueble->direccion,
            $inmueble->ciudad,
            $inmueble->estado,
        ], fn ($value) => filled($value));

        $slug = static::fromArray($components);

        if ($slug !== '') {
            return $slug;
        }

        return 'inmueble_' . $inmueble->id;
    }

    /**
     * @param  array<int, string|null>  $values
     */
    public static function fromArray(array $values): string
    {
        $value = trim(implode(' ', array_filter($values, fn ($item) => filled($item))));

        if ($value === '') {
            return '';
        }

        return static::slugify($value);
    }

    public static function slugify(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->toString();
    }
}
