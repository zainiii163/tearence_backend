<?php

namespace App\Filament\Widgets;

use App\Models\FeaturedAdvert;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentFeaturedAdvertsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FeaturedAdvert::query()
                    ->with(['customer', 'category', 'country'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record): string => $record->title),
                
                Tables\Columns\BadgeColumn::make('upsell_tier')
                    ->label('Tier')
                    ->colors([
                        'warning' => 'promoted',
                        'success' => 'featured',
                        'danger' => 'sponsored',
                    ]),
                
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                    ]),
                
                Tables\Columns\TextColumn::make('formatted_price')
                    ->label('Price')
                    ->money('GBP'),
                
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->limit(20),
                
                Tables\Columns\TextColumn::make('country')
                    ->label('Country')
                    ->searchable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record): string => route('filament.admin.resources.featured-adverts.view', $record))
                    ->openUrlInNewTab(false),
            ])
            ->paginated([5, 10, 25])
            ->striped()
            ->heading('Recent Featured Adverts');
    }
}
