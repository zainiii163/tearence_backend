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
                            ->required()
                            ->options([
                                'accommodation' => 'Accommodation',
                                'transport' => 'Transport Services',
                                'experience' => 'Travel Experiences',
                            ])
                            ->reactive(),
                        Forms\Components\Select::make('accommodation_type')
                            ->required()
                            ->options([
                                'resort' => 'Resort',
                                'hotel' => 'Hotel',
                                'bnb' => 'B&B',
                                'guest_house' => 'Guest House',
                                'holiday_home' => 'Holiday Home',
                                'villa' => 'Villa',
                                'lodge' => 'Lodge',
                            ])
                            ->visible(fn (callable $get) => $get('advert_type') === 'accommodation'),
                        Forms\Components\Select::make('transport_type')
                            ->required()
                            ->options([
                                'airport_transfer' => 'Airport Transfer',
                                'taxi_chauffeur' => 'Taxi / Chauffeur',
                                'car_hire' => 'Car Hire',
                                'shuttle_bus' => 'Shuttle Bus',
                                'tour_bus' => 'Tour Bus',
                                'boat_ferry' => 'Boat / Ferry',
                                'motorbike_scooter' => 'Motorbike / Scooter Rental',
                            ])
                            ->visible(fn (callable $get) => $get('advert_type') === 'transport'),
                        Forms\Components\Select::make('experience_type')
                            ->required()
                            ->options([
                                'tours' => 'Tours',
                                'excursions' => 'Excursions',
                                'adventure_packages' => 'Adventure Packages',
                                'wellness_retreats' => 'Wellness Retreats',
                            ])
                            ->visible(fn (callable $get) => $get('advert_type') === 'experience'),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('country')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('city')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\Textarea::make('address')
                            ->maxLength(65535),
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.000001)
                            ->min(-90)
                            ->max(90),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.000001)
                            ->min(-180)
                            ->max(180),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price_per_night')
                            ->numeric()
                            ->step(0.01)
                            ->min(0)
                            ->prefix('£'),
                        Forms\Components\TextInput::make('price_per_trip')
                            ->numeric()
                            ->step(0.01)
                            ->min(0)
                            ->prefix('£'),
                        Forms\Components\TextInput::make('price_per_service')
                            ->numeric()
                            ->step(0.01)
                            ->min(0)
                            ->prefix('£'),
                        Forms\Components\Select::make('currency')
                            ->options([
                                'GBP' => 'GBP (£)',
                                'USD' => 'USD ($)',
                                'EUR' => 'EUR (€)',
                            ])
                            ->default('GBP'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Availability')
                    ->schema([
                        Forms\Components\DatePicker::make('availability_start')
                            ->date(),
                        Forms\Components\DatePicker::make('availability_end')
                            ->date()
                            ->after('availability_start'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Accommodation Details')
                    ->schema([
                        Forms\Components\CheckboxList::make('room_types')
                            ->options([
                                'single' => 'Single Room',
                                'double' => 'Double Room',
                                'twin' => 'Twin Room',
                                'suite' => 'Suite',
                                'family' => 'Family Room',
                                'dormitory' => 'Dormitory',
                            ]),
                        Forms\Components\CheckboxList::make('amenities')
                            ->options([
                                'wi_fi' => 'Wi-Fi',
                                'pool' => 'Swimming Pool',
                                'parking' => 'Parking',
                                'breakfast' => 'Breakfast Included',
                                'air_conditioning' => 'Air Conditioning',
                                'heating' => 'Heating',
                                'kitchen' => 'Kitchen',
                                'tv' => 'TV',
                                'washing_machine' => 'Washing Machine',
                                'elevator' => 'Elevator',
                                'wheelchair_access' => 'Wheelchair Access',
                                'pet_friendly' => 'Pet Friendly',
                                'gym' => 'Gym/Fitness Center',
                                'spa' => 'Spa/Wellness',
                                'restaurant' => 'Restaurant',
                                'bar' => 'Bar/Lounge',
                                'room_service' => 'Room Service',
                                'concierge' => 'Concierge Service',
                                'business_center' => 'Business Center',
                                'meeting_rooms' => 'Meeting Rooms',
                                'airport_shuttle' => 'Airport Shuttle',
                                'beach_access' => 'Beach Access',
                                'golf_course' => 'Golf Course',
                                'tennis_court' => 'Tennis Court',
                                'kids_club' => 'Kids Club',
                                'babysitting' => 'Babysitting Service',
                                'laundry_service' => 'Laundry Service',
                                'dry_cleaning' => 'Dry Cleaning',
                                'currency_exchange' => 'Currency Exchange',
                                'atm' => 'ATM on-site',
                                'safety_deposit_box' => 'Safety Deposit Box',
                                '24_hour_front_desk' => '24-Hour Front Desk',
                                'multilingual_staff' => 'Multilingual Staff',
                            ])
                            ->columns(3),
                        Forms\Components\TextInput::make('distance_to_city_centre')
                            ->numeric()
                            ->min(0)
                            ->suffix('km'),
                        Forms\Components\TimePicker::make('check_in_time'),
                        Forms\Components\TimePicker::make('check_out_time'),
                        Forms\Components\TextInput::make('guest_capacity')
                            ->numeric()
                            ->min(1),
                    ])
                    ->visible(fn (callable $get) => $get('advert_type') === 'accommodation')
                    ->columns(2),

                Forms\Components\Section::make('Transport Details')
                    ->schema([
                        Forms\Components\TextInput::make('vehicle_type')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('passenger_capacity')
                            ->numeric()
                            ->min(1),
                        Forms\Components\TextInput::make('luggage_capacity')
                            ->numeric()
                            ->min(0),
                        Forms\Components\Textarea::make('service_area')
                            ->maxLength(65535),
                        Forms\Components\CheckboxList::make('operating_hours')
                            ->options([
                                'monday' => 'Monday',
                                'tuesday' => 'Tuesday',
                                'wednesday' => 'Wednesday',
                                'thursday' => 'Thursday',
                                'friday' => 'Friday',
                                'saturday' => 'Saturday',
                                'sunday' => 'Sunday',
                            ]),
                        Forms\Components\Checkbox::make('airport_pickup'),
                    ])
                    ->visible(fn (callable $get) => $get('advert_type') === 'transport')
                    ->columns(2),

                Forms\Components\Section::make('Experience Details')
                    ->schema([
                        Forms\Components\TextInput::make('duration')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('group_size')
                            ->numeric()
                            ->min(1),
                        Forms\Components\Textarea::make('whats_included')
                            ->maxLength(65535),
                        Forms\Components\Textarea::make('what_to_bring')
                            ->maxLength(65535),
                    ])
                    ->visible(fn (callable $get) => $get('advert_type') === 'experience')
                    ->columns(2),

                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('overview')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('key_features')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('why_travellers_love_this')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('nearby_attractions')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('additional_notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('contact_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('business_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone_number')
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->required()
                            ->email(),
                        Forms\Components\TextInput::make('website')
                            ->url(),
                        Forms\Components\Repeater::make('social_links')
                            ->schema([
                                Forms\Components\TextInput::make('link')
                                    ->url(),
                            ]),
                        Forms\Components\FileUpload::make('logo')
                            ->image()
                            ->directory('resorts-travel/logos')
                            ->maxSize(1024),
                        Forms\Components\Checkbox::make('verified_business'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('main_image')
                            ->image()
                            ->directory('resorts-travel')
                            ->maxSize(2048),
                        Forms\Components\FileUpload::make('images')
                            ->multiple()
                            ->image()
                            ->directory('resorts-travel')
                            ->maxSize(2048)
                            ->maxFiles(10),
                        Forms\Components\TextInput::make('video_link')
                            ->url(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Promotion')
                    ->schema([
                        Forms\Components\Select::make('promotion_tier')
                            ->options([
                                'standard' => 'Standard',
                                'promoted' => 'Promoted',
                                'featured' => 'Featured',
                                'sponsored' => 'Sponsored',
                                'network_wide' => 'Network-Wide Boost',
                            ])
                            ->default('standard'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_approximate_location')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->circular()
                    ->defaultImageUrl(url('/placeholder.png')),
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
                Tables\Columns\TextColumn::make('display_price.formatted')
                    ->label('Price')
                    ->sortable(),
                Tables\Columns\TextColumn::make('promotion_tier')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'standard' => 'gray',
                        'promoted' => 'blue',
                        'featured' => 'purple',
                        'sponsored' => 'orange',
                        'network_wide' => 'red',
                    }),
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
                Tables\Filters\SelectFilter::make('promotion_tier')
                    ->options([
                        'standard' => 'Standard',
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        'network_wide' => 'Network-Wide Boost',
                    ]),
                Tables\Filters\SelectFilter::make('country')
                    ->searchable()
                    ->options(fn () => ResortsTravel::distinct()->pluck('country', 'country')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\TernaryFilter::make('verified_business')
                    ->label('Verified Business'),
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
            'index' => Pages\ListResortsTravels::route('/'),
            'create' => Pages\CreateResortsTravel::route('/create'),
            'view' => Pages\ViewResortsTravel::route('/{record}'),
            'edit' => Pages\EditResortsTravel::route('/{record}/edit'),
        ];
    }
}
