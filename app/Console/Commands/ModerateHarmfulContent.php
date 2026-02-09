<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Listing;
use Illuminate\Support\Facades\Log;

class ModerateHarmfulContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ads:moderate-harmful {--delete : Actually delete harmful content, otherwise just report}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect and handle harmful content in ads';

    /**
     * List of harmful keywords and patterns
     */
    protected $harmfulKeywords = [
        'illegal', 'scam', 'fraud', 'fake', 'counterfeit', 'stolen',
        'weapons', 'drugs', 'prostitution', 'escort', 'adult services',
        'gambling', 'casino', 'betting', 'loan shark', 'money laundering',
        'terrorism', 'extremist', 'hate speech', 'racist', 'discrimination',
        'violence', 'murder', 'kill', 'harm', 'abuse', 'exploitation'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Scanning for harmful content in ads...");
        $shouldDelete = $this->option('delete');

        $listings = Listing::where('is_harmful', false)->get();
        $harmfulCount = 0;
        $deletedCount = 0;

        foreach ($listings as $listing) {
            $harmfulScore = $this->calculateHarmfulScore($listing);

            if ($harmfulScore > 0) {
                $harmfulCount++;
                $reason = $this->getHarmfulReason($listing, $harmfulScore);

                $this->warn("Potentially harmful ad found:");
                $this->line("  ID: {$listing->listing_id}");
                $this->line("  Title: {$listing->title}");
                $this->line("  Harmful Score: {$harmfulScore}");
                $this->line("  Reason: {$reason}");

                if ($shouldDelete) {
                    try {
                        $listing->markAsHarmful($reason);
                        $deletedCount++;
                        $this->info("  ✓ Marked as harmful and deactivated");
                        Log::warning("Ad marked as harmful: ID {$listing->listing_id}, Reason: {$reason}");
                    } catch (\Exception $e) {
                        $this->error("  ✗ Failed to mark as harmful: " . $e->getMessage());
                        Log::error("Failed to mark harmful ad ID {$listing->listing_id}: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Scan completed. Found {$harmfulCount} potentially harmful ads.");
        
        if ($shouldDelete) {
            $this->info("Successfully processed {$deletedCount} harmful ads.");
        } else {
            $this->info("Use --delete flag to actually mark harmful ads and deactivate them.");
        }

        Log::info("Harmful content moderation completed. Found: {$harmfulCount}, Processed: {$deletedCount}");

        return 0;
    }

    /**
     * Calculate harmful content score
     */
    private function calculateHarmfulScore(Listing $listing): int
    {
        $score = 0;
        $text = strtolower($listing->title . ' ' . $listing->description);

        foreach ($this->harmfulKeywords as $keyword) {
            if (strpos($text, strtolower($keyword)) !== false) {
                $score++;
            }
        }

        // Additional checks
        if ($this->containsSuspiciousPatterns($text)) {
            $score += 2;
        }

        if ($this->hasExcessiveCapitalization($listing->title)) {
            $score += 1;
        }

        return $score;
    }

    /**
     * Check for suspicious patterns
     */
    private function containsSuspiciousPatterns(string $text): bool
    {
        // Check for phone number patterns that might be spam
        if (preg_match('/\b\d{3}[-.\s]?\d{3}[-.\s]?\d{4}\b/', $text) && substr_count($text, '$') > 3) {
            return true;
        }

        // Check for excessive punctuation
        if (substr_count($text, '!') > 3 || substr_count($text, '?') > 2) {
            return true;
        }

        // Check for URL spam
        if (substr_count($text, 'http') > 2) {
            return true;
        }

        return false;
    }

    /**
     * Check for excessive capitalization
     */
    private function hasExcessiveCapitalization(string $title): bool
    {
        $uppercase = preg_match_all('/[A-Z]/', $title);
        $total = strlen($title);
        
        return $total > 0 && ($uppercase / $total) > 0.5;
    }

    /**
     * Get harmful reason based on score
     */
    private function getHarmfulReason(Listing $listing, int $score): string
    {
        $reasons = [];

        $text = strtolower($listing->title . ' ' . $listing->description);

        foreach ($this->harmfulKeywords as $keyword) {
            if (strpos($text, strtolower($keyword)) !== false) {
                $reasons[] = "Contains prohibited keyword: {$keyword}";
            }
        }

        if ($this->containsSuspiciousPatterns($text)) {
            $reasons[] = "Contains suspicious patterns";
        }

        if ($this->hasExcessiveCapitalization($listing->title)) {
            $reasons[] = "Excessive capitalization";
        }

        return implode('; ', $reasons);
    }
}
