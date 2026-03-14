<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateApplicationResource\Pages;
use App\Models\AffiliateApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AffiliateApplicationResource extends Resource
{
    protected static ?string $model = AffiliateApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Affiliates Hub';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Application Details')
                    ->schema([
                        Forms\Components\Select::make('business_affiliate_offer_id')
                            ->relationship('businessAffiliateOffer', 'product_service_title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Textarea::make('message')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('promotion_methods')
                            ->schema([
                                Forms\Components\TextInput::make('method')
                                    ->label('Promotion Method'),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('audience_details')
                            ->schema([
                                Forms\Components\TextInput::make('detail')
                                    ->label('Audience Detail'),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('website_url')
                            ->url()
                            ->maxLength(255),

                        Forms\Components\Repeater::make('social_media_links')
                            ->schema([
                                Forms\Components\TextInput::make('platform')
                                    ->label('Platform'),
                                Forms\Components\TextInput::make('url')
                                    ->label('URL')
                                    ->url(),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('estimated_monthly_visitors')
                            ->numeric()
                            ->label('Estimated Monthly Visitors'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Application Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'withdrawn' => 'Withdrawn',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('rejection_reason')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->visible(fn (callable $get) => $get('status') === 'rejected'),

                        Forms\Components\Textarea::make('approval_notes')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->visible(fn (callable $get) => $get('status') === 'approved'),

                        Forms\Components\Select::make('reviewed_by')
                            ->relationship('reviewer', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('reviewed_at')
                            ->label('Reviewed At')
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Business Response')
                    ->schema([
                        Forms\Components\Textarea::make('business_response')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('business_responded_at')
                            ->label('Business Responded At')
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('businessAffiliateOffer.product_service_title')
                    ->label('Business Offer')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('businessAffiliateOffer.business_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'gray' => 'withdrawn',
                    ]),

                Tables\Columns\TextColumn::make('estimated_monthly_visitors')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state) : 'N/A')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reviewer.name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Not reviewed')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not reviewed')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('business_responded_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('No response')
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
                        'withdrawn' => 'Withdrawn',
                    ]),

                Tables\Filters\SelectFilter::make('business_affiliate_offer_id')
                    ->relationship('businessAffiliateOffer', 'product_service_title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('reviewed_by')
                    ->relationship('reviewer', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Reviewed By'),
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
            'index' => Pages\ListAffiliateApplications::route('/'),
            'create' => Pages\CreateAffiliateApplication::route('/create'),
            'view' => Pages\ViewAffiliateApplication::route('/{record}'),
            'edit' => Pages\EditAffiliateApplication::route('/{record}/edit'),
        ];
    }
}
