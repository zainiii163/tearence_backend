<?php

namespace App\Filament\Pages;

use App\Services\AdvertLifecycleService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;

/**
 * Clive: one place to see Live / Expiring / Expired (and Pending) adverts across categories.
 */
class AdvertsLifecycle extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Adverts Lifecycle';

    protected static ?string $title = 'Adverts Lifecycle';

    protected static ?string $navigationGroup = 'Marketing & Ads';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'adverts-lifecycle';

    protected static string $view = 'filament.pages.adverts-lifecycle';

    public string $bucket = 'live';

    public string $typeFilter = 'all';

    public static function canAccess(): bool
    {
        $user = auth('admin-web')->user() ?? auth()->user();
        if (! $user) {
            return false;
        }

        if (! empty($user->is_super_admin)) {
            return true;
        }

        return Gate::forUser($user)->allows('view-listings')
            || Gate::forUser($user)->allows('view-dashboard');
    }

    public function getSubheading(): ?string
    {
        return 'Live, expiring within '.AdvertLifecycleService::EXPIRING_DAYS.' days, expired, and pending-payment adverts across Featured, Sponsored, Promoted, Banner, Buy & Sell, and Vehicles.';
    }

    public function setBucket(string $bucket): void
    {
        if (! in_array($bucket, ['live', 'expiring', 'expired', 'pending'], true)) {
            return;
        }
        $this->bucket = $bucket;
    }

    public function getViewData(): array
    {
        $service = app(AdvertLifecycleService::class);
        $type = $this->typeFilter === 'all' ? null : $this->typeFilter;
        $snapshot = $service->snapshot($this->bucket, $type);

        // Types for filter should come from full set, not filtered bucket
        $allTypes = $service->collectAll()->pluck('type')->unique()->sort()->values()->all();

        return [
            'bucket' => $this->bucket,
            'typeFilter' => $this->typeFilter,
            'counts' => $snapshot['counts'],
            'rows' => $snapshot['rows'],
            'types' => $allTypes,
            'expiringDays' => $snapshot['expiring_days'],
        ];
    }
}
