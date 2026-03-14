<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BuySellCategory extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $keyType = 'string';
    
    public $incrementing = false;

    protected $table = 'buysell_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image_url',
        'parent_id',
        'level',
        'sort_order',
        'is_active',
        'advert_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
        'sort_order' => 'integer',
        'advert_count' => 'integer',
    ];

    public function adverts(): HasMany
    {
        return $this->hasMany(BuySellAdvert::class, 'category_id');
    }

    public function activeAdverts(): HasMany
    {
        return $this->hasMany(BuySellAdvert::class, 'category_id')
            ->where('status', 'active');
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(BuySellCategory::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(BuySellCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(BuySellCategory::class, 'parent_id')
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc');
    }

    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function updateAdvertCount()
    {
        $this->update(['advert_count' => $this->activeAdverts()->count()]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            
            if (empty($category->level)) {
                $category->level = $category->parent_id ? 2 : 1;
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            
            if ($category->isDirty('parent_id')) {
                $category->level = $category->parent_id ? 2 : 1;
            }
        });
    }
}
