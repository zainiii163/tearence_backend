<?php

namespace App\Filament\Relations;

use App\Models\JobUpsell;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobUpsellsRelationManager extends RelationManager
{
    protected static string $relationship = 'upsells';

    protected static ?string $recordTitleAttribute = 'upsell_type';

    protected static ?string $label = 'Upsell';

    protected static ?string $pluralLabel = 'Upsells';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('upsell_type')
                    ->options([
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        'top_category' => 'Top of Category',
                    ])
                    ->required(),
                
                Forms\Components\DatePicker::make('start_date')
                    ->required(),
                
                Forms\Components\DatePicker::make('end_date')
                    ->required(),
                
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->step(0.01)
                    ->required(),
                
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('active'),
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
                        'promoted' => 'success',
                        'featured' => 'info',
                        'sponsored' => 'warning',
                        'top_category' => 'danger',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('price')
                    ->money('GBP')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'cancelled' => 'warning',
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('upsell_type')
                    ->options([
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        'top_category' => 'Top of Category',
                    ]),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
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
            ]);
    }
}
