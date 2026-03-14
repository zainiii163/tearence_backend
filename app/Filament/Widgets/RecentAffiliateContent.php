<?php

namespace App\Filament\Widgets;

use App\Models\BusinessAffiliateOffer;
use App\Models\UserAffiliatePost;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentAffiliateContent extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Get recent business offers and user posts combined
                BusinessAffiliateOffer::with(['user', 'affiliateCategory'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('business_name')
                    ->label('Business Name')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('product_service_title')
                    ->label('Product/Service')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('affiliateCategory.name')
                    ->label('Category')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label('Commission')
                    ->formatStateUsing(fn ($record) => $record->display_commission)
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->paginated([5, 10, 25])
            ->striped();
    }
}
