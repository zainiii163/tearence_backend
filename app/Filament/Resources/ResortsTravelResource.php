<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResortsTravelResource\Pages;
use App\Models\ResortsTravel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResortsTravelResource extends Resource
{
    protected static ?string $model = ResortsTravel::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Resorts & Travel';

    protected static ?string $modelLabel = 'Travel Advert';

    protected static ?string $pluralModelLabel = 'Travel Adverts';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('tagline')
                            ->maxLength(255),
                        Forms\Components\Select::make('advert_type')
                            ->options([
                                'accommodation' => 'Accommodation',
                                'transport' => 'Transport Services',
                                'experience' => 'Travel Experiences',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('accommodation_type', null))
                            ->afterStateUpdated(fn (callable $set) => $set('transport_type', null))
                            ->afterStateUpdated(fn (callable $set) => $set('experience_type', null)),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('country')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('city')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('address')
                            ->nullable(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->nullable(),
                                Forms\Components\TextInput::make('longitude')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->nullable(),
                            ]),
                        Forms\Components\Toggle::make('is_approximate_location')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Accommodation Details')
                    ->schema([
                        Forms\Components\Select::make('accommodation_type')
                            ->options([
                                'resort' => 'Resort',
                                'hotel' => 'Hotel',
                                'bnb' => 'B&B',
                                'guest_house' => 'Guest House',
                                'holiday_home' => 'Holiday Home',
                                'villa' => 'Villa',
                                'lodge' => 'Lodge',
                            ])
                            ->visible(fn (callable $get) => $get('advert_type') === 'accommodation')
                            ->required(fn (callable $get) => $get('advert_type') === 'accommodation'),
                        Forms\Components\TextInput::make('price_per_night')
                            ->numeric()
                            ->prefix('£')
                            ->visible(fn (callable $get) => $get('advert_type') === 'accommodation')
                            ->nullable(),
                        Forms\Components\TextInput::make('room_types')
                            ->placeholder('e.g., Single, Double, Suite')
                            ->visible(fn (callable $get) => $get('advert_type') === 'accommodation')
                            ->nullable(),
                        Forms\Components\TextInput::make('guest_capacity')
                            ->numeric()
                            ->visible(fn (callable $get) => $get('advert_type') === 'accommodation')
                            ->nullable(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('check_in_time')
                                    ->type('time')
                                    ->visible(fn (callable $get) => $get('advert_type') === 'accommodation')
                                    ->nullable(),
                                Forms\Components\TextInput::make('check_out_time')
                                    ->type('time')
                                    ->visible(fn (callable $get) => $get('advert_type') === 'accommodation')
                                    ->nullable(),
                            ]),
                        Forms\Components\TextInput::make('distance_to_city_centre')
                            ->numeric()
                            ->suffix('km')
                            ->visible(fn (callable $get) => $get('advert_type') === 'accommodation')
                            ->nullable(),
                        Forms\Components\TextInput::make('amenities')
                            ->placeholder('e.g., Wi-Fi, Pool, Parking')
                            ->visible(fn (callable $get) => $get('advert_type') === 'accommodation')
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->visible(fn (callable $get) => $get('advert_type') === 'accommodation'),

                Forms\Components\Section::make('Transport Details')
                    ->schema([
                        Forms\Components\Select::make('transport_type')
                            ->options([
                                'airport_transfer' => 'Airport Transfer',
                                'taxi_chauffeur' => 'Taxi / Chauffeur',
                                'car_hire' => 'Car Hire',
                                'shuttle_bus' => 'Shuttle Bus',
                                'tour_bus' => 'Tour Bus',
                                'boat_ferry' => 'Boat / Ferry',
                                'motorbike_scooter' => 'Motorbike / Scooter Rental',
                            ])
                            ->visible(fn (callable $get) => $get('advert_type') === 'transport')
                            ->required(fn (callable $get) => $get('advert_type') === 'transport'),
                        Forms\Components\TextInput::make('price_per_trip')
                            ->numeric()
                            ->prefix('£')
                            ->visible(fn (callable $get) => $get('advert_type') === 'transport')
                            ->nullable(),
                        Forms\Components\TextInput::make('vehicle_type')
                            ->visible(fn (callable $get) => $get('advert_type') === 'transport')
                            ->nullable(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('passenger_capacity')
                                    ->numeric()
                                    ->visible(fn (callable $get) => $get('advert_type') === 'transport')
                                    ->nullable(),
                                Forms\Components\TextInput::make('luggage_capacity')
                                    ->numeric()
                                    ->visible(fn (callable $get) => $get('advert_type') === 'transport')
                                    ->nullable(),
                            ]),
                        Forms\Components\TextInput::make('service_area')
                            ->visible(fn (callable $get) => $get('advert_type') === 'transport')
                            ->nullable(),
                        Forms\Components\TextInput::make('operating_hours')
                            ->placeholder('e.g., 24/7, 8AM-8PM')
                            ->visible(fn (callable $get) => $get('advert_type') === 'transport')
                            ->nullable(),
                        Forms\Components\Toggle::make('airport_pickup')
                            ->visible(fn (callable $get) => $get('advert_type') === 'transport')
                            ->default(false),
                    ])
                    ->columns(2)
                    ->visible(fn (callable $get) => $get('advert_type') === 'transport'),

                Forms\Components\Section::make('Experience Details')
                    ->schema([
                        Forms\Components\Select::make('experience_type')
                            ->options([
                                'tours' => 'Tours',
                                'excursions' => 'Excursions',
                                'adventure_packages' => 'Adventure Packages',
                                'wellness_retreats' => 'Wellness Retreats',
                            ])
                            ->visible(fn (callable $get) => $get('advert_type') === 'experience')
                            ->required(fn (callable $get) => $get('advert_type') === 'experience'),
                        Forms\Components\TextInput::make('price_per_service')
                            ->numeric()
                            ->prefix('£')
                            ->visible(fn (callable $get) => $get('advert_type') === 'experience')
                            ->nullable(),
                        Forms\Components\TextInput::make('duration')
                            ->placeholder('e.g., 2 hours, 1 day')
                            ->visible(fn (callable $get) => $get('advert_type') === 'experience')
                            ->nullable(),
                        Forms\Components\TextInput::make('group_size')
                            ->numeric()
                            ->visible(fn (callable $get) => $get('advert_type') === 'experience')
                            ->nullable(),
                        Forms\Components\Textarea::make('whats_included')
                            ->rows(3)
                            ->visible(fn (callable $get) => $get('advert_type') === 'experience')
                            ->nullable(),
                        Forms\Components\Textarea::make('what_to_bring')
                            ->rows(3)
                            ->visible(fn (callable $get) => $get('advert_type') === 'experience')
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->visible(fn (callable $get) => $get('advert_type') === 'experience'),

                Forms\Components\Section::make('Pricing & Availability')
                    ->schema([
                        Forms\Components\Select::make('currency')
                            ->options([
                                'GBP' => 'GBP (£)',
                                'USD' => 'USD ($)',
                                'EUR' => 'EUR (€)',
                                'AED' => 'AED (د.إ)',
                            ])
                            ->default('GBP')
                            ->required(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('availability_start')
                                    ->nullable(),
                                Forms\Components\DatePicker::make('availability_end')
                                    ->nullable()
                                    ->after('availability_start'),
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\Textarea::make('overview')
                            ->rows(3)
                            ->nullable(),
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('key_features')
                            ->rows(3)
                            ->nullable(),
                        Forms\Components\Textarea::make('why_travellers_love_this')
                            ->rows(3)
                            ->nullable(),
                        Forms\Components\Textarea::make('nearby_attractions')
                            ->rows(3)
                            ->nullable(),
                        Forms\Components\Textarea::make('additional_notes')
                            ->rows(3)
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('contact_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('business_name')
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\TextInput::make('phone_number')
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->nullable(),
                        Forms\Components\TextInput::make('social_links')
                            ->placeholder('e.g., facebook,instagram,twitter')
                            ->nullable(),
                        Forms\Components\TextInput::make('logo')
                            ->nullable(),
                        Forms\Components\Toggle::make('verified_business')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\TextInput::make('main_image')
                            ->nullable(),
                        Forms\Components\TextInput::make('images')
                            ->placeholder('Comma-separated image paths')
                            ->nullable(),
                        Forms\Components\TextInput::make('video_link')
                            ->url()
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Promotion')
                    ->schema([
                        Forms\Components\Select::make('promotion_tier')
                            ->options([
                                'standard' => 'Standard (Free)',
                                'promoted' => 'Promoted (£29.99)',
                                'featured' => 'Featured (£59.99)',
                                'sponsored' => 'Sponsored (£99.99)',
                                'network_wide' => 'Network-Wide Boost (£199.99)',
                            ])
                            ->default('standard')
                            ->required(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active'),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('advert_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'accommodation' => 'success',
                        'transport' => 'warning',
                        'experience' => 'info',
                    }),
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price_per_night')
                    ->label('Price/Night')
                    ->money('GBP')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->price_per_night ? '£' . number_format($record->price_per_night, 2) : '-'),
                Tables\Columns\ToggleColumn::make('is_active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('advert_type')
                    ->options([
                        'accommodation' => 'Accommodation',
                        'transport' => 'Transport Services',
                        'experience' => 'Travel Experiences',
                    ]),
                Tables\Filters\SelectFilter::make('country')
                    ->searchable()
                    ->options(fn () => collect([
                        'United Kingdom' => 'United Kingdom',
                        'United States' => 'United States',
                        'France' => 'France',
                        'Germany' => 'Germany',
                        'Italy' => 'Italy',
                        'Spain' => 'Spain',
                        'Netherlands' => 'Netherlands',
                        'Belgium' => 'Belgium',
                        'Switzerland' => 'Switzerland',
                        'Austria' => 'Austria',
                    ])),
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
            ->defaultPaginationPageOption(25)
            ->extremePaginationLinks();
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
            'index' => Pages\ListResortsTravels::route('/'),
            'create' => Pages\CreateResortsTravel::route('/create'),
            'view' => Pages\ViewResortsTravel::route('/{record}'),
            'edit' => Pages\EditResortsTravel::route('/{record}/edit'),
        ];
    }
}
