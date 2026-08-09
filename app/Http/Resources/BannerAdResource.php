<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerAdResource extends JsonResource
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
            'business_name' => $this->business_name,
            'contact_person' => $this->contact_person,
            'email' => $this->email,
            'phone' => $this->phone,
            'website_url' => $this->website_url,
            'business_logo' => $this->business_logo_url,
            'banner_type' => $this->banner_type,
            'banner_size' => $this->banner_size,
            'banner_size_display' => $this->banner_size_display,
            // Public browse uses watermarked preview — never the raw storage URL
            'banner_image' => url('/api/v1/banner-ads/'.$this->id.'/preview'),
            'banner_image_preview' => url('/api/v1/banner-ads/'.$this->id.'/preview'),
            // Hide creative file URLs — Visit must not open a free download
            'destination_link' => $this->safePublicDestinationLink(),
            'call_to_action' => $this->call_to_action,
            'price' => $this->promotion_price,
            'is_paid_creative' => true,
            'requires_purchase' => true,
            'key_selling_points' => $this->key_selling_points,
            'offer_details' => $this->offer_details,
            'validity_start' => $this->validity_start,
            'validity_end' => $this->validity_end,
            'category' => new BannerCategoryResource($this->whenLoaded('category')),
            'country' => $this->country,
            'city' => $this->city,
            'target_countries' => $this->target_countries,
            'target_audience' => $this->target_audience,
            'promotion_tier' => $this->promotion_tier,
            'promotion_badge' => $this->promotion_badge,
            'promotion_price' => $this->promotion_price,
            'promotion_start' => $this->promotion_start,
            'promotion_end' => $this->promotion_end,
            'is_verified_business' => $this->is_verified_business,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'views_count' => $this->views_count,
            'clicks_count' => $this->clicks_count,
            'ctr' => $this->ctr,
            'is_currently_promoted' => $this->isCurrentlyPromoted(),
            'is_currently_valid' => $this->isCurrentlyValid(),
            'approved_at' => $this->approved_at,
            'user' => $this->when(
                $this->relationLoaded('user') && $this->user,
                function () {
                    return [
                        'id' => $this->user->user_id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                    ];
                }
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Only expose real marketing URLs — never the banner creative file itself.
     */
    protected function safePublicDestinationLink(): ?string
    {
        $link = $this->destination_link;
        if (!$link || !is_string($link)) {
            return null;
        }

        $lower = strtolower($link);
        if (
            str_contains($lower, '/storage/banner-images')
            || str_contains($lower, '/img/banners/marketplace')
            || (str_contains($lower, '/banner-ads/') && str_contains($lower, '/preview'))
            || str_contains($lower, '/banner-ads/download/')
        ) {
            return null;
        }

        $imageUrl = $this->banner_image_url;
        if ($imageUrl && rtrim($link, '/') === rtrim($imageUrl, '/')) {
            return null;
        }

        return $link;
    }
}
