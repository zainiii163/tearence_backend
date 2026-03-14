<?php

namespace App\Filament\Resources\JobResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UpsellsRelationManager extends RelationManager
{
    protected static string $relationship = 'upsells';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('upsell_type')
                    ->required()
                    ->options([
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        'network' => 'Network-Wide Boost',
                    ]),
                
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                
                Forms\Components\Select::make('currency')
                    ->default('USD')
                    ->options([
                        'USD' => 'USD',
                        'EUR' => 'EUR',
                        'GBP' => 'GBP',
                        'CAD' => 'CAD',
                        'AUD' => 'AUD',
                    ]),
                
                Forms\Components\TextInput::make('duration_months')
                    ->required()
                    ->numeric()
                    ->default(1),
                
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                    ]),
                
                Forms\Components\Select::make('payment_status')
                    ->required()
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                
                Forms\Components\DateTimePicker::make('activated_at'),
                
                Forms\Components\DateTimePicker::make('expires_at'),
                
                Forms\Components\Textarea::make('payment_notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('upsell_type')
            ->columns([
                Tables\Columns\TextColumn::make('upsell_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'promoted' => 'info',
                        'featured' => 'warning',
                        'sponsored' => 'success',
                        'network' => 'primary',
                    }),
                
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('duration_months')
                    ->numeric()
                    ->suffix(' months')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'cancelled' => 'danger',
                        'expired' => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'info',
                    }),
                
                Tables\Columns\TextColumn::make('activated_at')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created At'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('upsell_type')
                    ->options([
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        'network' => 'Network-Wide Boost',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                    ]),
                
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
            ->defaultSort('created_at', 'desc');
    }
}
