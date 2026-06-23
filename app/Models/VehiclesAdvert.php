<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehiclesAdvert extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'price_negotiable' => 'boolean',
        'insurance_included' => 'boolean',
        'maintenance_included' => 'boolean',
        'delivery_available' => 'boolean',
        'is_approximate_location' => 'boolean',
        'verified_seller' => 'boolean',
        'dealership' => 'boolean',
        'is_active' => 'boolean',
        'images' => 'array',
        'social_links' => 'array',
        'additional_features' => 'array',
        'promotion_start' => 'datetime',
        'promotion_end' => 'datetime',
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get user that created the vehicle advert.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment view count.
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    /**
     * Increment save count.
     */
    public function incrementSaves(): void
    {
        $this->increment('save_count');
    }

    /**
     * Increment contact count.
     */
    public function incrementContacts(): void
    {
        $this->increment('contact_count');
    }

    /**
     * Get vehicle types.
     */
    public static function getVehicleTypes(): array
    {
        return [
            'car' => 'Car',
            'van' => 'Van',
            'motorbike' => 'Motorbike',
            'truck' => 'Truck',
            'bus' => 'Bus',
            'coach' => 'Coach',
            'electric_vehicle' => 'Electric Vehicle',
            'classic_car' => 'Classic Car',
            'luxury_vehicle' => 'Luxury Vehicle',
            'caravan' => 'Caravan',
            'motorhome' => 'Motorhome',
            'boat' => 'Boat',
            'jet_ski' => 'Jet Ski',
            'agricultural' => 'Agricultural Vehicle',
            'construction' => 'Construction Vehicle',
            'other' => 'Other',
        ];
    }

    /**
     * Get vehicle categories.
     */
    public static function getCategories(): array
    {
        return [
            'sale' => 'For Sale',
            'hire' => 'For Hire',
            'lease' => 'For Lease',
        ];
    }
}
