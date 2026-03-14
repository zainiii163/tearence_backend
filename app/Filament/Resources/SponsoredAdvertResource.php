<?php

namespace App\Filament\Resources;

use App\Models\SponsoredAdvert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SponsoredAdvertResource extends Resource
{
    protected static ?string $model = SponsoredAdvert::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->default(null)
                    ->columnSpan(6),
                Forms\Components\Select::make('currency')
                    ->options([
                        'USD' => 'USD',
                        'EUR' => 'EUR',
                        'GBP' => 'GBP',
                    ])
                    ->default('USD')
                    ->columnSpan(6),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->required()
                    ->columnSpan(6),
                Forms\Components\TextInput::make('country')
                    ->maxLength(100)
                    ->columnSpan(6),
                Forms\Components\TextInput::make('city')
                    ->maxLength(100)
                    ->default(null)
                    ->columnSpan(6),
                Forms\Components\Repeater::make('images')
                    ->label('Images')
                    ->schema([
                        Forms\Components\TextInput::make('url')
                            ->label('Image URL')
                            ->url(),
                    ])
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('video_url')
                    ->label('Video URL')
                    ->url()
                    ->default(null)
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('seller_info')
                    ->label('Seller Information')
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('location')
                    ->label('Location')
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'expired' => 'Expired',
                        'paused' => 'Paused',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->columnSpan(6),
                Forms\Components\Select::make('promotion_plan')
                    ->options([
                        'free' => 'Free',
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                    ])
                    ->default('free')
                    ->columnSpan(6),
                Forms\Components\Checkbox::make('featured')
                    ->label('Featured')
                    ->default(false)
                    ->columnSpan(4),
                Forms\Components\Checkbox::make('promoted')
                    ->label('Promoted')
                    ->default(false)
                    ->columnSpan(4),
                Forms\Components\Checkbox::make('sponsored')
                    ->label('Sponsored')
                    ->default(false)
                    ->columnSpan(4),
                Forms\Components\DateTimePicker::make('promotion_expires_at')
                    ->label('Promotion Expires At')
                    ->default(null)
                    ->columnSpan(6),
                Forms\Components\TextInput::make('website')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('social_links')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('logo')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Toggle::make('verified_seller')
                    ->required(),
                Forms\Components\TextInput::make('sponsorship_tier')
                    ->required(),
                Forms\Components\TextInput::make('sponsorship_price')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('payment_status')
                    ->required()
                    ->maxLength(20)
                    ->default('pending'),
                Forms\Components\TextInput::make('payment_transaction_id')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('sponsorship_start_date'),
                Forms\Components\DateTimePicker::make('sponsorship_end_date'),
                Forms\Components\TextInput::make('views_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('saves_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('inquiries_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('rating')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('rating_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\Toggle::make('is_featured')
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('tags')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('seo_meta')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('created_by')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('updated_by')
                    ->numeric()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                Tables\Columns\TextColumn::make('views')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rating')
                    ->sortable(),
                Tables\Columns\IconColumn::make('featured')
                    ->boolean(),
                Tables\Columns\IconColumn::make('promoted')
                    ->boolean(),
                Tables\Columns\IconColumn::make('sponsored')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'expired' => 'danger',
                        'paused' => 'info',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('promotion_plan')
                    ->label('Plan')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'free' => 'gray',
                        'promoted' => 'info',
                        'featured' => 'warning',
                        'sponsored' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'expired' => 'Expired',
                        'paused' => 'Paused',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('promotion_plan')
                    ->options([
                        'free' => 'Free',
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                    ]),
                Tables\Filters\SelectFilter::make('featured')
                    ->options([
                        true => 'Yes',
                        false => 'No',
                    ]),
                Tables\Filters\SelectFilter::make('promoted')
                    ->options([
                        true => 'Yes',
                        false => 'No',
                    ]),
                Tables\Filters\SelectFilter::make('sponsored')
                    ->options([
                        true => 'Yes',
                        false => 'No',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }
}
