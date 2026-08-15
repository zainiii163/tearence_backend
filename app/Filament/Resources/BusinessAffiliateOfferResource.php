<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessAffiliateOfferResource\Pages;
use App\Models\BusinessAffiliateOffer;
use App\Models\AffiliateCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BusinessAffiliateOfferResource extends Resource
{

    protected static ?string $navigationLabel = 'Marketplace Programs';
    protected static ?string $model = BusinessAffiliateOffer::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Affiliates Hub';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('affiliate_category_id')
                            ->relationship('affiliateCategory', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('business_name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('product_service_title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('tagline')
                            ->maxLength(80),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('country')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('region')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Offer Details')
                    ->schema([
                        Forms\Components\Select::make('commission_type')
                            ->options([
                                'percentage' => 'Percentage',
                                'fixed' => 'Fixed Amount',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('commission_rate')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->prefix(fn ($get) => $get('commission_type') === 'percentage' ? '%' : '$'),

                        Forms\Components\TextInput::make('cookie_duration')
                            ->numeric()
                            ->required()
                            ->suffix('days'),

                        Forms\Components\CheckboxList::make('allowed_traffic_types')
                            ->options([
                                'social_media' => 'Social Media',
                                'email' => 'Email',
                                'ppc' => 'PPC',
                                'blogging' => 'Blogging',
                                'influencer' => 'Influencer',
                                'other' => 'Other',
                            ])
                            ->columns(3),

                        Forms\Components\Textarea::make('restrictions')
                            ->maxLength(65535)
                            ->helperText('Rules promoters must follow (e.g. no brand bidding).')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('join_instructions')
                            ->label('Join / payout instructions')
                            ->maxLength(65535)
                            ->helperText('Shown to affiliates after they join (payouts, codes, next steps).')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Deals & drops')
                    ->description('YouTube Shopping-style promotions affiliates can tag and promote.')
                    ->schema([
                        Forms\Components\TextInput::make('sale_price')
                            ->label('Current product price')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$'),

                        Forms\Components\TextInput::make('compare_at_price')
                            ->label('Compare-at / original price')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->helperText('If higher than the current price, the offer shows as on sale.'),

                        Forms\Components\TextInput::make('discount_code')
                            ->label('Discount code')
                            ->maxLength(64),

                        Forms\Components\Select::make('promotion_type')
                            ->options([
                                'none' => 'None',
                                'percent_off' => '% off',
                                'amount_off' => 'Amount off',
                                'sale' => 'Sale price',
                                'price_drop' => 'Price drop',
                                'product_drop' => 'Product drop',
                            ])
                            ->default('none'),

                        Forms\Components\TextInput::make('promotion_label')
                            ->label('Badge label')
                            ->maxLength(80)
                            ->helperText('Optional. Leave blank to auto-generate (e.g. 20% off).'),

                        Forms\Components\DateTimePicker::make('drop_at')
                            ->label('Drop time')
                            ->helperText('For product drops: hide-then-reveal time. Shows “Dropping soon” until then.'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Links & Assets')
                    ->schema([
                        Forms\Components\TextInput::make('tracking_link')
                            ->label('Destination URL')
                            ->helperText('Merchant sales/checkout page. Promoter hop links redirect here.')
                            ->url()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TagsInput::make('promotional_assets')
                            ->label('Promotional creatives (URLs)')
                            ->placeholder('Paste banner or image URL and press Enter')
                            ->helperText('Shown in the public offer creatives library for affiliates.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('business_email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('website_url')
                            ->url()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('verification_document')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Visibility')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),

                        Forms\Components\Toggle::make('is_verified')
                            ->default(false),

                        Forms\Components\Toggle::make('is_promoted')
                            ->default(false),

                        Forms\Components\Toggle::make('is_featured')
                            ->default(false),

                        Forms\Components\Toggle::make('is_sponsored')
                            ->default(false),

                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->default(0.00),

                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->default('pending'),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At'),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('business_name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product_service_title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('affiliateCategory.name')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->formatStateUsing(fn ($record) => $record->display_commission)
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.first_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_promoted')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_sponsored')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cookie_duration')
                    ->label('Cookie')
                    ->suffix('d')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('clicks')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('applications')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                    ])
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('affiliate_category_id')
                    ->relationship('affiliateCategory', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('is_verified')
                    ->options([
                        true => 'Verified',
                        false => 'Not Verified',
                    ]),

                Tables\Filters\SelectFilter::make('is_promoted')
                    ->options([
                        true => 'Promoted',
                        false => 'Not Promoted',
                    ]),

                Tables\Filters\SelectFilter::make('is_featured')
                    ->options([
                        true => 'Featured',
                        false => 'Not Featured',
                    ]),

                Tables\Filters\SelectFilter::make('is_sponsored')
                    ->options([
                        true => 'Sponsored',
                        false => 'Not Sponsored',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
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
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListBusinessAffiliateOffers::route('/'),
            'create' => Pages\CreateBusinessAffiliateOffer::route('/create'),
            'view' => Pages\ViewBusinessAffiliateOffer::route('/{record}'),
            'edit' => Pages\EditBusinessAffiliateOffer::route('/{record}/edit'),
        ];
    }
}
