<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Listing;
use App\Models\ListingImage;
use Illuminate\Support\Facades\DB;

class ConvertListingImagesToJson extends Command
{
    // The name and signature of the console command
    protected $signature = 'convert:listing-images';

    // The console command description
    protected $description = 'Converts listing images to JSON format and updates the listing table';

    // Execute the console command
    public function handle()
    {
        // Start database transaction
        DB::beginTransaction();

        try {
            // Fetch all listings
            // $listings = Listing::where('created_at', '>', '2023-01-01 00:00:00')->get();
            $listings = Listing::all();

            // Loop through each listing
            foreach ($listings as $listing) {
                // Fetch associated listing images
                $listingImages = ListingImage::where('listing_id', $listing->listing_id)->get();

                // Check if there are images to convert
                if ($listingImages->count() > 0) {
                    // Map the images to an array of file paths
                    $images = $listingImages->map(function ($image) {
                        return 'listings/' . $image->image_path;
                    })->toArray();

                    // Update the listing's `attachments` column with the JSON
                    $listing->update([
                        'attachments' => $images,
                    ]);

                    // Output the result for this listing
                    $this->info("Listing ID {$listing->listing_id} updated with images in JSON format.");
                }
            }

            // Commit the transaction
            DB::commit();

            // Return a success message
            $this->info('All listings with images have been successfully updated.');
        } catch (\Exception $e) {
            // Rollback transaction on failure
            DB::rollBack();

            // Output the error message
            $this->error('Failed to update listings: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
