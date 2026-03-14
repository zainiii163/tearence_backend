<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuySellItemResource\Pages;
use App\Models\BuySellItem;
use App\Models\BuySellCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BuySellItemResource extends Resource
{
    protected static ?string $model = BuySellItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Buy & Sell';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, $set) => 
                                $operation === 'create' ? $set('slug', Str::slug($state) . '-' . time()) : null),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(BuySellItem::class, 'slug', ignoreRecord: true),

                        Forms\Components\Select::make('item_type')
                            ->options([
                                'for_sale' => 'For Sale',
                                'for_swap' => 'For Swap',
                                'give_away' => 'Give Away',
                            ])
                            ->required(),

                        Forms\Components\Select::make('condition')
                            ->options([
                                'new' => 'New',
                                'like_new' => 'Like New',
                                'good' => 'Good',
                                'fair' => 'Fair',
                                'poor' => 'Poor',
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Item Details')
                    ->schema([
                        Forms\Components\TextInput::make('brand')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('model')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('color')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('weight')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('kg'),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required(fn ($get) => $get('item_type') === 'for_sale'),

                        Forms\Components\Select::make('currency')
                            ->options([
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                                'CAD' => 'CAD',
                                'AUD' => 'AUD',
                            ])
                            ->default('USD'),

                        Forms\Components\Toggle::make('is_negotiable')
                            ->label('Price is negotiable')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('country')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('city')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.00000001),

                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.00000001),

                        Forms\Components\Textarea::make('location_details')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Promotion')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'paused' => 'Paused',
                                'sold' => 'Sold',
                                'suspended' => 'Suspended',
                            ])
                            ->required(),

                        Forms\Components\Select::make('promotion_type')
                            ->options([
                                'standard' => 'Standard',
                                'promoted' => 'Promoted',
                                'featured' => 'Featured',
                                'sponsored' => 'Sponsored',
                                'network_boost' => 'Network Boost',
                            ])
                            ->default('standard'),

                        Forms\Components\DateTimePicker::make('promotion_expires_at')
                            ->label('Promotion Expires At'),

                        Forms\Components\Toggle::make('is_verified')
                            ->label('Verified Item')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\KeyValue::make('key_features')
                            ->label('Key Features')
                            ->keyLabel('Feature')
                            ->valueLabel('Description')
                            ->columnSpanFull(),

                        Forms\Components\KeyValue::make('usage_notes')
                            ->label('Usage Notes')
                            ->keyLabel('Note')
                            ->valueLabel('Description')
                            ->columnSpanFull(),

                        Forms\Components\KeyValue::make('meta_data')
                            ->label('Meta Data')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Seller')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('item_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'for_sale' => 'success',
                        'for_swap' => 'warning',
                        'give_away' => 'info',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'paused' => 'warning',
                        'sold' => 'info',
                        'suspended' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('promotion_type')
                    ->label('Promotion')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'standard' => 'gray',
                        'promoted' => 'info',
                        'featured' => 'warning',
                        'sponsored' => 'success',
                        'network_boost' => 'danger',
                    }),

                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean(),

                Tables\Columns\TextColumn::make('views')
                    ->label('Views')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'sold' => 'Sold',
                        'suspended' => 'Suspended',
                    ]),

                Tables\Filters\SelectFilter::make('item_type')
                    ->options([
                        'for_sale' => 'For Sale',
                        'for_swap' => 'For Swap',
                        'give_away' => 'Give Away',
                    ]),

                Tables\Filters\SelectFilter::make('promotion_type')
                    ->options([
                        'standard' => 'Standard',
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        'network_boost' => 'Network Boost',
                    ]),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable(),

                Tables\Filters\Filter::make('is_verified')
                    ->label('Verified Items Only')
                    ->query(fn ($query) => $query->where('is_verified', true))
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
            'index' => Pages\ListBuySellItems::route('/'),
            'create' => Pages\CreateBuySellItem::route('/create'),
            'view' => Pages\ViewBuySellItem::route('/{record}'),
            'edit' => Pages\EditBuySellItem::route('/{record}/edit'),
        ];
    }
}
