<?php

namespace Database\Seeders;

use App\Models\Blog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if blog table exists
        if (!\Schema::hasTable('blog')) {
            $this->command->warn('Blog table does not exist. Skipping BlogSeeder.');
            return;
        }

        $blogs = [
            [
                'title' => 'Getting Started with Laravel',
                'slug' => Str::slug('Getting Started with Laravel'),
                'content' => 'This is a comprehensive guide to getting started with Laravel framework. Learn the basics and build your first application.',
                'excerpt' => 'Learn how to get started with Laravel framework',
                'image' => 'blog/laravel-getting-started.jpg',
                'status' => 'published',
                'is_active' => true,
            ],
            [
                'title' => 'Best Practices for Web Development',
                'slug' => Str::slug('Best Practices for Web Development'),
                'content' => 'Discover the best practices for modern web development including code organization, security, and performance optimization.',
                'excerpt' => 'Essential best practices for web developers',
                'image' => 'blog/web-dev-best-practices.jpg',
                'status' => 'published',
                'is_active' => true,
            ],
            [
                'title' => 'Introduction to Vue.js',
                'slug' => Str::slug('Introduction to Vue.js'),
                'content' => 'A beginner-friendly introduction to Vue.js framework. Learn how to build reactive user interfaces with ease.',
                'excerpt' => 'Start learning Vue.js today',
                'image' => 'blog/vuejs-intro.jpg',
                'status' => 'published',
                'is_active' => true,
            ],
            [
                'title' => 'API Development Tips',
                'slug' => Str::slug('API Development Tips'),
                'content' => 'Learn essential tips and tricks for developing robust and secure REST APIs for your applications.',
                'excerpt' => 'Expert tips for API development',
                'image' => 'blog/api-development-tips.jpg',
                'status' => 'published',
                'is_active' => true,
            ],
            [
                'title' => 'Database Optimization Techniques',
                'slug' => Str::slug('Database Optimization Techniques'),
                'content' => 'Explore various techniques to optimize your database queries and improve application performance.',
                'excerpt' => 'Improve your database performance',
                'image' => 'blog/database-optimization.jpg',
                'status' => 'published',
                'is_active' => true,
            ],
        ];

        foreach ($blogs as $blog) {
            Blog::firstOrCreate(
                ['slug' => $blog['slug']],
                $blog
            );
        }
    }
}

