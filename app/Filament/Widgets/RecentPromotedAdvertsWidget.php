<?php

namespace App\Filament\Widgets;

use App\Models\PromotedAdvert;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentPromotedAdvertsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PromotedAdvert::with(['category', 'user'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Image')
                    ->size(40),
                
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('seller_name')
                    ->label('Seller')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('promotion_tier')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'promoted_basic' => 'Basic',
                        'promoted_plus' => 'Plus',
                        'promoted_premium' => 'Premium',
                        'network_wide_boost' => 'Network',
                        default => 'Standard',
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'promoted_basic' => 'gray',
                        'promoted_plus' => 'blue',
                        'promoted_premium' => 'purple',
                        'network_wide_boost' => 'gold',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('price')
                    ->money('GBP')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'active' => 'success',
                        'rejected' => 'danger',
                        'expired' => 'info',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (PromotedAdvert $record): string => route('filament.admin.resources.promoted-adverts.view', $record))
                    ->icon('heroicon-o-eye'),
            ])
            ->emptyStateHeading('No recent promoted adverts')
            ->emptyStateDescription('Create your first promoted advert to see it here.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Create Promoted Advert')
                    ->url(fn (): string => route('filament.admin.resources.promoted-adverts.create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ]);
    }
}
