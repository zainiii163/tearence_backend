<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class MediaUrlHelper
{
    /**
     * Resolve a stored path or absolute URL to a public media URL.
     * Rewrites localhost/127.0.0.1 storage URLs to the configured public host.
     */
    public static function resolve(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim($value);

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return self::rewriteLocalStorageUrl($value);
        }

        if (str_starts_with($value, '/storage/')) {
            return rtrim(self::publicBaseUrl(), '/') . $value;
        }

        if (str_starts_with($value, 'storage/')) {
            return rtrim(self::publicBaseUrl(), '/') . '/' . ltrim($value, '/');
        }

        // Relative disk path e.g. properties/cover/xxx.jpg
        return self::rewriteLocalStorageUrl(
            Storage::disk('public')->url(ltrim($value, '/'))
        );
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

    public static function publicBaseUrl(): string
    {
        if ($custom = env('MEDIA_PUBLIC_URL')) {
            return rtrim($custom, '/');
        }

        if (app()->environment('production')) {
            return 'https://api.worldwideadverts.info';
        }

        $url = rtrim((string) config('app.url'), '/');

        return $url !== '' ? $url : 'http://127.0.0.1:8000';
    }

    public static function rewriteLocalStorageUrl(string $url): string
    {
        if (preg_match(
            '#^https?://(?:127\.0\.0\.1|localhost)(?::\d+)?/storage/(.+)$#i',
            $url,
            $m
        )) {
            return rtrim(self::publicBaseUrl(), '/') . '/storage/' . $m[1];
        }

        return $url;
    }

    /** Whether a public-disk relative path exists on disk. */
    public static function existsOnPublicDisk(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        $path = $value;

        if (preg_match('#/storage/(.+)$#i', $value, $m)) {
            $path = $m[1];
        } elseif (str_starts_with($value, 'storage/')) {
            $path = substr($value, strlen('storage/'));
        } elseif (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            // Absolute remote URL — cannot verify local disk from URL alone
            if (preg_match('#/storage/(.+)$#i', parse_url($value, PHP_URL_PATH) ?? '', $m)) {
                $path = $m[1];
            } else {
                return false;
            }
        }

        return Storage::disk('public')->exists(ltrim(urldecode($path), '/'));
    }
}
