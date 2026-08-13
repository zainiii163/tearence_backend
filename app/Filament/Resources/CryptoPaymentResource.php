<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CryptoPaymentResource\Pages;
use App\Models\CryptoPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CryptoPaymentResource extends Resource
{
    protected static ?string $model = CryptoPayment::class;

    protected static ?string $slug = 'crypto-payments';

    protected static ?string $navigationLabel = 'Crypto payments';

    protected static ?string $modelLabel = 'Crypto payment';

    protected static ?string $pluralModelLabel = 'Crypto payments';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Affiliates Hub';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice')
                    ->schema([
                        Forms\Components\TextInput::make('ledger_id')->disabled(),
                        Forms\Components\TextInput::make('provider')->disabled(),
                        Forms\Components\TextInput::make('provider_invoice_id')->disabled(),
                        Forms\Components\TextInput::make('status')->disabled(),
                        Forms\Components\TextInput::make('amount')->prefix('$')->disabled(),
                        Forms\Components\TextInput::make('currency')->disabled(),
                        Forms\Components\TextInput::make('pay_currency')->disabled(),
                        Forms\Components\TextInput::make('network')->disabled(),
                        Forms\Components\TextInput::make('pay_address')->columnSpanFull()->disabled(),
                        Forms\Components\TextInput::make('tx_hash')->columnSpanFull()->disabled(),
                        Forms\Components\Textarea::make('raw_webhook_json')
                            ->label('Webhook JSON')
                            ->columnSpanFull()
                            ->disabled()
                            ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : (string) $state),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ledger_id')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('customer.email')->label('Customer')->toggleable(),
                Tables\Columns\TextColumn::make('amount')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('pay_currency')->label('Coin')->toggleable(),
                Tables\Columns\TextColumn::make('network')->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'waiting',
                        'info' => 'confirming',
                        'success' => 'finished',
                        'danger' => 'failed',
                    ]),
                Tables\Columns\TextColumn::make('tx_hash')->limit(16)->copyable()->toggleable(),
                Tables\Columns\IconColumn::make('mock')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'waiting' => 'Waiting',
                        'confirming' => 'Confirming',
                        'finished' => 'Finished',
                        'failed' => 'Failed',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\SelectFilter::make('provider')
                    ->options([
                        'nowpayments' => 'NOWPayments',
                        'crypto_mock' => 'Mock',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCryptoPayments::route('/'),
            'view' => Pages\ViewCryptoPayment::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
