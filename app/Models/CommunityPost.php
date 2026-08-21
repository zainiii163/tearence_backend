<?php

namespace App\Models;

use App\Helpers\MediaUrlHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'community_posts';
    protected $primaryKey = 'post_id';
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $appends = ['cover_image_url', 'media_urls'];

    protected $fillable = [
        'post_id',
        'user_id',
        'post_type',
        'advert_type',
        'advert_id',
        'title',
        'content',
        'cover_image',
        'media',
        'views_count',
        'comments_count',
        'reactions_count',
        'saves_count',
        'shares_count',
        'is_pinned',
        'is_featured',
        'is_sponsored',
        'is_verified',
        'is_flagged',
        'flag_reason',
        'tags',
        'category_id',
        'location',
        'country',
        'city',
        'discussion_type',
        'is_poll',
        'poll_options',
        'poll_ends_at',
        'poll_votes_count',
    ];

    protected $casts = [
        'media' => 'array',
        'tags' => 'array',
        'poll_options' => 'array',
        'poll_ends_at' => 'datetime',
        'is_poll' => 'boolean',
        'is_pinned' => 'boolean',
        'is_featured' => 'boolean',
        'is_sponsored' => 'boolean',
        'is_verified' => 'boolean',
        'is_flagged' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function communities()
    {
        return $this->belongsToMany(Community::class, 'community_post_communities', 'post_id', 'community_id')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    public function primaryCommunity()
    {
        return $this->belongsToMany(Community::class, 'community_post_communities', 'post_id', 'community_id')
                    ->wherePivot('is_primary', true);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id', 'post_id');
    }

    public function reactions()
    {
        return $this->hasMany(PostReaction::class, 'post_id', 'post_id');
    }

    public function savedBy()
    {
        return $this->hasMany(SavedPost::class, 'post_id', 'post_id');
    }

    public function pollVotes()
    {
        return $this->hasMany(CommunityPollVote::class, 'post_id', 'post_id');
    }

    public function isPollOpen(): bool
    {
        if (!$this->is_poll) {
            return false;
        }
        if (!$this->poll_ends_at) {
            return true;
        }
        return $this->poll_ends_at->isFuture();
    }

    /**
     * Attach vote tallies + current user vote for API responses.
     */
    public function withPollState(?int $userId = null): self
    {
        if (!$this->is_poll || !is_array($this->poll_options)) {
            return $this;
        }

        $options = collect($this->poll_options)->values()->map(function ($opt, $index) {
            $id = $opt['id'] ?? ('opt_'.($index + 1));
            return [
                'id' => (string) $id,
                'text' => (string) ($opt['text'] ?? ''),
                'votes' => (int) ($opt['votes'] ?? 0),
            ];
        })->all();

        $total = (int) ($this->poll_votes_count ?: collect($options)->sum('votes'));
        $this->poll_options = $options;
        $this->poll_votes_count = $total;
        $this->poll_is_open = $this->isPollOpen();
        $this->user_voted_option_id = null;

        if ($userId) {
            $vote = CommunityPollVote::where('post_id', $this->post_id)
                ->where('user_id', $userId)
                ->value('option_id');
            $this->user_voted_option_id = $vote;
        }

        return $this;
    }

    public function scopeAdThreads($query)
    {
        return $query->where('post_type', 'ad_thread');
    }

    public function scopeDiscussionThreads($query)
    {
        return $query->where('post_type', 'discussion_thread');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeSponsored($query)
    {
        return $query->where('is_sponsored', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeNotFlagged($query)
    {
        return $query->where('is_flagged', false);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeByCommunity($query, $communityId)
    {
        // Qualify column: both communities and community_post_communities have community_id
        // (ambiguous WHERE causes MySQL 500s on community feeds).
        return $query->whereHas('communities', function ($q) use ($communityId) {
            $q->where(function ($inner) use ($communityId) {
                $inner->where('communities.community_id', $communityId)
                    ->orWhere('communities.slug', $communityId);
            });
        });
    }

    public function scopeTrending($query)
    {
        return $query->orderBy('reactions_count', 'desc')
                     ->orderBy('comments_count', 'desc')
                     ->orderBy('views_count', 'desc');
    }

    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeTopRated($query)
    {
        return $query->orderBy('reactions_count', 'desc');
    }

    public function scopeNearMe($query, $lat, $lng, $radius = 50)
    {
        // This would require geolocation implementation
        // For now, return by city/country
        return $query->whereNotNull('city')->whereNotNull('country');
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementComments()
    {
        $this->increment('comments_count');
    }

    public function incrementReactions()
    {
        $this->increment('reactions_count');
    }

    public function incrementSaves()
    {
        $this->increment('saves_count');
    }

    public function incrementShares()
    {
        $this->increment('shares_count');
    }

    public function decrementComments()
    {
        $this->decrement('comments_count');
    }

    public function decrementReactions()
    {
        $this->decrement('reactions_count');
    }

    public function decrementSaves()
    {
        $this->decrement('saves_count');
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return MediaUrlHelper::resolve($this->cover_image);
    }

    /**
     * @return array<int, string>
     */
    public function getMediaUrlsAttribute(): array
    {
        return MediaUrlHelper::resolveMany($this->media);
    }
}
