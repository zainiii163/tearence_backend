<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuySellAdvertResource\Pages;
use App\Models\BuySellAdvert;
use App\Models\BuySellCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BuySellAdvertResource extends Resource
{
    protected static ?string $model = BuySellAdvert::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Buy & Sell';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->maxLength(5000)
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('subcategory_id', null)),
                        
                        Forms\Components\Select::make('subcategory_id')
                            ->options(function (callable $get) {
                                $categoryId = $get('category_id');
                                if (!$categoryId) return [];
                                
                                return BuySellCategory::where('parent_id', $categoryId)
                                    ->where('is_active', true)
                                    ->pluck('name', 'id');
                            })
                            ->searchable(),
                        
                        Forms\Components\Select::make('condition')
                            ->options([
                                'new' => 'New',
                                'like_new' => 'Like New',
                                'excellent' => 'Excellent',
                                'good' => 'Good',
                                'fair' => 'Fair',
                                'poor' => 'Poor',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->step('0.01')
                            ->prefix('$'),
                        
                        Forms\Components\Toggle::make('negotiable')
                            ->default(false),
                        
                        Forms\Components\Select::make('currency')
                            ->options([
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                            ])
                            ->default('USD'),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('country')
                            ->required()
                            ->maxLength(100),
                        
                        Forms\Components\TextInput::make('city')
                            ->maxLength(100),
                        
                        Forms\Components\TextInput::make('state_province')
                            ->maxLength(100),
                        
                        Forms\Components\TextInput::make('postal_code')
                            ->maxLength(20),
                        
                        Forms\Components\Textarea::make('address')
                            ->maxLength(500)
                            ->rows(2),
                        
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.000001),
                        
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.000001),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Item Details')
                    ->schema([
                        Forms\Components\TextInput::make('brand')
                            ->maxLength(100),
                        
                        Forms\Components\TextInput::make('model')
                            ->maxLength(100),
                        
                        Forms\Components\TextInput::make('color')
                            ->maxLength(50),
                        
                        Forms\Components\TextInput::make('material')
                            ->maxLength(100),
                        
                        Forms\Components\Textarea::make('dimensions')
                            ->maxLength(200)
                            ->rows(2),
                        
                        Forms\Components\TextInput::make('weight')
                            ->numeric()
                            ->step('0.01'),
                        
                        Forms\Components\TextInput::make('usage_duration')
                            ->maxLength(100),
                        
                        Forms\Components\Textarea::make('reason_for_selling')
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Seller Information')
                    ->schema([
                        Forms\Components\TextInput::make('seller_name')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('seller_email')
                            ->required()
                            ->email()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('seller_phone')
                            ->maxLength(50),
                        
                        Forms\Components\TextInput::make('seller_website')
                            ->url()
                            ->maxLength(255),
                        
                        Forms\Components\Toggle::make('verified_seller')
                            ->default(false),
                        
                        Forms\Components\Toggle::make('show_phone')
                            ->default(false),
                        
                        Forms\Components\Select::make('preferred_contact')
                            ->options([
                                'email' => 'Email',
                                'phone' => 'Phone',
                                'website' => 'Website',
                            ])
                            ->default('email'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\KeyValue::make('images')
                            ->label('Image URLs')
                            ->keyLabel('URL')
                            ->valueLabel('Label')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('video_url')
                            ->url()
                            ->maxLength(500)
                            ->label('Video URL'),
                    ]),
                
                Forms\Components\Section::make('Promotion')
                    ->schema([
                        Forms\Components\Select::make('promotion_plan')
                            ->options([
                                'basic' => 'Basic',
                                'promoted' => 'Promoted',
                                'featured' => 'Featured',
                                'sponsored' => 'Sponsored',
                            ]),
                        
                        Forms\Components\DateTimePicker::make('promotion_start_date'),
                        
                        Forms\Components\DateTimePicker::make('promotion_end_date'),
                        
                        Forms\Components\Select::make('promotion_status')
                            ->options([
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('active'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'expired' => 'Expired',
                                'sold' => 'Sold',
                            ])
                            ->default('active'),
                        
                        Forms\Components\Toggle::make('featured')
                            ->default(false),
                        
                        Forms\Components\Toggle::make('is_promoted')
                            ->default(false),
                        
                        Forms\Components\Toggle::make('is_sponsored')
                            ->default(false),
                        
                        Forms\Components\Toggle::make('is_urgent')
                            ->default(false),
                        
                        Forms\Components\Toggle::make('is_new')
                            ->default(false),
                        
                        Forms\Components\Toggle::make('is_hot')
                            ->default(false),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Analytics')
                    ->schema([
                        Forms\Components\TextInput::make('views_count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('saves_count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('contacts_count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('shares_count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('condition')
                    ->badge()
                    ->colors([
                        'primary' => 'new',
                        'success' => 'like_new',
                        'info' => 'excellent',
                        'warning' => 'good',
                        'danger' => 'fair',
                        'gray' => 'poor',
                    ]),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'inactive',
                        'danger' => 'expired',
                        'primary' => 'sold',
                    ]),
                
                Tables\Columns\IconColumn::make('featured')
                    ->boolean()
                    ->trueColor('warning'),
                
                Tables\Columns\IconColumn::make('is_promoted')
                    ->boolean()
                    ->trueColor('success'),
                
                Tables\Columns\TextColumn::make('seller_name')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('condition')
                    ->options([
                        'new' => 'New',
                        'like_new' => 'Like New',
                        'excellent' => 'Excellent',
                        'good' => 'Good',
                        'fair' => 'Fair',
                        'poor' => 'Poor',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'expired' => 'Expired',
                        'sold' => 'Sold',
                    ]),
                
                Tables\Filters\TernaryFilter::make('featured')
                    ->placeholder('All')
                    ->trueLabel('Featured')
                    ->falseLabel('Not Featured'),
                
                Tables\Filters\TernaryFilter::make('is_promoted')
                    ->placeholder('All')
                    ->trueLabel('Promoted')
                    ->falseLabel('Not Promoted'),
                
                Tables\Filters\TernaryFilter::make('verified_seller')
                    ->placeholder('All')
                    ->trueLabel('Verified')
                    ->falseLabel('Not Verified'),
                
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('min_price')
                            ->numeric()
                            ->placeholder('Min price'),
                        
                        Forms\Components\TextInput::make('max_price')
                            ->numeric()
                            ->placeholder('Max price'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_price'],
                                fn (Builder $query, $price): Builder => $query->where('price', '>=', $price)
                            )
                            ->when(
                                $data['max_price'],
                                fn (Builder $query, $price): Builder => $query->where('price', '<=', $price)
                            );
                    }),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->placeholder('Start date'),
                        
                        Forms\Components\DatePicker::make('created_until')
                            ->placeholder('End date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('markAsFeatured')
                        ->label('Mark as Featured')
                        ->icon('heroicon-o-star')
                        ->action(function ($records) {
                            $records->each->update(['featured' => true]);
                        }),
                    Tables\Actions\BulkAction::make('markAsPromoted')
                        ->label('Mark as Promoted')
                        ->icon('heroicon-o-bolt')
                        ->action(function ($records) {
                            $records->each->update(['is_promoted' => true]);
                        }),
                ]),
            ]);
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
            'index' => Pages\ListBuySellAdverts::route('/'),
            'create' => Pages\CreateBuySellAdvert::route('/create'),
            'view' => Pages\ViewBuySellAdvert::route('/{record}'),
            'edit' => Pages\EditBuySellAdvert::route('/{record}/edit'),
        ];
    }
}
