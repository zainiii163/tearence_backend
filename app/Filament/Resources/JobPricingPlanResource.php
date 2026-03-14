<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobPricingPlanResource\Pages;
use App\Models\JobPricingPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobPricingPlanResource extends Resource
{
    protected static ?string $model = JobPricingPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Jobs & Vacancies';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Plan Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),
                        
                        Forms\Components\Select::make('currency')
                            ->default('USD')
                            ->options([
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                                'CAD' => 'CAD',
                                'AUD' => 'AUD',
                            ]),
                        
                        Forms\Components\Select::make('period')
                            ->required()
                            ->options([
                                'month' => 'Per Month',
                                'week' => 'Per Week',
                                'day' => 'Per Day',
                                'year' => 'Per Year',
                            ]),
                        
                        Forms\Components\TextInput::make('duration_months')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->label('Duration (Months)'),
                        
                        Forms\Components\TextInput::make('visibility_multiplier')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->label('Visibility Multiplier (1x, 2x, 3x, etc.)'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Features')
                    ->schema([
                        Forms\Components\Repeater::make('features')
                            ->schema([
                                Forms\Components\TextInput::make('feature')
                                    ->required()
                                    ->placeholder('e.g., Highlighted listing'),
                            ])
                            ->label('Features')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->label('Active')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('recommended')
                            ->label('Recommended'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('period')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'month' => 'primary',
                        'week' => 'success',
                        'day' => 'warning',
                        'year' => 'info',
                    }),
                
                Tables\Columns\TextColumn::make('duration_months')
                    ->numeric()
                    ->suffix(' months')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('visibility_multiplier')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 10 => 'purple',
                        $state >= 5 => 'success',
                        $state >= 3 => 'warning',
                        $state >= 2 => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => $state . 'x')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('recommended')
                    ->boolean()
                    ->label('Recommended'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('period')
                    ->options([
                        'month' => 'Per Month',
                        'week' => 'Per Week',
                        'day' => 'Per Day',
                        'year' => 'Per Year',
                    ]),
                
                Tables\Filters\TernaryFilter::make('active')
                    ->label('Active'),
                
                Tables\Filters\TernaryFilter::make('recommended')
                    ->label('Recommended'),
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
            'index' => Pages\ListJobPricingPlans::route('/'),
            'create' => Pages\CreateJobPricingPlan::route('/create'),
            'view' => Pages\ViewJobPricingPlan::route('/{record}'),
            'edit' => Pages\EditJobPricingPlan::route('/{record}/edit'),
        ];
    }
}
