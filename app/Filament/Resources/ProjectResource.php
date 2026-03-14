<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Funding System';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Project Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->label('Project Owner'),

                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Project::class, 'slug', ignoreRecord: true)
                            ->disabled(),

                        Forms\Components\Textarea::make('tagline')
                            ->maxLength(500)
                            ->rows(2),

                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('story')
                            ->columnSpanFull()
                            ->label('Project Story'),

                        Forms\Components\RichEditor::make('vision')
                            ->columnSpanFull()
                            ->label('Project Vision'),

                        Forms\Components\Select::make('project_type')
                            ->options([
                                'technology' => 'Technology',
                                'social' => 'Social Impact',
                                'environment' => 'Environment',
                                'healthcare' => 'Healthcare',
                                'education' => 'Education',
                                'arts' => 'Arts & Culture',
                                'business' => 'Business',
                                'other' => 'Other',
                            ])
                            ->required(),

                        Forms\Components\Select::make('funding_model')
                            ->options([
                                'donation' => 'Donation',
                                'reward' => 'Reward-based',
                                'equity' => 'Equity',
                                'loan' => 'Loan',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('funding_goal')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->step(0.01),

                        Forms\Components\TextInput::make('current_funding')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->step(0.01),

                        Forms\Components\Select::make('currency')
                            ->options([
                                'USD' => 'USD - US Dollar',
                                'EUR' => 'EUR - Euro',
                                'GBP' => 'GBP - British Pound',
                                'CAD' => 'CAD - Canadian Dollar',
                                'AUD' => 'AUD - Australian Dollar',
                            ])
                            ->default('USD')
                            ->required(),

                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Campaign Start Date'),

                        Forms\Components\DateTimePicker::make('end_date')
                            ->label('Campaign End Date'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('draft'),

                        Forms\Components\Select::make('promotion_tier')
                            ->options([
                                'basic' => 'Basic',
                                'promoted' => 'Promoted',
                                'featured' => 'Featured',
                                'sponsored' => 'Sponsored',
                            ])
                            ->required()
                            ->default('basic'),

                        Forms\Components\DateTimePicker::make('submitted_at')
                            ->label('Submission Date'),

                        Forms\Components\KeyValue::make('metadata')
                            ->label('Additional Metadata')
                            ->columnSpanFull(),
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
                    ->limit(50),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('project_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'technology' => 'primary',
                        'social' => 'success',
                        'environment' => 'warning',
                        'healthcare' => 'danger',
                        'education' => 'info',
                        'arts' => 'secondary',
                        'business' => 'gray',
                        'other' => 'gray',
                    }),

                Tables\Columns\TextColumn::make('funding_model')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'donation' => 'primary',
                        'reward' => 'success',
                        'equity' => 'warning',
                        'loan' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('funding_goal')
                    ->money()
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_funding')
                    ->money()
                    ->sortable(),

                Tables\Columns\TextColumn::make('funding_progress')
                    ->label('Progress')
                    ->getStateUsing(fn (Project $record): string => 
                        $record->funding_goal > 0 
                            ? round(($record->current_funding / $record->funding_goal) * 100, 1) . '%'
                            : '0%'
                    )
                    ->badge()
                    ->color(function (string $state): string {
                        $progress = (float) str_replace('%', '', $state);
                        return $progress >= 100 ? 'success' : 
                               ($progress >= 50 ? 'warning' : 'danger');
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'completed' => 'primary',
                        'cancelled' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('promotion_tier')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'basic' => 'gray',
                        'promoted' => 'success',
                        'featured' => 'warning',
                        'sponsored' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('project_type')
                    ->options([
                        'technology' => 'Technology',
                        'social' => 'Social Impact',
                        'environment' => 'Environment',
                        'healthcare' => 'Healthcare',
                        'education' => 'Education',
                        'arts' => 'Arts & Culture',
                        'business' => 'Business',
                        'other' => 'Other',
                    ]),

                Tables\Filters\SelectFilter::make('funding_model')
                    ->options([
                        'donation' => 'Donation',
                        'reward' => 'Reward-based',
                        'equity' => 'Equity',
                        'loan' => 'Loan',
                    ]),

                Tables\Filters\SelectFilter::make('promotion_tier')
                    ->options([
                        'basic' => 'Basic',
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                    ]),

                Tables\Filters\Filter::make('active_campaigns')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'active'))
                    ->label('Active Campaigns Only'),

                Tables\Filters\Filter::make('needs_review')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'draft')->whereNotNull('submitted_at'))
                    ->label('Needs Review'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Project $record) => $record->update(['status' => 'active']))
                    ->visible(fn (Project $record): bool => $record->status === 'draft' && $record->submitted_at),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Project $record) => $record->update(['status' => 'cancelled']))
                    ->visible(fn (Project $record): bool => $record->status === 'draft' && $record->submitted_at),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['status' => 'active']))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FundingDetailRelationManager::class,
            RelationManagers\VerificationRelationManager::class,
            RelationManagers\RewardsRelationManager::class,
            RelationManagers\MarketingAssetsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
            RelationManagers\PromotionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'draft')->whereNotNull('submitted_at')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
