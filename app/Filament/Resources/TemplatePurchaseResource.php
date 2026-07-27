<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplatePurchaseResource\Pages;
use App\Models\TemplatePurchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TemplatePurchaseResource extends Resource
{
    protected static ?string $model = TemplatePurchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationGroup = 'Templates';

    protected static ?string $navigationLabel = 'Template Purchases';

    protected static ?string $modelLabel = 'Template purchase';

    protected static ?string $pluralModelLabel = 'Template Purchases';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->disabled(),
                Forms\Components\TextInput::make('template_slug')->disabled(),
                Forms\Components\TextInput::make('price_paid')->numeric()->prefix('$')->disabled(),
                Forms\Components\TextInput::make('platform_fee')->numeric()->prefix('$')->disabled(),
                Forms\Components\TextInput::make('seller_amount')->numeric()->prefix('$')->disabled(),
                Forms\Components\Select::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('payment_method')->disabled(),
                Forms\Components\TextInput::make('download_token')->disabled()->columnSpanFull(),
                Forms\Components\DateTimePicker::make('download_token_expires_at')->disabled(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('template_slug')
                    ->label('Slug')
                    ->toggleable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('price_paid')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('platform_fee')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'secondary' => 'refunded',
                    ]),
                Tables\Columns\TextColumn::make('payment_method')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemplatePurchases::route('/'),
            'edit' => Pages\EditTemplatePurchase::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
