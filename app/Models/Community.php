<?php

namespace App\Models;

use App\Helpers\MediaUrlHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Community extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'communities';
    protected $primaryKey = 'community_id';
    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $appends = ['cover_image_url'];

    protected $fillable = [
        'community_id',
        'name',
        'slug',
        'description',
        'category_id',
        'cover_image',
        'scope',
        'region',
        'city',
        'members_count',
        'posts_count',
        'active_ads_count',
        'is_verified',
        'is_featured',
        'strict_moderation',
        'beginner_friendly',
        'rules',
        'created_by',
    ];

    protected $casts = [
        'rules' => 'array',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'strict_moderation' => 'boolean',
        'beginner_friendly' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function members()
    {
        return $this->hasMany(CommunityMember::class, 'community_id', 'community_id');
    }

    public function posts()
    {
        return $this->belongsToMany(CommunityPost::class, 'community_post_communities', 'community_id', 'post_id')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    public function followers()
    {
        return $this->hasMany(CommunityFollow::class, 'community_id', 'community_id');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByScope($query, $scope)
    {
        return $query->where('scope', $scope);
    }

    public function scopeByRegion($query, $region)
    {
        return $query->where('region', $region);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeTrending($query)
    {
        return $query->orderBy('posts_count', 'desc')
                     ->orderBy('members_count', 'desc');
    }

    public function incrementMembersCount()
    {
        $this->increment('members_count');
    }

    public function decrementMembersCount()
    {
        $this->decrement('members_count');
    }

    public function incrementPostsCount()
    {
        $this->increment('posts_count');
    }

    public function incrementActiveAdsCount()
    {
        $this->increment('active_ads_count');
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return MediaUrlHelper::resolve($this->cover_image);
    }
}
