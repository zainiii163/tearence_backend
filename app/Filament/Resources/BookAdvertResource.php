<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookAdvertResource\Pages;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BookAdvertResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Books Adverts';

    protected static ?string $modelLabel = 'Book';

    protected static ?string $pluralModelLabel = 'Books';

    protected static ?string $navigationGroup = 'Books Marketplace';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                            ->unique(Book::class, 'slug', ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('short_description')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

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
                    ])
                    ->columns(3),

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

                        Forms\Components\TextInput::make('country')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('language')
                            ->required()
                            ->maxLength(10)
                            ->default('English'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->disk('public')
                            ->directory('books/covers')
                            ->maxSize(2048)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('trailer_video_url')
                            ->url()
                            ->maxLength(500),
                    ]),

                Forms\Components\Section::make('Status & Promotion')
                    ->schema([
                        Forms\Components\Select::make('advert_type')
                            ->options([
                                'standard' => 'Standard',
                                'promoted' => 'Promoted',
                                'featured' => 'Featured',
                                'sponsored' => 'Sponsored',
                                'top_category' => 'Top of Category',
                            ])
                            ->default('standard'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'inactive' => 'Inactive',
                                'active' => 'Active',
                                'pending' => 'Pending',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\Toggle::make('verified_author')
                            ->default(false),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Owner')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'first_name')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\User::where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function ($user) {
                                        $fullName = trim($user->first_name . ' ' . $user->last_name);
                                        return [$user->user_id => $fullName];
                                    });
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $user = \App\Models\User::find($value);
                                if (!$user) return null;
                                return trim($user->first_name . ' ' . $user->last_name);
                            })
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->disk('public')
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
                    ->badge(),

                Tables\Columns\TextColumn::make('price')
                    ->money(fn ($record) => $record->currency ?? 'USD')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('advert_type')
                    ->color(fn (string $state): string => match ($state) {
                        'standard' => 'gray',
                        'promoted' => 'blue',
                        'featured' => 'yellow',
                        'sponsored' => 'orange',
                        'top_category' => 'purple',
                        default => 'gray',
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->color(fn (string $state): string => match ($state) {
                        'inactive' => 'gray',
                        'active' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('verified_author')
                    ->boolean(),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'inactive' => 'Inactive',
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Book $record) => $record->status === 'pending')
                    ->action(fn (Book $record) => $record->update(['status' => 'active'])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['status' => 'active'])),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
