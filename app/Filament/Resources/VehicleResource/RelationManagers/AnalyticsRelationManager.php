<?php

namespace App\Filament\Resources\VehicleResource\RelationManagers;

use App\Models\VehicleAnalytic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnalyticsRelationManager extends RelationManager
{
    protected static string $relationship = 'analytics';

    protected static ?string $recordTitleAttribute = 'event_type';

    protected static ?string $label = 'Analytic';

    protected static ?string $pluralLabel = 'Analytics';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('event_type')
                    ->options([
                        'view' => 'View',
                        'save' => 'Save',
                        'enquiry' => 'Enquiry',
                        'click' => 'Click',
                    ])
                    ->required(),
                
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('User'),
                
                Forms\Components\TextInput::make('ip_address')
                    ->label('IP Address')
                    ->nullable(),
                
                Forms\Components\TextInput::make('country')
                    ->label('Country')
                    ->nullable(),
                
                Forms\Components\TextInput::make('city')
                    ->label('City')
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('event_type')
            ->columns([
                Tables\Columns\TextColumn::make('event_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'view' => 'primary',
                        'save' => 'success',
                        'enquiry' => 'warning',
                        'click' => 'info',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('Guest'),
                
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('country')
                    ->label('Country')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('city')
                    ->label('City')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->options([
                        'view' => 'Views',
                        'save' => 'Saves',
                        'enquiry' => 'Enquiries',
                        'click' => 'Clicks',
                    ]),
                
                Tables\Filters\Filter::make('today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->label('Today'),
                
                Tables\Filters\Filter::make('this_week')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                    ->label('This Week'),
                
                Tables\Filters\Filter::make('this_month')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('created_at', now()->month))
                    ->label('This Month'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
