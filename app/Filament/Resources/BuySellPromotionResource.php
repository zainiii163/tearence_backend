<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuySellPromotionResource\Pages;
use App\Models\BuySellPromotion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BuySellPromotionResource extends Resource
{
    protected static ?string $model = BuySellPromotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Buy & Sell';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Promotion Information')
                    ->schema([
                        Forms\Components\Select::make('item_id')
                            ->relationship('item', 'title')
                            ->searchable()
                            ->required()
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\BuySellItem::where('title', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('title', 'id');
                            }),

                        Forms\Components\Select::make('promotion_type')
                            ->options([
                                'promoted' => 'Promoted ($29)',
                                'featured' => 'Featured ($49)',
                                'sponsored' => 'Sponsored ($99)',
                                'network_boost' => 'Network Boost ($199)',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                $prices = [
                                    'promoted' => 29,
                                    'featured' => 49,
                                    'sponsored' => 99,
                                    'network_boost' => 199,
                                ];
                                $set('price', $prices[$state] ?? 0);
                            }),

                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->disabled()
                            ->required(),

                        Forms\Components\Select::make('currency')
                            ->options([
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                            ])
                            ->default('USD'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Schedule')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Starts At')
                            ->required(),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Features')
                    ->schema([
                        Forms\Components\CheckboxList::make('features')
                            ->label('Included Features')
                            ->options(function (callable $get) {
                                $type = $get('promotion_type');
                                return match ($type) {
                                    'promoted' => [
                                        'highlighted_search' => 'Highlighted in search results',
                                        'priority_category' => 'Priority placement in category',
                                        'promoted_badge' => 'Promoted badge',
                                        'basic_analytics' => 'Basic analytics',
                                    ],
                                    'featured' => [
                                        'homepage_carousel' => 'Top placement in homepage carousel',
                                        'all_promoted_features' => 'All promoted features',
                                        'featured_badge' => 'Featured badge',
                                        'advanced_analytics' => 'Advanced analytics',
                                        'social_promotion' => 'Social media promotion',
                                    ],
                                    'sponsored' => [
                                        'premium_placement' => 'Premium placement across platform',
                                        'all_featured_features' => 'All featured features',
                                        'sponsored_badge' => 'Sponsored badge',
                                        'premium_analytics' => 'Premium analytics',
                                        'email_newsletter' => 'Email newsletter inclusion',
                                        'priority_support' => 'Priority support',
                                    ],
                                    'network_boost' => [
                                        'maximum_visibility' => 'Maximum visibility across network',
                                        'all_sponsored_features' => 'All sponsored features',
                                        'network_boost_badge' => 'Network boost badge',
                                        'full_analytics' => 'Full analytics suite',
                                        'cross_platform' => 'Cross-platform promotion',
                                        'dedicated_support' => 'Dedicated support',
                                        'ai_recommendations' => 'AI-powered recommendations',
                                    ],
                                    default => [],
                                };
                            })
                            ->columns(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('item.title')
                    ->label('Item')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('promotion_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'promoted' => 'info',
                        'featured' => 'warning',
                        'sponsored' => 'success',
                        'network_boost' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'expired' => 'gray',
                        'cancelled' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('promotion_type')
                    ->options([
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        'network_boost' => 'Network Boost',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\Filter::make('active')
                    ->label('Active Promotions')
                    ->query(fn ($query) => $query->where('status', 'active')
                        ->where('starts_at', '<=', now())
                        ->where('expires_at', '>', now()))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuySellPromotions::route('/'),
            'create' => Pages\CreateBuySellPromotion::route('/create'),
            'view' => Pages\ViewBuySellPromotion::route('/{record}'),
            'edit' => Pages\EditBuySellPromotion::route('/{record}/edit'),
        ];
    }
}
