<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VerificationRelationManager extends RelationManager
{
    protected static string $relationship = 'verification';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Section::make('Verification Information')
                    ->schema([
                        Forms\Components\Select::make('verification_status')
                            ->options([
                                'pending' => 'Pending',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('identity_document_url')
                            ->label('Identity Document URL')
                            ->url()
                            ->maxLength(500),

                        Forms\Components\KeyValue::make('social_links')
                            ->label('Social Media Links')
                            ->columnSpanFull(),

                        Forms\Components\KeyValue::make('verification_data')
                            ->label('Verification Data')
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label('Verification Date'),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Verification ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('verification_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'verified' => 'success',
                        'rejected' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('identity_document_url')
                    ->label('Identity Document')
                    ->formatStateUsing(fn ($state) => $state ? 'Uploaded' : 'Not uploaded')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('verification_status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update([
                        'verification_status' => 'verified',
                        'verified_at' => now(),
                    ]))
                    ->visible(fn ($record) => $record->verification_status === 'pending'),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update([
                        'verification_status' => 'rejected',
                    ]))
                    ->visible(fn ($record) => $record->verification_status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('verify')
                        ->label('Verify Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each(function ($record) {
                            $record->update([
                                'verification_status' => 'verified',
                                'verified_at' => now(),
                            ]);
                        }))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
