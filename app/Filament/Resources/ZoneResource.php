<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ZoneResource\Pages;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;

    protected static ?string $navigationLabel = 'Cities / States (Zones)';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 23;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Zone (City / State / Province)')
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->label('Country')
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('code')
                            ->maxLength(10)
                            ->helperText('Optional short code (e.g. NY, ON, LDN)'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->required(),
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Display order within country'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        $hasActive = Schema::hasTable('zone') && Schema::hasColumn('zone', 'is_active');
        $hasSortOrder = Schema::hasTable('zone') && Schema::hasColumn('zone', 'sort_order');

        $columns = [
            Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->sortable()
                ->description(fn (Zone $record) => $record->country?->name ?? '—'),
            Tables\Columns\TextColumn::make('country.name')
                ->label('Country')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('code')
                ->searchable()
                ->sortable()
                ->badge(),
        ];

        if ($hasActive) {
            $columns[] = Tables\Columns\IconColumn::make('is_active')->boolean();
        }

        if ($hasSortOrder) {
            $columns[] = Tables\Columns\TextColumn::make('sort_order')
                ->numeric()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true);
        }

        $columns[] = Tables\Columns\TextColumn::make('created_at')
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        $filters = [
            Tables\Filters\SelectFilter::make('country_id')
                ->relationship('country', 'name')
                ->label('Country'),
        ];

        if ($hasActive) {
            $filters[] = Tables\Filters\TernaryFilter::make('is_active');
        }

        return $table
            ->defaultSort('name')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('country'))
            ->columns($columns)
            ->filters($filters)
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListZones::route('/'),
            'create' => Pages\CreateZone::route('/create'),
            'edit' => Pages\EditZone::route('/{record}/edit'),
        ];
    }
}