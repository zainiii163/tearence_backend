<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Import PDF ebooks from a folder into the books marketplace (admin + frontend).
 *
 * Example:
 *   php artisan books:import-pdfs "D:/live/WWA-Frontend-New-main/docs/books"
 */
class ImportPdfBooksCommand extends Command
{
    protected $signature = 'books:import-pdfs
        {path? : Absolute path to a folder of PDF files}
        {--dry-run : Show what would be imported without writing}
        {--prepare-only : Generate covers + copy PDFs to public storage without DB inserts}
        {--from-manifest : Insert books from storage/app/book-imports/manifest.json (files already prepared)}
        {--force : Re-import and overwrite existing books with the same slug}';

    protected $description = 'Import PDF books with proper titles, generated covers, and sample files into the Books marketplace';

    /** @var array<int, array{0:int,1:int,2:int}> */
    private array $palettes = [
        [13, 148, 136],
        [2, 132, 199],
        [79, 70, 229],
        [180, 83, 9],
        [190, 24, 93],
        [21, 128, 61],
        [30, 64, 175],
        [124, 58, 237],
    ];

    public function handle(): int
    {
        if (!extension_loaded('gd') && !$this->option('from-manifest')) {
            $this->error('PHP GD extension is required to generate book covers.');
            return self::FAILURE;
        }

        if ($this->option('from-manifest')) {
            return $this->importFromManifest();
        }

        $path = $this->argument('path')
            ?: base_path('../WWA-Frontend-New-main/docs/books');

        // Common local layouts
        $candidates = array_filter([
            $path,
            'D:/live/WWA-Frontend-New-main/docs/books',
            'd:/live/WWA-Frontend-New-main/docs/books',
            base_path('docs/books'),
            storage_path('app/book-imports'),
        ]);

        $folder = null;
        foreach ($candidates as $candidate) {
            if ($candidate && is_dir($candidate)) {
                $folder = realpath($candidate);
                break;
            }
        }

        if (!$folder) {
            $this->error('PDF folder not found. Pass an absolute path, e.g. php artisan books:import-pdfs "D:/live/WWA-Frontend-New-main/docs/books"');
            return self::FAILURE;
        }

        $this->info("Importing PDFs from: {$folder}");

        $files = collect(File::files($folder))
            ->filter(fn ($f) => strtolower($f->getExtension()) === 'pdf')
            ->values();

        if ($files->isEmpty()) {
            $this->warn('No PDF files found in that folder.');
            return self::SUCCESS;
        }

        // Prefer non-duplicate names (skip "Book (1).pdf" when "Book.pdf" exists)
        $byKey = [];
        foreach ($files as $file) {
            $title = $this->titleFromFilename($file->getFilename());
            $key = Str::slug($title);
            if ($key === '') {
                continue;
            }
            $isDup = (bool) preg_match('/\(\s*\d+\s*\)\s*\.pdf$/i', $file->getFilename());
            if (!isset($byKey[$key]) || (!$isDup && isset($byKey[$key]['_dup']))) {
                $byKey[$key] = [
                    'file' => $file,
                    'title' => $title,
                    '_dup' => $isDup,
                ];
            }
        }

        $dry = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $prepareOnly = (bool) $this->option('prepare-only');

        if ($dry) {
            foreach ($byKey as $row) {
                $genre = $this->guessGenre($row['title']);
                $this->line("  [dry-run] {$row['title']} → {$genre}");
            }
            $this->info('Dry-run complete. '.count($byKey).' unique books.');
            return self::SUCCESS;
        }

        if (!$prepareOnly) {
            try {
                if (!Schema::hasTable('books')) {
                    $this->error('books table is missing. Run migrations first.');
                    return self::FAILURE;
                }
            } catch (\Throwable $e) {
                $this->error('Database unavailable: '.$e->getMessage());
                $this->line('Tip: use --prepare-only to generate covers/PDFs without DB, then import on the server.');
                return self::FAILURE;
            }
        }

        $ownerId = $prepareOnly ? 1 : $this->resolveOwnerUserId();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $prepared = 0;
        $manifest = [];

        Storage::disk('public')->makeDirectory('books/covers');
        Storage::disk('public')->makeDirectory('books/samples');

        foreach ($byKey as $slugBase => $row) {
            /** @var \SplFileInfo $file */
            $file = $row['file'];
            $title = $row['title'];
            $slug = $slugBase;

            if (strlen($slug) > 180) {
                $slug = substr($slug, 0, 180);
            }

            $genre = $this->guessGenre($title);
            $bookType = $this->guessBookType($genre);
            $description = $this->buildDescription($title, $genre);

            $coverRel = $this->generateCover($title, $slug, $prepared);
            $sampleRel = $this->storePdfSample($file->getPathname(), $slug);
            $prepared++;

            if ($prepareOnly) {
                $manifest[] = [
                    'title' => $title,
                    'slug' => $slug,
                    'genre' => $genre,
                    'book_type' => $bookType,
                    'description' => $description,
                    'cover_image' => $coverRel,
                    'sample_files' => [
                        [
                            'path' => $sampleRel,
                            'name' => $file->getFilename(),
                            'original_name' => $file->getFilename(),
                            'mime' => 'application/pdf',
                            'size' => $file->getSize(),
                        ],
                    ],
                ];
                $this->info("  prepared: {$title}");
                $created++;
                continue;
            }

            $existing = Book::where('slug', $slug)->first();
            if ($existing && !$force) {
                $this->line("  skip (exists): {$title}");
                $skipped++;
                continue;
            }

            $payload = [
                'title' => $title,
                'slug' => $slug,
                'description' => $description,
                'short_description' => Str::limit("Digital ebook: {$title}", 180, ''),
                'price' => 0,
                'currency' => 'USD',
                'cover_image' => $coverRel,
                'book_type' => $bookType,
                'genre' => $genre,
                'author_name' => 'WWA Books Collection',
                'country' => 'GB',
                'language' => 'en',
                'format' => 'ebook',
                'publisher' => 'Worldwide Adverts',
                'publication_date' => now()->toDateString(),
                'pages' => null,
                'status' => 'active',
                'advert_type' => 'standard',
                'user_id' => $ownerId,
                'verified_author' => false,
                'views_count' => 0,
                'saves_count' => 0,
                'sample_files' => [
                    [
                        'path' => $sampleRel,
                        'name' => $file->getFilename(),
                        'original_name' => $file->getFilename(),
                        'mime' => 'application/pdf',
                        'size' => $file->getSize(),
                    ],
                ],
            ];

            $filtered = [];
            foreach ($payload as $key => $value) {
                if (Schema::hasColumn('books', $key)) {
                    $filtered[$key] = $value;
                }
            }

            if ($existing) {
                $existing->update($filtered);
                $updated++;
                $this->info("  updated: {$title}");
            } else {
                Book::create($filtered);
                $created++;
                $this->info("  created: {$title}");
            }
        }

        $this->newLine();
        if ($prepareOnly) {
            $manifestPath = storage_path('app/book-imports/manifest.json');
            File::ensureDirectoryExists(dirname($manifestPath));
            File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info("Prepared {$prepared} covers + PDF samples under storage/app/public/books/");
            $this->info("Manifest: {$manifestPath}");
            $this->line('Upload storage/app/public/books and the manifest to the API server, then run:');
            $this->line('  php artisan books:import-pdfs --from-manifest');
        } else {
            $this->info("Done. created={$created} updated={$updated} skipped={$skipped}");
            $this->line('Admin: Filament → Marketplace → Books Adverts');
            $this->line('Site: /books  (covers via /storage/books/covers/…)');
            $this->line('Ensure: php artisan storage:link');
        }

        return self::SUCCESS;
    }

