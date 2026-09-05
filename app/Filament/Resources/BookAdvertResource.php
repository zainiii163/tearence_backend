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
use App\Filament\Forms\Components\CountrySelect;

class BookAdvertResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Books & Courses';

    protected static ?string $modelLabel = 'Publication';

    protected static ?string $pluralModelLabel = 'Books & Courses';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('content_kind')
                            ->label('Content type')
                            ->options(Book::CONTENT_KINDS)
                            ->default('book')
                            ->required()
                            ->helperText('Books, courses, guides, or manuals.'),

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
                            ->minLength(50)
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

                        Forms\Components\Select::make('genre')
                            ->required()
                            ->searchable()
                            ->options([
                                'Fiction' => 'Fiction',
                                'Non-Fiction' => 'Non-Fiction',
                                'Romance' => 'Romance',
                                'Mystery' => 'Mystery',
                                'Sci-Fi' => 'Sci-Fi',
                                'Biography' => 'Biography',
                                'History' => 'History',
                                'Self-Help' => 'Self-Help',
                                'Business' => 'Business',
                                'Programming' => 'Programming',
                                'Fantasy' => 'Fantasy',
                                'Thriller' => 'Thriller',
                                'Education' => 'Education',
                                'Textbook' => 'Textbook',
                                'Children' => 'Children',
                            ]),

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
                        Forms\Components\Toggle::make('is_free')
                            ->label('Free download / free listing')
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $state ? $set('price', 0) : null),

                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->default(0)
                            ->disabled(fn (Forms\Get $get) => (bool) $get('is_free'))
                            ->dehydrated(),

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

                        CountrySelect::makeIso('country')
                            ->required(),

                        Forms\Components\Select::make('language')
                            ->required()
                            ->options([
                                'English' => 'English',
                                'Spanish' => 'Spanish',
                                'French' => 'French',
                                'German' => 'German',
                                'Arabic' => 'Arabic',
                                'Chinese' => 'Chinese',
                                'Japanese' => 'Japanese',
                            ])
                            ->default('English'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Media & files')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->disk('public')
                            ->directory('books/covers')
                            ->maxSize(2048)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('digital_file')
                            ->label('Full digital file (PDF / EPUB / ZIP)')
                            ->disk('public')
                            ->directory('books/digital')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/epub+zip',
                                'application/zip',
                                'audio/mpeg',
                                'audio/mp4',
                            ])
                            ->maxSize(51200)
                            ->downloadable()
                            ->openable()
                            ->helperText('Buyers receive this file after purchase (or immediately if free). Samples can still be uploaded via the public post form.')
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
                                'active' => 'Active (published)',
                                'pending' => 'Pending publication',
                                'rejected' => 'Rejected',
                            ])
                            ->default('active')
                            ->required()
                            ->helperText('User submissions arrive as Pending — set Active to publish on the site.'),

                        Forms\Components\Toggle::make('verified_author')
                            ->default(false),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin notes')
                            ->rows(2)
                            ->columnSpanFull(),
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

                Tables\Columns\BadgeColumn::make('content_kind')
                    ->label('Kind')
                    ->formatStateUsing(fn (?string $state): string => Book::CONTENT_KINDS[$state] ?? ucfirst((string) $state))
                    ->colors([
                        'primary' => 'book',
                        'info' => 'course',
                        'success' => 'guide',
                        'warning' => 'manual',
                    ]),

                Tables\Columns\TextColumn::make('author_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('genre')
                    ->searchable(),

                Tables\Columns\TextColumn::make('format')
                    ->badge(),

                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(fn ($state, Book $record): string => ($record->is_free || (float) $state <= 0)
                        ? 'Free'
                        : (($record->currency ?? 'USD').' '.number_format((float) $state, 2)))
                    ->sortable(),

                Tables\Columns\IconColumn::make('digital_file')
                    ->label('File')
                    ->boolean()
                    ->getStateUsing(fn (Book $record): bool => filled($record->digital_file)),

                Tables\Columns\BadgeColumn::make('advert_type')
                    ->color(fn (?string $state): string => match ($state) {
                        'standard' => 'gray',
                        'promoted' => 'blue',
                        'featured' => 'yellow',
                        'sponsored' => 'orange',
                        'top_category' => 'purple',
                        default => 'gray',
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->color(fn (?string $state): string => match ($state) {
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
                        'pending' => 'Pending publication',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('content_kind')
                    ->label('Content type')
                    ->options(Book::CONTENT_KINDS),
                Tables\Filters\TernaryFilter::make('is_free')
                    ->label('Free'),
                Tables\Filters\TernaryFilter::make('has_digital_file')
                    ->label('Has digital file')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('digital_file')->where('digital_file', '!=', ''),
                        false: fn ($query) => $query->where(fn ($q) => $q->whereNull('digital_file')->orWhere('digital_file', '')),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->visible(fn (Book $record) => filled($record->digital_file)
                        || (is_array($record->sample_files) && count($record->sample_files) > 0)
                        || filled($record->cover_image))
                    ->action(function (Book $record) {
                        $disk = \Illuminate\Support\Facades\Storage::disk('public');
                        if (filled($record->digital_file) && $disk->exists($record->digital_file)) {
                            return response()->download($disk->path($record->digital_file), basename($record->digital_file));
                        }
                        $samples = is_array($record->sample_files) ? $record->sample_files : [];
                        if (! empty($samples)) {
                            $first = $samples[0];
                            $path = is_array($first) ? ($first['path'] ?? null) : $first;
                            if ($path && $disk->exists($path)) {
                                $name = is_array($first) ? ($first['name'] ?? basename($path)) : basename($path);

                                return response()->download($disk->path($path), $name);
                            }
                        }
                        if (filled($record->cover_image) && $disk->exists($record->cover_image)) {
                            return response()->download($disk->path($record->cover_image), basename($record->cover_image));
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('No downloadable file found')
                            ->warning()
                            ->send();
                    }),
                Tables\Actions\Action::make('approve')
                    ->label('Publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Book $record) => $record->status === 'pending')
                    ->action(fn (Book $record) => $record->update(['status' => 'active'])),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Book $record) => in_array($record->status, ['pending', 'active'], true))
                    ->requiresConfirmation()
                    ->action(fn (Book $record) => $record->update(['status' => 'rejected'])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Publish selected')
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
