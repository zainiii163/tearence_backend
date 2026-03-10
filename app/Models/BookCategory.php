<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'book_categories';

    /**
     * Get the books in the category.
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'genre', 'name');
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return asset('placeholder.png');
    }

    /**
     * Increment the books count.
     */
    public function incrementBooksCount()
    {
        $this->increment('books_count');
    }

    /**
     * Update active books count.
     */
    public function updateActiveBooksCount()
    {
        $activeCount = $this->books()->active()->count();
        $this->update(['active_books_count' => $activeCount]);
    }
}
