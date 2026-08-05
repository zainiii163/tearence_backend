<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoRewardCodeResource\Pages;
use App\Models\PromoRewardCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromoRewardCodeResource extends Resource
{
    protected static ?string $model = PromoRewardCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Advertising';

    protected static ?string $navigationLabel = 'Reward Codes';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required()->unique(ignoreRecord: true)->maxLength(64),
            Forms\Components\Select::make('type')
                ->options([
                    'percent' => 'Percent discount',
                    'fixed' => 'Fixed USD off',
                    'points' => 'Reward points',
                ])
                ->required(),
            Forms\Components\TextInput::make('value')->numeric()->required()->step(0.01),
            Forms\Components\TextInput::make('max_uses')->numeric()->nullable(),
            Forms\Components\TextInput::make('uses_count')->numeric()->disabled()->default(0),
            Forms\Components\DateTimePicker::make('valid_from'),
            Forms\Components\DateTimePicker::make('valid_until'),
            Forms\Components\CheckboxList::make('applies_to')
                ->options([
                    'paid' => 'Paid',
                    'promoted' => 'Promoted',
                    'featured' => 'Featured',
                    'sponsored' => 'Sponsored',
                ])
                ->columns(2)
                ->helperText('Leave empty to apply to all tiers'),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('description')->maxLength(255)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('value'),
                Tables\Columns\TextColumn::make('uses_count')->label('Uses'),
                Tables\Columns\TextColumn::make('max_uses')->label('Max'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('valid_until')->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPromoRewardCodes::route('/'),
            'create' => Pages\CreatePromoRewardCode::route('/create'),
            'edit' => Pages\EditPromoRewardCode::route('/{record}/edit'),
        ];
    }
}
