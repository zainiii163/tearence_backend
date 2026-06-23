<?php

namespace App\Filament\Resources;

use App\Models\SponsoredAdvert;
use App\Models\SponsoredCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Arr;

class SponsoredAdvertResource extends Resource
{
    protected static ?string $model = SponsoredAdvert::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                        Forms\Components\TextInput::make('tagline')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Select::make('advert_type')
                            ->label('Advert Type')
                            ->options([
                                'product' => 'Product',
                                'service' => 'Service',
                                'property' => 'Property',
                                'vehicle' => 'Vehicle',
                                'job' => 'Job',
                                'event' => 'Event',
                                'business' => 'Business',
                                'other' => 'Other',
                            ])
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->options(fn () => SponsoredCategory::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('condition')
                            ->options([
                                'new' => 'New',
                                'used' => 'Used',
                                'not_applicable' => 'Not Applicable',
                            ]),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->default(null),
                        Forms\Components\Select::make('currency')
                            ->options([
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                            ])
                            ->default('USD'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('country')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('latitude')
                            ->numeric(),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric(),
                        Forms\Components\Select::make('location_precision')
                            ->options([
                                'exact' => 'Exact',
                                'approximate' => 'Approximate',
                            ])
                            ->default('approximate'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Images')
                    ->schema([
                        Forms\Components\FileUpload::make('main_image')
                            ->label('Main Image')
                            ->image()
                            ->disk('public')
                            ->directory('sponsored/main')
                            ->maxSize(2048)
                            ->helperText('Click to upload main image (JPEG, PNG, JPG, GIF — max 2MB)')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('additional_images')
                            ->label('Additional Images')
                            ->image()
                            ->disk('public')
                            ->directory('sponsored/gallery')
                            ->multiple()
                            ->maxFiles(10)
                            ->maxSize(2048)
                            ->helperText('Click to upload additional images (up to 10)')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->disk('public')
                            ->directory('sponsored/logos')
                            ->maxSize(2048)
                            ->helperText('Click to upload logo (JPEG, PNG, JPG, GIF — max 2MB)')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('video_link')
                            ->label('Video Link')
                            ->url()
                            ->placeholder('https://youtube.com/watch?v=...')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Seller Information')
                    ->schema([
                        Forms\Components\TextInput::make('seller_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('business_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(500),
                        Forms\Components\TagsInput::make('social_links')
                            ->placeholder('Add social profile URLs')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('verified_seller')
                            ->label('Verified Seller')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Sponsorship & Status')
                    ->schema([
                        Forms\Components\Select::make('sponsorship_tier')
                            ->label('Promotion Tier')
                            ->options([
                                'basic' => 'Basic',
                                'plus' => 'Plus',
                                'premium' => 'Premium',
                            ])
                            ->default('basic')
                            ->required(),
                        Forms\Components\TextInput::make('sponsorship_price')
                            ->label('Sponsorship Price')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->default('pending'),
                        Forms\Components\DateTimePicker::make('sponsorship_start_date')
                            ->label('Sponsorship Start'),
                        Forms\Components\DateTimePicker::make('sponsorship_end_date')
                            ->label('Sponsorship End'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sponsored_advert_id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Image')
                    ->disk('public')
                    ->getStateUsing(fn (SponsoredAdvert $record) => static::fileUploadPath(
                        $record->main_image,
                        'sponsored/main'
                    )),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sponsorship_tier')
                    ->label('Tier')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    /**
     * Path for Filament FileUpload / ImageColumn on the public disk.
     */
    public static function fileUploadPath(?string $stored, string $directory): ?string
    {
        if (!$stored) {
            return null;
        }

        if (str_starts_with($stored, 'http://') || str_starts_with($stored, 'https://')) {
            $path = parse_url($stored, PHP_URL_PATH);

            if (!$path) {
                return null;
            }

            $path = ltrim($path, '/');

            if (str_starts_with($path, 'storage/')) {
                return substr($path, strlen('storage/'));
            }

            if (str_contains($path, '/')) {
                return $path;
            }

            return $directory . '/' . $path;
        }

        return str_contains($stored, '/')
            ? $stored
            : $directory . '/' . $stored;
    }

    public static function normalizeUploadValue(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = Arr::first(Arr::flatten($value));
        }

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * @param  array<int, mixed>|null  $images
     * @return array<int, string>|null
     */
    public static function normalizeAdditionalImages(?array $images): ?array
    {
        if ($images === null) {
            return null;
        }

        $normalized = array_values(array_filter(array_map(
            fn ($image) => static::normalizeUploadValue($image),
            $images
        )));

        return $normalized === [] ? null : $normalized;
    }

    public static function prepareMediaForFill(array $data): array
    {
        if (!empty($data['main_image'])) {
            $data['main_image'] = static::fileUploadPath($data['main_image'], 'sponsored/main');
        }

        if (!empty($data['logo'])) {
            $data['logo'] = static::fileUploadPath($data['logo'], 'sponsored/logos');
        }

        if (!empty($data['additional_images']) && is_array($data['additional_images'])) {
            $data['additional_images'] = array_values(array_filter(array_map(
                fn ($image) => static::fileUploadPath($image, 'sponsored/gallery'),
                $data['additional_images']
            )));
        }

        return $data;
    }

    public static function prepareMediaForSave(array $data): array
    {
        if (array_key_exists('main_image', $data)) {
            $data['main_image'] = static::normalizeUploadValue($data['main_image']);
        }

        if (array_key_exists('logo', $data)) {
            $data['logo'] = static::normalizeUploadValue($data['logo']);
        }

        if (array_key_exists('additional_images', $data)) {
            $data['additional_images'] = static::normalizeAdditionalImages($data['additional_images']);
        }

        return $data;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\SponsoredAdvertResource\Pages\ListSponsoredAdverts::route('/'),
            'create' => \App\Filament\Resources\SponsoredAdvertResource\Pages\CreateSponsoredAdvert::route('/create'),
            'edit' => \App\Filament\Resources\SponsoredAdvertResource\Pages\EditSponsoredAdvert::route('/{record}/edit'),
        ];
    }
}
