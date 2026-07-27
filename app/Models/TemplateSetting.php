<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class TemplateSetting extends Model
{
    protected $table = 'template_settings';

    protected $fillable = [
        'key',
        'value',
        'label',
    ];

    public const DEFAULTS = [
        'premium_monthly_fee' => '5.00',
        'premium_duration_days' => '30',
    ];

    public static function getValue(string $key, ?string $default = null): string
    {
        $fallback = $default ?? (self::DEFAULTS[$key] ?? '');

        try {
            if (!Schema::hasTable('template_settings')) {
                return $fallback;
            }

            return Cache::remember("template_setting_{$key}", 60, function () use ($key, $fallback) {
                $row = static::query()->where('key', $key)->first();

                return $row && $row->value !== null && $row->value !== ''
                    ? (string) $row->value
                    : $fallback;
            });
        } catch (\Throwable $e) {
            return $fallback;
        }
    }

    public static function setValue(string $key, string $value, ?string $label = null): self
    {
        $row = static::query()->updateOrCreate(
            ['key' => $key],
            array_filter([
                'value' => $value,
                'label' => $label,
            ], fn ($v) => $v !== null)
        );

        Cache::forget("template_setting_{$key}");

        return $row;
    }

    public static function premiumMonthlyFee(): float
    {
        return (float) self::getValue('premium_monthly_fee', '5.00');
    }

    public static function premiumDurationDays(): int
    {
        return max(1, (int) self::getValue('premium_duration_days', '30'));
    }

    public static function publicSettings(): array
    {
        return [
            'premium_monthly_fee' => self::premiumMonthlyFee(),
            'premium_duration_days' => self::premiumDurationDays(),
            'currency' => 'USD',
        ];
    }
}
