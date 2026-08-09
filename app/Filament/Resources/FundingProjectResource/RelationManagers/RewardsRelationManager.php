<?php

namespace App\Filament\Resources\FundingProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'rewards';

    protected static ?string $title = 'Backer rewards';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('minimum_contribution')
                    ->numeric()
                    ->required()
                    ->prefix('$')
                    ->minValue(1),
                Forms\Components\TextInput::make('limit')
                    ->numeric()
                    ->nullable()
                    ->helperText('Leave empty for unlimited stock'),
                Forms\Components\TextInput::make('claimed_count')
                    ->numeric()
                    ->default(0)
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Updated automatically when pledges complete'),
                Forms\Components\DatePicker::make('estimated_delivery_date')
                    ->required()
                    ->native(false),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('minimum_contribution')->money()->sortable(),
                Tables\Columns\TextColumn::make('limit')
                    ->label('Stock limit')
                    ->placeholder('Unlimited'),
                Tables\Columns\TextColumn::make('claimed_count')->label('Claimed'),
                Tables\Columns\TextColumn::make('estimated_delivery_date')->date()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
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
