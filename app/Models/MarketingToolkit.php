<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingToolkit extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_slug',
        'tool_type',
        'name',
        'description',
        'content',
        'items',
        'meta',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'items' => 'array',
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCategory($query, string $slug)
    {
        return $query->where('category_slug', $slug);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('tool_type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
