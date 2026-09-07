<?php

namespace App\Support;

/**
 * Country → currency mapping for Filament pricing forms.
 */
class CountryCurrencyOptions
{
    /** ISO-2 → [code, symbol] */
    private const MAP = [
        'US' => ['USD', '$'], 'GB' => ['GBP', '£'], 'EU' => ['EUR', '€'], 'IE' => ['EUR', '€'],
        'FR' => ['EUR', '€'], 'DE' => ['EUR', '€'], 'ES' => ['EUR', '€'], 'IT' => ['EUR', '€'],
        'NL' => ['EUR', '€'], 'BE' => ['EUR', '€'], 'PT' => ['EUR', '€'], 'AT' => ['EUR', '€'],
        'FI' => ['EUR', '€'], 'GR' => ['EUR', '€'], 'CA' => ['CAD', 'C$'], 'AU' => ['AUD', 'A$'],
        'NZ' => ['NZD', 'NZ$'], 'JP' => ['JPY', '¥'], 'CN' => ['CNY', '¥'], 'IN' => ['INR', '₹'],
        'PK' => ['PKR', 'Rs'], 'BD' => ['BDT', '৳'], 'NG' => ['NGN', '₦'], 'ZA' => ['ZAR', 'R'],
        'KE' => ['KES', 'KSh'], 'GH' => ['GHS', 'GH₵'], 'AE' => ['AED', 'د.إ'], 'SA' => ['SAR', '﷼'],
        'QA' => ['QAR', '﷼'], 'KW' => ['KWD', 'د.ك'], 'CH' => ['CHF', 'CHF'], 'SE' => ['SEK', 'kr'],
        'NO' => ['NOK', 'kr'], 'DK' => ['DKK', 'kr'], 'PL' => ['PLN', 'zł'], 'CZ' => ['CZK', 'Kč'],
        'TR' => ['TRY', '₺'], 'BR' => ['BRL', 'R$'], 'MX' => ['MXN', 'Mex$'], 'AR' => ['ARS', '$'],
        'CL' => ['CLP', '$'], 'CO' => ['COP', '$'], 'PE' => ['PEN', 'S/'], 'SG' => ['SGD', 'S$'],
        'HK' => ['HKD', 'HK$'], 'KR' => ['KRW', '₩'], 'TH' => ['THB', '฿'], 'MY' => ['MYR', 'RM'],
        'PH' => ['PHP', '₱'], 'ID' => ['IDR', 'Rp'], 'VN' => ['VND', '₫'], 'EG' => ['EGP', 'E£'],
        'MA' => ['MAD', 'د.م.'], 'TZ' => ['TZS', 'TSh'], 'UG' => ['UGX', 'USh'], 'RW' => ['RWF', 'FRw'],
        'JM' => ['JMD', 'J$'], 'TT' => ['TTD', 'TT$'], 'BB' => ['BBD', 'Bds$'], 'RU' => ['RUB', '₽'],
        'UA' => ['UAH', '₴'], 'IL' => ['ILS', '₪'], 'RO' => ['RON', 'lei'], 'HU' => ['HUF', 'Ft'],
    ];

    /**
     * Options keyed by country name: "🇬🇧 United Kingdom (GBP £)"
     *
     * @return array<string, string>
     */
    public static function byCountryName(): array
    {
        $out = [];
        foreach (WorldCountries::all() as $row) {
            $iso = strtoupper((string) ($row['iso_code'] ?? ''));
            $name = (string) ($row['name'] ?? '');
            if ($name === '' || $iso === '') {
                continue;
            }
            [$code, $symbol] = self::MAP[$iso] ?? ['USD', '$'];
            $flag = WorldCountries::flagEmoji($iso) ?: '🏳️';
            $out[$name] = "{$flag} {$name} ({$code} {$symbol})";
        }

        asort($out);

        return $out;
    }

    public static function currencyCodeForCountryName(?string $countryName): string
    {
        $iso = self::isoForCountryName($countryName);
        if (! $iso) {
            return 'USD';
        }

        return self::MAP[$iso][0] ?? 'USD';
    }

    public static function symbolForCountryName(?string $countryName): string
    {
        $iso = self::isoForCountryName($countryName);
        if (! $iso) {
            return '$';
        }

        return self::MAP[$iso][1] ?? '$';
    }

    public static function symbolForCurrency(?string $currency): string
    {
        $code = strtoupper((string) $currency);
        foreach (self::MAP as [$c, $symbol]) {
            if ($c === $code) {
                return $symbol;
            }
        }

        return '$';
    }

    public static function isoForCountryName(?string $countryName): ?string
    {
        $needle = strtolower(trim((string) $countryName));
        if ($needle === '') {
            return null;
        }

        // Common aliases
        if (in_array($needle, ['uk', 'u.k.', 'great britain', 'britain', 'england'], true)) {
            return 'GB';
        }
        if (in_array($needle, ['usa', 'u.s.', 'u.s.a.', 'united states of america', 'america'], true)) {
            return 'US';
        }

        foreach (WorldCountries::all() as $row) {
            if (strtolower((string) $row['name']) === $needle) {
                return strtoupper((string) $row['iso_code']);
            }
        }

        return null;
    }
}
