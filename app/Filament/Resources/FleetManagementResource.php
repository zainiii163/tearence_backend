<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FleetManagementResource\Pages;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Super-admin fleet board — operational status for all vehicles.
 */
class FleetManagementResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $slug = 'fleet-management';

    protected static ?string $navigationLabel = 'Fleet Management';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Vehicle Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Fleet vehicle';

    protected static ?string $pluralModelLabel = 'Fleet Management';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Fleet status')
                    ->schema([
                        Forms\Components\Placeholder::make('title_display')
                            ->label('Vehicle')
                            ->content(fn (?Vehicle $record): string => $record?->title ?? '—'),
                        Forms\Components\Select::make('fleet_status')
                            ->label('Fleet status')
                            ->options(self::fleetStatusOptions())
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('registration_number')
                            ->label('Registration')
                            ->maxLength(50)
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('')
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('title')
                    ->label('Vehicle')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->description(fn (Vehicle $record): string => trim(implode(' · ', array_filter([
                        $record->registration_number,
                        $record->year,
                    ])))),
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Owner')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('business.business_name')
                    ->label('Business')
                    ->placeholder('—')
                    ->toggleable()
                    ->limit(24),
                Tables\Columns\TextColumn::make('advert_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => ucfirst(str_replace('_', ' ', (string) $state))),
                Tables\Columns\TextColumn::make('city')
                    ->label('Location')
                    ->formatStateUsing(fn ($state, Vehicle $record): string => trim(implode(', ', array_filter([
                        $record->city,
                        $record->country,
                    ]))) ?: '—')
                    ->toggleable(),
                Tables\Columns\SelectColumn::make('fleet_status')
                    ->label('Fleet status')
                    ->options(self::fleetStatusOptions())
                    ->selectablePlaceholder(false)
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Listing')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst((string) $state)),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Live')
                    ->boolean()
                    ->toggleable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('fleet_status')
                    ->label('Fleet status')
                    ->options(self::fleetStatusOptions()),
                Tables\Filters\SelectFilter::make('advert_type')
                    ->label('Advert type')
                    ->options([
                        'sale' => 'Sale',
                        'hire' => 'Hire',
                        'lease' => 'Lease',
                        'transport_service' => 'Transport service',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Listing status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Live on site'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Status')
                    ->modalHeading('Update fleet status'),
                Tables\Actions\Action::make('editListing')
                    ->label('Full edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Vehicle $record): string => VehicleResource::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('markAvailable')
                        ->label('Mark available')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['fleet_status' => 'available']))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('markMaintenance')
                        ->label('Mark maintenance')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['fleet_status' => 'maintenance']))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('markInService')
                        ->label('Mark in service')
                        ->icon('heroicon-o-clock')
                        ->color('info')
                        ->action(fn ($records) => $records->each->update(['fleet_status' => 'in_service']))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading('No vehicles in the fleet')
            ->emptyStateDescription('Vehicle listings will appear here once posted.')
            ->emptyStateIcon('heroicon-o-truck');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFleetManagement::route('/'),
            'edit' => Pages\EditFleetManagement::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user', 'business', 'make', 'vehicleModel']);
    }

    public static function fleetStatusOptions(): array
    {
        return [
            'available' => 'Available',
            'in_service' => 'In service / on hire',
            'maintenance' => 'Maintenance',
            'sold' => 'Sold',
        ];
    }
}
