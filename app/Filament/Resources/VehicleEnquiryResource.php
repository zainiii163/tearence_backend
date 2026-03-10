<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleEnquiryResource\Pages;
use App\Models\VehicleEnquiry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VehicleEnquiryResource extends Resource
{
    protected static ?string $model = VehicleEnquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Vehicle Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Vehicle Enquiry';

    protected static ?string $pluralModelLabel = 'Vehicle Enquiries';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('vehicle_id')
                    ->label('Vehicle')
                    ->relationship('vehicle', 'title')
                    ->searchable()
                    ->required()
                    ->disabled(),
                
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->nullable()
                    ->disabled(),
                
                Forms\Components\TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(100)
                    ->disabled(),
                
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(100)
                    ->disabled(),
                
                Forms\Components\TextInput::make('phone')
                    ->label('Phone')
                    ->maxLength(50)
                    ->disabled(),
                
                Forms\Components\Textarea::make('message')
                    ->label('Message')
                    ->required()
                    ->columnSpan('full')
                    ->rows(4)
                    ->disabled(),
                
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'replied' => 'Replied',
                        'closed' => 'Closed'
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.title')
                    ->label('Vehicle')
                    ->searchable()
                    ->limit(50)
                    ->wrap()
                    ->url(fn ($record) => route('filament.admin.resources.vehicles.edit', $record->vehicle_id)),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->limit(100)
                    ->wrap()
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'replied',
                        'gray' => 'closed',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->limit(20)
                    ->placeholder('Guest')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('M j, Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'replied' => 'Replied',
                        'closed' => 'Closed',
                    ]),
                
                Tables\Filters\Filter::make('recent')
                    ->label('Recent (7 days)')
                    ->query(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(7))),
                
                Tables\Filters\Filter::make('unread')
                    ->label('Pending')
                    ->query(fn (Builder $query) => $query->where('status', 'pending')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('mark_replied')
                    ->label('Mark as Replied')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->markAsReplied();
                    }),
                
                Tables\Actions\Action::make('mark_closed')
                    ->label('Mark as Closed')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->visible(fn ($record) => $record->status !== 'closed')
                    ->action(function ($record) {
                        $record->markAsClosed();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('mark_replied_bulk')
                        ->label('Mark as Replied')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->markAsReplied();
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('mark_closed_bulk')
                        ->label('Mark as Closed')
                        ->icon('heroicon-o-x-mark')
                        ->color('gray')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status !== 'closed') {
                                    $record->markAsClosed();
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListVehicleEnquiries::route('/'),
            'view' => Pages\ViewVehicleEnquiry::route('/{record}'),
            'edit' => Pages\EditVehicleEnquiry::route('/{record}/edit'),
        ];
    }
}
