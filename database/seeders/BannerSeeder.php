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
                'banner_size' => '1920x600',
                'destination_url' => 'https://example.com/welcome',
                'country' => 'US',
                'city' => 'New York',
                'created_by' => 'system',
            ],
            [
                'title' => 'Promotional Banner',
                'url_link' => 'https://example.com/promo',
                'img' => 'banners/promo-banner.jpg',
                'size_img' => '1920x600',
                'banner_size' => '1920x600',
                'destination_url' => 'https://example.com/promo',
                'country' => 'US',
                'city' => 'Los Angeles',
                'created_by' => 'system',
            ],
            [
                'title' => 'Featured Banner',
                'url_link' => 'https://example.com/featured',
                'img' => 'banners/featured-banner.jpg',
                'size_img' => '728x90',
                'banner_size' => '728x90',
                'destination_url' => 'https://example.com/featured',
                'country' => 'US',
                'city' => 'Chicago',
                'created_by' => 'system',
            ],
            [
                'title' => 'Sidebar Banner',
                'url_link' => 'https://example.com/sidebar',
                'img' => 'banners/sidebar-banner.jpg',
                'size_img' => '300x250',
                'banner_size' => '300x250',
                'destination_url' => 'https://example.com/sidebar',
                'country' => 'US',
                'city' => 'Houston',
                'created_by' => 'system',
            ],
        ];

        foreach ($banners as $banner) {
            Banner::create($banner);
        }
    }
}

