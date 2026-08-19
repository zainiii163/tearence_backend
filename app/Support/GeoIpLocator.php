<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoIpLocator
{
    /**
     * Resolve city/country from the visitor IP first, then request fallback.
     * Client city/country is only used when IP lookup cannot run (localhost / private IP).
     *
     * @return array{city:?string,country:?string,country_code:?string,ip:?string}
     */
    public static function locate(Request $request): array
    {
        $ip = $request->ip();
        $city = null;
        $country = null;
        $countryCode = null;

        $cf = strtoupper((string) $request->header('CF-IPCountry', ''));
        if ($cf && $cf !== 'XX' && $cf !== 'T1') {
            $countryCode = $cf;
            $country = self::countryNameFromCode($cf);
        }

        if ($ip && ! self::isPrivateIp($ip)) {
            try {
                $res = Http::timeout(2)->get("https://ipapi.co/{$ip}/json/");
                if ($res->ok()) {
                    $json = $res->json();
                    $city = trim((string) ($json['city'] ?? '')) ?: $city;
                    $country = trim((string) ($json['country_name'] ?? '')) ?: $country;
                    $code = strtoupper((string) ($json['country'] ?? $json['country_code'] ?? ''));
                    if (strlen($code) === 2) {
                        $countryCode = $code;
                    }
                }
            } catch (\Throwable $e) {
                Log::debug('GeoIP lookup skipped: '.$e->getMessage());
            }
        }

        if (! $city) {
            $city = trim((string) $request->input('city', '')) ?: null;
        }
        if (! $country) {
            $fallbackCountry = trim((string) $request->input('country', ''));
            if ($fallbackCountry !== '') {
                if (strlen($fallbackCountry) === 2) {
                    $countryCode = $countryCode ?: strtoupper($fallbackCountry);
                    $country = self::countryNameFromCode($fallbackCountry);
                } else {
                    $country = $fallbackCountry;
                }
            }
        }

        return [
            'city' => $city,
            'country' => $country,
            'country_code' => $countryCode,
            'ip' => $ip,
        ];
    }

    public static function countryNameFromCode(?string $code): ?string
    {
        $code = strtoupper(trim((string) $code));
        if (strlen($code) !== 2) {
            return $code !== '' ? $code : null;
        }

        if (function_exists('locale_get_display_region')) {
            $name = locale_get_display_region('-'.$code, 'en');
            if (is_string($name) && $name !== '' && strtoupper($name) !== $code) {
                return $name;
            }
        }

        return $code;
    }

    public static function normalizeCountry(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }
        if (strlen($value) === 2) {
            $named = self::countryNameFromCode($value);
            return mb_strtolower($named ?: $value);
        }

        return mb_strtolower($value);
    }

    protected static function isPrivateIp(string $ip): bool
    {
        return ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
