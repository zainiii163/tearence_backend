<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CandidateUpsellResource\Pages;
use App\Models\CandidateUpsell;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class CandidateUpsellResource extends Resource
{
    protected static ?string $model = CandidateUpsell::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Monetization';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Candidate Upsells';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Upsell Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('candidate_profile_id')
                            ->label('Candidate Profile')
                            ->relationship('candidateProfile', 'headline')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('upsell_type')
                            ->label('Upsell Type')
                            ->options([
                                'featured_profile' => 'Featured Profile',
                                'job_alerts_boost' => 'Job Alerts Boost',
                            ])
                            ->required()
                            ->reactive(),
                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->default(0),
                        Forms\Components\TextInput::make('duration_days')
                            ->label('Duration (Days)')
                            ->numeric()
                            ->required()
                            ->default(30)
                            ->helperText('Number of days the upsell will be active'),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->default('pending'),
                        Forms\Components\TextInput::make('payment_transaction_id')
                            ->label('Payment Transaction ID')
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Starts At'),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->helperText('Leave empty to calculate based on duration_days'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('candidateProfile.headline')
                    ->label('Candidate Profile')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('candidateProfile.customer.name')
                    ->label('Candidate')
                    ->searchable(),
                TextColumn::make('upsell_type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'featured_profile' => 'success',
                        'job_alerts_boost' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('duration_days')
                    ->label('Duration (Days)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'expired' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Expires')
                    ->color(fn($record) => $record->expires_at && $record->expires_at->isPast() ? 'danger' : null),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('upsell_type')
                    ->options([
                        'featured_profile' => 'Featured Profile',
                        'job_alerts_boost' => 'Job Alerts Boost',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (CandidateUpsell $record) => $record->activate())
                    ->visible(fn (CandidateUpsell $record) => $record->status !== 'active'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCandidateUpsells::route('/'),
            'create' => Pages\CreateCandidateUpsell::route('/create'),
            'edit' => Pages\EditCandidateUpsell::route('/{record}/edit'),
        ];
    }
}

