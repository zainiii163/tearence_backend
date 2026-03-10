<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotedAdvertResource\Pages;
use App\Models\PromotedAdvert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotedAdvertResource extends Resource
{
    protected static ?string $model = PromotedAdvert::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Promoted Adverts';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Basic Information
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(PromotedAdvert::class, 'slug', ignoreRecord: true),

                        Forms\Components\TextInput::make('tagline')
                            ->maxLength(80)
                            ->helperText('Max 80 characters'),

                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('key_features')
                            ->helperText('Enter each feature on a new line')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('special_notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // Category and Type
                Forms\Components\Section::make('Category and Type')
                    ->schema([
                        Forms\Components\Select::make('advert_type')
                            ->required()
                            ->options([
                                'product' => 'Product / Item for Sale',
                                'service' => 'Service / Business Offer',
                                'property' => 'Property / Real Estate',
                                'vehicle' => 'Vehicle / Motors',
                                'job' => 'Job / Vacancy',
                                'event' => 'Event / Experience',
                                'business' => 'Business Opportunity',
                                'miscellaneous' => 'Miscellaneous / Other',
                            ]),

                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                // Location
                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\Select::make('country')
                            ->required()
                            ->options(fn () => \App\Models\Country::pluck('name', 'name'))
                            ->searchable(),

                        Forms\Components\TextInput::make('city')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.00000001),

                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.00000001),

                        Forms\Components\Select::make('location_privacy')
                            ->options([
                                'exact' => 'Exact Location',
                                'approximate' => 'Approximate Location',
                            ])
                            ->default('exact'),
                    ])
                    ->columns(2),

                // Pricing
                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('£')
                            ->step(0.01),

                        Forms\Components\Select::make('currency')
                            ->options([
                                'GBP' => 'GBP (£)',
                                'USD' => 'USD ($)',
                                'EUR' => 'EUR (€)',
                            ])
                            ->default('GBP'),

                        Forms\Components\Select::make('price_type')
                            ->options([
                                'fixed' => 'Fixed Price',
                                'negotiable' => 'Negotiable',
                                'free' => 'Free',
                            ])
                            ->default('fixed'),

                        Forms\Components\Select::make('condition')
                            ->options([
                                'new' => 'New',
                                'used' => 'Used',
                                'not_applicable' => 'Not Applicable',
                            ]),
                    ])
                    ->columns(2),

                // Media
                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('main_image')
                            ->required()
                            ->image()
                            ->directory('promoted-adverts')
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('additional_images')
                            ->image()
                            ->directory('promoted-adverts')
                            ->multiple()
                            ->maxFiles(10)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('video_link')
                            ->url()
                            ->placeholder('https://youtube.com/watch?v=...'),
                    ]),

                // Seller Information
                Forms\Components\Section::make('Seller Information')
                    ->schema([
                        Forms\Components\TextInput::make('seller_name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('business_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->required()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('email')
                            ->required()
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('logo')
                            ->image()
                            ->directory('promoted-adverts/logos'),

                        Forms\Components\Toggle::make('verified_seller')
                            ->default(false),
                    ])
                    ->columns(2),

                // Promotion
                Forms\Components\Section::make('Promotion Settings')
                    ->schema([
                        Forms\Components\Select::make('promotion_tier')
                            ->required()
                            ->options([
                                'promoted_basic' => 'Promoted Basic (£29.99)',
                                'promoted_plus' => 'Promoted Plus (£59.99) - Most Popular',
                                'promoted_premium' => 'Promoted Premium (£99.99)',
                                'network_wide_boost' => 'Network-Wide Boost (£199.99)',
                            ])
                            ->reactive()
                            ->afterStateUpdated(fn (string $state, Forms\Set $set) => 
                                $set('promotion_price', match($state) {
                                    'promoted_basic' => 29.99,
                                    'promoted_plus' => 59.99,
                                    'promoted_premium' => 99.99,
                                    'network_wide_boost' => 199.99,
                                    default => 0,
                                })
                            ),

                        Forms\Components\TextInput::make('promotion_price')
                            ->numeric()
                            ->prefix('£')
                            ->step(0.01)
                            ->disabled(),

                        Forms\Components\DatePicker::make('promotion_start')
                            ->date(),

                        Forms\Components\DatePicker::make('promotion_end')
                            ->date()
                            ->after('promotion_start'),
                    ])
                    ->columns(2),

                // Status
                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'rejected' => 'Rejected',
                                'expired' => 'Expired',
                            ])
                            ->default('draft'),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true),

                        Forms\Components\Toggle::make('is_featured')
                            ->default(false),

                        Forms\Components\DateTimePicker::make('approved_at')
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Image')
                    ->size(60),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('advert_type')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'product' => 'Product',
                        'service' => 'Service',
                        'property' => 'Property',
                        'vehicle' => 'Vehicle',
                        'job' => 'Job',
                        'event' => 'Event',
                        'business' => 'Business',
                        'miscellaneous' => 'Other',
                        default => ucwords(str_replace('_', ' ', $state)),
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('promotion_tier')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'promoted_basic' => 'Basic',
                        'promoted_plus' => 'Plus',
                        'promoted_premium' => 'Premium',
                        'network_wide_boost' => 'Network',
                        default => 'Standard',
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'promoted_basic' => 'gray',
                        'promoted_plus' => 'blue',
                        'promoted_premium' => 'purple',
                        'network_wide_boost' => 'gold',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('GBP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'active' => 'success',
                        'rejected' => 'danger',
                        'expired' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('saves_count')
                    ->label('Saves')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('advert_type')
                    ->options([
                        'product' => 'Product',
                        'service' => 'Service',
                        'property' => 'Property',
                        'vehicle' => 'Vehicle',
                        'job' => 'Job',
                        'event' => 'Event',
                        'business' => 'Business',
                        'miscellaneous' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('promotion_tier')
                    ->options([
                        'promoted_basic' => 'Promoted Basic',
                        'promoted_plus' => 'Promoted Plus',
                        'promoted_premium' => 'Promoted Premium',
                        'network_wide_boost' => 'Network-Wide Boost',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                    ]),

                Tables\Filters\SelectFilter::make('country')
                    ->options(fn () => \App\Models\Country::pluck('name', 'name'))
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->placeholder('All')
                    ->trueLabel('Featured')
                    ->falseLabel('Not Featured'),
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
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListPromotedAdverts::route('/'),
            'create' => Pages\CreatePromotedAdvert::route('/create'),
            'view' => Pages\ViewPromotedAdvert::route('/{record}'),
            'edit' => Pages\EditPromotedAdvert::route('/{record}/edit'),
        ];
    }
}