    /**
     * Insert DB rows from a previously prepared storage/manifest package.
     * Expects covers in storage/app/public/books/covers and PDFs in books/samples.
     */
    private function importFromManifest(): int
    {
        $manifestPath = storage_path('app/book-imports/manifest.json');
        if (!is_file($manifestPath)) {
            $this->error("Manifest not found: {$manifestPath}");
            $this->line('Run with --prepare-only first (or upload the manifest + storage/app/public/books).');
            return self::FAILURE;
        }

        try {
            if (!Schema::hasTable('books')) {
                $this->error('books table is missing. Run migrations first.');
                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error('Database unavailable: '.$e->getMessage());
            return self::FAILURE;
        }

        $rows = json_decode(File::get($manifestPath), true);
        if (!is_array($rows) || $rows === []) {
            $this->error('Manifest is empty or invalid JSON.');
            return self::FAILURE;
        }

        $ownerId = $this->resolveOwnerUserId();
        $force = (bool) $this->option('force');
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $title = (string) ($row['title'] ?? '');
            $slug = (string) ($row['slug'] ?? Str::slug($title));
            if ($title === '' || $slug === '') {
                continue;
            }

            $cover = (string) ($row['cover_image'] ?? '');
            $samples = $row['sample_files'] ?? [];
            if ($cover && !Storage::disk('public')->exists($cover)) {
                $this->warn("  missing cover in storage: {$cover}");
            }
            if (is_array($samples)) {
                foreach ($samples as $sample) {
                    $p = $sample['path'] ?? null;
                    if ($p && !Storage::disk('public')->exists($p)) {
                        $this->warn("  missing PDF in storage: {$p}");
                    }
                }
            }

            $existing = Book::where('slug', $slug)->first();
            if ($existing && !$force) {
                $this->line("  skip (exists): {$title}");
                $skipped++;
                continue;
            }

            $payload = [
                'title' => $title,
                'slug' => $slug,
                'description' => (string) ($row['description'] ?? $this->buildDescription($title, (string) ($row['genre'] ?? 'Non-Fiction'))),
                'short_description' => Str::limit('Digital ebook: '.$title, 180, ''),
                'price' => 0,
                'currency' => 'USD',
                'cover_image' => $cover,
                'book_type' => (string) ($row['book_type'] ?? 'non-fiction'),
                'genre' => (string) ($row['genre'] ?? 'Non-Fiction'),
                'author_name' => 'WWA Books Collection',
                'country' => 'GB',
                'language' => 'en',
                'format' => 'ebook',
                'publisher' => 'Worldwide Adverts',
                'publication_date' => now()->toDateString(),
                'status' => 'active',
                'advert_type' => 'standard',
                'user_id' => $ownerId,
                'verified_author' => false,
                'views_count' => 0,
                'saves_count' => 0,
                'sample_files' => is_array($samples) ? $samples : [],
            ];

            $filtered = [];
            foreach ($payload as $key => $value) {
                if (Schema::hasColumn('books', $key)) {
                    $filtered[$key] = $value;
                }
            }

            if ($existing) {
                $existing->update($filtered);
                $updated++;
                $this->info("  updated: {$title}");
            } else {
                Book::create($filtered);
                $created++;
                $this->info("  created: {$title}");
            }
        }

        $this->newLine();
        $this->info("Manifest import done. created={$created} updated={$updated} skipped={$skipped}");
        $this->line('Files live under storage/app/public/books/{covers,samples}');
        $this->line('Public URLs: /storage/books/covers/... and /storage/books/samples/...');
        $this->line('Ensure: php artisan storage:link');

        return self::SUCCESS;
    }

