<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateHopConversionResource\Pages;
use App\Models\AffiliateHopConversion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AffiliateHopConversionResource extends Resource
{
    protected static ?string $model = AffiliateHopConversion::class;

    protected static ?string $navigationLabel = 'Hop conversions';

    protected static ?string $modelLabel = 'Hop conversion';

    protected static ?string $pluralModelLabel = 'Hop conversions';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Affiliates Hub';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Conversion')
                    ->schema([
                        Forms\Components\Select::make('business_affiliate_offer_id')
                            ->relationship('offer', 'product_service_title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('affiliate_application_id')
                            ->relationship('application', 'tracking_code')
                            ->getOptionLabelFromRecordUsing(
                                fn ($record) => ($record->tracking_code ?: '#' . $record->id)
                                    . ' · user ' . $record->user_id
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('tracking_code')
                            ->maxLength(64),

                        Forms\Components\TextInput::make('order_id')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('sale_amount')
                            ->numeric()
                            ->prefix('$')
                            ->required(),

                        Forms\Components\TextInput::make('commission_amount')
                            ->numeric()
                            ->prefix('$')
                            ->required(),

                        Forms\Components\TextInput::make('commission_rate')
                            ->numeric()
                            ->helperText('Snapshot of % or fixed rate used'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'recorded' => 'Recorded',
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'paid' => 'Paid',
                                'reversed' => 'Reversed',
                            ])
                            ->default('recorded'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('offer.product_service_title')
                    ->label('Offer')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),

                Tables\Columns\TextColumn::make('tracking_code')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('order_id')
                    ->toggleable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('sale_amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commission')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'recorded',
                        'warning' => 'pending',
                        'success' => fn ($state) => in_array($state, ['confirmed', 'paid'], true),
                        'danger' => 'reversed',
                    ]),

                Tables\Columns\TextColumn::make('application.user.name')
                    ->label('Promoter')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'recorded' => 'Recorded',
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'paid' => 'Paid',
                        'reversed' => 'Reversed',
                    ]),
                Tables\Filters\SelectFilter::make('business_affiliate_offer_id')
                    ->relationship('offer', 'product_service_title')
                    ->searchable()
                    ->preload()
                    ->label('Offer'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAffiliateHopConversions::route('/'),
            'create' => Pages\CreateAffiliateHopConversion::route('/create'),
            'view' => Pages\ViewAffiliateHopConversion::route('/{record}'),
            'edit' => Pages\EditAffiliateHopConversion::route('/{record}/edit'),
        ];
    }
}
