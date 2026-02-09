<?php

namespace App\Filament\CustomerResources;

use App\Filament\CustomerResources\CustomerUpsellResource\Pages;
use App\Models\ListingUpsell;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CustomerUpsellResource extends Resource
{
    protected static ?string $model = ListingUpsell::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'My Upsells';

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('customer_id', Auth::user()->customer_id ?? 0);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Upsell Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('listing_id')
                            ->relationship('listing', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Listing')
                            ->default(fn() => Auth::user()->listings()->pluck('listing_id')->first()),
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
                            ->step(0.01)
                            ->disabled(),
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
                    ]),
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
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expiry Date')
                    ->dateTime()
                    ->sortable(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerUpsells::route('/'),
            'create' => Pages\CreateCustomerUpsell::route('/create'),
            'view' => Pages\ViewCustomerUpsell::route('/{record}'),
            'edit' => Pages\EditCustomerUpsell::route('/{record}/edit'),
        ];
    }
}
