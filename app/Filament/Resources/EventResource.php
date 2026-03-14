<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use App\Models\Venue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Events';

    protected static ?string $modelLabel = 'Event';

    protected static ?string $pluralModelLabel = 'Events';

    protected static ?string $navigationGroup = 'Events & Venues';

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
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Event::class, 'slug', ignoreRecord: true)
                            ->disabled(),

                        Forms\Components\Select::make('category')
                            ->required()
                            ->options([
                                'concert' => 'Concerts & Music',
                                'workshop' => 'Workshops',
                                'party' => 'Parties & Nightlife',
                                'festival' => 'Festivals',
                                'conference' => 'Business Conferences',
                                'sports' => 'Sports Events',
                                'cultural' => 'Cultural Events',
                                'food_drink' => 'Food & Drink',
                                'charity' => 'Charity Events',
                                'other' => 'Other',
                            ])
                            ->searchable(),

                        Forms\Components\DateTimePicker::make('date_time')
                            ->required()
                            ->label('Event Date & Time'),

                        Forms\Components\Select::make('venue_id')
                            ->label('Associated Venue')
                            ->options(Venue::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\TextInput::make('venue_name')
                            ->label('Venue Name (if not associated)')
                            ->maxLength(255)
                            ->nullable(),
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
                                'free' => 'Free',
                                'paid' => 'Paid',
                                'donation' => 'Donation',
                            ])
                            ->reactive()
                            ->afterStateUpdated(fn (string $state, Forms\Set $set) => $state === 'free' ? $set('ticket_price', null) : null),

                        Forms\Components\TextInput::make('ticket_price')
                            ->label('Ticket Price')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->nullable()
                            ->visible(fn (callable $get) => $get('price_type') === 'paid'),

                        Forms\Components\TextInput::make('ticket_link')
                            ->label('Ticket Booking Link')
                            ->url()
                            ->maxLength(500)
                            ->nullable(),
                    ])
                    ->columns(2),

                // Event Details
                Forms\Components\Section::make('Event Details')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('schedule')
                            ->label('Schedule/Agenda')
                            ->rows(3)
                            ->nullable(),

                        Forms\Components\TextInput::make('age_restrictions')
                            ->maxLength(100)
                            ->nullable(),

                        Forms\Components\TextInput::make('dress_code')
                            ->maxLength(100)
                            ->nullable(),

                        Forms\Components\TextInput::make('expected_attendance')
                            ->label('Expected Attendance')
                            ->numeric()
                            ->nullable(),
                    ])
                    ->columns(2),

                // Contact Information
                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('contact_email')
                            ->required()
                            ->email()
                            ->maxLength(255),

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
                            ->label('Event Images')
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
                            ->label('Video Link')
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

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'concert' => 'Concerts & Music',
                        'workshop' => 'Workshops',
                        'party' => 'Parties & Nightlife',
                        'festival' => 'Festivals',
                        'conference' => 'Business Conferences',
                        'sports' => 'Sports Events',
                        'cultural' => 'Cultural Events',
                        'food_drink' => 'Food & Drink',
                        'charity' => 'Charity Events',
                        'other' => 'Other',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date_time')
                    ->label('Event Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('formatted_price')
                    ->label('Price')
                    ->sortable()
                    ->getStateUsing(fn (Event $record): string => $record->formatted_price),

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
                        'concert' => 'Concerts & Music',
                        'workshop' => 'Workshops',
                        'party' => 'Parties & Nightlife',
                        'festival' => 'Festivals',
                        'conference' => 'Business Conferences',
                        'sports' => 'Sports Events',
                        'cultural' => 'Cultural Events',
                        'food_drink' => 'Food & Drink',
                        'charity' => 'Charity Events',
                        'other' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('price_type')
                    ->options([
                        'free' => 'Free',
                        'paid' => 'Paid',
                        'donation' => 'Donation',
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
                    ->placeholder('All events')
                    ->trueLabel('Active events')
                    ->falseLabel('Inactive events'),
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
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'view' => Pages\ViewEvent::route('/{record}'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
