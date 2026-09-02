<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Friendship between Social Hub users (users.user_id).
 *
 * Statuses:
 *  - pending    request sent, awaiting addressee response
 *  - accepted   mutual friendship
 *  - declined   addressee declined the request
 *  - cancelled  requester cancelled a pending request
 *  - blocked    requester blocked addressee
 */
class Friendship extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_BLOCKED = 'blocked';

    protected $table = 'friendships';

    protected $fillable = [
        'requester_id',
        'addressee_id',
        'status',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id', 'user_id');
    }

    public function addressee()
    {
        return $this->belongsTo(User::class, 'addressee_id', 'user_id');
    }

    /**
     * Whether the given user is a participant on either side.
     */
    public function involves($userId): bool
    {
        return (int) $this->requester_id === (int) $userId
            || (int) $this->addressee_id === (int) $userId;
    }

    /**
     * The "other" participant given one side.
     */
    public function other($userId): ?int
    {
        if ((int) $this->requester_id === (int) $userId) {
            return (int) $this->addressee_id;
        }
        if ((int) $this->addressee_id === (int) $userId) {
            return (int) $this->requester_id;
        }
        return null;
    }

    public static function areFriends($aId, $bId): bool
    {
        return static::where('status', self::STATUS_ACCEPTED)
            ->where(function ($q) use ($aId, $bId) {
                $q->where('requester_id', $aId)->where('addressee_id', $bId)
                  ->orWhere('requester_id', $bId)->where('addressee_id', $aId);
            })
            ->exists();
    }

    /**
     * Find any existing friendship row between two users (either direction).
     */
    public static function between($aId, $bId): ?self
    {
        return static::where(function ($q) use ($aId, $bId) {
            $q->where('requester_id', $aId)->where('addressee_id', $bId)
              ->orWhere('requester_id', $bId)->where('addressee_id', $aId);
        })->first();
    }
}
