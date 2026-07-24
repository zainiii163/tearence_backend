<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\Listing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SendAdvertExpiryReminders extends Command
{
    protected $signature = 'ads:send-expiry-reminders {--days=7 : Look-ahead window in days}';

    protected $description = 'Notify users about adverts / promotions ending soon';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $now = now();
        $until = now()->addDays($days);
        $created = 0;

        $listings = Listing::query()
            ->whereNotNull('customer_id')
            ->where(function ($q) use ($now, $until) {
                foreach (['featured_expires_at', 'promoted_expires_at', 'sponsored_expires_at', 'paid_expires_at', 'end_date'] as $col) {
                    if (Schema::hasColumn('listing', $col)) {
                        $q->orWhereBetween($col, [$now, $until]);
                    }
                }
            })
            ->limit(500)
            ->get();

        foreach ($listings as $listing) {
            $ends = collect([
                ['type' => CustomerNotification::TYPE_FEATURED_ENDING, 'at' => $listing->featured_expires_at ?? null],
                ['type' => CustomerNotification::TYPE_PROMOTION_ENDING, 'at' => $listing->promoted_expires_at ?? null],
                ['type' => CustomerNotification::TYPE_SPONSORED_ENDING, 'at' => $listing->sponsored_expires_at ?? null],
                ['type' => CustomerNotification::TYPE_ADVERT_EXPIRING, 'at' => $listing->paid_expires_at ?? $listing->end_date ?? null],
            ])->filter(fn ($row) => $row['at'] && $row['at'] >= $now && $row['at'] <= $until);

            foreach ($ends as $row) {
                if (!$this->wantsReminders((int) $listing->customer_id, 'advert_expiry')) {
                    continue;
                }

                $exists = CustomerNotification::where('customer_id', $listing->customer_id)
                    ->where('type', $row['type'])
                    ->where('created_at', '>=', now()->subDay())
                    ->where('data->listing_id', $listing->listing_id ?? $listing->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $title = match ($row['type']) {
                    CustomerNotification::TYPE_FEATURED_ENDING => 'Featured listing ending soon',
                    CustomerNotification::TYPE_PROMOTION_ENDING => 'Promoted listing ending soon',
                    CustomerNotification::TYPE_SPONSORED_ENDING => 'Sponsored listing ending soon',
                    default => 'Advert expiring soon',
                };

                CustomerNotification::notify(
                    (int) $listing->customer_id,
                    $row['type'],
                    sprintf('"%s" ends on %s. Renew to keep visibility.', $listing->title ?? 'Your listing', $row['at']),
                    $title,
                    [
                        'listing_id' => $listing->listing_id ?? $listing->id,
                        'expires_at' => (string) $row['at'],
                    ]
                );
                $created++;
            }
        }

        if (Schema::hasTable('banners') || class_exists(Banner::class)) {
            try {
                $banners = Banner::query()
                    ->whereNotNull('user_id')
                    ->whereBetween('end_date', [$now, $until])
                    ->limit(200)
                    ->get();

                foreach ($banners as $banner) {
                    if (!$this->wantsReminders((int) $banner->user_id, 'advert_expiry')) {
                        continue;
                    }
                    CustomerNotification::notify(
                        (int) $banner->user_id,
                        CustomerNotification::TYPE_ADVERT_EXPIRING,
                        sprintf('Your banner advert ends on %s. Renew to keep it live.', $banner->end_date),
                        'Banner advert expiring soon',
                        ['banner_id' => $banner->id ?? $banner->banner_id]
                    );
                    $created++;
                }
            } catch (\Throwable $e) {
                $this->warn('Banner reminders skipped: ' . $e->getMessage());
            }
        }

        $this->info("Created {$created} expiry reminder notification(s).");
        return self::SUCCESS;
    }

    private function wantsReminders(int $customerId, string $prefKey): bool
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            return false;
        }
        $prefs = $customer->notification_prefs ?? [];
        if (!is_array($prefs) || !array_key_exists($prefKey, $prefs)) {
            return true;
        }
        return (bool) $prefs[$prefKey];
    }
}
