<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use App\Models\SponsoredAdvert;
use App\Models\SponsoredCategory;
use App\Models\SavedAdvert;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class SponsoredOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected static ?string $heading = 'Sponsored Adverts Overview';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Get latest sponsored adverts stats
                DB::table('sponsored_adverts')
                    ->selectRaw('
                        COUNT(*) as total_adverts,
                        SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_adverts,
                        SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_adverts,
                        SUM(views) as total_views,
                        AVG(rating) as avg_rating,
                        SUM(CASE WHEN featured = 1 THEN 1 ELSE 0 END) as featured_count,
                        SUM(CASE WHEN promoted = 1 THEN 1 ELSE 0 END) as promoted_count,
                        SUM(CASE WHEN sponsored = 1 THEN 1 ELSE 0 END) as sponsored_count
                    ')
                    ->first()
            )
            ->columns([
                Tables\Columns\TextColumn::make('total_adverts')
                    ->label('Total Adverts')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('active_adverts')
                    ->label('Active')
                    ->numeric()
                    ->sortable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('pending_adverts')
                    ->label('Pending')
                    ->numeric()
                    ->sortable()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('total_views')
                    ->label('Total Views')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('avg_rating')
                    ->label('Avg Rating')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2)),
                Tables\Columns\TextColumn::make('featured_count')
                    ->label('Featured')
                    ->numeric()
                    ->sortable()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('promoted_count')
                    ->label('Promoted')
                    ->numeric()
                    ->sortable()
                    ->color('info'),
                Tables\Columns\TextColumn::make('sponsored_count')
                    ->label('Sponsored')
                    ->numeric()
                    ->sortable()
                    ->color('success'),
            ])
            ->actions([
                // No actions needed for overview
            ]);
    }
}
