<?php

namespace App\Console\Commands;

use App\Services\EventsVenuesSyncService;
use Illuminate\Console\Command;

class SyncEventsVenuesFromAdmin extends Command
{
    protected $signature = 'events-venues:sync-from-admin';

    protected $description = 'Sync Filament admin events and venues into events_venues_adverts for the public website';

    public function handle(EventsVenuesSyncService $syncService): int
    {
        $counts = $syncService->syncAll();

        $this->info("Synced {$counts['events']} event(s) and {$counts['venues']} venue(s) to the public Events & Venues feed.");

        return self::SUCCESS;
    }
}
