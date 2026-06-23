<?php

namespace App\Filament\Widgets;

use App\Models\Venue;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentVenuesWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Recent Venues';

    protected static ?int $poll = 60; // Refresh every 60 seconds

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Venue::with('user')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('images.0')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(url('/placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('venue_type_label')
                    ->label('Venue Type')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacity')
                    ->formatStateUsing(fn (int $state): string => number_format($state) . ' guests')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('promotion_tier')
                    ->colors([
                        'secondary' => 'standard',
                        'warning' => 'promoted',
                        'success' => 'featured',
                        'info' => 'sponsored',
                        'danger' => 'spotlight',
                    ]),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }
}
