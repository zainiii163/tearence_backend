<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuySellAdvertResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'currency' => $this->currency ?? 'USD',
            'condition' => $this->condition,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'icon' => $this->category->icon,
            ],
            'subcategory' => $this->subcategory ? [
                'id' => $this->subcategory->id,
                'name' => $this->subcategory->name,
                'slug' => $this->subcategory->slug,
            ] : null,
            'location' => [
                'country' => $this->country,
                'city' => $this->city,
                'address' => $this->address,
                'postal_code' => $this->postal_code,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'contact' => [
                'phone' => $this->phone,
                'email' => $this->email,
                'whatsapp' => $this->whatsapp,
                'preferred_contact' => $this->preferred_contact,
            ],
            'media' => [
                'images' => $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => asset('storage/' . $image->image_path),
                        'thumbnail' => asset('storage/' . $image->thumbnail_path),
                        'alt_text' => $image->alt_text,
                        'sort_order' => $image->sort_order,
                    ];
                }),
                'videos' => $this->videos->map(function ($video) {
                    return [
                        'id' => $video->id,
                        'url' => asset('storage/' . $video->video_path),
                        'thumbnail' => asset('storage/' . $video->thumbnail_path),
                        'title' => $video->title,
                        'duration' => $video->duration,
                    ];
                }),
            ],
            'promotion' => [
                'is_featured' => $this->featured,
                'is_urgent' => $this->urgent,
                'is_promoted' => $this->promoted,
                'promotion_plan' => $this->promotion ? [
                    'name' => $this->promotion->plan->name,
                    'badge' => $this->promotion->plan->slug,
                    'features' => $this->promotion->plan->features,
                ] : null,
                'expires_at' => $this->expires_at,
            ],
            'user' => $this->user ? [
                'id' => $this->user->user_id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'verified' => $this->user->verified ?? false,
                'rating' => $this->user->rating ?? 0,
                'total_sales' => $this->user->total_sales ?? 0,
            ] : null,
            'stats' => [
                'views_count' => $this->views_count ?? 0,
                'favorites_count' => $this->favorites_count ?? 0,
                'enquiries_count' => $this->enquiries_count ?? 0,
                'shares_count' => $this->shares_count ?? 0,
            ],
            'status' => $this->status,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
        ];
    }
}
