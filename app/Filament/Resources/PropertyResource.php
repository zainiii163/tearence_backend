<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Filament\Resources\PropertyResource\RelationManagers;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Properties';

    protected static ?string $modelLabel = 'Property';

    protected static ?string $pluralModelLabel = 'Properties';

    protected static ?string $navigationGroup = 'Property Hub';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Basic Information
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        Forms\Components\TextInput::make('tagline')
                            ->maxLength(255),
                        Forms\Components\Select::make('category')
                            ->options([
                                'buy' => 'Buy',
                                'rent' => 'Rent',
                                'lease' => 'Lease',
                                'auction' => 'Auction',
                                'invest' => 'Invest',
                            ])
                            ->required(),
                        Forms\Components\Select::make('property_type')
                            ->options([
                                'residential' => 'Residential Property',
                                'commercial' => 'Commercial Property',
                                'industrial' => 'Industrial Property',
                                'land' => 'Land / Plots',
                                'agricultural' => 'Agricultural Land',
                                'luxury' => 'Luxury Property',
                                'short_term_rental' => 'Short-Term Rental / Holiday Home',
                                'investment' => 'Investment Property',
                                'new_development' => 'New Development / Off-Plan',
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                // Location
                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('country')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('city')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.00000001),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.00000001),
                    ])
                    ->columns(2),

                // Pricing
                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),
                        Forms\Components\Select::make('currency')
                            ->options([
                                'USD' => 'USD ($)',
                                'EUR' => 'EUR (€)',
                                'GBP' => 'GBP (£)',
                                'AED' => 'AED (د.إ)',
                                'SAR' => 'SAR (﷼)',
                            ])
                            ->default('USD'),
                        Forms\Components\Checkbox::make('negotiable'),
                        Forms\Components\TextInput::make('deposit')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),
                        Forms\Components\TextInput::make('service_charges')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),
                        Forms\Components\TextInput::make('maintenance_fees')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),
                    ])
                    ->columns(2),

                // Media
                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->directory('properties/cover')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('additional_images')
                            ->multiple()
                            ->image()
                            ->directory('properties/additional')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('video_tour_link')
                            ->url()
                            ->maxLength(255),
                    ]),

                // Description
                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('specifications')
                            ->addActionLabel('Add specification')
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('amenities')
                            ->placeholder('Add amenities')
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('location_highlights')
                            ->addActionLabel('Add location highlight')
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('transport_links')
                            ->addActionLabel('Add transport link')
                            ->columnSpanFull(),
                    ]),

                // Seller/Agent Information
                Forms\Components\Section::make('Seller/Agent Information')
                    ->schema([
                        Forms\Components\TextInput::make('seller_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('seller_company')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('seller_phone')
                            ->required()
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('seller_email')
                            ->required()
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('seller_website')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('seller_logo')
                            ->image()
                            ->directory('properties/logos'),
                        Forms\Components\Checkbox::make('verified_agent'),
                    ])
                    ->columns(2),

                // Status & Promotion
                Forms\Components\Section::make('Status & Promotion')
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->default(true),
                        Forms\Components\Toggle::make('approved')
                            ->default(false),
                        Forms\Components\Select::make('advert_type')
                            ->options([
                                'standard' => 'Standard',
                                'promoted' => 'Promoted',
                                'featured' => 'Featured',
                                'sponsored' => 'Sponsored',
                            ])
                            ->default('standard'),
                        Forms\Components\DateTimePicker::make('promoted_until')
                            ->label('Promoted Until'),
                        Forms\Components\DateTimePicker::make('featured_until')
                            ->label('Featured Until'),
                        Forms\Components\DateTimePicker::make('sponsored_until')
                            ->label('Sponsored Until'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->circular()
                    ->defaultImageUrl(url('placeholder.jpg')),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
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
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('approved')
                    ->boolean(),
                Tables\Columns\TextColumn::make('advert_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'standard' => 'gray',
                        'promoted' => 'warning',
                        'featured' => 'primary',
                        'sponsored' => 'success',
                    }),
                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('saves')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('enquiries')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('property_type')
                    ->options([
                        'residential' => 'Residential Property',
                        'commercial' => 'Commercial Property',
                        'industrial' => 'Industrial Property',
                        'land' => 'Land / Plots',
                        'agricultural' => 'Agricultural Land',
                        'luxury' => 'Luxury Property',
                        'short_term_rental' => 'Short-Term Rental / Holiday Home',
                        'investment' => 'Investment Property',
                        'new_development' => 'New Development / Off-Plan',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'buy' => 'Buy',
                        'rent' => 'Rent',
                        'lease' => 'Lease',
                        'auction' => 'Auction',
                        'invest' => 'Invest',
                    ]),
                Tables\Filters\SelectFilter::make('advert_type')
                    ->options([
                        'standard' => 'Standard',
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                    ]),
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Active')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
                Tables\Filters\TernaryFilter::make('approved')
                    ->label('Approved')
                    ->placeholder('All')
                    ->trueLabel('Approved')
                    ->falseLabel('Not Approved'),
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
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FavouritesRelationManager::class,
            RelationManagers\AnalyticsRelationManager::class,
            RelationManagers\EnquiriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'view' => Pages\ViewProperty::route('/{record}'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
