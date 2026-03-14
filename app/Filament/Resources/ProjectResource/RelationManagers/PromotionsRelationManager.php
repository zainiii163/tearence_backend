<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotionsRelationManager extends RelationManager
{
    protected static string $relationship = 'promotions';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Section::make('Promotion Information')
                    ->schema([
                        Forms\Components\Select::make('plan_id')
                            ->relationship('promotionPlan', 'name')
                            ->required()
                            ->label('Promotion Plan'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),

                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Start Date')
                            ->required(),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('End Date'),

                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Amount Paid ($)')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),

                        Forms\Components\TextInput::make('payment_id')
                            ->label('Payment ID')
                            ->maxLength(255),
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
                    ->label('Promotion ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('promotionPlan.name')
                    ->label('Plan')
                    ->badge()
                    ->color(fn ($record) => $record->promotionPlan->badge_color ?? 'gray'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                    }),

                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_paid')
                    ->money()
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_id')
                    ->label('Payment ID')
                    ->limit(20),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('plan_id')
                    ->relationship('promotionPlan', 'name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->activate())
                    ->visible(fn ($record) => $record->status === 'pending'),

                Tables\Actions\Action::make('expire')
                    ->label('Expire')
                    ->icon('heroicon-o-stop-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->expire())
                    ->visible(fn ($record) => $record->status === 'active'),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->cancel())
                    ->visible(fn ($record) => in_array($record->status, ['pending', 'active'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-play-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->activate())
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
