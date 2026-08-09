<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $table = 'conversations';

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'listing_id',
        'listing_type',
        'listing_key',
        'listing_title',
        'subject',
        'status',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public static function makeListingKey(?string $listingType, $listingId): string
    {
        $type = trim((string) $listingType);
        $id = trim((string) $listingId);

        if ($type === '' && $id === '') {
            return 'none';
        }

        return strtolower($type !== '' ? $type : 'listing').':'.($id !== '' ? $id : 'none');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'buyer_id', 'customer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'seller_id', 'customer_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany();
    }

    public function otherParticipantId(int $userId): int
    {
        return (int) $this->buyer_id === $userId
            ? (int) $this->seller_id
            : (int) $this->buyer_id;
    }

    public function involvesUser(int $userId): bool
    {
        return (int) $this->buyer_id === $userId || (int) $this->seller_id === $userId;
    }
}
