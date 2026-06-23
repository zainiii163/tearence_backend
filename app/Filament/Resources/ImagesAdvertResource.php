<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImagesAdvertResource\Pages;
use App\Filament\Resources\ImagesAdvertResource\RelationManagers;
use App\Models\ImagesAdvert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ImagesAdvertResource extends Resource
{
    protected static ?string $model = ImagesAdvert::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Stock Images';

    protected static ?string $modelLabel = 'Stock Image';

    protected static ?string $pluralModelLabel = 'Stock Images';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 10;

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
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ImagesAdvert::class, 'slug', ignoreRecord: true)
                            ->disabled(),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('short_description')
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('category')
                            ->required()
                            ->options([
                                'business' => 'Business',
                                'people' => 'People',
                                'nature' => 'Nature',
                                'food' => 'Food',
                                'technology' => 'Technology',
                                'real_estate' => 'Real Estate',
                                'travel' => 'Travel',
                                'sports' => 'Sports',
                                'education' => 'Education',
                                'health' => 'Health',
                                'art' => 'Art',
                                'other' => 'Other',
                            ]),
                    ])
                    ->columns(2),

                // Image Details
                Forms\Components\Section::make('Image Details')
                    ->schema([
                        Forms\Components\FileUpload::make('main_image')
                            ->image()
                            ->directory('images')
                            ->required(),

                        Forms\Components\FileUpload::make('images')
                            ->multiple()
                            ->image()
                            ->directory('images')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('orientation')
                            ->options([
                                'landscape' => 'Landscape',
                                'portrait' => 'Portrait',
                                'square' => 'Square',
                            ]),

                        Forms\Components\Select::make('color_type')
                            ->options([
                                'color' => 'Color',
                                'black_and_white' => 'Black and White',
                                'sepia' => 'Sepia',
                            ]),

                        Forms\Components\TextInput::make('resolution_width')
                            ->numeric()
                            ->label('Width (px)'),

                        Forms\Components\TextInput::make('resolution_height')
                            ->numeric()
                            ->label('Height (px)'),
                    ])
                    ->columns(2),

                // Pricing
                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\Select::make('currency')
                            ->options([
                                'USD' => 'USD',
                                'GBP' => 'GBP',
                                'EUR' => 'EUR',
                            ]),

                        Forms\Components\Select::make('license_type')
                            ->required()
                            ->options([
                                'royalty_free' => 'Royalty Free',
                                'rights_managed' => 'Rights Managed',
                                'extended' => 'Extended License',
                            ]),

                        Forms\Components\Select::make('promotion_tier')
                            ->options([
                                'standard' => 'Standard',
                                'promoted' => 'Promoted',
                                'featured' => 'Featured',
                                'sponsored' => 'Sponsored',
                            ]),
                    ])
                    ->columns(2),

                // Contact Information
                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('contact_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('contact_email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('contact_phone')
                            ->maxLength(20),

                        Forms\Components\TextInput::make('website_url')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                // Verification
                Forms\Components\Section::make('Verification')
                    ->schema([
                        Forms\Components\Select::make('verification_status')
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending'),

                        Forms\Components\Toggle::make('is_verified_creator')
                            ->label('Verified Creator'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Image'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),

                Tables\Columns\TextColumn::make('verification_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verified' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable(),

                Tables\Columns\TextColumn::make('downloads_count')
                    ->label('Downloads')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'business' => 'Business',
                        'people' => 'People',
                        'nature' => 'Nature',
                        'food' => 'Food',
                        'technology' => 'Technology',
                        'real_estate' => 'Real Estate',
                        'travel' => 'Travel',
                        'sports' => 'Sports',
                        'education' => 'Education',
                        'health' => 'Health',
                        'art' => 'Art',
                        'other' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('verification_status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImagesAdverts::route('/'),
            'create' => Pages\CreateImagesAdvert::route('/create'),
            'edit' => Pages\EditImagesAdvert::route('/{record}/edit'),
        ];
    }
}
