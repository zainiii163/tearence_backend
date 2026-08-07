<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImagesAdvertResource\Pages;
use App\Models\ImagesAdvert;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ImagesAdvertResource extends Resource
{
    protected static ?string $model = ImagesAdvert::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Images & Media';

    protected static ?string $modelLabel = 'Stock Image';

    protected static ?string $pluralModelLabel = 'Images & Media';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Owner')
                            ->options(fn () => User::query()
                                ->orderBy('first_name')
                                ->limit(200)
                                ->get()
                                ->mapWithKeys(fn ($u) => [
                                    $u->user_id => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) . ' (' . $u->email . ')',
                                ])
                                ->all())
                            ->searchable()
                            ->required()
                            ->default(fn () => auth()->id()),

                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ImagesAdvert::class, 'slug', ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('short_description')
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('image_category')
                            ->label('Category')
                            ->required()
                            ->options([
                                'business' => 'Business',
                                'people' => 'People',
                                'nature' => 'Nature',
                                'food' => 'Food',
                                'technology' => 'Technology',
                                'real_estate' => 'Real Estate',
                                'travel' => 'Travel',
                                'abstract' => 'Abstract',
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Upload image / video')
                    ->description('Upload files here from the admin panel')
                    ->schema([
                        Forms\Components\FileUpload::make('main_image')
                            ->label('Main image')
                            ->image()
                            ->directory('images')
                            ->disk('public')
                            ->imageEditor()
                            ->required(fn ($get) => $get('media_type') !== 'video')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('media_type')
                            ->label('Media type')
                            ->options([
                                'image' => 'Image',
                                'video' => 'Short video advert',
                            ])
                            ->default('image')
                            ->required()
                            ->live(),

                        Forms\Components\FileUpload::make('video_path')
                            ->label('Video file (MP4)')
                            ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime'])
                            ->directory('images/videos')
                            ->disk('public')
                            ->maxSize(51200)
                            ->visible(fn ($get) => $get('media_type') === 'video'),

                        Forms\Components\TextInput::make('video_url')
                            ->label('Or external video URL')
                            ->url()
                            ->maxLength(500)
                            ->visible(fn ($get) => $get('media_type') === 'video'),

                        Forms\Components\FileUpload::make('images')
                            ->label('Additional images')
                            ->multiple()
                            ->image()
                            ->directory('images')
                            ->disk('public')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('orientation')
                            ->options([
                                'landscape' => 'Landscape',
                                'portrait' => 'Portrait',
                                'square' => 'Square',
                            ])
                            ->default('landscape')
                            ->required(),

                        Forms\Components\Select::make('color_type')
                            ->options([
                                'color' => 'Color',
                                'black_white' => 'Black and White',
                            ])
                            ->default('color')
                            ->required(),

                        Forms\Components\TextInput::make('width')
                            ->numeric()
                            ->label('Width (px)'),

                        Forms\Components\TextInput::make('height')
                            ->numeric()
                            ->label('Height (px)'),

                        Forms\Components\TagsInput::make('tags')
                            ->placeholder('Add tag')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('standard_price')
                            ->label('Standard price')
                            ->required()
                            ->numeric()
                            ->default(9.99)
                            ->prefix('£'),

                        Forms\Components\TextInput::make('extended_price')
                            ->numeric()
                            ->default(29.99)
                            ->prefix('£'),

                        Forms\Components\TextInput::make('exclusive_price')
                            ->numeric()
                            ->default(199.99)
                            ->prefix('£'),

                        Forms\Components\Select::make('currency')
                            ->options([
                                'GBP' => 'GBP',
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                            ])
                            ->default('GBP')
                            ->required(),

                        Forms\Components\Select::make('license_type')
                            ->required()
                            ->options([
                                'royalty_free' => 'Royalty Free',
                                'rights_managed' => 'Rights Managed',
                                'extended' => 'Extended License',
                                'editorial' => 'Editorial',
                                'exclusive' => 'Exclusive',
                            ])
                            ->default('royalty_free'),

                        Forms\Components\Select::make('promotion_tier')
                            ->options([
                                'standard' => 'Standard',
                                'promoted' => 'Promoted',
                                'featured' => 'Featured',
                                'sponsored' => 'Sponsored',
                            ])
                            ->default('standard'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Contact')
                    ->schema([
                        Forms\Components\TextInput::make('contact_name')
                            ->required()
                            ->maxLength(255)
                            ->default(fn () => auth()->user()?->getFilamentName() ?? 'WWA Admin'),

                        Forms\Components\TextInput::make('contact_email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->default(fn () => auth()->user()?->email),

                        Forms\Components\TextInput::make('contact_phone')
                            ->tel()
                            ->maxLength(40),

                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Select::make('verification_status')
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ])
                            ->default('verified')
                            ->required(),

                        Forms\Components\Toggle::make('is_verified_creator')
                            ->label('Verified Creator')
                            ->default(true),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Image')
                    ->disk('public')
                    ->square(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('image_category')
                    ->label('Category')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Owner')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('standard_price')
                    ->label('Price')
                    ->money(fn ($record) => $record->currency ?? 'GBP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('verification_status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'verified' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('image_category')
                    ->label('Category')
                    ->options([
                        'business' => 'Business',
                        'people' => 'People',
                        'nature' => 'Nature',
                        'food' => 'Food',
                        'technology' => 'Technology',
                        'real_estate' => 'Real Estate',
                        'travel' => 'Travel',
                        'abstract' => 'Abstract',
                    ]),

                Tables\Filters\SelectFilter::make('verification_status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImagesAdverts::route('/'),
            'create' => Pages\CreateImagesAdvert::route('/create'),
            'edit' => Pages\EditImagesAdvert::route('/{record}/edit'),
        ];
    }
}
