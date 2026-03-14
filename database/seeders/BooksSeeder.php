<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BooksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $books = [
            [
                'title' => 'Laravel: The Complete Guide',
                'slug' => Str::slug('Laravel: The Complete Guide'),
                'description' => 'Comprehensive guide to Laravel framework covering all aspects from basics to advanced topics.',
                'short_description' => 'Complete Laravel guide',
                'book_type' => 'non-fiction',
                'genre' => 'Programming',
                'author_name' => 'John Developer',
                'price' => 49.99,
                'currency' => 'USD',
                'format' => 'paperback',
                'cover_image' => 'books/laravel-complete-guide.jpg',
                'country' => 'US',
                'language' => 'en',
                'status' => 'active',
                'user_id' => 1, // Assuming user_id 1 exists
                'advert_type' => 'standard',
            ],
            [
                'title' => 'PHP Mastery: Advanced Techniques',
                'slug' => Str::slug('PHP Mastery: Advanced Techniques'),
                'description' => 'Learn advanced PHP techniques and best practices for professional development.',
                'short_description' => 'Advanced PHP techniques',
                'book_type' => 'non-fiction',
                'genre' => 'Programming',
                'author_name' => 'Jane Coder',
                'price' => 39.99,
                'currency' => 'USD',
                'format' => 'hardcover',
                'cover_image' => 'books/php-mastery.jpg',
                'country' => 'US',
                'language' => 'en',
                'status' => 'active',
                'user_id' => 1,
                'advert_type' => 'standard',
            ],
            [
                'title' => 'JavaScript: Modern Development',
                'slug' => Str::slug('JavaScript: Modern Development'),
                'description' => 'Master modern JavaScript development with ES6+ features and frameworks.',
                'short_description' => 'Modern JS development',
                'book_type' => 'non-fiction',
                'genre' => 'Programming',
                'author_name' => 'Bob Script',
                'price' => 44.99,
                'currency' => 'USD',
                'format' => 'ebook',
                'cover_image' => 'books/javascript-modern.jpg',
                'country' => 'US',
                'language' => 'en',
                'status' => 'active',
                'user_id' => 1,
                'advert_type' => 'promoted',
            ],
            [
                'title' => 'Vue.js: From Zero to Hero',
                'slug' => Str::slug('Vue.js: From Zero to Hero'),
                'description' => 'Complete course on Vue.js framework for building modern web applications.',
                'short_description' => 'Vue.js complete course',
                'book_type' => 'non-fiction',
                'genre' => 'Programming',
                'author_name' => 'Alice Vue',
                'price' => 34.99,
                'currency' => 'USD',
                'format' => 'ebook',
                'cover_image' => 'books/vuejs-zero-hero.jpg',
                'country' => 'US',
                'language' => 'en',
                'status' => 'active',
                'user_id' => 1,
                'advert_type' => 'featured',
            ],
            [
                'title' => 'React Development Handbook',
                'slug' => Str::slug('React Development Handbook'),
                'description' => 'Comprehensive handbook for React development with hooks and modern patterns.',
                'short_description' => 'React development guide',
                'book_type' => 'non-fiction',
                'genre' => 'Programming',
                'author_name' => 'Charlie React',
                'price' => 42.99,
                'currency' => 'USD',
                'format' => 'paperback',
                'cover_image' => 'books/react-handbook.jpg',
                'country' => 'US',
                'language' => 'en',
                'status' => 'active',
                'user_id' => 1,
                'advert_type' => 'sponsored',
            ],
        ];

        foreach ($books as $book) {
            Book::create($book);
        }
    }
}

