<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyCategoryResource\Pages;
use App\Models\PropertyCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PropertyCategoryResource extends Resource
{
    protected static ?string $model = PropertyCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Property Categories';

    protected static ?string $modelLabel = 'Property Category';

    protected static ?string $pluralModelLabel = 'Property Categories';

    protected static ?string $navigationGroup = 'Property Hub';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('icon')
                    ->maxLength(255)
                    ->placeholder('heroicon-o-home'),
                Forms\Components\Select::make('type')
                    ->options([
                        'residential' => 'Residential',
                        'commercial' => 'Commercial',
                        'industrial' => 'Industrial',
                        'land' => 'Land',
                        'agricultural' => 'Agricultural',
                        'luxury' => 'Luxury',
                        'investment' => 'Investment',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('active')
                    ->default(true),
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'residential' => 'primary',
                        'commercial' => 'success',
                        'industrial' => 'warning',
                        'land' => 'info',
                        'agricultural' => 'gray',
                        'luxury' => 'purple',
                        'investment' => 'orange',
                    })
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
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
                    ->sortable
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'residential' => 'Residential',
                        'commercial' => 'Commercial',
                        'industrial' => 'Industrial',
                        'land' => 'Land',
                        'agricultural' => 'Agricultural',
                        'luxury' => 'Luxury',
                        'investment' => 'Investment',
                    ]),
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Active')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
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
            'index' => Pages\ListPropertyCategories::route('/'),
            'create' => Pages\CreatePropertyCategory::route('/create'),
            'view' => Pages\ViewPropertyCategory::route('/{record}'),
            'edit' => Pages\EditPropertyCategory::route('/{record}/edit'),
        ];
    }
}
