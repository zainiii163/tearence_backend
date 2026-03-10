<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
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
            'user_id' => $this->user_id,
            'service_provider_id' => $this->service_provider_id,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'description' => $this->description,
            'whats_included' => $this->whats_included,
            'whats_not_included' => $this->whats_not_included,
            'requirements' => $this->requirements,
            'service_type' => $this->service_type,
            'starting_price' => $this->starting_price,
            'formatted_price' => $this->formatted_price,
            'currency' => $this->currency,
            'delivery_time' => $this->delivery_time,
            'availability' => $this->availability,
            'country' => $this->country,
            'city' => $this->city,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'service_area_radius' => $this->service_area_radius,
            'views' => $this->views,
            'enquiries' => $this->enquiries,
            'rating' => $this->rating,
            'review_count' => $this->review_count,
            'status' => $this->status,
            'promotion_type' => $this->promotion_type,
            'promotion_expires_at' => $this->promotion_expires_at,
            'is_verified' => $this->is_verified,
            'languages' => $this->languages,
            'provider_name' => $this->provider_name,
            'provider_photo' => $this->provider_photo,
            'thumbnail_url' => $this->thumbnail_url,
            'promotion_badge' => $this->promotion_badge,
            'is_promoted' => $this->isPromoted(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Relationships
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                    'icon' => $this->category->icon,
                ];
            }),
            
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'profile_photo_url' => $this->user->profile_photo_url,
                ];
            }),
            
            'service_provider' => $this->whenLoaded('serviceProvider', function () {
                return [
                    'id' => $this->serviceProvider->id,
                    'business_name' => $this->serviceProvider->business_name,
                    'bio' => $this->serviceProvider->bio,
                    'country' => $this->serviceProvider->country,
                    'city' => $this->serviceProvider->city,
                    'is_verified' => $this->serviceProvider->is_verified,
                    'rating' => $this->serviceProvider->rating,
                    'review_count' => $this->serviceProvider->review_count,
                    'skills' => $this->serviceProvider->skills,
                ];
            }),
            
            'packages' => $this->whenLoaded('packages', function () {
                return $this->packages->map(function ($package) {
                    return [
                        'id' => $package->id,
                        'name' => $package->name,
                        'description' => $package->description,
                        'price' => $package->price,
                        'formatted_price' => $package->formatted_price,
                        'currency' => $package->currency,
                        'delivery_time' => $package->delivery_time,
                        'features' => $package->features,
                        'revisions' => $package->revisions,
                        'sort_order' => $package->sort_order,
                        'is_active' => $package->is_active,
                    ];
                });
            }),
            
            'media' => $this->whenLoaded('media', function () {
                return $this->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'type' => $media->type,
                        'file_path' => $media->file_path,
                        'file_name' => $media->file_name,
                        'mime_type' => $media->mime_type,
                        'file_size' => $media->file_size,
                        'caption' => $media->caption,
                        'sort_order' => $media->sort_order,
                        'is_thumbnail' => $media->is_thumbnail,
                        'full_url' => $media->full_url,
                    ];
                });
            }),
        ];
    }
}
