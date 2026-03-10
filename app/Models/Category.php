<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'category';

    protected $primaryKey = 'category_id';

    protected $casts = [
        'filter_config' => 'array',
        'posting_form_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function childs() {
        return $this->hasMany(Category::class, 'parent_id', 'category_id') ;
    }

    public function parent() {
        return $this->belongsTo(Category::class, 'parent_id', 'category_id');
    }

    /**
     * Get default filter configuration for this category
     */
    public function getDefaultFilterConfig(): array
    {
        return $this->filter_config ?? [
            'price_range' => true,
            'location' => true,
            'date_posted' => true,
            'sort_options' => ['newest', 'oldest', 'price_low', 'price_high', 'relevance'],
        ];
    }
}
