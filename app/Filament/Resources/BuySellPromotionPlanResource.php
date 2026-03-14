<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuySellPromotionPlanResource\Pages;
use App\Models\BuySellPromotionPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BuySellPromotionPlanResource extends Resource
{
    protected static ?string $model = BuySellPromotionPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationGroup = 'Buy & Sell';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Plan Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                        
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(100)
                            ->unique(BuySellPromotionPlan::class, 'slug', ignoreRecord: true),
                        
                        Forms\Components\Textarea::make('description')
                            ->maxLength(1000)
                            ->rows(3),
                    ]),
                
                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->step('0.01')
                            ->prefix('$')
                            ->label('Price'),
                        
                        Forms\Components\TextInput::make('duration_days')
                            ->required()
                            ->numeric()
                            ->label('Duration (Days)')
                            ->suffix('days'),
                        
                        Forms\Components\TextInput::make('visibility_multiplier')
                            ->numeric()
                            ->step('0.1')
                            ->default(1.0)
                            ->label('Visibility Multiplier')
                            ->helperText('How much visibility boost this plan provides (1.0 = normal, 2.0 = 2x, etc.)'),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Features')
                    ->schema([
                        Forms\Components\KeyValue::make('features')
                            ->label('Plan Features')
                            ->keyLabel('Feature')
                            ->valueLabel('Description')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active'),
                        
                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->label('Sort Order'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Duration')
                    ->suffix(' days')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('visibility_multiplier')
                    ->label('Visibility')
                    ->formatStateUsing(fn ($state) => $state . 'x')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
                
                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\TextInput::make('min_price')
                            ->numeric()
                            ->placeholder('Min price'),
                        
                        Forms\Components\TextInput::make('max_price')
                            ->numeric()
                            ->placeholder('Max price'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['min_price'], fn ($query, $price) => $query->where('price', '>=', $price))
                            ->when($data['max_price'], fn ($query, $price) => $query->where('price', '<=', $price));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                        }),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-mark')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                        }),
                ]),
            ])
            ->reorderable('sort_order');
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
            'index' => Pages\ListBuySellPromotionPlans::route('/'),
            'create' => Pages\CreateBuySellPromotionPlan::route('/create'),
            'view' => Pages\ViewBuySellPromotionPlan::route('/{record}'),
            'edit' => Pages\EditBuySellPromotionPlan::route('/{record}/edit'),
        ];
    }
}
