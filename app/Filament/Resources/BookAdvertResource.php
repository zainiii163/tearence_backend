<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookAdvertResource\Pages;
use App\Filament\Resources\BookAdvertResource\RelationManagers;
use App\Models\BookAdvert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BookAdvertResource extends Resource
{
    protected static ?string $model = BookAdvert::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Books Adverts';

    protected static ?string $modelLabel = 'Books Advert';

    protected static ?string $pluralModelLabel = 'Books Adverts';

    protected static ?string $navigationGroup = 'Books Marketplace';

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
                            ->unique(BookAdvert::class, 'slug', ignoreRecord: true)
                            ->disabled(),

                        Forms\Components\TextInput::make('subtitle')
                            ->maxLength(500),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('short_description')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                // Book Details
                Forms\Components\Section::make('Book Details')
                    ->schema([
                        Forms\Components\Select::make('book_type')
                            ->required()
                            ->options([
                                'fiction' => 'Fiction',
                                'non-fiction' => 'Non-Fiction',
                                'children' => 'Children\'s Book',
                                'poetry' => 'Poetry',
                                'academic' => 'Academic / Educational',
                                'self-help' => 'Self-Help / Personal Development',
                                'business' => 'Business / Finance',
                                'other' => 'Other',
                            ]),

                        Forms\Components\TextInput::make('genre')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('author_name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('format')
                            ->required()
                            ->options([
                                'paperback' => 'Paperback',
                                'hardcover' => 'Hardcover',
                                'ebook' => 'eBook',
                                'audiobook' => 'Audiobook',
                            ]),

                        Forms\Components\TextInput::make('isbn')
                            ->maxLength(20),

                        Forms\Components\TextInput::make('publisher')
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('publication_date')
                            ->maxDate(now()),

                        Forms\Components\TextInput::make('pages')
                            ->numeric()
                            ->minValue(1),

                        Forms\Components\TextInput::make('age_range')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('series_name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('edition')
                            ->maxLength(100),
                    ])
                    ->columns(3),

                // Pricing and Location
                Forms\Components\Section::make('Pricing and Location')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),

                        Forms\Components\Select::make('currency')
                            ->required()
                            ->options([
                                'USD' => 'USD',
                                'GBP' => 'GBP',
                                'EUR' => 'EUR',
                                'JPY' => 'JPY',
                                'CAD' => 'CAD',
                                'AUD' => 'AUD',
                            ])
                            ->default('USD'),

                        Forms\Components\Select::make('country')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search) => 
                                \App\Models\Country::where('name', 'like', "%{$search}%")
                                    ->orWhere('code', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('name', 'code')
                            ),

                        Forms\Components\TextInput::make('language')
                            ->required()
                            ->maxLength(10)
                            ->default('en'),
                    ])
                    ->columns(2),

                // Author Information
                Forms\Components\Section::make('Author Information')
                    ->schema([
                        Forms\Components\Textarea::make('author_bio')
                            ->maxLength(2000)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('author_photo')
                            ->image()
                            ->directory('books/authors')
                            ->maxSize(2048),

                        Forms\Components\Repeater::make('author_social_links')
                            ->schema([
                                Forms\Components\TextInput::make('url')
                                    ->url()
                                    ->required(),
                            ])
                            ->columnSpanFull(),
                    ]),

                // Media Files
                Forms\Components\Section::make('Media Files')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image')
                            ->required()
                            ->image()
                            ->directory('books/covers')
                            ->maxSize(2048)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('additional_images')
                            ->multiple()
                            ->image()
                            ->directory('books/additional')
                            ->maxSize(2048)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('trailer_video_url')
                            ->url()
                            ->maxLength(500),

                        Forms\Components\FileUpload::make('sample_files')
                            ->multiple()
                            ->directory('books/samples')
                            ->maxSize(10240)
                            ->acceptedFileTypes(['pdf', 'mp3', 'm4a', 'wav', 'epub'])
                            ->columnSpanFull(),
                    ]),

                // Purchase Links
                Forms\Components\Section::make('Purchase Links')
                    ->schema([
                        Forms\Components\Repeater::make('purchase_links')
                            ->schema([
                                Forms\Components\TextInput::make('platform')
                                    ->required()
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('url')
                                    ->url()
                                    ->required(),
                            ])
                            ->columnSpanFull(),
                    ]),

                // Upsell and Status
                Forms\Components\Section::make('Premium Upsell and Status')
                    ->schema([
                        Forms\Components\Select::make('advert_type')
                            ->options([
                                'standard' => 'Standard',
                                'promoted' => 'Promoted',
                                'featured' => 'Featured',
                                'sponsored' => 'Sponsored',
                                'top_category' => 'Top of Category',
                            ])
                            ->default('standard')
                            ->reactive()
                            ->afterStateUpdated(function (string $state, Forms\Set $set) {
                                $set('is_promoted', in_array($state, ['promoted', 'featured', 'sponsored', 'top_category']));
                                $set('is_featured', in_array($state, ['featured', 'sponsored', 'top_category']));
                                $set('is_sponsored', in_array($state, ['sponsored', 'top_category']));
                                $set('is_top_category', $state === 'top_category');
                            }),

                        Forms\Components\Toggle::make('is_promoted'),
                        Forms\Components\Toggle::make('is_featured'),
                        Forms\Components\Toggle::make('is_sponsored'),
                        Forms\Components\Toggle::make('is_top_category'),

                        Forms\Components\TextInput::make('upsell_price')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->default(0.00),

                        Forms\Components\Select::make('status')
                            ->options([
                                'inactive' => 'Inactive',
                                'active' => 'Active',
                                'pending' => 'Pending',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending'),

                        Forms\Components\Toggle::make('verified_author')
                            ->default(false),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->nullable(),
                    ])
                    ->columns(3),

                // Relations
                Forms\Components\Section::make('Relations')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('pricing_plan_id')
                            ->relationship('pricingPlan', 'name')
                            ->searchable()
                            ->nullable(),
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
                    ->defaultImageUrl(url('/placeholder.png')),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('author_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('genre')
                    ->searchable(),

                Tables\Columns\TextColumn::make('format')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paperback' => 'gray',
                        'hardcover' => 'blue',
                        'ebook' => 'green',
                        'audiobook' => 'purple',
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('advert_type')
                    ->color(fn (string $state): string => match ($state) {
                        'standard' => 'gray',
                        'promoted' => 'blue',
                        'featured' => 'yellow',
                        'sponsored' => 'orange',
                        'top_category' => 'red',
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->color(fn (string $state): string => match ($state) {
                        'inactive' => 'gray',
                        'active' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                    }),

                Tables\Columns\IconColumn::make('verified_author')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('saves_count')
                    ->label('Saves')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('book_type')
                    ->options([
                        'fiction' => 'Fiction',
                        'non-fiction' => 'Non-Fiction',
                        'children' => 'Children\'s Book',
                        'poetry' => 'Poetry',
                        'academic' => 'Academic',
                        'self-help' => 'Self-Help',
                        'business' => 'Business',
                        'other' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('format')
                    ->options([
                        'paperback' => 'Paperback',
                        'hardcover' => 'Hardcover',
                        'ebook' => 'eBook',
                        'audiobook' => 'Audiobook',
                    ]),

                Tables\Filters\SelectFilter::make('advert_type')
                    ->options([
                        'standard' => 'Standard',
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        'top_category' => 'Top of Category',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'inactive' => 'Inactive',
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\TernaryFilter::make('verified_author')
                    ->label('Verified Author')
                    ->placeholder('All')
                    ->trueLabel('Verified')
                    ->falseLabel('Not Verified'),
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
            'index' => Pages\ListBookAdverts::route('/'),
            'create' => Pages\CreateBookAdvert::route('/create'),
            'view' => Pages\ViewBookAdvert::route('/{record}'),
            'edit' => Pages\EditBookAdvert::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }
}
