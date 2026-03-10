<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BooksAuthorsSeeder extends Seeder
{
    public function run()
    {
        // Create Book Categories
        $categories = [
            ['name' => 'Fiction', 'slug' => 'fiction', 'description' => 'Novels and short stories', 'icon' => 'book-open', 'sort_order' => 1],
            ['name' => 'Non-Fiction', 'slug' => 'non-fiction', 'description' => 'Biographies, history, and educational books', 'icon' => 'book', 'sort_order' => 2],
            ['name' => 'Science Fiction', 'slug' => 'science-fiction', 'description' => 'Sci-fi and fantasy literature', 'icon' => 'rocket', 'sort_order' => 3],
            ['name' => 'Mystery & Thriller', 'slug' => 'mystery-thriller', 'description' => 'Mystery novels and thrillers', 'icon' => 'user-secret', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            DB::table('book_categories')->insert(array_merge($category, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Authors
        $authors = [
            [
                'name' => 'Sarah Mitchell',
                'slug' => 'sarah-mitchell',
                'bio' => 'Bestselling author of contemporary fiction and mystery novels. Sarah has published over 15 books and has been translated into 25 languages.',
                'photo' => 'authors/sarah-mitchell.jpg',
                'email' => 'contact@sarahmitchell.com',
                'website' => 'https://sarahmitchell.com',
                'social_links' => json_encode([
                    'twitter' => 'https://twitter.com/sarahmitchell',
                    'instagram' => 'https://instagram.com/sarahmitchell'
                ]),
                'country' => 'US',
                'verified' => true,
                'user_id' => 2,
                'books_count' => 15,
                'average_rating' => 4.6,
                'total_reviews' => 2847,
            ],
            [
                'name' => 'Dr. James Chen',
                'slug' => 'james-chen',
                'bio' => 'Physicist and science fiction author. Dr. Chen combines his scientific expertise with imaginative storytelling to create compelling sci-fi narratives.',
                'photo' => 'authors/james-chen.jpg',
                'email' => 'james@jameschen.com',
                'website' => 'https://jameschen.com',
                'social_links' => json_encode([
                    'twitter' => 'https://twitter.com/drjameschen',
                    'linkedin' => 'https://linkedin.com/in/jameschen'
                ]),
                'country' => 'US',
                'verified' => true,
                'user_id' => 3,
                'books_count' => 8,
                'average_rating' => 4.4,
                'total_reviews' => 1256,
            ],
            [
                'name' => 'Elena Rodriguez',
                'slug' => 'elena-rodriguez',
                'bio' => 'Mystery thriller writer known for her gripping psychological thrillers and complex plot twists.',
                'photo' => 'authors/elena-rodriguez.jpg',
                'email' => 'elena@elenarodriguez.com',
                'website' => 'https://elenarodriguez.com',
                'social_links' => json_encode([
                    'twitter' => 'https://twitter.com/elenarodriguez',
                    'facebook' => 'https://facebook.com/elenarodriguez'
                ]),
                'country' => 'ES',
                'verified' => true,
                'user_id' => 4,
                'books_count' => 12,
                'average_rating' => 4.7,
                'total_reviews' => 1923,
            ],
        ];

        foreach ($authors as $author) {
            DB::table('authors')->insert(array_merge($author, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Book Upsells
        $upsells = [
            [
                'book_id' => 1,
                'upsell_type' => 'featured',
                'price' => 50.00,
                'currency' => 'USD',
                'duration_days' => 30,
                'starts_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addDays(30),
                'status' => 'active',
                'benefits' => json_encode(['Homepage placement', 'Newsletter feature', 'Social media promotion']),
                'payment_reference' => 'book_upsell_001',
                'payment_date' => Carbon::now(),
                'user_id' => 2,
            ],
            [
                'book_id' => 2,
                'upsell_type' => 'promoted',
                'price' => 35.00,
                'currency' => 'USD',
                'duration_days' => 21,
                'starts_at' => Carbon::now()->subDays(5),
                'expires_at' => Carbon::now()->addDays(16),
                'status' => 'active',
                'benefits' => json_encode(['Category placement', 'Email blast']),
                'payment_reference' => 'book_upsell_002',
                'payment_date' => Carbon::now()->subDays(5),
                'user_id' => 3,
            ],
            [
                'book_id' => 3,
                'upsell_type' => 'sponsored',
                'price' => 75.00,
                'currency' => 'USD',
                'duration_days' => 45,
                'starts_at' => Carbon::now()->subDays(10),
                'expires_at' => Carbon::now()->addDays(35),
                'status' => 'active',
                'benefits' => json_encode(['Premium placement', 'Author interview', 'Blog feature']),
                'payment_reference' => 'book_upsell_003',
                'payment_date' => Carbon::now()->subDays(10),
                'user_id' => 4,
            ],
        ];

        foreach ($upsells as $upsell) {
            DB::table('book_upsells')->insert(array_merge($upsell, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Book Saves
        $saves = [
            ['book_id' => 1, 'user_id' => 5],
            ['book_id' => 1, 'user_id' => 6],
            ['book_id' => 2, 'user_id' => 5],
            ['book_id' => 2, 'user_id' => 7],
            ['book_id' => 3, 'user_id' => 6],
            ['book_id' => 3, 'user_id' => 7],
            ['book_id' => 3, 'user_id' => 8],
        ];

        foreach ($saves as $save) {
            DB::table('book_saves')->insert(array_merge($save, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Books & Authors seeder completed successfully!');
    }
}