    private function resolveOwnerUserId(): int
    {
        $user = User::query()->orderBy('user_id')->first();
        if ($user && isset($user->user_id)) {
            return (int) $user->user_id;
        }

        $customer = Customer::query()->orderBy('id')->first();
        if ($customer) {
            return (int) ($customer->user_id ?? $customer->id ?? 1);
        }

        return 1;
    }

    private function titleFromFilename(string $filename): string
    {
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/\(\s*\d+\s*\)$/', '', $name) ?? $name;
        $name = str_replace(['_', '-', '.'], ' ', $name);
        // Split camelCase / glued words: 21Incomestreams → 21 Incomestreams
        $name = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $name) ?? $name;
        $name = preg_replace('/(?<=[A-Za-z])(?=\d)/', ' ', $name) ?? $name;
        $name = preg_replace('/(?<=\d)(?=[A-Za-z])/', ' ', $name) ?? $name;
        $name = preg_replace('/\s+/', ' ', trim($name)) ?? '';

        // Title-case while keeping small words tidy
        $titled = Str::title(Str::lower($name));
        $titled = preg_replace('/\b(Pdf|Ebook|E Book)\b/i', '', $titled) ?? $titled;
        $titled = preg_replace('/\s+/', ' ', trim($titled)) ?? $titled;

