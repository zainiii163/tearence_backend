<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookSave extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'saved_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'book_saves';

    /**
     * Get the book that was saved.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(BookAdvert::class, 'book_id');
    }

    /**
     * Get the user who saved the book.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to get saves by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to get recent saves.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('saved_at', '>=', now()->subDays($days));
    }
}
