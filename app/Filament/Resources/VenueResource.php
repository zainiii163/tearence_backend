<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VenueResource\Pages;
use App\Models\Venue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class VenueResource extends Resource
{
    protected static ?string $model = Venue::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Venues';

    protected static ?string $modelLabel = 'Venue';

    protected static ?string $pluralModelLabel = 'Venues';

    protected static ?string $navigationGroup = 'Events & Venues';

    protected static ?int $navigationSort = 2;

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
                            ->unique(Venue::class, 'slug', ignoreRecord: true)
                            ->disabled(),

                        Forms\Components\Select::make('venue_type')
                            ->required()
                            ->label('Venue Type')
                            ->options([
                                'wedding_hall' => 'Wedding Hall',
                                'conference_centre' => 'Conference Centre',
                                'party_hall' => 'Party Hall',
                                'outdoor_space' => 'Outdoor Space',
                                'hotel_banquet' => 'Hotel & Banquet Room',
                                'bar_restaurant' => 'Bar & Restaurant',
                                'meeting_room' => 'Meeting Room',
                                'exhibition_space' => 'Exhibition Space',
                                'sports_venue' => 'Sports Venue',
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

                // Capacity & Pricing
                Forms\Components\Section::make('Capacity & Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('capacity')
                            ->required()
                            ->numeric()
                            ->suffix('guests'),

                        Forms\Components\TextInput::make('min_price')
                            ->label('Minimum Price')
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

                // Venue Details
                Forms\Components\Section::make('Venue Details')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\CheckboxList::make('amenities')
                            ->label('Amenities')
                            ->options([
                                'wi_fi' => 'Wi-Fi',
                                'parking' => 'Parking',
                                'catering' => 'Catering',
                                'av_equipment' => 'AV Equipment',
                                'air_conditioning' => 'Air Conditioning',
                                'heating' => 'Heating',
                                'sound_system' => 'Sound System',
                                'lighting' => 'Lighting',
                                'stage' => 'Stage',
                                'dance_floor' => 'Dance Floor',
                                'bar' => 'Bar',
                                'kitchen' => 'Kitchen',
                                'restrooms' => 'Restrooms',
                                'wheelchair_access' => 'Wheelchair Access',
                                'elevator' => 'Elevator',
                                'security' => 'Security',
                            ])
                            ->columns(3),

                        Forms\Components\Textarea::make('opening_hours')
                            ->label('Opening Hours')
                            ->rows(3)
                            ->helperText('e.g., Mon-Fri: 9AM-9PM, Sat-Sun: 10AM-11PM')
                            ->nullable(),
                    ])
                    ->columns(1),

                // Features
                Forms\Components\Section::make('Features')
                    ->schema([
                        Forms\Components\Toggle::make('indoor')
                            ->label('Indoor')
                            ->default(true),

                        Forms\Components\Toggle::make('outdoor')
                            ->label('Outdoor')
                            ->default(false),

                        Forms\Components\Toggle::make('catering_available')
                            ->label('Catering Available')
                            ->default(false),

                        Forms\Components\Toggle::make('parking_available')
                            ->label('Parking Available')
                            ->default(false),

                        Forms\Components\Toggle::make('accessibility')
                            ->label('Wheelchair Accessible')
                            ->default(false),
                    ])
                    ->columns(3),

                // Contact Information
                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('contact_email')
                            ->required()
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('booking_link')
                            ->label('Booking Link')
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
                            ->label('Venue Images')
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

                        Forms\Components\TextInput::make('floor_plan')
                            ->label('Floor Plan URL')
                            ->url()
                            ->maxLength(500)
                            ->nullable(),

                        Forms\Components\TextInput::make('video_link')
                            ->label('Video Tour Link')
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

                Tables\Columns\TextColumn::make('venue_type_label')
                    ->label('Venue Type')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacity')
                    ->formatStateUsing(fn (int $state): string => number_format($state) . ' guests')
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
                Tables\Filters\SelectFilter::make('venue_type')
                    ->options([
                        'wedding_hall' => 'Wedding Hall',
                        'conference_centre' => 'Conference Centre',
                        'party_hall' => 'Party Hall',
                        'outdoor_space' => 'Outdoor Space',
                        'hotel_banquet' => 'Hotel & Banquet Room',
                        'bar_restaurant' => 'Bar & Restaurant',
                        'meeting_room' => 'Meeting Room',
                        'exhibition_space' => 'Exhibition Space',
                        'sports_venue' => 'Sports Venue',
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
                    ->placeholder('All venues')
                    ->trueLabel('Active venues')
                    ->falseLabel('Inactive venues'),

                Tables\Filters\Filter::make('capacity_range')
                    ->form([
                        Forms\Components\TextInput::make('min_capacity')
                            ->label('Minimum Capacity')
                            ->numeric()
                            ->placeholder('e.g., 50'),

                        Forms\Components\TextInput::make('max_capacity')
                            ->label('Maximum Capacity')
                            ->numeric()
                            ->placeholder('e.g., 500'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['min_capacity'], fn (Builder $query, $value) => $query->where('capacity', '>=', $value))
                            ->when($data['max_capacity'], fn (Builder $query, $value) => $query->where('capacity', '<=', $value));
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
            'index' => Pages\ListVenues::route('/'),
            'create' => Pages\CreateVenue::route('/create'),
            'view' => Pages\ViewVenue::route('/{record}'),
            'edit' => Pages\EditVenue::route('/{record}/edit'),
        ];
    }
}
