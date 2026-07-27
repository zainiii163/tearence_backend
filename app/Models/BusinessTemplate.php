<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BusinessTemplate extends Model
{
    use HasFactory;

    protected $table = 'business_templates';

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'blurb',
        'description',
        'vertical',
        'category_slug',
        'headline',
        'section_description',
        'price',
        'price_label',
        'currency',
        'template_type',
        'preview_image',
        'file_url',
        'status',
        'is_catalog',
        'is_premium',
        'premium_until',
        'premium_fee_paid',
        'sort_order',
        'views',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'premium_fee_paid' => 'decimal:2',
        'is_catalog' => 'boolean',
        'is_premium' => 'boolean',
        'premium_until' => 'datetime',
        'views' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = ['display_price', 'is_premium_active'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function getDisplayPriceAttribute(): string
    {
        if (!empty($this->price_label)) {
            return $this->price_label;
        }

        $symbol = $this->currency === 'GBP' ? '£' : ($this->currency === 'EUR' ? '€' : '$');
        $amount = number_format((float) $this->price, 0);

        return "From {$symbol}{$amount}";
    }

    public function getIsPremiumActiveAttribute(): bool
    {
        if (!$this->is_premium) {
            return false;
        }

        if (!$this->premium_until) {
            return true;
        }

        return $this->premium_until->isFuture();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePremiumActive(Builder $query): Builder
    {
        return $query->where('is_premium', true)
            ->where(function (Builder $q) {
                $q->whereNull('premium_until')
                    ->orWhere('premium_until', '>', now());
            });
    }

    public function scopeForCategory(Builder $query, string $vertical, ?string $categorySlug = null): Builder
    {
        $query->where('vertical', $vertical);

        if ($categorySlug && $categorySlug !== '') {
            $query->where(function (Builder $q) use ($categorySlug) {
                $q->where('category_slug', $categorySlug)
                    ->orWhere('category_slug', 'default');
            });
        }

        return $query;
    }

    public function applyPremium(?int $days = null, ?float $feePaid = null): void
    {
        $days = $days ?? TemplateSetting::premiumDurationDays();
        $base = ($this->premium_until && $this->premium_until->isFuture())
            ? $this->premium_until->copy()
            : now();

        $this->forceFill([
            'is_premium' => true,
            'premium_until' => $base->addDays($days),
            'premium_fee_paid' => $feePaid ?? TemplateSetting::premiumMonthlyFee(),
        ])->save();
    }

    public function clearPremium(): void
    {
        $this->forceFill([
            'is_premium' => false,
            'premium_until' => null,
        ])->save();
    }

    public static function makeSlug(string $title, string $vertical, string $categorySlug): string
    {
        $base = Str::slug("{$vertical}-{$categorySlug}-{$title}");
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
