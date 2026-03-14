<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'rewards';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Section::make('Reward Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->rows(3),

                        Forms\Components\TextInput::make('minimum_contribution')
                            ->label('Minimum Contribution ($)')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->step(0.01),

                        Forms\Components\TextInput::make('limit_quantity')
                            ->label('Quantity Limit (optional)')
                            ->numeric()
                            ->helperText('Leave blank for unlimited'),

                        Forms\Components\DatePicker::make('estimated_delivery')
                            ->label('Estimated Delivery Date')
                            ->helperText('When backers will receive this reward'),

                        Forms\Components\Checkbox::make('includes_shipping')
                            ->label('Includes Shipping'),

                        Forms\Components\TextInput::make('shipping_cost')
                            ->label('Shipping Cost ($)')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->step(0.01),

                        Forms\Components\TextInput::make('order_index')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('minimum_contribution')
                    ->money()
                    ->sortable()
                    ->label('Min. Contribution'),

                Tables\Columns\TextColumn::make('limit_quantity')
                    ->label('Quantity Limit')
                    ->formatStateUsing(fn ($state) => $state ?? 'Unlimited'),

                Tables\Columns\TextColumn::make('estimated_delivery')
                    ->date()
                    ->sortable(),

                Tables\Columns\IconColumn::make('includes_shipping')
                    ->boolean(),

                Tables\Columns\TextColumn::make('shipping_cost')
                    ->money()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order_index')
                    ->label('Order')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_shipping')
                    ->query(fn (Builder $query): Builder => $query->where('includes_shipping', true))
                    ->label('Includes Shipping Only'),

                Tables\Filters\Filter::make('limited_quantity')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('limit_quantity'))
                    ->label('Limited Quantity Only'),
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
            ->defaultSort('order_index');
    }
}
