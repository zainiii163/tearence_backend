<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventsVenuesAdvert;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Str;

class EventsVenuesSyncService
{
    public const SOURCE_EVENT = 'admin_event';

    public const SOURCE_VENUE = 'admin_venue';

    public function syncEvent(Event $event): ?EventsVenuesAdvert
    {
        if ($event->trashed()) {
            $this->removeBySource(self::SOURCE_EVENT, $event->id);

            return null;
        }

        $user = $this->resolveUser($event->user_id);
        $images = $this->normalizeImages($event->images);
        $mainImage = $images[0] ?? null;

        $payload = [
            'user_id' => $event->user_id,
            'sync_source_type' => self::SOURCE_EVENT,
            'sync_source_id' => $event->id,
            'advert_type' => 'event',
            'title' => $event->title,
            'slug' => $this->uniqueSlug($event->slug, self::SOURCE_EVENT, $event->id),
            'description' => strip_tags((string) $event->description),
            'short_description' => Str::limit(strip_tags((string) $event->description), 500),
            'event_date' => $event->date_time?->toDateString(),
            'event_time' => $event->date_time?->format('H:i:s'),
            'venue_name' => $event->venue_name ?: $event->venue?->name,
            'ticket_price' => $event->price_type === 'free' ? null : $event->ticket_price,
            'ticket_currency' => 'USD',
            'free_event' => $event->price_type === 'free',
            'event_category' => $event->category,
            'country' => $event->country,
            'city' => $event->city,
            'contact_name' => $this->contactName($user),
            'email' => $event->contact_email,
            'website' => $event->ticket_link,
            'social_links' => $this->normalizeSocialLinks($event->social_links),
            'main_image' => $mainImage,
            'images' => $images,
            'video_url' => $event->video_link,
            'promotion_tier' => $this->mapPromotionTier($event->promotion_tier),
            'status' => $event->is_active ? 'active' : 'draft',
            'is_active' => (bool) $event->is_active,
            'terms_accepted' => true,
            'accurate_info' => true,
            'expires_at' => $event->date_time?->copy()->addYear(),
        ];

        return $this->upsert(self::SOURCE_EVENT, $event->id, $payload);
    }

    public function syncVenue(Venue $venue): ?EventsVenuesAdvert
    {
        if ($venue->trashed()) {
            $this->removeBySource(self::SOURCE_VENUE, $venue->id);

            return null;
        }

        $user = $this->resolveUser($venue->user_id);
        $images = $this->normalizeImages($venue->images);
        $mainImage = $images[0] ?? null;

        $payload = [
            'user_id' => $venue->user_id,
            'sync_source_type' => self::SOURCE_VENUE,
            'sync_source_id' => $venue->id,
            'advert_type' => 'venue',
            'title' => $venue->name,
            'slug' => $this->uniqueSlug($venue->slug, self::SOURCE_VENUE, $venue->id),
            'description' => strip_tags((string) $venue->description),
            'short_description' => Str::limit(strip_tags((string) $venue->description), 500),
            'venue_type' => $venue->venue_type,
            'capacity' => $venue->capacity,
            'price_range' => $venue->formatted_price_range ?? $this->formatPriceRange($venue->min_price, $venue->max_price),
            'amenities' => is_array($venue->amenities) ? $venue->amenities : [],
            'country' => $venue->country,
            'city' => $venue->city,
            'contact_name' => $this->contactName($user),
            'email' => $venue->contact_email,
            'website' => $venue->booking_link,
            'social_links' => $this->normalizeSocialLinks($venue->social_links),
            'main_image' => $mainImage,
            'images' => $images,
            'video_url' => $venue->video_link,
            'indoor_outdoor' => (bool) $venue->indoor,
            'catering_available' => (bool) $venue->catering_available,
            'parking_available' => (bool) $venue->parking_available,
            'accessible' => (bool) $venue->accessibility,
            'promotion_tier' => $this->mapPromotionTier($venue->promotion_tier),
            'status' => $venue->is_active ? 'active' : 'draft',
            'is_active' => (bool) $venue->is_active,
            'terms_accepted' => true,
            'accurate_info' => true,
            'expires_at' => now()->addYear(),
        ];

        return $this->upsert(self::SOURCE_VENUE, $venue->id, $payload);
    }

