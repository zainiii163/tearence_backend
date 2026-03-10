<?php

namespace App\Filament\Widgets;

use App\Models\BannerAd;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentBannersWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Recent Banner Submissions';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BannerAd::with(['category', 'user'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Banner Title')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('business_name')
                    ->label('Business')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color(fn ($record) => $record->category?->color ?? 'gray'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'pending',
                        'success' => 'active',
                        'danger' => 'rejected',
                        'gray' => 'expired',
                    ]),
                
                Tables\Columns\BadgeColumn::make('promotion_tier')
                    ->label('Tier')
                    ->colors([
                        'secondary' => 'standard',
                        'info' => 'promoted',
                        'warning' => 'featured',
                        'success' => 'sponsored',
                        'danger' => 'network_boost',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'standard' => 'Standard',
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        'network_boost' => 'Network Boost',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.banner-ads.edit', $record)),
            ])
            ->paginated([5, 10, 25])
            ->striped();
    }
}
