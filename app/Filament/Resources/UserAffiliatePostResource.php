<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserAffiliatePostResource\Pages;
use App\Models\UserAffiliatePost;
use App\Models\AffiliateCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserAffiliatePostResource extends Resource
{
    protected static ?string $model = UserAffiliatePost::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Affiliates Hub';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('affiliate_category_id')
                            ->relationship('affiliateCategory', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('country')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('region')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('target_audience')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Affiliate Content')
                    ->schema([
                        Forms\Components\TextInput::make('affiliate_link')
                            ->url()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('image')
                            ->maxLength(255)
                            ->helperText('Path to uploaded image'),

                        Forms\Components\Repeater::make('hashtags')
                            ->schema([
                                Forms\Components\TextInput::make('tag')
                                    ->label('Hashtag')
                                    ->placeholder('#example'),
                            ])
                            ->columnSpanFull(),
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

                Forms\Components\Section::make('Moderation')
                    ->schema([
                        Forms\Components\Textarea::make('moderation_notes')
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('moderated_by')
                            ->relationship('moderator', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('moderated_at')
                            ->label('Moderated At')
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('affiliateCategory.name')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                Tables\Columns\IconColumn::make('is_promoted')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_sponsored')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('clicks')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('shares')
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
            'index' => Pages\ListUserAffiliatePosts::route('/'),
            'create' => Pages\CreateUserAffiliatePost::route('/create'),
            'view' => Pages\ViewUserAffiliatePost::route('/{record}'),
            'edit' => Pages\EditUserAffiliatePost::route('/{record}/edit'),
        ];
    }
}
