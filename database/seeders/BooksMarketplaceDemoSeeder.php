<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Seed realistic active books so /books grid + featured reel are not empty.
 * Safe to re-run (updates by slug).
 */
class BooksMarketplaceDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('books')) {
            $this->command?->warn('books table missing — skip BooksMarketplaceDemoSeeder');
            return;
        }

        $owner = Customer::where('email', 'books-demo@worldwideadverts.info')->first()
            ?? Customer::where('email', 'zainii@gmail.com')->first()
            ?? Customer::query()->first();

        $userId = $owner?->user_id ?? $owner?->id ?? 1;

        $covers = [
            'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1476275466078-4007374efbbe?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1519682337058-a94d519337bc?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1532012197267-da84d127e765?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1456513080080-7e9d0f6f3b0b?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1463320726281-696a485928c7?auto=format&fit=crop&w=600&q=80',
            'https://images.unsplash.com/photo-1516979187457-637abb4f9353?auto=format&fit=crop&w=600&q=80',
        ];

        $books = [
            ['title' => 'The Quiet Harbour', 'genre' => 'Fiction', 'type' => 'fiction', 'author' => 'Amelia Croft', 'price' => 14.99, 'advert' => 'featured'],
            ['title' => 'Code & Compass', 'genre' => 'Programming', 'type' => 'academic', 'author' => 'Noah Patel', 'price' => 29.00, 'advert' => 'promoted'],
            ['title' => 'Midnight Ledger', 'genre' => 'Mystery', 'type' => 'fiction', 'author' => 'Elena Vargas', 'price' => 12.50, 'advert' => 'sponsored'],
            ['title' => 'Startups That Stick', 'genre' => 'Business', 'type' => 'business', 'author' => 'James Okonkwo', 'price' => 19.99, 'advert' => 'featured'],
            ['title' => 'Garden of Small Joys', 'genre' => 'Self-Help', 'type' => 'self-help', 'author' => 'Priya Shah', 'price' => 11.00, 'advert' => 'standard'],
            ['title' => 'Orbit of Us', 'genre' => 'Sci-Fi', 'type' => 'fiction', 'author' => 'Marcus Hale', 'price' => 16.75, 'advert' => 'promoted'],
            ['title' => 'Empire of Dust', 'genre' => 'History', 'type' => 'non-fiction', 'author' => 'Claire Bennett', 'price' => 22.00, 'advert' => 'standard'],
            ['title' => 'Hearts in Transit', 'genre' => 'Romance', 'type' => 'fiction', 'author' => 'Sofia Almeida', 'price' => 9.99, 'advert' => 'featured'],
            ['title' => 'The Founder\'s Notebook', 'genre' => 'Business', 'type' => 'business', 'author' => 'Liam Chen', 'price' => 24.50, 'advert' => 'standard'],
            ['title' => 'Whispers Under Glass', 'genre' => 'Thriller', 'type' => 'fiction', 'author' => 'Ivy Moreau', 'price' => 13.25, 'advert' => 'promoted'],
            ['title' => 'Maps of Memory', 'genre' => 'Biography', 'type' => 'non-fiction', 'author' => 'Daniel Okoro', 'price' => 17.00, 'advert' => 'standard'],
            ['title' => 'Little Storms', 'genre' => 'Children', 'type' => 'children', 'author' => 'Maya Brooks', 'price' => 8.50, 'advert' => 'featured'],
        ];

        foreach ($books as $i => $row) {
            $slug = Str::slug($row['title']).'-demo';
            $payload = [
                'title' => $row['title'],
                'slug' => $slug,
                'description' => $row['title'].' is a Worldwide Adverts marketplace demo title. Discover stories, authors and formats across Fiction, Business, Programming and more. Perfect for testing the Books hub grid, covers and featured reel.',
                'short_description' => 'Demo marketplace title for the Books category.',
                'price' => $row['price'],
                'currency' => 'USD',
                'cover_image' => $covers[$i % count($covers)],
                'book_type' => $row['type'],
                'genre' => $row['genre'],
                'author_name' => $row['author'],
                'country' => 'GB',
                'language' => 'en',
                'format' => $i % 2 === 0 ? 'paperback' : 'ebook',
                'publisher' => 'WWA Demo Press',
                'publication_date' => now()->subYears(1 + ($i % 5))->toDateString(),
                'pages' => 180 + ($i * 17),
                'status' => 'active',
                'advert_type' => $row['advert'],
                'user_id' => $userId,
                'verified_author' => $i % 3 === 0,
                'views_count' => 40 + ($i * 13),
                'saves_count' => 2 + ($i % 5),
            ];

            // Drop columns that may not exist on older DBs
            $filtered = [];
            foreach ($payload as $key => $value) {
                if (Schema::hasColumn('books', $key)) {
                    $filtered[$key] = $value;
                }
            }

            Book::updateOrCreate(['slug' => $slug], $filtered);
            $this->command?->info("Book ready: {$row['title']}");
        }

        // Clean the broken placeholder listing if still present
        Book::where('slug', 'like', 'qui-ullam-inventore%')
            ->where('title', 'Qui ullam inventore')
            ->update(['status' => 'inactive']);

        $this->command?->info('BooksMarketplaceDemoSeeder done. Visit /books');
    }
}
