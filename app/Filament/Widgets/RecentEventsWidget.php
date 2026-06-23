<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentEventsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Recent Events';

    protected static ?int $poll = 60; // Refresh every 60 seconds

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::with('user')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('images.0')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(url('/placeholder.png')),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'concert' => 'Concerts & Music',
                        'workshop' => 'Workshops',
                        'party' => 'Parties & Nightlife',
                        'festival' => 'Festivals',
                        'conference' => 'Business Conferences',
                        'sports' => 'Sports Events',
                        'cultural' => 'Cultural Events',
                        'food_drink' => 'Food & Drink',
                        'charity' => 'Charity Events',
                        'other' => 'Other',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Event Date')
                    ->dateTime('M j, Y')
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
