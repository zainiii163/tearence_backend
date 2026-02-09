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
    protected $signature = 'ads:delete-old {days=21 : The age in days after which ads should be deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete ads older than specified number of days (default: 21 days)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->argument('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Deleting ads older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')})...");

        // Get old ads
        $oldAds = Listing::where('created_at', '<', $cutoffDate)
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
