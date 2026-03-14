<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FundingRewardResource\Pages;
use App\Models\FundingReward;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FundingRewardResource extends Resource
{
    protected static ?string $model = FundingReward::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Fundraising';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Reward Details')
                    ->schema([
                        Forms\Components\Select::make('funding_project_id')
                            ->relationship('fundingProject', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('minimum_contribution')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('limit')
                            ->numeric()
                            ->nullable()
                            ->helperText('Leave empty for unlimited'),
                        Forms\Components\DatePicker::make('estimated_delivery_date'),
                        Forms\Components\Toggle::make('is_active')->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fundingProject.title')->searchable(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('minimum_contribution')->money(),
                Tables\Columns\TextColumn::make('limit')->numeric(),
                Tables\Columns\TextColumn::make('claimed_count')->numeric(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFundingRewards::route('/'),
            'create' => Pages\CreateFundingReward::route('/create'),
            'edit' => Pages\EditFundingReward::route('/{record}/edit'),
        ];
    }
}
