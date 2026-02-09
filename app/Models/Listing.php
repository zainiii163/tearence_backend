<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Listing extends Model
{
    use HasFactory;

    protected $guarded = ['listing_id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'location_id',
        'category_id',
        'currency_id',
        'package_id',
        'title',
        'slug',
        'description',
        'display_name',
        'price',
        'type',
        'status',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'is_admin_post',
        'post_type',
        'is_harmful',
        'moderation_notes',
        'last_reposted_at',
        'attachments',
        'is_featured',
        'is_suggested',
        'is_paid',
        'is_promoted',
        'is_business',
        'is_store',
        'is_downloadable',
        'featured_expires_at',
        'suggested_expires_at',
        'paid_expires_at',
        'promoted_expires_at',
        'business_expires_at',
        'store_expires_at',
        'end_date',
        'job_type',
        'salary_min',
        'salary_max',
        'apply_url',
        'book_type',
        'genre',
        'format',
        'file_path',
        'file_size',
        'venue_name',
        'venue_type',
        'capacity',
        'country',
        'price_per_hour',
        'price_per_day',
        'facilities',
        'contact_email',
        'contact_phone',
        'venue_website',
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'listing_id';

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['package', 'customer', 'location', 'category', 'currency'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'listing';

    protected $casts = [
        'attachments' => 'array',
        'is_featured' => 'boolean',
        'is_suggested' => 'boolean',
        'is_paid' => 'boolean',
        'is_promoted' => 'boolean',
        'is_business' => 'boolean',
        'is_store' => 'boolean',
        'is_admin_post' => 'boolean',
        'is_harmful' => 'boolean',
        'is_downloadable' => 'boolean',
        'facilities' => 'array',
        'featured_expires_at' => 'datetime',
        'suggested_expires_at' => 'datetime',
        'paid_expires_at' => 'datetime',
        'promoted_expires_at' => 'datetime',
        'business_expires_at' => 'datetime',
        'store_expires_at' => 'datetime',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'last_reposted_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
    ];

    /**
     * Get the customer that owns the listing.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the business associated with this listing (if any).
     */
    public function business()
    {
        return $this->hasOne(CustomerBusiness::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the store associated with this listing (if any).
     */
    public function store()
    {
        return $this->hasOne(CustomerStore::class, 'customer_id', 'customer_id');
    }

    /**
     * Get the listingImages for the listing.
     */
    // public function images()
    // {
    //     return $this->hasMany(ListingImage::class, 'listing_id', 'listing_id');
    // }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'package_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'currency_id');
    }

    public function favorite()
    {
        return $this->belongsTo(ListingFavorite::class, 'listing_id', 'listing_id');
    }

    /**
     * Get the job upsells for the listing.
     */
    public function jobUpsells()
    {
        return $this->hasMany(JobUpsell::class, 'listing_id', 'listing_id');
    }

    /**
     * Get the listing upsells for the listing.
     */
    public function upsells()
    {
        return $this->hasMany(ListingUpsell::class, 'listing_id', 'listing_id');
    }

    /**
     * Get the book purchases for the listing.
     */
    public function bookPurchases()
    {
        return $this->hasMany(BookPurchase::class, 'listing_id', 'listing_id');
    }

    /**
     * Get active upsells for the listing.
     */
    public function activeUpsells()
    {
        return $this->hasMany(ListingUpsell::class, 'listing_id', 'listing_id')
                    ->active();
    }

    /**
     * Get active featured upsell
     */
    public function activeFeaturedUpsell()
    {
        return $this->hasOne(JobUpsell::class, 'listing_id', 'listing_id')
            ->where('upsell_type', 'featured')
            ->where('status', 'active')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Get active suggested upsell
     */
    public function activeSuggestedUpsell()
    {
        return $this->hasOne(JobUpsell::class, 'listing_id', 'listing_id')
            ->where('upsell_type', 'suggested')
            ->where('status', 'active')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            });
    }

    protected $appends = ['parent_category_id', 'images'];

    // Define the accessor for parent_category_id
    public function getParentCategoryIdAttribute()
    {
        if ($this->category_id) {
            $category = Category::find($this->category_id);
            return $category ? $category->parent_id : null;
        }
        return null;
    }

    // Accessor for the full image paths
    public function getImagesAttribute()
    {
        // Check if images is null or empty, return an empty array
        if (is_null($this->attachments) || empty($this->attachments)) {
            return [];
        }

        $fullImagesPath = [];

        foreach ($this->attachments as $image) {
            $fullImagesPath[]['image_path'] = asset('storage/' . $image); // Prepend the storage path to each image
        }

        return $fullImagesPath;
    }

    /**
     * Get the admin who approved the listing
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    /**
     * Approve the listing
     */
    public function approve(int $adminId, string $postType = 'regular'): void
    {
        $this->approval_status = 'approved';
        $this->approved_by = $adminId;
        $this->approved_at = now();
        $this->rejection_reason = null;
        $this->post_type = $postType;
        $this->is_admin_post = $postType !== 'regular';
        $this->save();
    }

    /**
     * Reject the listing
     */
    public function reject(string $reason): void
    {
        $this->approval_status = 'rejected';
        $this->rejection_reason = $reason;
        $this->save();
    }

    /**
     * Mark as harmful content
     */
    public function markAsHarmful(string $reason): void
    {
        $this->is_harmful = true;
        $this->moderation_notes = $reason;
        $this->status = 'inactive';
        $this->save();
    }

    /**
     * Check if listing is approved and active
     */
    public function isApprovedAndActive(): bool
    {
        return $this->approval_status === 'approved' && $this->status === 'active';
    }

    /**
     * Scope to get only approved listings
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Scope to get only active listings
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get pending listings
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Scope to get harmful listings
     */
    public function scopeHarmful($query)
    {
        return $query->where('is_harmful', true);
    }

    /**
     * Scope to get old listings (older than 3 weeks)
     */
    public function scopeOlderThan($query, int $days)
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }

    /**
     * Get the search priority score based on active upsells
     */
    public function getSearchPriorityScore(): int
    {
        $score = 0;
        
        // Get active upsells and sum their priority scores
        foreach ($this->activeUpsells as $upsell) {
            $score += $upsell->getPriorityScore();
        }
        
        // Add bonus for existing featured/sponsored flags
        if ($this->is_featured && $this->featured_expires_at && $this->featured_expires_at > now()) {
            $score += 600;
        }
        
        if ($this->is_sponsored && $this->sponsored_expires_at && $this->sponsored_expires_at > now()) {
            $score += 800;
        }
        
        if ($this->is_promoted && $this->promoted_expires_at && $this->promoted_expires_at > now()) {
            $score += 400;
        }
        
        return $score;
    }

    /**
     * Scope to order listings by search priority (highest first)
     */
    public function scopeOrderBySearchPriority($query)
    {
        return $query->selectRaw('listing.*, (
            SELECT COALESCE(SUM(
                CASE 
                    WHEN upsell_type = "premium" THEN 1000
                    WHEN upsell_type = "sponsored" THEN 800
                    WHEN upsell_type = "featured" THEN 600
                    WHEN upsell_type = "priority" THEN 400
                    ELSE 0
                END
            ), 0) 
            FROM listing_upsells 
            WHERE listing_upsells.listing_id = listing.listing_id 
            AND listing_upsells.status = "active" 
            AND listing_upsells.starts_at <= NOW() 
            AND listing_upsells.expires_at > NOW()
        ) as upsell_priority_score')
        ->selectRaw('(
            CASE 
                WHEN is_sponsored = 1 AND sponsored_expires_at > NOW() THEN 800
                ELSE 0
            END
        ) as sponsored_priority')
        ->selectRaw('(
            CASE 
                WHEN is_featured = 1 AND featured_expires_at > NOW() THEN 600
                ELSE 0
            END
        ) as featured_priority')
        ->selectRaw('(
            CASE 
                WHEN is_promoted = 1 AND promoted_expires_at > NOW() THEN 400
                ELSE 0
            END
        ) as promoted_priority')
        ->selectRaw('(upsell_priority_score + sponsored_priority + featured_priority + promoted_priority) as total_priority_score')
        ->orderByDesc('total_priority_score')
        ->orderByDesc('created_at'); // Secondary sort by newest
    }

    /**
     * Scope to get only listings with active upsells
     */
    public function scopeWithActiveUpsells($query)
    {
        return $query->whereHas('activeUpsells');
    }

    /**
     * Scope to get premium listings (highest priority)
     */
    public function scopePremium($query)
    {
        return $query->whereHas('activeUpsells', function($q) {
            $q->where('upsell_type', ListingUpsell::TYPE_PREMIUM);
        });
    }

    /**
     * Scope to get sponsored listings
     */
    public function scopeSponsored($query)
    {
        return $query->where(function($q) {
            $q->whereHas('activeUpsells', function($subQ) {
                $subQ->where('upsell_type', ListingUpsell::TYPE_SPONSORED);
            })->orWhere('is_sponsored', true);
        });
    }

    /**
     * Scope to get featured listings
     */
    public function scopeFeatured($query)
    {
        return $query->where(function($q) {
            $q->whereHas('activeUpsells', function($subQ) {
                $subQ->where('upsell_type', ListingUpsell::TYPE_FEATURED);
            })->orWhere('is_featured', true);
        });
    }

    /**
     * Scope to get book listings
     */
    public function scopeBooks($query)
    {
        return $query->whereHas('category', function($q) {
            $q->where('name', 'Books');
        });
    }

    /**
     * Scope to filter by book type
     */
    public function scopeByBookType($query, $bookType)
    {
        return $query->where('book_type', $bookType);
    }

    /**
     * Scope to filter by genre
     */
    public function scopeByGenre($query, $genre)
    {
        return $query->where('genre', $genre);
    }

    /**
     * Scope to filter by format
     */
    public function scopeByFormat($query, $format)
    {
        return $query->where('format', $format);
    }

    /**
     * Scope to get downloadable books
     */
    public function scopeDownloadable($query)
    {
        return $query->where('is_downloadable', true)
                    ->whereNotNull('file_path');
    }

    /**
     * Check if this is a book listing
     */
    public function isBook(): bool
    {
        return $this->category && $this->category->name === 'Books';
    }

    /**
     * Get the file URL for download
     */
    public function getFileUrl(): string
    {
        if (!$this->file_path) {
            return '';
        }

        return asset('storage/' . $this->file_path);
    }

    /**
     * Get the file size in human readable format
     */
    public function getFormattedFileSize(): string
    {
        if (!$this->file_size) {
            return '';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get total revenue from book purchases
     */
    public function getTotalRevenue(): float
    {
        return $this->bookPurchases()->completed()->sum('price_paid');
    }

    /**
     * Get total downloads count
     */
    public function getTotalDownloads(): int
    {
        return $this->bookPurchases()->completed()->sum('total_downloads');
    }

    /**
     * Get the appropriate display name for the listing
     */
    public function getDisplayName(): string
    {
        // If display_name is already set, use it
        if ($this->display_name) {
            return $this->display_name;
        }

        // Admin posts should show generic admin identifier to protect identity
        if ($this->is_admin_post) {
            return 'Admin';
        }

        // Business posts should show business name
        if ($this->is_business && $this->business) {
            return $this->business->business_name;
        }

        // Store posts should show store name
        if ($this->is_store && $this->store) {
            return $this->store->store_name ?? $this->store->business_name ?? 'Store';
        }

        // Default to customer name
        if ($this->customer) {
            return $this->customer->name ?? 'Anonymous';
        }

        return 'Anonymous';
    }

    /**
     * Set the display name based on the posting context
     */
    public function setDisplayName(): void
    {
        if ($this->is_admin_post) {
            // Use generic admin identifier to protect admin identity
            $this->display_name = 'Admin';
        } elseif ($this->is_business && $this->business) {
            $this->display_name = $this->business->business_name;
        } elseif ($this->is_store && $this->store) {
            $this->display_name = $this->store->store_name ?? $this->store->business_name ?? 'Store';
        } else {
            // For regular posts, don't set display_name - will use customer name
            $this->display_name = null;
        }
    }

    /**
     * Boot method to automatically set display name
     */
    protected static function booted()
    {
        // static::deleted(function ($listing) {
        //     foreach ($listing->attachments as $image) {
        //         Storage::delete($image);
        //     }
        // });

        // static::updating(function ($listing) {
        //     $attachmentsToDelete = array_diff($listing->getOriginal('attachments'), $listing->attachments);

        //     foreach ($attachmentsToDelete as $image) {
        //         Storage::delete($image);
        //     }
        // });

        // Handle data before creating the listing
        static::creating(function ($listing) {
            if (empty($listing->slug)) {
                $listing->slug = Str::slug($listing->title);
            }

            // Set default values
            $listing->type = "international";
            $listing->status = "active";
            $listing->approval_status = "pending"; // All ads start as pending
            $listing->promo_expire_at = date("Y-m-d", strtotime("+1 year"));
            
            // Set display name based on context
            $listing->setDisplayName();
        });

        // Create admin notification after listing is created
        static::created(function ($listing) {
            // Only create notification if it's not an admin post
            if (!$listing->is_admin_post) {
                AdminNotification::notifyAllAdmins(
                    AdminNotification::TYPE_NEW_POST,
                    "New post submitted: {$listing->title}",
                    [
                        'listing_id' => $listing->listing_id,
                        'category_id' => $listing->category_id,
                        'customer_id' => $listing->customer_id,
                        'title' => $listing->title,
                    ]
                );
            }
        });

        // Handle reposting - update date to current date
        static::updating(function ($listing) {
            if ($listing->isDirty('status') && $listing->status === 'active' && $listing->getOriginal('status') !== 'active') {
                $listing->last_reposted_at = now();
                $listing->approval_status = 'pending'; // Require re-approval on repost
                $listing->approved_at = null;
                $listing->approved_by = null;
            }
            
            // Update display name if relevant fields changed
            if ($listing->isDirty(['is_admin_post', 'is_business', 'is_store', 'approved_by'])) {
                $listing->setDisplayName();
            }
        });
    }
}
