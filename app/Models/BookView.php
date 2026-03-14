<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookView extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'book_views';

    /**
     * Get the book that was viewed.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(BookAdvert::class, 'book_id');
    }

    /**
     * Get the user who viewed the book (if logged in).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to get views by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to get views by book.
     */
    public function scopeByBook($query, $bookId)
    {
        return $query->where('book_id', $bookId);
    }

    /**
     * Scope a query to get recent views.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to get unique views (by IP address).
     */
    public function scopeUnique($query)
    {
        return $query->distinct('ip_address');
    }
}
