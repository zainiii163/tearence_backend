<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Listing;
use Illuminate\Support\Facades\Log;

class DeleteOldAds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:delete-old {days=90 : The age in days after which inactive ads may be deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hard-delete inactive ads older than specified days (default: 90). Prefer ads:disable-expired for live windows.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->argument('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Deleting ads older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')})...");

        // Only hard-delete inactive ads past the cutoff (active ones are disabled by ads:disable-expired)
        $oldAds = Listing::where('created_at', '<', $cutoffDate)
            ->when(
                \Illuminate\Support\Facades\Schema::hasColumn('listings', 'is_active'),
                fn ($q) => $q->where('is_active', false)
            )
            ->get();

        if ($oldAds->isEmpty()) {
            $this->info("No ads found older than {$days} days.");
            return 0;
        }

        $deletedCount = 0;
        $harmfulCount = 0;

        foreach ($oldAds as $ad) {
            try {
                // Log before deletion
                Log::info("Deleting old ad: ID {$ad->listing_id}, Title: {$ad->title}, Created: {$ad->created_at}");
                
                if ($ad->is_harmful) {
                    $harmfulCount++;
                    $this->line("Deleting harmful ad: ID {$ad->listing_id}, Title: {$ad->title}");
                }

                $ad->delete();
                $deletedCount++;

            } catch (\Exception $e) {
                Log::error("Failed to delete ad ID {$ad->listing_id}: " . $e->getMessage());
                $this->error("Failed to delete ad ID {$ad->listing_id}: " . $e->getMessage());
            }
        }

        $this->info("Successfully deleted {$deletedCount} ads (including {$harmfulCount} harmful ads).");
        Log::info("Old ads cleanup completed. Deleted {$deletedCount} ads (including {$harmfulCount} harmful ads).");

        return $deletedCount;
    }
}
