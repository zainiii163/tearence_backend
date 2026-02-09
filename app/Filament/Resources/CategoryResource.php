<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('icon')
                    ->image()
                    ->directory('categories'),
                Forms\Components\TextInput::make('icon_color')
                    ->label('Icon Color')
                    ->placeholder('#000000')
                    ->helperText('Hex color code for category icon'),
                Forms\Components\TextInput::make('page_title')
                    ->label('Page Title')
                    ->maxLength(255)
                    ->helperText('Custom page title for this category'),
                Forms\Components\Textarea::make('page_meta_description')
                    ->label('Page Meta Description')
                    ->maxLength(500)
                    ->rows(3)
                    ->helperText('SEO meta description for category page'),
                Forms\Components\KeyValue::make('filter_config')
                    ->label('Filter Configuration')
                    ->helperText('Category-specific filter settings (JSON format)')
                    ->keyLabel('Filter Key')
                    ->valueLabel('Filter Value')
                    ->columnSpanFull(),
                Forms\Components\Section::make('Posting Form Configuration')
                    ->description('Configure the form fields for listings in this category')
                    ->schema([
                        Forms\Components\Textarea::make('posting_form_config')
                            ->label('Form Fields Configuration')
                            ->helperText('JSON configuration for form fields')
                            ->rows(8)
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->collapsible(),
                Forms\Components\Select::make('parent_id')
                    ->label('Parent Category')
                    ->options(function ($record) {
                        $query = Category::whereNull('parent_id')->orderBy('name', 'ASC');
                        
                        // When editing, exclude the current category to prevent self-referencing
                        if ($record) {
                            $query->where('category_id', '!=', $record->category_id);
                        }
                        
                        return $query->pluck('name', 'category_id');
                    })
                    ->searchable()
                    ->placeholder('Select an option')
                    ->nullable(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent Category'),
                Tables\Columns\ImageColumn::make('icon'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
} 