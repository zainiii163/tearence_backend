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
        'sort_order',
        'views',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_catalog' => 'boolean',
        'views' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = ['display_price'];

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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
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
