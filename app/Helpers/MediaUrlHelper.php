<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class MediaUrlHelper
{
    public static function resolve(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (str_starts_with($value, '/storage/')) {
            return rtrim(config('app.url'), '/') . $value;
        }

        if (str_starts_with($value, 'storage/')) {
            return rtrim(config('app.url'), '/') . '/' . $value;
        }

        return Storage::disk('public')->url(ltrim($value, '/'));
    }

    /**
     * @param  array<int, string>|null  $values
     * @return array<int, string>
     */
    public static function resolveMany(?array $values): array
    {
        if (empty($values)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($value) => self::resolve(is_string($value) ? $value : null),
            $values
        )));
    }
}
