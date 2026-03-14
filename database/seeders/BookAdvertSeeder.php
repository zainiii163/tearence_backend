<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BookAdvert;
use App\Models\User;
use Illuminate\Support\Str;

class BookAdvertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get a sample user (or create one)
        $user = User::first() ?: User::factory()->create([
            'name' => 'Sample Author',
            'email' => 'author@example.com',
            'password' => bcrypt('password'),
        ]);

        $sampleBooks = [
            [
                'user_id' => $user->id,
                'title' => 'The Great Adventure',
                'subtitle' => 'An Epic Journey',
                'slug' => 'the-great-adventure-' . Str::random(6),
                'description' => 'A thrilling adventure story that takes readers on an unforgettable journey through mysterious lands and ancient secrets. Follow our hero as they discover hidden treasures and face incredible challenges.',
                'short_description' => 'An epic adventure story full of mystery and excitement.',
                'author_name' => 'John Smith',
                'author_bio' => 'John Smith is an award-winning author with over 20 years of experience writing adventure novels.',
                'publisher' => 'Adventure Publishing House',
                'publication_date' => '2024-01-15',
                'isbn' => '978-0123456789',
                'pages' => 350,
                'language' => 'en',
                'genre' => 'Fiction',
                'format' => 'paperback',
                'book_type' => 'fiction',
                'price' => 19.99,
                'currency' => 'USD',
                'country' => 'United States',
                'location_address' => '123 Main St, New York, NY',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'cover_image_url' => 'https://via.placeholder.com/400x600.png?text=Book+Cover+1',
                'additional_images' => [
                    'https://via.placeholder.com/400x600.png?text=Book+Image+1',
                    'https://via.placeholder.com/400x600.png?text=Book+Image+2'
                ],
                'trailer_video_url' => 'https://youtube.com/watch?v=example',
                'sample_files' => [
                    'https://example.com/sample-chapter1.pdf'
                ],
                'purchase_links' => [
                    [
                        'platform' => 'Amazon',
                        'url' => 'https://amazon.com/dp/B00EXAMPLE'
                    ],
                    [
                        'platform' => 'Barnes & Noble',
                        'url' => 'https://barnesandnoble.com/w/example'
                    ]
                ],
                'views_count' => 1250,
                'saves_count' => 89,
                'rating' => 4.5,
                'reviews_count' => 23,
                'verified_author' => true,
                'advert_type' => 'featured',
                'upsell_tier' => 3,
                'promoted_until' => now()->addDays(60),
                'status' => 'active',
                'agreed_to_terms' => true,
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(10),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Mystery Tales',
                'subtitle' => 'Stories That Keep You Guessing',
                'slug' => 'mystery-tales-' . Str::random(6),
                'description' => 'A collection of spine-chilling mystery stories that will keep you on the edge of your seat. Each tale is carefully crafted to deliver maximum suspense and unexpected twists.',
                'short_description' => 'A collection of thrilling mystery stories.',
                'author_name' => 'Sarah Johnson',
                'author_bio' => 'Sarah Johnson is a master of the mystery genre, with numerous bestsellers to her name.',
                'publisher' => 'Mystery Press',
                'publication_date' => '2024-02-20',
                'isbn' => '978-0987654321',
                'pages' => 280,
                'language' => 'en',
                'genre' => 'Mystery',
                'format' => 'hardcover',
                'book_type' => 'fiction',
                'price' => 24.99,
                'currency' => 'USD',
                'country' => 'United Kingdom',
                'location_address' => '45 Baker Street, London',
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'cover_image_url' => 'https://via.placeholder.com/400x600.png?text=Book+Cover+2',
                'additional_images' => [
                    'https://via.placeholder.com/400x600.png?text=Book+Image+3',
                    'https://via.placeholder.com/400x600.png?text=Book+Image+4'
                ],
                'trailer_video_url' => 'https://youtube.com/watch?v=example2',
                'sample_files' => [
                    'https://example.com/sample-chapter2.pdf'
                ],
                'purchase_links' => [
                    [
                        'platform' => 'Amazon UK',
                        'url' => 'https://amazon.co.uk/dp/B00EXAMPLE2'
                    ]
                ],
                'views_count' => 890,
                'saves_count' => 67,
                'rating' => 4.2,
                'reviews_count' => 18,
                'verified_author' => false,
                'advert_type' => 'promoted',
                'upsell_tier' => 2,
                'promoted_until' => now()->addDays(30),
                'status' => 'active',
                'agreed_to_terms' => true,
                'created_at' => now()->subDays(10),
                'updated_at' => now()->subDays(5),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Digital Marketing Guide',
                'subtitle' => 'Strategies for Success',
                'slug' => 'digital-marketing-guide-' . Str::random(6),
                'description' => 'A comprehensive guide to digital marketing strategies that work in today\'s competitive landscape. Learn from industry experts and case studies.',
                'short_description' => 'Complete digital marketing strategies guide.',
                'author_name' => 'Michael Chen',
                'author_bio' => 'Michael Chen is a digital marketing expert with 15 years of experience helping businesses grow online.',
                'publisher' => 'Business Publications',
                'publication_date' => '2024-03-01',
                'isbn' => '978-1234567890',
                'pages' => 420,
                'language' => 'en',
                'genre' => 'Business',
                'format' => 'ebook',
                'book_type' => 'non-fiction',
                'price' => 0.00,
                'currency' => 'USD',
                'country' => 'Canada',
                'location_address' => '789 Commerce St, Toronto, ON',
                'latitude' => 43.6532,
                'longitude' => -79.3832,
                'cover_image_url' => 'https://via.placeholder.com/400x600.png?text=Book+Cover+3',
                'additional_images' => [
                    'https://via.placeholder.com/400x600.png?text=Book+Image+5'
                ],
                'trailer_video_url' => null,
                'sample_files' => [
                    'https://example.com/sample-chapter3.pdf'
                ],
                'purchase_links' => [
                    [
                        'platform' => 'Apple Books',
                        'url' => 'https://books.apple.com/book/example'
                    ]
                ],
                'views_count' => 2340,
                'saves_count' => 156,
                'rating' => 4.8,
                'reviews_count' => 45,
                'verified_author' => true,
                'advert_type' => 'sponsored',
                'upsell_tier' => 4,
                'promoted_until' => now()->addDays(90),
                'status' => 'active',
                'agreed_to_terms' => true,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(2),
            ]
        ];

        foreach ($sampleBooks as $book) {
            BookAdvert::create($book);
        }
    }
}
