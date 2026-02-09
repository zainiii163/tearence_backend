<?php

namespace Database\Seeders;

use App\Models\Affiliate;
use Illuminate\Database\Seeder;

class AffiliateLinksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $affiliateLinks = [
            [
                'position' => 'header',
                'link' => 'https://example.com/partner1',
                'title' => 'Partner Link 1',
                'status' => 'active',
            ],
            [
                'position' => 'footer',
                'link' => 'https://example.com/partner2',
                'title' => 'Partner Link 2',
                'status' => 'active',
            ],
            [
                'position' => 'sidebar',
                'link' => 'https://example.com/partner3',
                'title' => 'Partner Link 3',
                'status' => 'active',
            ],
            [
                'position' => 'header',
                'link' => 'https://example.com/partner4',
                'title' => 'Partner Link 4',
                'status' => 'active',
            ],
        ];

        foreach ($affiliateLinks as $link) {
            Affiliate::create($link);
        }
    }
}

