<?php

namespace App\Console\Commands;

use App\Models\BuySellAdvert;
use Illuminate\Console\Command;

class FixBuySellDemoImages extends Command
{
    protected $signature = 'buysell:fix-demo-images';

    protected $description = 'Replace fake example.com Buy/Sell demo image URLs with real Unsplash photos';

    /** @var array<string, list<string>> */
    private array $byTitle = [
        'iPhone 14 Pro 256GB - Like New' => [
            'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1580910051074-3eb694886505?auto=format&fit=crop&w=800&q=80',
        ],
        '2021 Toyota Camry SE - Low Miles' => [
            'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1583121274602-3e2820c69888?auto=format&fit=crop&w=800&q=80',
        ],
        'Modern Leather Sofa Set - 3 Pieces' => [
            'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1567016432779-094069958ea5?auto=format&fit=crop&w=800&q=80',
        ],
        'Nike Air Jordan 1 Retro High - Size 10' => [
            'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=800&q=80',
        ],
        'Peloton Bike+ - Excellent Condition' => [
            'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1576678927484-cc899799fb56?auto=format&fit=crop&w=800&q=80',
        ],
        'MacBook Pro 16" M1 Max - 32GB RAM' => [
            'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?auto=format&fit=crop&w=800&q=80',
        ],
        'Vintage Rolex Submariner - 1978' => [
            'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1587836374828-4dbafa94cf0e?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1614164185128-e4ec99c436d7?auto=format&fit=crop&w=800&q=80',
        ],
        'Professional DSLR Camera Kit - Canon 5D Mark IV' => [
            'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?auto=format&fit=crop&w=800&q=80',
        ],
        'Dining Table Set - Solid Wood 6 Seater' => [
            'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1615066390971-03e4e1c36ddf?auto=format&fit=crop&w=800&q=80',
        ],
    ];

    public function handle(): int
    {
        $updated = 0;

        foreach ($this->byTitle as $title => $images) {
            $rows = BuySellAdvert::query()->where('title', $title)->get();
            foreach ($rows as $row) {
                $row->images = $images;
                $row->save();
                $updated++;
                $this->info("Updated: {$title}");
            }
        }

        // Catch any remaining example.com image payloads
        $extras = BuySellAdvert::query()->get()->filter(function (BuySellAdvert $row) {
            $imgs = $row->images;
            if (! is_array($imgs)) {
                return false;
            }
            foreach ($imgs as $img) {
                if (is_string($img) && str_contains($img, 'example.com')) {
                    return true;
                }
            }

            return false;
        });

        foreach ($extras as $row) {
            $row->images = [
                'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=800&q=80',
            ];
            $row->save();
            $updated++;
            $this->warn("Replaced example.com images on: {$row->title}");
        }

        $this->info("Done. Updated {$updated} listing(s).");

        return self::SUCCESS;
    }
}
