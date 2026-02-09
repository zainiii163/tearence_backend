<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdPricingPlanResource\Pages;
use App\Filament\Resources\AdPricingPlanResource\RelationManagers;
use App\Models\AdPricingPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class AdPricingPlanResource extends Resource
{
    protected static ?string $model = AdPricingPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Monetization';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Select::make('ad_type')
                    ->label('Ad Type')
                    ->options([
                        'banner' => 'Banner Ad',
                        'affiliate' => 'Affiliate Ad',
                        'classified' => 'Classified Ad',
                    ])
                    ->reactive()
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state && $get('price')) {
                            $duration = $get('duration_days', 30);
                            $price = $get('price');
                            $dailyRate = $price / $duration;
                            $set('estimated_daily_impressions', round($dailyRate * 100));
                        }
                    }),
                Forms\Components\TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state && $get('ad_type')) {
                            $duration = $get('duration_days', 30);
                            $dailyRate = $state / $duration;
                            $set('estimated_daily_impressions', round($dailyRate * 100));
                        }
                    }),
                Forms\Components\TextInput::make('duration_days')
                    ->label('Duration (Days)')
                    ->numeric()
                    ->default(30)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state && $get('price')) {
                            $price = $get('price');
                            $dailyRate = $price / $state;
                            $set('estimated_daily_impressions', round($dailyRate * 100));
                        }
                    }),
                Forms\Components\Placeholder::make('estimated_daily_impressions')
                    ->label('Estimated Daily Impressions')
                    ->content(fn ($get) => $get('estimated_daily_impressions') ?? 'N/A'),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->helperText('Describe what this pricing plan includes'),
                Forms\Components\Repeater::make('features')
                    ->label('Features')
                    ->schema([
                        Forms\Components\TextInput::make('feature')
                            ->label('Feature')
                            ->required(),
                        Forms\Components\TextInput::make('value')
                            ->label('Value')
                            ->required(),
                    ])
                    ->columns(2)
                    ->collapsible(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\Toggle::make('is_featured')
                    ->label('Featured')
                    ->default(false),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('ad_type')
                    ->colors([
                        'primary' => 'banner',
                        'success' => 'affiliate',
                        'warning' => 'classified',
                    ]),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state . ' days')
                    ->sortable(),
                Tables\Columns\TextColumn::make('daily_rate')
                    ->label('Daily Rate')
                    ->money('USD')
                    ->formatStateUsing(fn ($record) => $record->price / $record->duration_days)
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ad_type')
                    ->options([
                        'banner' => 'Banner Ad',
                        'affiliate' => 'Affiliate Ad',
                        'classified' => 'Classified Ad',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                Tables\Filters\Filter::make('price_range')
                    ->label('Price Range')
                    ->form([
                        Forms\Components\TextInput::make('min_price')
                            ->label('Min Price')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('max_price')
                            ->label('Max Price')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['min_price'], fn ($query) => $query->where('price', '>=', $data['min_price']))
                            ->when($data['max_price'], fn ($query) => $query->where('price', '<=', $data['max_price']));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->action(function ($record) {
                        $newPlan = $record->replicate();
                        $newPlan->name = $record->name . ' (Copy)';
                        $newPlan->is_active = false;
                        $newPlan->save();
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('toggle_featured')
                    ->label(fn ($record) => $record->is_featured ? 'Remove Featured' : 'Make Featured')
                    ->icon(fn ($record) => $record->is_featured ? 'heroicon-m-star' : 'heroicon-m-plus-circle')
                    ->color(fn ($record) => $record->is_featured ? 'secondary' : 'warning')
                    ->action(function ($record) {
                        $record->update(['is_featured' => !$record->is_featured]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
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
            'index' => Pages\ListAdPricingPlans::route('/'),
            'create' => Pages\CreateAdPricingPlan::route('/create'),
            'edit' => Pages\EditAdPricingPlan::route('/{record}/edit'),
        ];
    }
}
