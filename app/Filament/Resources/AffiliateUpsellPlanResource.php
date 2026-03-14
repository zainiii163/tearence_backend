<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateUpsellPlanResource\Pages;
use App\Models\AffiliateUpsellPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AffiliateUpsellPlanResource extends Resource
{
    protected static ?string $model = AffiliateUpsellPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Affiliates Hub';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(AffiliateUpsellPlan::class, 'slug', ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->prefix('$'),

                        Forms\Components\Select::make('duration_type')
                            ->options([
                                'weekly' => 'Weekly',
                                'monthly' => 'Monthly',
                                'yearly' => 'Yearly',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('duration_days')
                            ->numeric()
                            ->required()
                            ->suffix('days'),

                        Forms\Components\Repeater::make('features')
                            ->schema([
                                Forms\Components\TextInput::make('feature')
                                    ->label('Feature')
                                    ->required(),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('benefits')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Visibility Features')
                    ->schema([
                        Forms\Components\Toggle::make('highlighted_background')
                            ->default(false),

                        Forms\Components\Toggle::make('above_standard_posts')
                            ->default(false),

                        Forms\Components\Toggle::make('top_category_placement')
                            ->default(false),

                        Forms\Components\Toggle::make('larger_card_size')
                            ->default(false),

                        Forms\Components\Toggle::make('priority_search')
                            ->default(false),

                        Forms\Components\Toggle::make('homepage_placement')
                            ->default(false),

                        Forms\Components\Toggle::make('category_top_placement')
                            ->default(false),

                        Forms\Components\Toggle::make('homepage_slider')
                            ->default(false),

                        Forms\Components\Toggle::make('social_media_promotion')
                            ->default(false),

                        Forms\Components\Toggle::make('email_blast_inclusion')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Badge Settings')
                    ->schema([
                        Forms\Components\TextInput::make('badge_text')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('badge_color')
                            ->maxLength(255)
                            ->placeholder('#FF0000'),

                        Forms\Components\Toggle::make('is_active')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
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
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_type')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('duration_days')
                    ->numeric()
                    ->sortable()
                    ->suffix(' days'),

                Tables\Columns\TextColumn::make('duration_description')
                    ->getStateUsing(fn (AffiliateUpsellPlan $record): string => $record->getDurationDescriptionAttribute())
                    ->sortable(),

                Tables\Columns\IconColumn::make('highlighted_background')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('homepage_placement')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('social_media_promotion')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('badge_text')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => $record->badge_color ?? 'primary')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('duration_type')
                    ->options([
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                        'yearly' => 'Yearly',
                    ]),

                Tables\Filters\SelectFilter::make('is_active')
                    ->options([
                        true => 'Active',
                        false => 'Inactive',
                    ]),

                Tables\Filters\SelectFilter::make('homepage_placement')
                    ->options([
                        true => 'Homepage Placement',
                        false => 'No Homepage',
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
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
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
            'index' => Pages\ListAffiliateUpsellPlans::route('/'),
            'create' => Pages\CreateAffiliateUpsellPlan::route('/create'),
            'view' => Pages\ViewAffiliateUpsellPlan::route('/{record}'),
            'edit' => Pages\EditAffiliateUpsellPlan::route('/{record}/edit'),
        ];
    }
}
