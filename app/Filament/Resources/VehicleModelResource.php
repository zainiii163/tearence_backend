<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleModelResource\Pages;
use App\Models\VehicleModel;
use App\Models\VehicleMake;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleModelResource extends Resource
{
    protected static ?string $model = VehicleModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Vehicle Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Vehicle Model';

    protected static ?string $pluralModelLabel = 'Vehicle Models';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('make_id')
                    ->label('Make')
                    ->relationship('make', 'name')
                    ->searchable()
                    ->required()
                    ->reactive(),

                Forms\Components\TextInput::make('name')
                    ->label('Model Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->helperText('URL-friendly version of the name'),

                Forms\Components\TextInput::make('year_start')
                    ->label('Year Start')
                    ->numeric()
                    ->nullable()
                    ->rules(['min:1900', 'max:' . (date('Y') + 1)]),

                Forms\Components\TextInput::make('year_end')
                    ->label('Year End')
                    ->numeric()
                    ->nullable()
                    ->rules(['min:1900', 'max:' . (date('Y') + 5)]),

                Forms\Components\Select::make('category')
                    ->label('Category')
                    ->options([
                        'sedan' => 'Sedan',
                        'suv' => 'SUV',
                        'truck' => 'Truck',
                        'van' => 'Van',
                        'coupe' => 'Coupe',
                        'convertible' => 'Convertible',
                        'hatchback' => 'Hatchback',
                        'wagon' => 'Wagon',
                        'sports' => 'Sports Car',
                        'luxury' => 'Luxury',
                        'hybrid' => 'Hybrid',
                        'electric' => 'Electric',
                        'commercial' => 'Commercial',
                    ])
                    ->nullable(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('make.name')
                    ->label('Make')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Model Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('year_range')
                    ->label('Year Range')
                    ->getStateUsing(function ($record) {
                        if ($record->year_start && $record->year_end) {
                            return $record->year_start . ' - ' . $record->year_end;
                        } elseif ($record->year_start) {
                            return $record->year_start . ' - Present';
                        }
                        return null;
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('make_id')
                    ->label('Make')
                    ->relationship('make', 'name')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Category')
                    ->options([
                        'sedan' => 'Sedan',
                        'suv' => 'SUV',
                        'truck' => 'Truck',
                        'van' => 'Van',
                        'coupe' => 'Coupe',
                        'convertible' => 'Convertible',
                        'hatchback' => 'Hatchback',
                        'wagon' => 'Wagon',
                        'sports' => 'Sports Car',
                        'luxury' => 'Luxury',
                        'hybrid' => 'Hybrid',
                        'electric' => 'Electric',
                        'commercial' => 'Commercial',
                    ]),

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
            'index' => Pages\ListVehicleModels::route('/'),
            'create' => Pages\CreateVehicleModel::route('/create'),
            'edit' => Pages\EditVehicleModel::route('/{record}/edit'),
        ];
    }
}
