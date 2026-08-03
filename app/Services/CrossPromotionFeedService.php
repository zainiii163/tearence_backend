<?php

namespace App\Services;

use App\Models\BuySellAdvert;
use App\Models\EventsVenuesAdvert;
use App\Models\PromotedAdvert;
use App\Models\Property;
use App\Models\ResortsTravel;
use App\Models\Service;
use App\Models\SponsoredAdvert;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Aggregates sponsored / promoted listings from across marketplace categories
 * into a single public feed (Clive: pull from existing category posts).
 */
class CrossPromotionFeedService
{
    /**
     * @param  string  $mode  sponsored|promoted
     * @param  array{search?:string,country?:string,per_page?:int,page?:int}  $filters
     */
    public function feed(string $mode = 'sponsored', array $filters = []): array
    {
        $perPage = max(1, min(50, (int) ($filters['per_page'] ?? 12)));
        $page = max(1, (int) ($filters['page'] ?? 1));
        $search = trim((string) ($filters['search'] ?? ''));
        $country = trim((string) ($filters['country'] ?? ''));

        $items = collect()
            ->merge($this->fromDedicatedSponsored($mode, $search, $country))
            ->merge($this->fromDedicatedPromoted($mode, $search, $country))
            ->merge($this->fromVehicles($mode, $search, $country))
            ->merge($this->fromProperties($mode, $search, $country))
            ->merge($this->fromBuySell($mode, $search, $country))
            ->merge($this->fromEventsVenues($mode, $search, $country))
            ->merge($this->fromServices($mode, $search, $country))
            ->merge($this->fromResorts($mode, $search, $country))
            ->filter()
            ->unique(fn ($row) => ($row['source'] ?? '') . ':' . ($row['source_id'] ?? ''))
            ->sortByDesc(fn ($row) => $row['created_at'] ?? '')
            ->values();

        $total = $items->count();
        $slice = $items->forPage($page, $perPage)->values();

        return [
            'data' => $slice,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /**
     * Trending topics derived from categories across sources.
     */
    public function trendingTopics(string $mode = 'sponsored', int $limit = 8): array
    {
        $feed = $this->feed($mode, ['per_page' => 80, 'page' => 1]);
        $counts = [];

        foreach ($feed['data'] as $item) {
            $topic = $item['category_name'] ?: $item['source_label'] ?: 'General';
            $counts[$topic] = ($counts[$topic] ?? 0) + 1;
        }

        arsort($counts);

        return collect($counts)
            ->take($limit)
            ->map(fn ($count, $name) => [
                'name' => $name,
                'count' => $count,
            ])
            ->values()
            ->all();
    }

    private function fromDedicatedSponsored(string $mode, string $search, string $country): Collection
    {
        if ($mode !== 'sponsored') {
            return collect();
        }

        try {
            $query = SponsoredAdvert::query()->active()->withActivePromotion();
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
            if ($country !== '') {
                $query->where('country', 'like', "%{$country}%");
            }

            return $query->orderByDesc('sponsored_advert_id')->limit(40)->get()->map(function ($ad) {
                return $this->normalize([
                    'source' => 'sponsored',
                    'source_id' => $ad->sponsored_advert_id,
                    'source_label' => 'Sponsored',
                    'title' => $ad->title,
                    'slug' => $ad->slug,
                    'description' => Str::limit(strip_tags((string) $ad->description), 160),
                    'price' => $ad->price,
                    'currency' => $ad->currency ?? 'USD',
                    'country' => $ad->country,
                    'city' => $ad->city,
                    'main_image' => $ad->main_image,
                    'views_count' => $ad->views_count ?? 0,
                    'category_name' => $ad->category?->name ?? $ad->advert_type,
                    'href' => '/sponsored-adverts/' . ($ad->slug ?: $ad->sponsored_advert_id),
                    'created_at' => optional($ad->created_at)?->toIso8601String(),
                    'badge' => 'Sponsored',
                ]);
            });
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function fromDedicatedPromoted(string $mode, string $search, string $country): Collection
    {
        if ($mode !== 'promoted') {
            return collect();
        }

        try {
            $query = PromotedAdvert::query();
            if (method_exists(PromotedAdvert::class, 'scopeActive')) {
                $query->active();
            }
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
            if ($country !== '') {
                $query->where('country', 'like', "%{$country}%");
            }

            return $query->orderByDesc('id')->limit(40)->get()->map(function ($ad) {
                return $this->normalize([
                    'source' => 'promoted',
                    'source_id' => $ad->id,
                    'source_label' => 'Promoted',
                    'title' => $ad->title,
                    'slug' => $ad->slug,
                    'description' => Str::limit(strip_tags((string) ($ad->description ?? '')), 160),
                    'price' => $ad->price ?? null,
                    'currency' => $ad->currency ?? 'USD',
                    'country' => $ad->country ?? null,
                    'city' => $ad->city ?? null,
                    'main_image' => $ad->main_image ?? $ad->image ?? null,
                    'views_count' => $ad->views_count ?? 0,
                    'category_name' => $ad->category?->name ?? ($ad->advert_type ?? 'Promoted'),
                    'href' => '/promoted-adverts/' . ($ad->slug ?: $ad->id),
                    'created_at' => optional($ad->created_at)?->toIso8601String(),
                    'badge' => 'Promoted',
                ]);
            });
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function fromVehicles(string $mode, string $search, string $country): Collection
    {
        try {
            $query = Vehicle::query();
            if ($mode === 'sponsored') {
                $query->where(function ($q) {
                    $q->where('is_sponsored', true)
                        ->orWhere('promotion_tier', 'sponsored');
                });
            } else {
                $query->where(function ($q) {
                    $q->where('is_promoted', true)
                        ->orWhereIn('promotion_tier', ['promoted', 'featured']);
                });
            }
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('make', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                });
            }
            if ($country !== '') {
                $query->where('country', 'like', "%{$country}%");
            }

            return $query->orderByDesc('id')->limit(30)->get()->map(function ($ad) use ($mode) {
                return $this->normalize([
                    'source' => 'vehicles',
                    'source_id' => $ad->id,
                    'source_label' => 'Vehicles',
                    'title' => $ad->title ?: trim(($ad->make ?? '') . ' ' . ($ad->model ?? '')),
                    'slug' => $ad->slug ?? (string) $ad->id,
                    'description' => Str::limit(strip_tags((string) ($ad->description ?? '')), 160),
                    'price' => $ad->price ?? null,
                    'currency' => $ad->currency ?? 'USD',
                    'country' => $ad->country ?? null,
                    'city' => $ad->city ?? null,
                    'main_image' => $ad->main_image ?? $ad->image ?? null,
                    'views_count' => $ad->view_count ?? $ad->views_count ?? 0,
                    'category_name' => 'Vehicles',
                    'href' => '/vehicles/' . ($ad->slug ?: $ad->id),
                    'created_at' => optional($ad->created_at)?->toIso8601String(),
                    'badge' => $mode === 'sponsored' ? 'Sponsored' : 'Promoted',
                ]);
            });
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function fromProperties(string $mode, string $search, string $country): Collection
    {
        try {
            $query = Property::query();
            if ($mode === 'sponsored') {
                $query->where('advert_type', 'sponsored');
            } else {
                $query->whereIn('advert_type', ['promoted', 'featured']);
            }
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
            if ($country !== '') {
                $query->where('country', 'like', "%{$country}%");
            }

            return $query->orderByDesc('id')->limit(30)->get()->map(function ($ad) use ($mode) {
                return $this->normalize([
                    'source' => 'property',
                    'source_id' => $ad->id,
                    'source_label' => 'Property',
                    'title' => $ad->title,
                    'slug' => $ad->slug ?? (string) $ad->id,
                    'description' => Str::limit(strip_tags((string) ($ad->description ?? '')), 160),
                    'price' => $ad->price ?? $ad->rent_price ?? null,
                    'currency' => $ad->currency ?? 'USD',
                    'country' => $ad->country ?? null,
                    'city' => $ad->city ?? null,
                    'main_image' => $ad->main_image ?? $ad->cover_image ?? null,
                    'views_count' => $ad->views ?? $ad->view_count ?? 0,
                    'category_name' => 'Property',
                    'href' => '/property/' . ($ad->slug ?: $ad->id),
                    'created_at' => optional($ad->created_at)?->toIso8601String(),
                    'badge' => $mode === 'sponsored' ? 'Sponsored' : 'Promoted',
                ]);
            });
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function fromBuySell(string $mode, string $search, string $country): Collection
    {
        try {
            $query = BuySellAdvert::query();
            if ($mode === 'sponsored') {
                $query->where('is_sponsored', true);
            } else {
                $query->where(function ($q) {
                    $q->where('is_promoted', true)
                        ->orWhere('is_featured', true)
                        ->orWhereIn('promotion_tier', ['promoted', 'featured']);
                });
            }
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
            if ($country !== '') {
                $query->where('country', 'like', "%{$country}%");
            }

            return $query->orderByDesc('id')->limit(30)->get()->map(function ($ad) use ($mode) {
                return $this->normalize([
                    'source' => 'buy_sell',
                    'source_id' => $ad->id,
                    'source_label' => 'Buy & Sell',
                    'title' => $ad->title,
                    'slug' => $ad->slug ?? (string) $ad->id,
                    'description' => Str::limit(strip_tags((string) ($ad->description ?? '')), 160),
                    'price' => $ad->price ?? null,
                    'currency' => $ad->currency ?? 'USD',
                    'country' => $ad->country ?? null,
                    'city' => $ad->city ?? null,
                    'main_image' => $ad->main_image ?? $ad->image ?? null,
                    'views_count' => $ad->views ?? $ad->views_count ?? 0,
                    'category_name' => $ad->category?->name ?? 'Buy & Sell',
                    'href' => '/buy-sell/' . ($ad->slug ?: $ad->id),
                    'created_at' => optional($ad->created_at)?->toIso8601String(),
                    'badge' => $mode === 'sponsored' ? 'Sponsored' : 'Promoted',
                ]);
            });
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function fromEventsVenues(string $mode, string $search, string $country): Collection
    {
        try {
            $query = EventsVenuesAdvert::query()->active();
            if ($mode === 'sponsored') {
                $query->where('promotion_tier', 'sponsored');
            } else {
                $query->whereIn('promotion_tier', ['promoted', 'featured']);
            }
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
            if ($country !== '') {
                $query->where('country', 'like', "%{$country}%");
            }

            return $query->orderByDesc('id')->limit(30)->get()->map(function ($ad) use ($mode) {
                return $this->normalize([
                    'source' => 'events_venues',
                    'source_id' => $ad->id,
                    'source_label' => 'Events & Venues',
                    'title' => $ad->title,
                    'slug' => $ad->slug ?? (string) $ad->id,
                    'description' => Str::limit(strip_tags((string) ($ad->description ?? '')), 160),
                    'price' => $ad->price ?? $ad->ticket_price ?? null,
                    'currency' => $ad->currency ?? 'USD',
                    'country' => $ad->country ?? null,
                    'city' => $ad->city ?? null,
                    'main_image' => $ad->main_image ?? null,
                    'views_count' => $ad->views_count ?? 0,
                    'category_name' => $ad->category?->name ?? 'Events & Venues',
                    'href' => '/events-venues/' . ($ad->slug ?: $ad->id),
                    'created_at' => optional($ad->created_at)?->toIso8601String(),
                    'badge' => $mode === 'sponsored' ? 'Sponsored' : 'Promoted',
                ]);
            });
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function fromServices(string $mode, string $search, string $country): Collection
    {
        try {
            $query = Service::query();
            if ($mode === 'sponsored') {
                $query->whereIn('promotion_type', ['sponsored', 'network_boost']);
            } else {
                $query->whereIn('promotion_type', ['promoted', 'featured']);
            }
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
            if ($country !== '') {
                $query->where('country', 'like', "%{$country}%");
            }

            return $query->orderByDesc('id')->limit(30)->get()->map(function ($ad) use ($mode) {
                return $this->normalize([
                    'source' => 'services',
                    'source_id' => $ad->id,
                    'source_label' => 'Services',
                    'title' => $ad->title,
                    'slug' => $ad->slug ?? (string) $ad->id,
                    'description' => Str::limit(strip_tags((string) ($ad->description ?? '')), 160),
                    'price' => $ad->price ?? null,
                    'currency' => $ad->currency ?? 'USD',
                    'country' => $ad->country ?? null,
                    'city' => $ad->city ?? null,
                    'main_image' => $ad->main_image ?? $ad->image ?? null,
                    'views_count' => $ad->views ?? $ad->views_count ?? 0,
                    'category_name' => $ad->category?->name ?? 'Services',
                    'href' => '/services/' . ($ad->slug ?: $ad->id),
                    'created_at' => optional($ad->created_at)?->toIso8601String(),
                    'badge' => $mode === 'sponsored' ? 'Sponsored' : 'Promoted',
                ]);
            });
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function fromResorts(string $mode, string $search, string $country): Collection
    {
        try {
            $query = ResortsTravel::query();
            if ($mode === 'sponsored') {
                $query->whereIn('promotion_tier', ['sponsored', 'network_wide']);
            } else {
                $query->whereIn('promotion_tier', ['promoted', 'featured']);
            }
            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
            if ($country !== '') {
                $query->where('country', 'like', "%{$country}%");
            }

            return $query->orderByDesc('id')->limit(20)->get()->map(function ($ad) use ($mode) {
                return $this->normalize([
                    'source' => 'resorts_travel',
                    'source_id' => $ad->id,
                    'source_label' => 'Travel & Experiences',
                    'title' => $ad->title,
                    'slug' => $ad->slug ?? (string) $ad->id,
                    'description' => Str::limit(strip_tags((string) ($ad->description ?? '')), 160),
                    'price' => $ad->price ?? null,
                    'currency' => $ad->currency ?? 'USD',
                    'country' => $ad->country ?? null,
                    'city' => $ad->city ?? null,
                    'main_image' => $ad->main_image ?? $ad->cover_image ?? null,
                    'views_count' => $ad->views ?? $ad->views_count ?? 0,
                    'category_name' => 'Travel & Experiences',
                    'href' => '/resorts-travel/' . ($ad->slug ?: $ad->id),
                    'created_at' => optional($ad->created_at)?->toIso8601String(),
                    'badge' => $mode === 'sponsored' ? 'Sponsored' : 'Promoted',
                ]);
            });
        } catch (\Throwable $e) {
            return collect();
        }
    }

    private function normalize(array $row): array
    {
        return array_merge([
            'id' => ($row['source'] ?? 'x') . '-' . ($row['source_id'] ?? Str::random(6)),
            'badges' => array_filter([$row['badge'] ?? null]),
            'image' => $row['main_image'] ?? null,
        ], $row);
    }
}
