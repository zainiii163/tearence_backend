<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Models\BookPurchase;
use App\Models\Listing;
use App\Models\Category;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $booksCategory = Category::where('name', 'Books')->first();
        
        if (!$booksCategory) {
            return [];
        }

        $totalBooks = Listing::where('category_id', $booksCategory->category_id)->count();
        $activeBooks = Listing::where('category_id', $booksCategory->category_id)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->count();
        
        $totalPurchases = BookPurchase::completed()->count();
        $totalRevenue = BookPurchase::completed()->sum('price_paid');
        $totalDownloads = BookPurchase::completed()->sum('total_downloads');
        
        $pendingBooks = Listing::where('category_id', $booksCategory->category_id)
            ->where('approval_status', 'pending')
            ->count();

        return [
            Stat::make('Total Books', $totalBooks)
                ->description('All book listings')
                ->icon('heroicon-o-book-open')
                ->color('primary'),
            
            Stat::make('Active Books', $activeBooks)
                ->description('Published and active')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
            
            Stat::make('Pending Review', $pendingBooks)
                ->description('Awaiting approval')
                ->icon('heroicon-o-clock')
                ->color('warning'),
            
            Stat::make('Total Purchases', $totalPurchases)
                ->description('Completed purchases')
                ->icon('heroicon-o-shopping-cart')
                ->color('info'),
            
            Stat::make('Total Revenue', '$' . number_format($totalRevenue, 2))
                ->description('From book sales')
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),
            
            Stat::make('Total Downloads', $totalDownloads)
                ->description('Files downloaded')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary'),
        ];
    }
}