        return $titled !== '' ? $titled : 'Untitled Book';
    }

    private function guessGenre(string $title): string
    {
        $t = Str::lower($title);
        $map = [
            'Business' => ['business', 'profit', 'income', 'ebay', 'auction', 'ads', 'list build', 'success', 'wealth', 'money', 'leak', 'startup', 'product'],
            'Self-Help' => ['stress', 'success tips', 'skills', 'tips', 'ways', 'keys', 'miracle', 'permanent'],
            'Health' => ['weight', 'diet', 'atkins', 'arms', 'stretch', 'lose', 'pounds', 'crock pot', 'recipes', 'food'],
            'Home & DIY' => ['home', 'nursery', 'handy', 'decorating', 'scrapbook', 'photoshop', 'windows'],
            'Humor' => ['jokes', 'blonde', 'grandpa'],
            'Religion' => ['bible', 'contradictions'],
            'Romance' => ['romantic'],
            'Sports' => ['martial', 'striking'],
            'Parenting' => ['baby', 'nursery'],
        ];

        foreach ($map as $genre => $words) {
            foreach ($words as $word) {
                if (str_contains($t, $word)) {
                    return $genre;
                }
            }
        }

        return 'Non-Fiction';
    }

    private function guessBookType(string $genre): string
    {
        return match ($genre) {
            'Romance', 'Humor' => 'fiction',
            'Business' => 'business',
            'Self-Help', 'Health', 'Parenting' => 'self-help',
            default => 'non-fiction',
        };
    }

    private function buildDescription(string $title, string $genre): string
    {
        return implode(' ', [
            "{$title} is a digital ebook available on Worldwide Adverts Books.",
            "Genre: {$genre}.",
            'Read or download the included PDF sample from the book page.',
            'Listed from the WWA Books Collection for members browsing the marketplace.',
            'Discover practical guides, ideas and tips across business, home, health and more.',
        ]);
    }

    private function generateCover(string $title, string $slug, int $index): string
    {
        $width = 600;
        $height = 900;
        $img = imagecreatetruecolor($width, $height);

        [$r, $g, $b] = $this->palettes[$index % count($this->palettes)];
        $bg = imagecolorallocate($img, $r, $g, $b);
        $bg2 = imagecolorallocate($img, max(0, $r - 40), max(0, $g - 40), max(0, $b - 40));
        $white = imagecolorallocate($img, 255, 255, 255);
        $muted = imagecolorallocate($img, 226, 232, 240);

        // Gradient-ish fill
        for ($y = 0; $y < $height; $y++) {
            $t = $y / $height;
            $rr = (int) ($r * (1 - $t) + ($r - 40) * $t);
            $gg = (int) ($g * (1 - $t) + ($g - 40) * $t);
            $bb = (int) ($b * (1 - $t) + ($b - 40) * $t);
            $col = imagecolorallocate($img, max(0, $rr), max(0, $gg), max(0, $bb));
            imageline($img, 0, $y, $width, $y, $col);
        }

        // Frame
        imagerectangle($img, 24, 24, $width - 25, $height - 25, $muted);
        imagerectangle($img, 28, 28, $width - 29, $height - 29, $white);

        $font = $this->resolveFont();
        $brand = 'WORLDWIDE ADVERTS';
        $lines = $this->wrapTitle($title, 22);

        if ($font) {
            imagettftext($img, 14, 0, 48, 70, $muted, $font, $brand);
            $y = 220;
            foreach ($lines as $line) {
                imagettftext($img, 28, 0, 48, $y, $white, $font, $line);
                $y += 44;
            }
            imagettftext($img, 16, 0, 48, $height - 80, $muted, $font, 'WWA Books Collection');
            imagettftext($img, 14, 0, 48, $height - 52, $muted, $font, 'Digital ebook');
        } else {
            imagestring($img, 5, 48, 50, $brand, $muted);
            $y = 200;
            foreach ($lines as $line) {
                imagestring($img, 5, 48, $y, $line, $white);
                $y += 28;
            }
            imagestring($img, 4, 48, $height - 80, 'WWA Books Collection', $muted);
        }

        $rel = 'books/covers/'.$slug.'.jpg';
        $absolute = Storage::disk('public')->path($rel);
        File::ensureDirectoryExists(dirname($absolute));
        imagejpeg($img, $absolute, 88);
        imagedestroy($img);

        return $rel;
    }

    private function resolveFont(): ?string
    {
        $candidates = [
            'C:/Windows/Fonts/arialbd.ttf',
            'C:/Windows/Fonts/arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ];
        foreach ($candidates as $font) {
            if (is_file($font)) {
                return $font;
            }
        }
        return null;
    }

    /** @return list<string> */
    private function wrapTitle(string $title, int $maxChars): array
    {
        $words = preg_split('/\s+/', $title) ?: [];
        $lines = [];
        $current = '';
        foreach ($words as $word) {
            $trial = $current === '' ? $word : $current.' '.$word;
            if (strlen($trial) > $maxChars && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $trial;
            }
        }
        if ($current !== '') {
            $lines[] = $current;
        }
        return array_slice($lines, 0, 8);
    }

    private function storePdfSample(string $sourcePath, string $slug): string
    {
        // Always persist under the public disk so Filament + /storage URLs work
        $rel = 'books/samples/'.$slug.'.pdf';
        $stream = fopen($sourcePath, 'rb');
        if ($stream === false) {
            throw new \RuntimeException("Cannot read PDF: {$sourcePath}");
        }
        Storage::disk('public')->put($rel, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
        return $rel;
    }
}
