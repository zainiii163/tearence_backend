<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Weidner\Goutte\GoutteFacade;

class ScrapeBooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:books';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape book data from BookWriting.com';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $book_created = 0;
        $crawler = GoutteFacade::request('GET', 'https://bookwritting.com');        
        $books = $crawler->filter('.wpcu-product__content')->each(function ($node) {
            $title = $node->filter('.wpcu-product__title')->text();
            // $price = $node->filter('bdi')->text();
            // die(var_dump($price));
            $image_url = $node->filter('.wpcu-product__img > a > img')->attr('src');
            $link_url = $node->filter('.wpcu-product__img > a')->attr('href');
            return (object)[
                'title' => $title,
                'slug' => Str::slug($title),
                'description' => '',
                'short_description' => '',
                'price' => 9.99,
                'image_url' => $image_url,
                'link_url' => $link_url,
            ];
        });

        // add progress bar
        $bar = $this->output->createProgressBar(count($books));
        $bar->start();

        if (count($books) > 0) {
            foreach ($books as $rowBook) {
                $bookExists = Book::where('slug', $rowBook->slug)->exists();
                if (!$bookExists) {
                    $book = new Book();
                    $book->title = $rowBook->title;
                    $book->slug = $rowBook->slug;
                    $book->description = $rowBook->description;
                    $book->short_description = $rowBook->short_description;
                    $book->price = $rowBook->price;
                    $book->image_url = $rowBook->image_url;
                    $book->link_url = $rowBook->link_url;
                    $book->save();
                    $book_created++;
                }
                $bar->advance();
            }
        }

        // print_r($books);

        $bar->finish();
        $this->newLine(2);
        $this->info('Successfully created book data. Total book created : ' . $book_created);
        
        return Command::SUCCESS;
    }
}
