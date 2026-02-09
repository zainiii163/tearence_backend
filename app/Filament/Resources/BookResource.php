<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Models\BookPurchase;
use App\Models\Listing;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class BookResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Books';

    protected static ?string $modelLabel = 'Book';

    protected static ?string $pluralModelLabel = 'Books';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        $booksCategory = Category::where('name', 'Books')->first();
        
        return $form
            ->schema([
                Forms\Components\Section::make('Book Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('author')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('isbn')
                                    ->maxLength(20),
                                
                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->step(0.01)
                                    ->default(0),
                            ]),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('book_type')
                                    ->options([
                                        'physical' => 'Physical Book',
                                        'pdf' => 'PDF Download',
                                        'audiobook' => 'Audiobook',
                                    ])
                                    ->required(),
                                
                                Forms\Components\Select::make('genre')
                                    ->options([
                                        'action' => 'Action',
                                        'education' => 'Education',
                                        'drama' => 'Drama',
                                        'thriller' => 'Thriller',
                                        'fiction' => 'Fiction',
                                        'non_fiction' => 'Non-Fiction',
                                        'textbook' => 'Textbook',
                                        'romance' => 'Romance',
                                        'mystery' => 'Mystery',
                                        'scifi' => 'Sci-Fi',
                                        'fantasy' => 'Fantasy',
                                        'biography' => 'Biography',
                                        'self_help' => 'Self-Help',
                                        'business' => 'Business',
                                        'children' => 'Children',
                                    ])
                                    ->required(),
                                
                                Forms\Components\Select::make('format')
                                    ->options([
                                        'physical' => 'Physical Book',
                                        'e_book' => 'E-book',
                                        'audiobook' => 'Audiobook',
                                    ])
                                    ->required(),
                            ]),
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('condition')
                                    ->options([
                                        'new' => 'New',
                                        'like_new' => 'Like New',
                                        'good' => 'Good',
                                        'fair' => 'Fair',
                                    ]),
                                
                                Forms\Components\Toggle::make('is_downloadable')
                                    ->label('Allow download after purchase'),
                            ]),
                        
                        Forms\Components\TextInput::make('website_url')
                            ->url()
                            ->label('External Website URL')
                            ->columnSpanFull(),
                        
                        Forms\Components\FileUpload::make('file')
                            ->label('Book File (PDF/Audio)')
                            ->acceptedFileTypes(['application/pdf', 'audio/mpeg', 'audio/mp3', 'audio/wav'])
                            ->maxSize(51200) // 50MB
                            ->directory('books')
                            ->visible(fn (callable $get) => $get('is_downloadable'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Images')
                    ->schema([
                        Forms\Components\FileUpload::make('attachments')
                            ->label('Book Cover/Images')
                            ->image()
                            ->multiple()
                            ->directory('listings')
                            ->maxFiles(5)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                
                Forms\Components\Section::make('Listing Details')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->required(),
                        
                        Forms\Components\Select::make('location_id')
                            ->relationship('location', 'name')
                            ->searchable(),
                        
                        Forms\Components\Toggle::make('status')
                            ->label('Active')
                            ->default(true),
                        
                        Forms\Components\Select::make('approval_status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $booksCategory = Category::where('name', 'Books')->first();
                if ($booksCategory) {
                    $query->where('category_id', $booksCategory->category_id);
                }
            })
            ->columns([
                TextColumn::make('listing_id')
                    ->label('ID')
                    ->sortable(),
                
                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                
                TextColumn::make('author')
                    ->searchable()
                    ->limit(30),
                
                BadgeColumn::make('genre')
                    ->colors([
                        'primary' => 'fiction',
                        'success' => 'education',
                        'warning' => 'business',
                        'danger' => 'thriller',
                    ]),
                
                BadgeColumn::make('book_type')
                    ->colors([
                        'primary' => 'physical',
                        'success' => 'pdf',
                        'warning' => 'audiobook',
                    ]),
                
                TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                
                IconColumn::make('is_downloadable')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                
                BadgeColumn::make('approval_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                
                IconColumn::make('status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Active'),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('genre')
                    ->options([
                        'action' => 'Action',
                        'education' => 'Education',
                        'drama' => 'Drama',
                        'thriller' => 'Thriller',
                        'fiction' => 'Fiction',
                        'non_fiction' => 'Non-Fiction',
                        'textbook' => 'Textbook',
                        'romance' => 'Romance',
                        'mystery' => 'Mystery',
                        'scifi' => 'Sci-Fi',
                        'fantasy' => 'Fantasy',
                        'biography' => 'Biography',
                        'self_help' => 'Self-Help',
                        'business' => 'Business',
                        'children' => 'Children',
                    ]),
                
                Tables\Filters\SelectFilter::make('book_type')
                    ->options([
                        'physical' => 'Physical Book',
                        'pdf' => 'PDF Download',
                        'audiobook' => 'Audiobook',
                    ]),
                
                Tables\Filters\SelectFilter::make('approval_status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_downloadable')
                    ->label('Downloadable'),
                
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Active'),
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
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'view' => Pages\ViewBook::route('/{record}'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
            'purchases' => Pages\BookPurchases::route('/purchases'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $booksCategory = Category::where('name', 'Books')->first();
        
        return parent::getEloquentQuery()
            ->when($booksCategory, function ($query) use ($booksCategory) {
                $query->where('category_id', $booksCategory->category_id);
            });
    }
}
