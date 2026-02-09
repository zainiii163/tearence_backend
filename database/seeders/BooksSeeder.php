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
                'price' => 49.99,
                'image_url' => 'books/laravel-complete-guide.jpg',
                'link_url' => 'https://example.com/books/laravel-guide',
                'status' => 'active',
            ],
            [
                'title' => 'PHP Mastery: Advanced Techniques',
                'slug' => Str::slug('PHP Mastery: Advanced Techniques'),
                'description' => 'Learn advanced PHP techniques and best practices for professional development.',
                'short_description' => 'Advanced PHP techniques',
                'price' => 39.99,
                'image_url' => 'books/php-mastery.jpg',
                'link_url' => 'https://example.com/books/php-mastery',
                'status' => 'active',
            ],
            [
                'title' => 'JavaScript: Modern Development',
                'slug' => Str::slug('JavaScript: Modern Development'),
                'description' => 'Master modern JavaScript development with ES6+ features and frameworks.',
                'short_description' => 'Modern JS development',
                'price' => 44.99,
                'image_url' => 'books/javascript-modern.jpg',
                'link_url' => 'https://example.com/books/javascript-modern',
                'status' => 'active',
            ],
            [
                'title' => 'Vue.js: From Zero to Hero',
                'slug' => Str::slug('Vue.js: From Zero to Hero'),
                'description' => 'Complete course on Vue.js framework for building modern web applications.',
                'short_description' => 'Vue.js complete course',
                'price' => 34.99,
                'image_url' => 'books/vuejs-zero-hero.jpg',
                'link_url' => 'https://example.com/books/vuejs-course',
                'status' => 'active',
            ],
            [
                'title' => 'React Development Handbook',
                'slug' => Str::slug('React Development Handbook'),
                'description' => 'Comprehensive handbook for React development with hooks and modern patterns.',
                'short_description' => 'React development guide',
                'price' => 42.99,
                'image_url' => 'books/react-handbook.jpg',
                'link_url' => 'https://example.com/books/react-handbook',
                'status' => 'active',
            ],
        ];

        foreach ($books as $book) {
            Book::create($book);
        }
    }
}

