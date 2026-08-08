<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replace broken placeholder image URLs (example.com, empty, localhost-only junk)
 * with live public Unsplash URLs so marketplace cards never show empty gradients.
 */
class FixBrokenListingImages extends Command
{
    protected $signature = 'media:fix-broken-images {--dry-run : Show what would change}';

    protected $description = 'Fix broken listing image URLs to use live public CDN/Unsplash URLs';

    private array $fallbacks = [
        'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=1200&q=80',
    ];

    private array $titleMap = [
        'iphone' => 'https://images.unsplash.com/photo-1616348436978-de5ecbf08563?auto=format&fit=crop&w=1200&q=80',
        'macbook' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=1200&q=80',
        'sofa' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=1200&q=80',
        'dining' => 'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&w=1200&q=80',
        'toyota' => 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=1200&q=80',
        'camry' => 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=1200&q=80',
        'rolex' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=1200&q=80',
        'watch' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=1200&q=80',
        'peloton' => 'https://images.unsplash.com/photo-1598514983318-2f64f8f4796c?auto=format&fit=crop&w=1200&q=80',
        'bike' => 'https://images.unsplash.com/photo-1485965120187-cf692dd03298?auto=format&fit=crop&w=1200&q=80',
        'camera' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=1200&q=80',
        'headphone' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=1200&q=80',
        'bag' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=1200&q=80',
        'nike' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=1200&q=80',
    ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $fixed = 0;

        if (Schema::hasTable('buysell_adverts')) {
            $fixed += $this->fixBuySell($dry);
        }

        $this->info($dry
            ? "Dry run complete. Would fix {$fixed} buy-sell rows."
            : "Fixed {$fixed} buy-sell listing image rows.");

        $this->line('Images now use live public URLs (images.unsplash.com).');

        return self::SUCCESS;
    }

    private function fixBuySell(bool $dry): int
    {
        $count = 0;
        $rows = DB::table('buysell_adverts')->select('id', 'title', 'images')->get();

        foreach ($rows as $row) {
            $images = $this->decodeImages($row->images);
            if (! $this->needsFix($images)) {
                continue;
            }

            $url = $this->pickUrlForTitle((string) $row->title);
            $newImages = [$url];
            $count++;

            if ($dry) {
                $this->line("Would fix: {$row->title}");
                continue;
            }

            DB::table('buysell_adverts')->where('id', $row->id)->update([
                'images' => json_encode($newImages),
                'updated_at' => now(),
            ]);
        }

        return $count;
    }

    private function decodeImages(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (! is_string($raw) || $raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function needsFix(array $images): bool
    {
        if ($images === []) {
            return true;
        }

        foreach ($images as $img) {
            $url = is_array($img) ? ($img['url'] ?? '') : (string) $img;
            if ($url === '') {
                return true;
            }
            if (preg_match('/example\.com|placehold|placeholder|127\.0\.0\.1|localhost/i', $url)) {
                return true;
            }
            // Relative path without a real upload is often broken on public site
            if (! str_starts_with($url, 'http') && ! str_starts_with($url, '/storage/')) {
                return true;
            }
        }

        return false;
    }

    private function pickUrlForTitle(string $title): string
    {
        $lower = strtolower($title);
        foreach ($this->titleMap as $needle => $url) {
            if (str_contains($lower, $needle)) {
                return $url;
            }
        }

        return $this->fallbacks[abs(crc32($title)) % count($this->fallbacks)];
    }
}
