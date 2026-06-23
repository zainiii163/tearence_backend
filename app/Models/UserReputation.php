<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReputation extends Model
{
    use HasFactory;

    protected $table = 'user_reputation';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'reputation_score',
        'posts_count',
        'comments_count',
        'helpful_count',
        'communities_count',
        'positive_reviews',
        'negative_reviews',
        'flags_received',
        'completed_deals',
        'badges',
    ];

    protected $casts = [
        'badges' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function incrementReputationScore($amount = 1)
    {
        $this->increment('reputation_score', $amount);
    }

    public function decrementReputationScore($amount = 1)
    {
        $this->decrement('reputation_score', $amount);
    }

    public function incrementPostsCount()
    {
        $this->increment('posts_count');
    }

    public function incrementCommentsCount()
    {
        $this->increment('comments_count');
    }

    public function incrementHelpfulCount()
    {
        $this->increment('helpful_count');
    }

    public function incrementCommunitiesCount()
    {
        $this->increment('communities_count');
    }

    public function incrementPositiveReviews()
    {
        $this->increment('positive_reviews');
    }

    public function incrementNegativeReviews()
    {
        $this->increment('negative_reviews');
    }

    public function incrementFlagsReceived()
    {
        $this->increment('flags_received');
    }

    public function incrementCompletedDeals()
    {
        $this->increment('completed_deals');
    }

    public function addBadge($badge)
    {
        $badges = $this->badges ?? [];
        if (!in_array($badge, $badges)) {
            $badges[] = $badge;
            $this->badges = $badges;
            $this->save();
        }
    }

    public function removeBadge($badge)
    {
        $badges = $this->badges ?? [];
        $key = array_search($badge, $badges);
        if ($key !== false) {
            unset($badges[$key]);
            $this->badges = array_values($badges);
            $this->save();
        }
    }

    public function hasBadge($badge)
    {
        $badges = $this->badges ?? [];
        return in_array($badge, $badges);
    }
}
