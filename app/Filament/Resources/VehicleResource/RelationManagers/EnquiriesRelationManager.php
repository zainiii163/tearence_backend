<?php

namespace App\Filament\Resources\VehicleResource\RelationManagers;

use App\Models\VehicleEnquiry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EnquiriesRelationManager extends RelationManager
{
    protected static string $relationship = 'enquiries';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $label = 'Enquiry';

    protected static ?string $pluralLabel = 'Enquiries';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('User'),
                
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(20),
                
                Forms\Components\Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'read' => 'Read',
                        'replied' => 'Replied',
                        'closed' => 'Closed',
                    ])
                    ->default('pending'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'read' => 'info',
                        'replied' => 'success',
                        'closed' => 'gray',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'read' => 'Read',
                        'replied' => 'Replied',
                        'closed' => 'Closed',
                    ]),
                
                Tables\Filters\Filter::make('unread')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'pending'))
                    ->label('Unread'),
                
                Tables\Filters\Filter::make('today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->label('Today'),
                
                Tables\Filters\Filter::make('this_week')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->label('This Week'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('mark_as_read')
                    ->label('Mark as Read')
                    ->icon('heroicon-o-envelope-open')
                    ->color('info')
                    ->action(function (VehicleEnquiry $record) {
                        $record->update(['status' => 'read']);
                    })
                    ->visible(fn (VehicleEnquiry $record): bool => $record->status === 'pending'),
                
                Tables\Actions\Action::make('mark_as_replied')
                    ->label('Mark as Replied')
                    ->icon('heroicon-o-reply')
                    ->color('success')
                    ->action(function (VehicleEnquiry $record) {
                        $record->update(['status' => 'replied']);
                    })
                    ->visible(fn (VehicleEnquiry $record): bool => in_array($record->status, ['pending', 'read'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                
                Tables\Actions\BulkAction::make('mark_as_read')
                    ->label('Mark as Read')
                    ->icon('heroicon-o-envelope-open')
                    ->color('info')
                    ->action(function (Collection $records) {
                        $records->each(function (VehicleEnquiry $record) {
                            if ($record->status === 'pending') {
                                $record->update(['status' => 'read']);
                            }
                        });
                    })
                    ->deselectRecordsAfterCompletion(),
                
                Tables\Actions\BulkAction::make('mark_as_replied')
                    ->label('Mark as Replied')
                    ->icon('heroicon-o-reply')
                    ->color('success')
                    ->action(function (Collection $records) {
                        $records->each(function (VehicleEnquiry $record) {
                            if (in_array($record->status, ['pending', 'read'])) {
                                $record->update(['status' => 'replied']);
                            }
                        });
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
