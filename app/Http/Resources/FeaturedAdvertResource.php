<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeaturedAdvertResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'listing_id' => $this->listing_id,
            'customer_id' => $this->customer_id,
            'category_id' => $this->category_id,
            'country_id' => $this->country_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'formatted_price' => $this->formatted_price,
            'currency' => $this->currency,
            'advert_type' => $this->advert_type,
            'condition' => $this->condition,
            'images' => $this->images,
            'main_image' => $this->main_image,
            'video_url' => $this->video_url,
            'contact_name' => $this->contact_name,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'website' => $this->website,
            'country' => $this->country,
            'city' => $this->city,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'upsell_tier' => $this->upsell_tier,
            'upsell_tier_display_name' => $this->upsell_tier_display_name,
            'upsell_price' => $this->upsell_price,
            'payment_status' => $this->payment_status,
            'payment_status_display_name' => $this->payment_status_display_name,
            'payment_reference' => $this->payment_reference,
            'starts_at' => $this->starts_at,
            'expires_at' => $this->expires_at,
            'is_active' => $this->is_active,
            'is_currently_active' => $this->isCurrentlyActive(),
            'is_expiring_soon' => $this->isExpiringSoon(),
            'days_remaining' => $this->getDaysRemaining(),
            'view_count' => $this->view_count,
            'save_count' => $this->save_count,
            'contact_count' => $this->contact_count,
            'rating' => $this->rating,
            'review_count' => $this->review_count,
            'is_verified_seller' => $this->is_verified_seller,
            'admin_notes' => $this->admin_notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'listing' => $this->when($this->relationLoaded('listing'), function () {
                return new ListingResource($this->listing);
            }),
            'customer' => $this->when($this->relationLoaded('customer'), function () {
                return new CustomerResource($this->customer);
            }),
            'category' => $this->when($this->relationLoaded('category'), function () {
                return new CategoryResource($this->category);
            }),
            'country' => $this->when($this->relationLoaded('country'), function () {
                return new CountryResource($this->country);
            }),
        ];
    }
}
