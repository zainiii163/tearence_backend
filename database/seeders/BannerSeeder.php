<?php

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
                'title' => 'Welcome Banner',
                'url_link' => 'https://example.com/welcome',
                'img' => 'banners/welcome-banner.jpg',
                'size_img' => '1920x600',
                'created_by' => 'system',
            ],
            [
                'title' => 'Promotional Banner',
                'url_link' => 'https://example.com/promo',
                'img' => 'banners/promo-banner.jpg',
                'size_img' => '1920x600',
                'created_by' => 'system',
            ],
            [
                'title' => 'Featured Banner',
                'url_link' => 'https://example.com/featured',
                'img' => 'banners/featured-banner.jpg',
                'size_img' => '728x90',
                'created_by' => 'system',
            ],
            [
                'title' => 'Sidebar Banner',
                'url_link' => 'https://example.com/sidebar',
                'img' => 'banners/sidebar-banner.jpg',
                'size_img' => '300x250',
                'created_by' => 'system',
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}

