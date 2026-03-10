<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'events';

    protected $fillable = [
        'title',
        'slug',
        'category',
        'date_time',
        'country',
        'city',
        'venue_name',
        'ticket_price',
        'price_type',
        'description',
        'schedule',
        'age_restrictions',
        'dress_code',
        'expected_attendance',
        'ticket_link',
        'contact_email',
        'social_links',
        'images',
        'video_link',
        'promotion_tier',
        'is_active',
        'user_id',
        'venue_id',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'ticket_price' => 'decimal:2',
        'social_links' => 'array',
        'images' => 'array',
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'deleted_at',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            $event->slug = Str::slug($event->title) . '-' . uniqid();
        });

        static::updating(function ($event) {
            if ($event->isDirty('title')) {
                $event->slug = Str::slug($event->title) . '-' . uniqid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function venueServices()
    {
        return $this->belongsToMany(VenueService::class, 'ea_event_venue_service')
            ->withPivot('status', 'notes')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByLocation($query, $country, $city = null)
    {
        $query->where('country', $country);
        if ($city) {
            $query->where('city', $city);
        }
        return $query;
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date_time', '>=', now());
    }

    public function scopeByPromotionTier($query, $tier)
    {
        return $query->where('promotion_tier', $tier);
    }

    public function getFormattedDateAttribute()
    {
        return $this->date_time->format('F j, Y \a\t g:i A');
    }

    public function getFormattedPriceAttribute()
    {
        if ($this->price_type === 'free') {
            return 'Free';
        } elseif ($this->price_type === 'donation') {
            return 'Donation';
        } else {
            return '$' . number_format($this->ticket_price, 2);
        }
    }

    public function getPromotionBadgeAttribute()
    {
        return match($this->promotion_tier) {
            'promoted' => 'Promoted',
            'featured' => 'Featured',
            'sponsored' => 'Sponsored',
            'spotlight' => 'Spotlight',
            default => null,
        };
    }

    public function isPromoted()
    {
        return in_array($this->promotion_tier, ['promoted', 'featured', 'sponsored', 'spotlight']);
    }
}
