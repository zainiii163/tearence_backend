<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerCategoryResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon_url,
            'image' => $this->image_url,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'active_banners_count' => $this->when(
                $this->active_banners_count !== null || $this->relationLoaded('activeBannerAds'),
                function () {
                    if ($this->relationLoaded('activeBannerAds')) {
                        return $this->activeBannerAds->count();
                    }
                    return $this->active_banners_count;
                }
            ),
            'banner_ads_count' => $this->when(
                $this->relationLoaded('bannerAds'),
                fn() => $this->bannerAds->count()
            ),
            'active_banner_ads_count' => $this->when(
                $this->relationLoaded('activeBannerAds'),
                fn() => $this->activeBannerAds->count()
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
