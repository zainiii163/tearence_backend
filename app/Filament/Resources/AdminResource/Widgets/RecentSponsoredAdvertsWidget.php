<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use App\Models\SponsoredAdvert;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class RecentSponsoredAdvertsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Recent Sponsored Adverts';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SponsoredAdvert::with(['category', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color(fn ($record) => $record->category->color ?? 'gray'),
                TextColumn::make('user.name')
                    ->label('Posted By')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('views')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2)),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'expired' => 'danger',
                        'paused' => 'info',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('promotion_plan')
                    ->label('Plan')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'free' => 'gray',
                        'promoted' => 'info',
                        'featured' => 'warning',
                        'sponsored' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                // No actions needed for recent items
            ]);
    }
}
