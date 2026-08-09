<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromoPricingPlanResource\Pages;
use App\Models\PromoPricingPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PromoPricingPlanResource extends Resource
{
    protected static ?string $model = PromoPricingPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Marketing & Ads';

    protected static ?string $navigationLabel = 'Promo Pricing Plans';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('vertical')
                ->options(PromoPricingPlan::VERTICALS)
                ->default('all')
                ->required()
                ->helperText('Choose which marketplace form this plan appears on. “All verticals” is the fallback.'),
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->helperText('Unique per vertical, e.g. promoted / featured / sponsored'),
            Forms\Components\Select::make('tier')
                ->options([
                    'paid' => 'Paid',
                    'promoted' => 'Promoted',
                    'featured' => 'Featured',
                    'sponsored' => 'Sponsored',
                ])
                ->required(),
            Forms\Components\TextInput::make('price_usd')->numeric()->required()->prefix('$')->step(0.01),
            Forms\Components\TextInput::make('duration_days')->numeric()->required()->suffix('days'),
            Forms\Components\Textarea::make('description')->columnSpanFull(),
            Forms\Components\TagsInput::make('features')
                ->helperText('Shown as bullet points on the post form'),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\Toggle::make('is_popular')
                ->label('Most popular badge')
                ->default(false),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vertical')
                    ->badge()
                    ->formatStateUsing(fn ($state) => PromoPricingPlan::VERTICALS[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('tier')->badge(),
                Tables\Columns\TextColumn::make('price_usd')->money('USD'),
                Tables\Columns\TextColumn::make('duration_days')->suffix(' days'),
                Tables\Columns\IconColumn::make('is_popular')->boolean()->label('Popular'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('vertical')
                    ->options(PromoPricingPlan::VERTICALS),
                Tables\Filters\SelectFilter::make('tier')
                    ->options([
                        'paid' => 'Paid',
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                    ]),
            ])
            ->actions([
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
            'index' => Pages\ListPromoPricingPlans::route('/'),
            'create' => Pages\CreatePromoPricingPlan::route('/create'),
            'edit' => Pages\EditPromoPricingPlan::route('/{record}/edit'),
        ];
    }
}
