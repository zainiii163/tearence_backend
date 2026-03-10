<?php

namespace App\Filament\Widgets;

use App\Models\ServicePromotion;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentPromotionsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected static ?string $heading = 'Recent Promotions';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ServicePromotion::with(['service', 'service.user'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('service.title')
                    ->label('Service')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('promotion_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'promoted' => 'info',
                        'featured' => 'primary',
                        'sponsored' => 'warning',
                        'network_boost' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'warning',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ServicePromotion $record): string => route('filament.admin.resources.services.view', $record->service_id)),
            ]);
    }
}
