<?php

namespace App\Console\Commands;

use App\Helpers\MediaUrlHelper;
use App\Models\Property;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class VerifyPropertyMedia extends Command
{
    protected $signature = 'property:verify-media
                            {--fix : Attempt to recreate the public/storage symlink}
                            {--limit=50 : Max properties to scan}';

    protected $description = 'Check property cover/logo files exist on the public disk and report missing storage';

    public function handle(): int
    {
        $this->info('Checking storage link…');
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (! File::exists($link)) {
            $this->error('public/storage is missing.');
            if ($this->option('fix')) {
                $this->call('storage:link');
            } else {
                $this->warn('Run: php artisan storage:link');
            }
        } else {
            $this->line("public/storage → OK ({$link})");
        }

        if (! File::isDirectory($target.'/properties')) {
            $this->warn('storage/app/public/properties directory does not exist yet.');
            File::ensureDirectoryExists($target.'/properties/cover');
            File::ensureDirectoryExists($target.'/properties/logos');
            File::ensureDirectoryExists($target.'/properties/additional');
            $this->line('Created properties/cover, logos, additional folders.');
        }

        $limit = (int) $this->option('limit');
        $missing = 0;
        $ok = 0;

        Property::query()
            ->whereNotNull('cover_image')
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'title', 'cover_image', 'seller_logo'])
            ->each(function (Property $property) use (&$missing, &$ok) {
                $coverOk = MediaUrlHelper::existsOnPublicDisk($property->cover_image);
                $url = MediaUrlHelper::resolve($property->cover_image);

                if ($coverOk) {
                    $ok++;
                    $this->line("OK  #{$property->id}  {$url}");
                } else {
                    $missing++;
                    $this->error("MISSING  #{$property->id}  db={$property->cover_image}  url={$url}");
                }
            });

        $this->newLine();
        $this->info("Done. OK={$ok} MISSING={$missing}");

        if ($missing > 0) {
            $this->warn('Upload missing files into storage/app/public/ (then ensure public/storage is linked).');
            $this->warn('On Hostinger: copy local storage/app/public/properties to the server path.');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
