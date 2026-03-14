<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FundingDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'fundingDetail';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Section::make('Funding Details')
                    ->schema([
                        Forms\Components\KeyValue::make('use_of_funds')
                            ->label('Use of Funds')
                            ->columnSpanFull()
                            ->helperText('Specify how the funds will be used'),

                        Forms\Components\KeyValue::make('milestones')
                            ->label('Milestones')
                            ->columnSpanFull()
                            ->helperText('Define project milestones and their requirements'),

                        Forms\Components\KeyValue::make('funding_breakdown')
                            ->label('Funding Breakdown')
                            ->columnSpanFull()
                            ->helperText('Breakdown of funding sources and amounts'),
                    ])
                    ->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Detail ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('use_of_funds')
                    ->label('Use of Funds')
                    ->limit(50)
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' items' : 'N/A'),

                Tables\Columns\TextColumn::make('milestones')
                    ->label('Milestones')
                    ->limit(50)
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' milestones' : 'N/A'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
