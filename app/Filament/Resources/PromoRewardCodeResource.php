<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoRewardCodeResource\Pages;
use App\Models\PromoRewardCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromoRewardCodeResource extends Resource
{
    protected static ?string $model = PromoRewardCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Marketing & Ads';

    protected static ?string $navigationLabel = 'Reward Codes';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')->required()->unique(ignoreRecord: true)->maxLength(64),
            Forms\Components\Select::make('type')
                ->options([
                    'percent' => 'Percent discount (checkout)',
                    'fixed' => 'Fixed USD off (checkout)',
                    'points' => 'Reward points (checkout)',
                    'free_posts' => 'Free promoted/featured/sponsored posts (onboarding)',
                ])
                ->required()
                ->live(),
            Forms\Components\Select::make('purpose')
                ->options([
                    'checkout' => 'Checkout discount only',
                    'onboarding' => 'Business signup (free posts)',
                    'both' => 'Both checkout + onboarding',
                ])
                ->default('checkout')
                ->required(),
            Forms\Components\Select::make('platform')
                ->options([
                    'both' => 'WWA + CarServices',
                    'wwa' => 'Worldwide Adverts only',
                    'carservices' => 'CarServices Ltd only',
                ])
                ->default('both')
                ->required()
                ->helperText('Which site business signup can redeem this code'),
            Forms\Components\TextInput::make('value')
                ->numeric()
                ->required()
                ->step(0.01)
                ->default(0)
                ->helperText('Discount amount / percent / points. Use 0 for free_posts.'),
            Forms\Components\Select::make('grant_tier')
                ->label('Free post tier')
                ->options([
                    'promoted' => 'Promoted',
                    'featured' => 'Featured',
                    'sponsored' => 'Sponsored',
                    'all' => 'All three (1 of each × quantity)',
                ])
                ->visible(fn (Get $get) => in_array($get('type'), ['free_posts'], true)
                    || in_array($get('purpose'), ['onboarding', 'both'], true)),
            Forms\Components\TextInput::make('grant_quantity')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->visible(fn (Get $get) => in_array($get('type'), ['free_posts'], true)
                    || in_array($get('purpose'), ['onboarding', 'both'], true)),
            Forms\Components\TextInput::make('grant_duration_days')
                ->numeric()
                ->default(7)
                ->minValue(1)
                ->visible(fn (Get $get) => in_array($get('type'), ['free_posts'], true)
                    || in_array($get('purpose'), ['onboarding', 'both'], true)),
            Forms\Components\TextInput::make('max_redemptions_per_user')
                ->numeric()
                ->default(1)
                ->helperText('How many times one business account can redeem this onboarding code'),
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
                ->helperText('Checkout codes only — leave empty to apply to all tiers')
                ->visible(fn (Get $get) => $get('type') !== 'free_posts'),
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
                Tables\Columns\TextColumn::make('purpose')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('platform')->badge()->toggleable(),
                Tables\Columns\TextColumn::make('grant_tier')->label('Grant')->toggleable(),
                Tables\Columns\TextColumn::make('grant_quantity')->label('Qty')->toggleable(),
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
