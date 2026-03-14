<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleCategoryResource\Pages;
use App\Models\VehicleCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class VehicleCategoryResource extends Resource
{
    protected static ?string $model = VehicleCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Vehicle Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Vehicle Category';

    protected static ?string $pluralModelLabel = 'Vehicle Categories';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                
                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(VehicleCategory::class, 'slug', ignoreRecord: true),
                
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->nullable()
                    ->columnSpan('full'),
                
                Forms\Components\TextInput::make('icon')
                    ->label('Icon')
                    ->maxLength(255)
                    ->placeholder('heroicon-o-truck')
                    ->helperText('Heroicon name or image path'),
                
                Forms\Components\FileUpload::make('image')
                    ->label('Image')
                    ->image()
                    ->maxSize(1024)
                    ->directory('vehicle-categories')
                    ->nullable(),
                
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                
                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->defaultImageUrl(url('/placeholder.png'))
                    ->square()
                    ->size(60),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('vehicles_count')
                    ->label('Vehicles')
                    ->counts('vehicles')
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
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
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc');
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
            'index' => Pages\ListVehicleCategories::route('/'),
            'create' => Pages\CreateVehicleCategory::route('/create'),
            'edit' => Pages\EditVehicleCategory::route('/{record}/edit'),
        ];
    }
}
