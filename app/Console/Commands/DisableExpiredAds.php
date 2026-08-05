<?php

namespace App\Console\Commands;

use App\Models\BusinessAffiliateOffer;
use App\Models\FeaturedAdvert;
use App\Models\Listing;
use App\Models\PromotedAdvert;
use App\Models\SponsoredAdvert;
use App\Models\UserAffiliatePost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DisableExpiredAds extends Command
{
    protected $signature = 'ads:disable-expired';

    protected $description = 'Disable (not delete) listings and promo ads whose live window has expired';

    public function handle(): int
    {
        $now = now();
        $total = 0;

        // Listings: end_date or promo expiry columns
        if (class_exists(Listing::class)) {
            $query = Listing::query()->where(function ($q) use ($now) {
                $q->where(function ($q2) use ($now) {
                    $q2->whereNotNull('end_date')->where('end_date', '<', $now->toDateString());
                });
                foreach (['featured_expires_at', 'promoted_expires_at', 'sponsored_expires_at', 'paid_expires_at'] as $col) {
                    if (Schema::hasColumn('listings', $col)) {
                        $q->orWhere(function ($q2) use ($now, $col) {
                            $q2->whereNotNull($col)->where($col, '<', $now);
                        });
                    }
                }
            });

            if (Schema::hasColumn('listings', 'is_active')) {
                $query->where('is_active', true);
            }

            $listings = $query->get();
            foreach ($listings as $listing) {
                $this->disableListing($listing, $now);
                $total++;
            }
        }

        $total += $this->disableModel(FeaturedAdvert::class, 'expires_at');
        $total += $this->disableModel(SponsoredAdvert::class, 'expires_at');
        $total += $this->disableModel(PromotedAdvert::class, 'expires_at');
        $total += $this->disableModel(UserAffiliatePost::class, 'expires_at');
        $total += $this->disableModel(BusinessAffiliateOffer::class, 'expires_at');

        $this->info("Disabled {$total} expired ads/listings.");
        Log::info("ads:disable-expired completed", ['disabled' => $total]);

        return 0;
    }

    private function disableListing(Listing $listing, $now): void
    {
        if (Schema::hasColumn($listing->getTable(), 'is_active')) {
            $listing->is_active = false;
        }
        if (Schema::hasColumn($listing->getTable(), 'status')) {
            // keep approved history but mark inactive via is_active; optional status tweak
        }
        // Clear expired promo flags
        if ($listing->featured_expires_at && $listing->featured_expires_at < $now) {
            $listing->is_featured = false;
        }
        if (isset($listing->promoted_expires_at) && $listing->promoted_expires_at && $listing->promoted_expires_at < $now) {
            if (Schema::hasColumn($listing->getTable(), 'is_promoted')) {
                $listing->is_promoted = false;
            }
        }
        if (isset($listing->sponsored_expires_at) && $listing->sponsored_expires_at && $listing->sponsored_expires_at < $now) {
            if (Schema::hasColumn($listing->getTable(), 'is_sponsored')) {
                $listing->is_sponsored = false;
            }
        }
        // If end_date passed, fully disable
        if ($listing->end_date && $listing->end_date < $now->toDateString()) {
            if (Schema::hasColumn($listing->getTable(), 'is_active')) {
                $listing->is_active = false;
            }
        }
        $listing->save();
    }

    private function disableModel(string $class, string $expiresColumn): int
    {
        if (!class_exists($class)) {
            return 0;
        }

        try {
            $query = $class::query()
                ->whereNotNull($expiresColumn)
                ->where($expiresColumn, '<', now());

            $sample = new $class;
            if (Schema::hasColumn($sample->getTable(), 'is_active')) {
                $query->where('is_active', true);
            }

            $count = 0;
            foreach ($query->get() as $row) {
                if (Schema::hasColumn($row->getTable(), 'is_active')) {
                    $row->is_active = false;
                }
                foreach (['is_promoted', 'is_featured', 'is_sponsored'] as $flag) {
                    if (Schema::hasColumn($row->getTable(), $flag)) {
                        $row->{$flag} = false;
                    }
                }
                $row->save();
                $count++;
            }
            return $count;
        } catch (\Throwable $e) {
            Log::warning("ads:disable-expired skipped {$class}: " . $e->getMessage());
            return 0;
        }
    }
}