    public function removeBySource(string $sourceType, int $sourceId): void
    {
        EventsVenuesAdvert::query()
            ->where('sync_source_type', $sourceType)
            ->where('sync_source_id', $sourceId)
            ->delete();
    }

    public function syncAll(): array
    {
        $counts = ['events' => 0, 'venues' => 0];

        Event::withTrashed()->with('venue')->chunk(100, function ($events) use (&$counts) {
            foreach ($events as $event) {
                if ($this->syncEvent($event)) {
                    $counts['events']++;
                }
            }
        });

        Venue::withTrashed()->chunk(100, function ($venues) use (&$counts) {
            foreach ($venues as $venue) {
                if ($this->syncVenue($venue)) {
                    $counts['venues']++;
                }
            }
        });

        return $counts;
    }

    protected function upsert(string $sourceType, int $sourceId, array $payload): EventsVenuesAdvert
    {
        $advert = EventsVenuesAdvert::query()
            ->where('sync_source_type', $sourceType)
            ->where('sync_source_id', $sourceId)
            ->first();

        if ($advert) {
            unset($payload['slug']);
            $advert->update($payload);

            return $advert->fresh();
        }

        return EventsVenuesAdvert::create($payload);
    }

    protected function uniqueSlug(string $baseSlug, string $sourceType, int $sourceId): string
    {
        $slug = $baseSlug ?: Str::slug('item-' . $sourceId);

        $exists = EventsVenuesAdvert::query()
            ->where('slug', $slug)
            ->where(function ($query) use ($sourceType, $sourceId) {
                $query->where('sync_source_type', '!=', $sourceType)
                    ->orWhere('sync_source_id', '!=', $sourceId);
            })
            ->exists();

        return $exists ? $slug . '-sync-' . $sourceId : $slug;
    }

    protected function resolveUser(?int $userId): ?User
    {
        if (! $userId) {
            return null;
        }

        return User::query()->find($userId);
    }

    protected function contactName(?User $user): string
    {
        if (! $user) {
            return 'Worldwide Adverts';
        }

        $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));

        return $name !== '' ? $name : ($user->name ?? 'Worldwide Adverts');
    }

    /**
     * @param  mixed  $images
     * @return array<int, string>
     */
    protected function normalizeImages($images): array
    {
        if (! is_array($images)) {
            return [];
        }

        $urls = [];

        foreach ($images as $item) {
            if (is_string($item) && $item !== '') {
                $urls[] = ltrim($item, '/');
            } elseif (is_array($item) && ! empty($item['url'])) {
                $urls[] = $item['url'];
            } elseif (is_array($item)) {
                foreach ($item as $nested) {
                    if (is_string($nested) && $nested !== '') {
                        $urls[] = ltrim($nested, '/');
                    }
                }
            }
        }

        return array_values(array_unique($urls));
    }

    /**
     * @param  mixed  $links
     * @return array<int, array<string, string>>
     */
    protected function normalizeSocialLinks($links): array
    {
        if (! is_array($links)) {
            return [];
        }

        $normalized = [];

        foreach ($links as $link) {
            if (is_array($link) && ! empty($link['link'])) {
                $normalized[] = ['link' => $link['link']];
            } elseif (is_string($link) && $link !== '') {
                $normalized[] = ['link' => $link];
            }
        }

        return $normalized;
    }

    protected function mapPromotionTier(?string $tier): string
    {
        return match ($tier) {
            'promoted' => 'promoted',
            'featured' => 'featured',
            'sponsored' => 'sponsored',
            'spotlight', 'network_boost' => 'network_boost',
            default => 'basic',
        };
    }

    protected function formatPriceRange($min, $max): ?string
    {
        if ($min === null && $max === null) {
            return null;
        }

        if ($min !== null && $max !== null) {
            return '$' . number_format((float) $min, 2) . ' - $' . number_format((float) $max, 2);
        }

        if ($min !== null) {
            return 'From $' . number_format((float) $min, 2);
        }

        return 'Up to $' . number_format((float) $max, 2);
    }
}
