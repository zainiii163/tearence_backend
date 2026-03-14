<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionPlanResource\Pages;
use App\Filament\Resources\PromotionPlanResource\RelationManagers;
use App\Models\PromotionPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotionPlanResource extends Resource
{
    protected static ?string $model = PromotionPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $navigationGroup = 'Funding System';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Section::make('Promotion Plan Information')
                    ->schema([
                        Forms\Components\TextInput::make('id')
                            ->required()
                            ->maxLength(50)
                            ->disabled(fn ($record) => $record !== null)
                            ->helperText('Unique plan identifier (e.g., basic, promoted, featured, sponsored)'),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Plan Name'),

                        Forms\Components\Textarea::make('description')
                            ->maxLength(500)
                            ->rows(3),

                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->step(0.01)
                            ->label('Price (USD)'),

                        Forms\Components\Select::make('currency')
                            ->options([
                                'USD' => 'USD - US Dollar',
                                'EUR' => 'EUR - Euro',
                                'GBP' => 'GBP - British Pound',
                            ])
                            ->default('USD')
                            ->required(),

                        Forms\Components\Repeater::make('features')
                            ->label('Features')
                            ->schema([
                                Forms\Components\TextInput::make('feature')
                                    ->required()
                                    ->label('Feature Description'),
                            ])
                            ->columns(1)
                            ->addActionLabel('Add Feature')
                            ->collapsible(),

                        Forms\Components\TextInput::make('visibility_multiplier')
                            ->label('Visibility Multiplier')
                            ->numeric()
                            ->default(1)
                            ->helperText('How much more visible this tier makes projects'),

                        Forms\Components\ColorPicker::make('badge_color')
                            ->label('Badge Color')
                            ->helperText('Color for the promotion badge'),

                        Forms\Components\TextInput::make('ribbon_text')
                            ->label('Ribbon Text')
                            ->maxLength(100)
                            ->helperText('Text to display on promotion ribbon'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Whether this plan is available for purchase'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Plan ID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50),

                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),

                Tables\Columns\TextColumn::make('visibility_multiplier')
                    ->label('Visibility')
                    ->formatStateUsing(fn ($state) => $state . 'x')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 5 => 'danger',
                        $state >= 3 => 'warning',
                        $state >= 2 => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\ColorColumn::make('badge_color')
                    ->label('Badge Color'),

                Tables\Columns\TextColumn::make('ribbon_text')
                    ->label('Ribbon')
                    ->badge()
                    ->color(fn ($record) => $record->badge_color ?? 'gray'),

                Tables\Columns\TextColumn::make('features')
                    ->label('Features')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' features' : 'None')
                    ->badge()
                    ->color(fn ($state) => is_array($state) && count($state) > 0 ? 'success' : 'danger'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Active Plans Only'),

                Tables\Filters\Filter::make('paid')
                    ->query(fn (Builder $query): Builder => $query->where('price', '>', 0))
                    ->label('Paid Plans Only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('toggle_active')
                    ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->action(fn ($record) => $record->update(['is_active' => !$record->is_active])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('price');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PromotionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotionPlans::route('/'),
            'create' => Pages\CreatePromotionPlan::route('/create'),
            'view' => Pages\ViewPromotionPlan::route('/{record}'),
            'edit' => Pages\EditPromotionPlan::route('/{record}/edit'),
        ];
    }
}
