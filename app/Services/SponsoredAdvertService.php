<?php

namespace App\Services;

use App\Models\SponsoredAdvert;
use App\Models\SponsoredAdvertAnalytic;
use App\Models\SponsoredAdvertFavourite;
use App\Models\SponsoredAdvertInquiry;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class SponsoredAdvertService
{
    /**
     * Create a new sponsored advert.
     */
    public function createAdvert(array $data): SponsoredAdvert
    {
        // Set tier price based on sponsored tier
        $tierPrices = [
            'basic' => 29.99,
            'plus' => 59.99,
            'premium' => 99.99,
        ];

        $data['tier_price'] = $tierPrices[$data['sponsored_tier']] ?? 0;
        $data['status'] = 'pending'; // All new adverts start as pending
        $data['slug'] = $this->generateUniqueSlug($data['title']);

        // Handle image uploads
        if (isset($data['main_image']) && $data['main_image'] instanceof UploadedFile) {
            $data['main_image'] = $this->handleImageUpload($data['main_image'], 'sponsored-adverts/main');
        }

        if (isset($data['additional_images']) && is_array($data['additional_images'])) {
            $additionalImages = [];
            foreach ($data['additional_images'] as $image) {
                if ($image instanceof UploadedFile) {
                    $additionalImages[] = $this->handleImageUpload($image, 'sponsored-adverts/additional');
                }
            }
            $data['additional_images'] = $additionalImages;
        }

        if (isset($data['logo_url']) && $data['logo_url'] instanceof UploadedFile) {
            $data['logo_url'] = $this->handleImageUpload($data['logo_url'], 'sponsored-adverts/logos');
        }

        return SponsoredAdvert::create($data);
    }

    /**
     * Update an existing sponsored advert.
     */
    public function updateAdvert(SponsoredAdvert $advert, array $data): SponsoredAdvert
    {
        // Update slug if title changed
        if (isset($data['title']) && $data['title'] !== $advert->title) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }

        // Update tier price if tier changed
        if (isset($data['sponsored_tier']) && $data['sponsored_tier'] !== $advert->sponsored_tier) {
            $tierPrices = [
                'basic' => 29.99,
                'plus' => 59.99,
                'premium' => 99.99,
            ];
            $data['tier_price'] = $tierPrices[$data['sponsored_tier']] ?? $advert->tier_price;
        }

        // Handle image uploads
        if (isset($data['main_image']) && $data['main_image'] instanceof UploadedFile) {
            $data['main_image'] = $this->handleImageUpload($data['main_image'], 'sponsored-adverts/main');
        }

        if (isset($data['additional_images']) && is_array($data['additional_images'])) {
            $additionalImages = [];
            foreach ($data['additional_images'] as $image) {
                if ($image instanceof UploadedFile) {
                    $additionalImages[] = $this->handleImageUpload($image, 'sponsored-adverts/additional');
                }
            }
            $data['additional_images'] = $additionalImages;
        }

        if (isset($data['logo_url']) && $data['logo_url'] instanceof UploadedFile) {
            $data['logo_url'] = $this->handleImageUpload($data['logo_url'], 'sponsored-adverts/logos');
        }

        $advert->update($data);
        return $advert->fresh();
    }

    /**
     * Submit an inquiry for a sponsored advert.
     */
    public function submitInquiry(SponsoredAdvert $advert, array $data): SponsoredAdvertInquiry
    {
        $inquiry = SponsoredAdvertInquiry::create([
            'sponsored_advert_id' => $advert->id,
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'],
            'inquiry_type' => $data['inquiry_type'] ?? 'general',
        ]);

        // Track the inquiry
        $this->trackAnalytics($advert->id, 'inquiry', [
            'user_id' => auth()->id(),
            'inquiry_id' => $inquiry->id,
        ]);

        // Increment inquiry count
        $advert->incrementInquiries();

        return $inquiry;
    }

    /**
     * Toggle favourite status for a sponsored advert.
     */
    public function toggleFavourite(SponsoredAdvert $advert, int $userId): bool
    {
        $favourite = SponsoredAdvertFavourite::where('sponsored_advert_id', $advert->id)
            ->where('user_id', $userId)
            ->first();

        if ($favourite) {
            $favourite->delete();
            $advert->decrement('saves_count');
            return false;
        } else {
            SponsoredAdvertFavourite::create([
                'sponsored_advert_id' => $advert->id,
                'user_id' => $userId,
            ]);
            $advert->incrementSaves();
            return true;
        }
    }

    /**
     * Track analytics event for a sponsored advert.
     */
    public function trackAnalytics(int $advertId, string $eventType, array $metadata = []): void
    {
        $data = [
            'sponsored_advert_id' => $advertId,
            'event_type' => $eventType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
            'metadata' => $metadata,
        ];

        // Get location data (you might want to use a geolocation service here)
        $data['country'] = $this->getUserCountry();
        $data['city'] = $this->getUserCity();

        SponsoredAdvertAnalytic::create($data);
    }

    /**
     * Get featured sponsored adverts for carousel.
     */
    public function getFeaturedAdverts(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return SponsoredAdvert::with(['user'])
            ->active()
            ->currentlySponsored()
            ->orderByTier()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get sponsored adverts by category.
     */
    public function getAdvertsByCategory(string $category, int $perPage = 12): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return SponsoredAdvert::with(['user'])
            ->active()
            ->currentlySponsored()
            ->where('category', $category)
            ->orderByTier()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get sponsored adverts by country.
     */
    public function getAdvertsByCountry(string $country, int $perPage = 12): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return SponsoredAdvert::with(['user'])
            ->active()
            ->currentlySponsored()
            ->where('country', $country)
            ->orderByTier()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get trending sponsored adverts.
     */
    public function getTrendingAdverts(int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return SponsoredAdvert::with(['user'])
            ->active()
            ->currentlySponsored()
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByPopularity()
            ->limit($limit)
            ->get();
    }

    /**
     * Search sponsored adverts.
     */
    public function searchAdverts(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = SponsoredAdvert::with(['user'])
            ->active()
            ->currentlySponsored();

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('tagline', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['advert_type'])) {
            $query->where('advert_type', $filters['advert_type']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        if (!empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        if (!empty($filters['sponsored_tier'])) {
            $query->where('sponsored_tier', $filters['sponsored_tier']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        switch ($sortBy) {
            case 'views':
                $query->orderBy('views_count', $sortOrder);
                break;
            case 'clicks':
                $query->orderBy('clicks_count', $sortOrder);
                break;
            case 'saves':
                $query->orderBy('saves_count', $sortOrder);
                break;
            case 'tier':
                $query->orderByTier();
                break;
            case 'popularity':
                $query->orderByPopularity();
                break;
            case 'price':
                $query->orderBy('price', $sortOrder);
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
                break;
        }

        return $query->paginate($filters['per_page'] ?? 12);
    }

    /**
     * Generate a unique slug.
     */
    private function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (SponsoredAdvert::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Handle image upload.
     */
    private function handleImageUpload(UploadedFile $file, string $directory): string
    {
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');
        return $path;
    }

    /**
     * Get user country from IP.
     */
    private function getUserCountry(): ?string
    {
        // You can implement geolocation logic here
        // For now, return a placeholder
        return null;
    }

    /**
     * Get user city from IP.
     */
    private function getUserCity(): ?string
    {
        // You can implement geolocation logic here
        // For now, return a placeholder
        return null;
    }

    /**
     * Get sponsored tier options for forms.
     */
    public function getTierOptions(): array
    {
        return [
            'basic' => [
                'name' => 'Sponsored Basic',
                'price' => 29.99,
                'description' => 'Perfect for getting started with sponsored advertising',
                'features' => [
                    'Listed on Sponsored Adverts Page',
                    'Highlighted card',
                    '"Sponsored" badge',
                    '3× more visibility than standard ads',
                ],
            ],
            'plus' => [
                'name' => 'Sponsored Plus',
                'price' => 59.99,
                'description' => 'Enhanced visibility and features for growing your reach',
                'features' => [
                    'All Basic features',
                    'Top of category placement',
                    'Larger advert card',
                    'Priority in search results',
                    'Included in weekly "Sponsored Highlights" email',
                ],
            ],
            'premium' => [
                'name' => 'Sponsored Premium',
                'price' => 99.99,
                'description' => 'Maximum impact and reach across our platform',
                'features' => [
                    'Homepage placement',
                    'Featured in homepage slider',
                    'Category top placement',
                    'Included in social media promotion',
                    '"Premium Sponsored" badge',
                    'Maximum visibility across the platform',
                ],
            ],
        ];
    }

    /**
     * Get advert type options for forms.
     */
    public function getAdvertTypeOptions(): array
    {
        return [
            'product' => 'Product / Item for Sale',
            'service' => 'Service / Business Offer',
            'property' => 'Property / Real Estate',
            'job' => 'Job / Recruitment',
            'event' => 'Event / Experience',
            'vehicle' => 'Vehicle / Motors',
            'business_opportunity' => 'Business Opportunity',
            'miscellaneous' => 'Miscellaneous / Other',
        ];
    }

    /**
     * Get condition options for forms.
     */
    public function getConditionOptions(): array
    {
        return [
            'new' => 'New',
            'used' => 'Used',
            'not_applicable' => 'Not Applicable',
        ];
    }
}
