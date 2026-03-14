<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VenueServiceResource\Pages;
use App\Models\VenueService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class VenueServiceResource extends Resource
{
    protected static ?string $model = VenueService::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Venue Services';

    protected static ?string $modelLabel = 'Venue Service';

    protected static ?string $pluralModelLabel = 'Venue Services';

    protected static ?string $navigationGroup = 'Events & Venues';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Basic Information
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(VenueService::class, 'slug', ignoreRecord: true)
                            ->disabled(),

                        Forms\Components\Select::make('category')
                            ->required()
                            ->options([
                                'catering' => 'Catering',
                                'decor' => 'Decor',
                                'dj' => 'DJ Services',
                                'photography' => 'Photography',
                                'videography' => 'Videography',
                                'security' => 'Security',
                                'event_planning' => 'Event Planning',
                                'lighting' => 'Lighting',
                                'sound' => 'Sound System',
                                'transportation' => 'Transportation',
                                'other' => 'Other',
                            ])
                            ->searchable(),
                    ])
                    ->columns(2),

                // Location Information
                Forms\Components\Section::make('Location Information')
                    ->schema([
                        Forms\Components\TextInput::make('country')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('city')
                            ->required()
                            ->maxLength(100),
                    ])
                    ->columns(2),

                // Pricing Information
                Forms\Components\Section::make('Pricing Information')
                    ->schema([
                        Forms\Components\Select::make('price_type')
                            ->required()
                            ->options([
                                'fixed' => 'Fixed Price',
                                'hourly' => 'Hourly Rate',
                                'package' => 'Package Based',
                                'custom' => 'Custom Quote',
                            ]),

                        Forms\Components\TextInput::make('min_price')
                            ->label('Starting Price')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->nullable(),

                        Forms\Components\TextInput::make('max_price')
                            ->label('Maximum Price')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->nullable(),
                    ])
                    ->columns(3),

                // Service Details
                Forms\Components\Section::make('Service Details')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('packages_offered')
                            ->label('Packages Offered')
                            ->rows(3)
                            ->helperText('Describe the packages you offer')
                            ->nullable(),

                        Forms\Components\Textarea::make('availability')
                            ->label('Availability')
                            ->rows(2)
                            ->helperText('e.g., Available weekends, Weekdays after 6PM')
                            ->nullable(),
                    ])
                    ->columns(1),

                // Contact Information
                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('contact_email')
                            ->required()
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->maxLength(500)
                            ->nullable(),

                        Forms\Components\Repeater::make('social_links')
                            ->label('Social Media Links')
                            ->schema([
                                Forms\Components\TextInput::make('link')
                                    ->label('Link')
                                    ->url()
                                    ->required(),
                            ])
                            ->columns(1)
                            ->collapsed()
                            ->collapsible()
                            ->nullable(),
                    ])
                    ->columns(1),

                // Media
                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\Repeater::make('images')
                            ->label('Portfolio Images')
                            ->schema([
                                Forms\Components\TextInput::make('url')
                                    ->label('Image URL')
                                    ->url()
                                    ->required(),
                            ])
                            ->columns(1)
                            ->collapsed()
                            ->collapsible()
                            ->nullable(),

                        Forms\Components\TextInput::make('video_link')
                            ->label('Promo Video Link')
                            ->url()
                            ->maxLength(500)
                            ->nullable(),
                    ])
                    ->columns(1),

                // Promotion Settings
                Forms\Components\Section::make('Promotion Settings')
                    ->schema([
                        Forms\Components\Select::make('promotion_tier')
                            ->options([
                                'standard' => 'Standard',
                                'promoted' => 'Promoted',
                                'featured' => 'Featured',
                                'sponsored' => 'Sponsored',
                                'spotlight' => 'Spotlight',
                            ])
                            ->default('standard'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),

                // User Information
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Posted By')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images.0')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(url('/placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'catering' => 'Catering',
                        'decor' => 'Decor',
                        'dj' => 'DJ Services',
                        'photography' => 'Photography',
                        'videography' => 'Videography',
                        'security' => 'Security',
                        'event_planning' => 'Event Planning',
                        'lighting' => 'Lighting',
                        'sound' => 'Sound System',
                        'transportation' => 'Transportation',
                        'other' => 'Other',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('formatted_price_range')
                    ->label('Price Range')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('promotion_tier')
                    ->colors([
                        'secondary' => 'standard',
                        'warning' => 'promoted',
                        'success' => 'featured',
                        'info' => 'sponsored',
                        'danger' => 'spotlight',
                    ]),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Posted By')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'catering' => 'Catering',
                        'decor' => 'Decor',
                        'dj' => 'DJ Services',
                        'photography' => 'Photography',
                        'videography' => 'Videography',
                        'security' => 'Security',
                        'event_planning' => 'Event Planning',
                        'lighting' => 'Lighting',
                        'sound' => 'Sound System',
                        'transportation' => 'Transportation',
                        'other' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('promotion_tier')
                    ->options([
                        'standard' => 'Standard',
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        'spotlight' => 'Spotlight',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->placeholder('All services')
                    ->trueLabel('Active services')
                    ->falseLabel('Inactive services'),
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
            'index' => Pages\ListVenueServices::route('/'),
            'create' => Pages\CreateVenueService::route('/create'),
            'view' => Pages\ViewVenueService::route('/{record}'),
            'edit' => Pages\EditVenueService::route('/{record}/edit'),
        ];
    }
}
