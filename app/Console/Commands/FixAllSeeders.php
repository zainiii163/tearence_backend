<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixAllSeeders extends Command
{
    protected $signature = 'db:fix-all-seeders';
    protected $description = 'Fix all seeders to match actual database table structures';

    public function handle()
    {
        $this->info('🔧 Fixing all seeders to match database structures...');
        
        $seedersPath = database_path('seeders/');
        $seedersToFix = [
            'BannerSeeder.php',
            'ListingSeeder.php',
            'BooksSeeder.php',
            'SampleListingsSeeder.php',
        ];

        $fixed = 0;

        foreach ($seedersToFix as $file) {
            $filePath = $seedersPath . $file;
            
            if (!File::exists($filePath)) {
                $this->line("⏭️  $file (not found)");
                continue;
            }

            $this->info("🔧 Fixing $file...");
            
            if ($file === 'BannerSeeder.php') {
                $this->fixBannerSeeder($filePath);
                $fixed++;
            } elseif ($file === 'ListingSeeder.php') {
                $this->fixListingSeeder($filePath);
                $fixed++;
            } elseif ($file === 'BooksSeeder.php') {
                $this->fixBooksSeeder($filePath);
                $fixed++;
            } elseif ($file === 'SampleListingsSeeder.php') {
                $this->fixSampleListingsSeeder($filePath);
                $fixed++;
            }
        }

        $this->info("\n📊 Fixed $fixed seeder files");
        $this->info("Now run: php artisan db:seed");

        return 0;
    }

    private function fixBannerSeeder($filePath)
    {
        $content = File::get($filePath);
        
        $content = preg_replace(
            '/<\?php.*?class BannerSeeder.*?\{.*?public function run\(\): void\s*\{.*?\$banners = \[.*?\];.*?foreach.*?\}/s',
            $this->getBannerSeederContent(),
            $content
        );
        
        File::put($filePath, $content);
        $this->info("✅ Fixed BannerSeeder");
    }

    private function fixListingSeeder($filePath)
    {
        $content = File::get($filePath);
        
        // Remove approval_status references since they don't exist in the table
        $content = preg_replace(
            "/'approval_status' => 'pending',\s*\n/",
            '',
            $content
        );
        
        $content = preg_replace(
            "/'approved_by' => 1,\s*\n/",
            '',
            $content
        );
        
        $content = preg_replace(
            "/'approved_at' => now\(\),\s*\n/",
            '',
            $content
        );
        
        File::put($filePath, $content);
        $this->info("✅ Fixed ListingSeeder");
    }

    private function fixBooksSeeder($filePath)
    {
        $content = File::get($filePath);
        
        // Remove is_promoted, is_featured, is_sponsored references
        $content = preg_replace(
            "/'is_promoted' => true,\s*\n/",
            '',
            $content
        );
        
        $content = preg_replace(
            "/'is_featured' => true,\s*\n/",
            '',
            $content
        );
        
        $content = preg_replace(
            "/'is_sponsored' => true,\s*\n/",
            '',
            $content
        );
        
        File::put($filePath, $content);
        $this->info("✅ Fixed BooksSeeder");
    }

    private function fixSampleListingsSeeder($filePath)
    {
        $content = File::get($filePath);
        
        // Remove approval_status references
        $content = preg_replace(
            "/'approval_status' => 'pending',\s*\n/",
            '',
            $content
        );
        
        $content = preg_replace(
            "/'approved_by' => 1,\s*\n/",
            '',
            $content
        );
        
        $content = preg_replace(
            "/'approved_at' => now\(\),\s*\n/",
            '',
            $content
        );
        
        File::put($filePath, $content);
        $this->info("✅ Fixed SampleListingsSeeder");
    }

    private function getBannerSeederContent()
    {
        return '<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                \'title\' => \'Welcome Banner\',
                \'url_link\' => \'https://example.com/welcome\',
                \'img\' => \'banners/welcome-banner.jpg\',
                \'size_img\' => \'1920x600\',
                \'banner_size\' => \'1920x600\',
                \'destination_url\' => \'https://example.com/welcome\',
                \'country\' => \'US\',
                \'city\' => \'New York\',
                \'created_by\' => \'system\',
            ],
            [
                \'title\' => \'Promotional Banner\',
                \'url_link\' => \'https://example.com/promo\',
                \'img\' => \'banners/promo-banner.jpg\',
                \'size_img\' => \'1920x600\',
                \'banner_size\' => \'1920x600\',
                \'destination_url\' => \'https://example.com/promo\',
                \'country\' => \'US\',
                \'city\' => \'Los Angeles\',
                \'created_by\' => \'system\',
            ],
            [
                \'title\' => \'Featured Banner\',
                \'url_link\' => \'https://example.com/featured\',
                \'img\' => \'banners/featured-banner.jpg\',
                \'size_img\' => \'728x90\',
                \'banner_size\' => \'728x90\',
                \'destination_url\' => \'https://example.com/featured\',
                \'country\' => \'US\',
                \'city\' => \'Chicago\',
                \'created_by\' => \'system\',
            ],
            [
                \'title\' => \'Sidebar Banner\',
                \'url_link\' => \'https://example.com/sidebar\',
                \'img\' => \'banners/sidebar-banner.jpg\',
                \'size_img\' => \'300x250\',
                \'banner_size\' => \'300x250\',
                \'destination_url\' => \'https://example.com/sidebar\',
                \'country\' => \'US\',
                \'city\' => \'Houston\',
                \'created_by\' => \'system\',
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}';
    }
}
