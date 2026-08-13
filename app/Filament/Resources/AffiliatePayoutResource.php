<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliatePayoutResource\Pages;
use App\Models\AffiliatePayout;
use App\Services\CryptoPayoutService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Throwable;

class AffiliatePayoutResource extends Resource
{
    protected static ?string $model = AffiliatePayout::class;

    protected static ?string $navigationLabel = 'Payout requests';

    protected static ?string $modelLabel = 'Payout request';

    protected static ?string $pluralModelLabel = 'Payout requests';

    protected static ?string $slug = 'affiliate-payouts';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Affiliates Hub';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payout')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' · ' . ($record->email ?? ''))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->minValue(1),

                        Forms\Components\Select::make('method')
                            ->options([
                                'paypal' => 'PayPal',
                                'crypto' => 'Crypto (USDT / USDC)',
                                'bank' => 'Bank transfer',
                                'wise' => 'Wise',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->default('paypal')
                            ->live(),

                        Forms\Components\Select::make('crypto_network')
                            ->label('Crypto network')
                            ->options([
                                'trc20' => 'USDT → TRC20',
                                'erc20' => 'USDT → ERC20',
                                'polygon' => 'USDC → Polygon',
                            ])
                            ->visible(fn (Forms\Get $get) => $get('method') === 'crypto'),

                        Forms\Components\TextInput::make('crypto_address')
                            ->label('Wallet address')
                            ->helperText('Address and network must match. Wrong-network sends can be permanently lost.')
                            ->maxLength(191)
                            ->visible(fn (Forms\Get $get) => $get('method') === 'crypto'),

                        Forms\Components\TextInput::make('crypto_currency')
                            ->label('Currency')
                            ->maxLength(16)
                            ->visible(fn (Forms\Get $get) => $get('method') === 'crypto'),

                        Forms\Components\TextInput::make('payout_details')
                            ->label('Payout details')
                            ->helperText('PayPal email, IBAN, or account reference')
                            ->maxLength(255),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'paid' => 'Paid',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('pending'),

                        Forms\Components\TextInput::make('provider_payout_id')
                            ->label('Provider payout ID')
                            ->maxLength(191)
                            ->visible(fn (Forms\Get $get) => $get('method') === 'crypto'),

                        Forms\Components\TextInput::make('tx_hash')
                            ->label('Transaction hash')
                            ->maxLength(191)
                            ->visible(fn (Forms\Get $get) => $get('method') === 'crypto'),

                        Forms\Components\TextInput::make('reference')
                            ->maxLength(100),

                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Paid at'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Affiliate')
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.email')
                    ->label('Customer email')
                    ->toggleable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('method')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('crypto_network')
                    ->label('Network')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('crypto_address')
                    ->label('Wallet')
                    ->limit(18)
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('payout_details')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'success' => 'paid',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('tx_hash')
                    ->label('Tx hash')
                    ->limit(16)
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('provider_payout_id')
                    ->label('Provider ID')
                    ->limit(16)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'paid' => 'Paid',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('method')
                    ->options([
                        'paypal' => 'PayPal',
                        'crypto' => 'Crypto',
                        'bank' => 'Bank',
                        'wise' => 'Wise',
                        'other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('send_crypto')
                    ->label('Approve & send crypto')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Send crypto payout via NOWPayments')
                    ->modalDescription('Wallet address and network must match. Sending on the wrong network can result in permanent loss. WWA ledger stays pending → processing → paid.')
                    ->visible(fn (AffiliatePayout $record) => $record->method === 'crypto' && in_array($record->status, ['pending', 'processing'], true) && empty($record->provider_payout_id))
                    ->action(function (AffiliatePayout $record) {
                        try {
                            $updated = app(CryptoPayoutService::class)->approveAndSend($record);
                            Notification::make()
                                ->title($updated->status === 'paid' ? 'Crypto payout sent' : 'Crypto payout submitted')
                                ->body('Provider ID: '.($updated->provider_payout_id ?: 'n/a').($updated->tx_hash ? ' · Tx: '.$updated->tx_hash : ''))
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Crypto payout failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('mark_processing')
                    ->label('Processing')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (AffiliatePayout $record) => $record->status === 'pending')
                    ->action(function (AffiliatePayout $record) {
                        $record->update(['status' => 'processing']);
                        Notification::make()->title('Marked processing')->success()->send();
                    }),

                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (AffiliatePayout $record) => in_array($record->status, ['pending', 'processing'], true))
                    ->action(function (AffiliatePayout $record) {
                        $record->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                        Notification::make()->title('Payout marked paid')->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (AffiliatePayout $record) => in_array($record->status, ['pending', 'processing'], true))
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Rejection note')
                            ->required(),
                    ])
                    ->action(function (AffiliatePayout $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'notes' => trim(($record->notes ? $record->notes . "\n" : '') . $data['notes']),
                        ]);
                        Notification::make()->title('Payout rejected')->warning()->send();
                    }),

                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListAffiliatePayouts::route('/'),
            'create' => Pages\CreateAffiliatePayout::route('/create'),
            'view' => Pages\ViewAffiliatePayout::route('/{record}'),
            'edit' => Pages\EditAffiliatePayout::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $count = AffiliatePayout::query()->whereIn('status', ['pending', 'processing'])->count();

            return $count > 0 ? (string) $count : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
