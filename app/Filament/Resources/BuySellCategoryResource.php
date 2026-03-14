<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuySellCategoryResource\Pages;
use App\Models\BuySellCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BuySellCategoryResource extends Resource
{
    protected static ?string $model = BuySellCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Buy & Sell';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Category Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                        
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(100)
                            ->unique(BuySellCategory::class, 'slug', ignoreRecord: true),
                        
                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->rows(3),
                        
                        Forms\Components\TextInput::make('icon')
                            ->maxLength(50)
                            ->placeholder('e.g., 💻, 🚗, 📱'),
                        
                        Forms\Components\TextInput::make('image_url')
                            ->url()
                            ->maxLength(500)
                            ->label('Image URL'),
                    ]),
                
                Forms\Components\Section::make('Hierarchy')
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->placeholder('Select parent category (optional)')
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('level', $state ? 2 : 1)),
                        
                        Forms\Components\TextInput::make('level')
                            ->numeric()
                            ->default(1)
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active'),
                        
                        Forms\Components\TextInput::make('advert_count')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false)
                            ->label('Advert Count'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent Category')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('level')
                    ->badge()
                    ->colors([
                        'primary' => 1,
                        'success' => 2,
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('icon')
                    ->limit(10),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('advert_count')
                    ->label('Adverts')
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->placeholder('All categories')
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('level')
                    ->options([
                        1 => 'Main Categories',
                        2 => 'Subcategories',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
                
                Tables\Filters\Filter::make('has_adverts')
                    ->query(fn ($query) => $query->where('advert_count', '>', 0))
                    ->label('Has Adverts'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                        }),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                        }),
                ]),
            ])
            ->reorderable('sort_order');
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
            'index' => Pages\ListBuySellCategories::route('/'),
            'create' => Pages\CreateBuySellCategory::route('/create'),
            'view' => Pages\ViewBuySellCategory::route('/{record}'),
            'edit' => Pages\EditBuySellCategory::route('/{record}/edit'),
        ];
    }
}
