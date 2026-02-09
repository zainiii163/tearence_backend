<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingUpsellResource\Pages;
use App\Models\ListingUpsell;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListingUpsellResource extends Resource
{
    protected static ?string $model = ListingUpsell::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Monetization';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('listing_id')
                    ->relationship('listing', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Listing'),
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Customer'),
                Forms\Components\Select::make('upsell_type')
                    ->options([
                        ListingUpsell::TYPE_PRIORITY => 'Priority Placement',
                        ListingUpsell::TYPE_FEATURED => 'Featured Listing',
                        ListingUpsell::TYPE_SPONSORED => 'Sponsored Listing',
                        ListingUpsell::TYPE_PREMIUM => 'Premium Placement',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => 
                        $set('price', match($state) {
                            ListingUpsell::TYPE_PRIORITY => 10.00,
                            ListingUpsell::TYPE_FEATURED => 15.00,
                            ListingUpsell::TYPE_SPONSORED => 25.00,
                            ListingUpsell::TYPE_PREMIUM => 50.00,
                            default => 0.00,
                        })
                    ),
                Forms\Components\TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->prefix('$')
                    ->required()
                    ->step(0.01),
                Forms\Components\TextInput::make('duration_days')
                    ->label('Duration (Days)')
                    ->numeric()
                    ->required()
                    ->default(7),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('Start Date & Time')
                    ->required()
                    ->default(now()),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expiry Date & Time')
                    ->required()
                    ->default(fn () => now()->addDays(7)),
                Forms\Components\Select::make('status')
                    ->options([
                        ListingUpsell::STATUS_ACTIVE => 'Active',
                        ListingUpsell::STATUS_EXPIRED => 'Expired',
                        ListingUpsell::STATUS_CANCELLED => 'Cancelled',
                    ])
                    ->required()
                    ->default(ListingUpsell::STATUS_ACTIVE),
                Forms\Components\Select::make('payment_status')
                    ->options([
                        ListingUpsell::PAYMENT_PENDING => 'Pending',
                        ListingUpsell::PAYMENT_PAID => 'Paid',
                        ListingUpsell::PAYMENT_FAILED => 'Failed',
                        ListingUpsell::PAYMENT_REFUNDED => 'Refunded',
                    ])
                    ->required()
                    ->default(ListingUpsell::PAYMENT_PENDING),
                Forms\Components\Textarea::make('payment_details')
                    ->label('Payment Details')
                    ->helperText('JSON format for payment transaction details')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('listing.title')
                    ->label('Listing')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('upsell_type')
                    ->colors([
                        'primary' => ListingUpsell::TYPE_PRIORITY,
                        'warning' => ListingUpsell::TYPE_FEATURED,
                        'success' => ListingUpsell::TYPE_SPONSORED,
                        'danger' => ListingUpsell::TYPE_PREMIUM,
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        ListingUpsell::TYPE_PRIORITY => 'Priority',
                        ListingUpsell::TYPE_FEATURED => 'Featured',
                        ListingUpsell::TYPE_SPONSORED => 'Sponsored',
                        ListingUpsell::TYPE_PREMIUM => 'Premium',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => "{$state} days")
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => ListingUpsell::STATUS_ACTIVE,
                        'warning' => ListingUpsell::STATUS_EXPIRED,
                        'danger' => ListingUpsell::STATUS_CANCELLED,
                    ]),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'warning' => ListingUpsell::PAYMENT_PENDING,
                        'success' => ListingUpsell::PAYMENT_PAID,
                        'danger' => ListingUpsell::PAYMENT_FAILED,
                        'info' => ListingUpsell::PAYMENT_REFUNDED,
                    ]),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Start Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expiry Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('upsell_type')
                    ->options([
                        ListingUpsell::TYPE_PRIORITY => 'Priority Placement',
                        ListingUpsell::TYPE_FEATURED => 'Featured Listing',
                        ListingUpsell::TYPE_SPONSORED => 'Sponsored Listing',
                        ListingUpsell::TYPE_PREMIUM => 'Premium Placement',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        ListingUpsell::STATUS_ACTIVE => 'Active',
                        ListingUpsell::STATUS_EXPIRED => 'Expired',
                        ListingUpsell::STATUS_CANCELLED => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        ListingUpsell::PAYMENT_PENDING => 'Pending',
                        ListingUpsell::PAYMENT_PAID => 'Paid',
                        ListingUpsell::PAYMENT_FAILED => 'Failed',
                        ListingUpsell::PAYMENT_REFUNDED => 'Refunded',
                    ]),
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->active())
                    ->label('Active Only'),
                Tables\Filters\Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<=', now()))
                    ->label('Expired'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('mark_as_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ListingUpsell $record): bool => $record->payment_status === ListingUpsell::PAYMENT_PENDING)
                    ->action(fn (ListingUpsell $record) => $record->update(['payment_status' => ListingUpsell::PAYMENT_PAID])),
                Tables\Actions\Action::make('mark_as_expired')
                    ->label('Mark as Expired')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (ListingUpsell $record): bool => $record->status === ListingUpsell::STATUS_ACTIVE && $record->expires_at > now())
                    ->action(fn (ListingUpsell $record) => $record->markAsExpired()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_all_as_paid')
                        ->label('Mark All as Paid')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each(fn (ListingUpsell $record) => 
                            $record->payment_status === ListingUpsell::PAYMENT_PENDING && 
                            $record->update(['payment_status' => ListingUpsell::PAYMENT_PAID])
                        )),
                    Tables\Actions\BulkAction::make('mark_all_as_expired')
                        ->label('Mark All as Expired')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(fn ($records) => $records->each(fn (ListingUpsell $record) => 
                            $record->status === ListingUpsell::STATUS_ACTIVE && 
                            $record->markAsExpired()
                        )),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListListingUpsells::route('/'),
            'create' => Pages\CreateListingUpsell::route('/create'),
            'edit' => Pages\EditListingUpsell::route('/{record}/edit'),
        ];
    }
}
