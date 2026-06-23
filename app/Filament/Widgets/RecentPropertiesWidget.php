<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentPropertiesWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Property::with('user')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->circular()
                    ->defaultImageUrl(url('placeholder.jpg'))
                    ->size(40),
                    
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50)
                    ->weight('medium'),
                    
                Tables\Columns\TextColumn::make('property_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'residential' => 'primary',
                        'commercial' => 'success',
                        'industrial' => 'warning',
                        'land' => 'info',
                        'agricultural' => 'gray',
                        'luxury' => 'purple',
                        'short_term_rental' => 'pink',
                        'investment' => 'orange',
                        'new_development' => 'cyan',
                    }),
                    
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'buy' => 'success',
                        'rent' => 'primary',
                        'lease' => 'warning',
                        'auction' => 'danger',
                        'invest' => 'info',
                    }),
                    
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                    
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                    
                Tables\Columns\IconColumn::make('approved')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Added'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Property $record): string => route('filament.admin.resources.properties.view', $record))
                    ->icon('heroicon-m-eye'),
            ])
            ->paginated([5, 10, 25])
            ->striped();
    }
}
