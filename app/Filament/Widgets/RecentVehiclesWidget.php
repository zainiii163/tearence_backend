<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentVehiclesWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected static ?string $heading = 'Recent Vehicles';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Vehicle::with(['user', 'category', 'make', 'vehicleModel'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),
                
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30)
                    ->description(fn (Vehicle $record): string => $record->tagline ?? ''),
                
                Tables\Columns\TextColumn::make('make.name')
                    ->label('Make')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('vehicleModel.name')
                    ->label('Model')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('year')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('price')
                    ->money('GBP')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('advert_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sale' => 'success',
                        'hire' => 'warning',
                        'lease' => 'info',
                        'transport_service' => 'primary',
                    }),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        'expired' => 'gray',
                        'sold' => 'primary',
                    }),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Vehicle $record): string => route('filament.admin.resources.vehicles.view', $record))
                    ->openUrlInNewTab(),
            ])
            ->paginated([5, 10, 25])
            ->striped();
    }
}
