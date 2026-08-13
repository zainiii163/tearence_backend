<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SellerMarketplacePayoutResource\Pages;
use App\Models\SellerMarketplacePayout;
use App\Services\CategoryMoneyFlowService;
use App\Services\CryptoPayoutService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Throwable;

class SellerMarketplacePayoutResource extends Resource
{
    protected static ?string $model = SellerMarketplacePayout::class;

    protected static ?string $navigationLabel = 'Seller payouts';

    protected static ?string $modelLabel = 'Seller payout';

    protected static ?string $pluralModelLabel = 'Seller payouts';

    protected static ?string $slug = 'seller-payouts';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Commerce';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Seller marketplace payout')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => ($record->name ?? '').' · '.($record->email ?? ''))
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
                                'crypto' => 'Crypto (USDT / USDC)',
                                'paypal' => 'PayPal',
                                'bank' => 'Bank transfer',
                                'other' => 'Other',
                            ])
                            ->required()
                            ->default('crypto')
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
                            ->maxLength(191)
                            ->visible(fn (Forms\Get $get) => $get('method') === 'crypto'),

                        Forms\Components\TextInput::make('crypto_currency')
                            ->label('Currency')
                            ->maxLength(16)
                            ->visible(fn (Forms\Get $get) => $get('method') === 'crypto'),

                        Forms\Components\TextInput::make('payout_details')
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

                        Forms\Components\TextInput::make('reference')->maxLength(100),
                        Forms\Components\Textarea::make('notes')->rows(3)->columnSpanFull(),
                        Forms\Components\DateTimePicker::make('paid_at'),
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
                Tables\Columns\TextColumn::make('user.name')->label('Seller')->searchable(['first_name', 'last_name', 'email']),
                Tables\Columns\TextColumn::make('amount')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('method')->badge(),
                Tables\Columns\TextColumn::make('crypto_network')->label('Network')->toggleable(),
                Tables\Columns\TextColumn::make('crypto_address')->label('Wallet')->limit(18)->copyable()->toggleable(),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'warning' => 'pending',
                    'info' => 'processing',
                    'success' => 'paid',
                    'danger' => 'rejected',
                ]),
                Tables\Columns\TextColumn::make('tx_hash')->limit(16)->copyable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'processing' => 'Processing',
                    'paid' => 'Paid',
                    'rejected' => 'Rejected',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('send_crypto')
                    ->label('Approve & send crypto')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (SellerMarketplacePayout $record) => $record->method === 'crypto' && in_array($record->status, ['pending', 'processing'], true) && empty($record->provider_payout_id))
                    ->action(function (SellerMarketplacePayout $record) {
                        try {
                            $updated = app(CryptoPayoutService::class)->approveAndSend($record);
                            Notification::make()
                                ->title($updated->status === 'paid' ? 'Seller crypto payout sent' : 'Crypto payout submitted')
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()->title('Crypto payout failed')->body($e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (SellerMarketplacePayout $record) => in_array($record->status, ['pending', 'processing'], true))
                    ->action(function (SellerMarketplacePayout $record) {
                        $record->update(['status' => 'paid', 'paid_at' => now()]);
                        try {
                            app(CategoryMoneyFlowService::class)->recordSellerPayout(
                                'other',
                                (float) $record->amount,
                                'seller_marketplace_payout',
                                $record->id,
                                (int) $record->user_id,
                                $record->reference,
                                $record->currency ?: 'USD',
                                'Seller marketplace payout paid'
                            );
                        } catch (Throwable) {
                        }
                        Notification::make()->title('Seller payout marked paid')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (SellerMarketplacePayout $record) => in_array($record->status, ['pending', 'processing'], true))
                    ->form([Forms\Components\Textarea::make('notes')->label('Rejection note')->required()])
                    ->action(function (SellerMarketplacePayout $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'notes' => trim(($record->notes ? $record->notes."\n" : '').$data['notes']),
                        ]);
                        Notification::make()->title('Payout rejected')->warning()->send();
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSellerMarketplacePayouts::route('/'),
            'view' => Pages\ViewSellerMarketplacePayout::route('/{record}'),
            'edit' => Pages\EditSellerMarketplacePayout::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            $count = SellerMarketplacePayout::query()->whereIn('status', ['pending', 'processing'])->count();

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
