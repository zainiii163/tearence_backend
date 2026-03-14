<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FundingProjectResource\Pages;
use App\Models\FundingProject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FundingProjectResource extends Resource
{
    protected static ?string $model = FundingProject::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $navigationGroup = 'Fundraising';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Project Type & Basic Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('project_type')
                            ->options([
                                'personal' => 'Personal Project',
                                'startup' => 'Startup / Business Project',
                                'community' => 'Community / Charity Project',
                                'creative' => 'Creative / Innovation Project',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('category', null)),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Project title should be catchy and descriptive'),
                        Forms\Components\TextInput::make('tagline')
                            ->maxLength(80)
                            ->helperText('Short tagline (max 80 characters)'),
                        Forms\Components\Select::make('category')
                            ->options([
                                'technology' => 'Technology',
                                'creative_arts' => 'Creative Arts',
                                'community_social_impact' => 'Community & Social Impact',
                                'health_wellness' => 'Health & Wellness',
                                'education' => 'Education',
                                'real_estate' => 'Real Estate',
                                'environment' => 'Environment',
                                'startups_business' => 'Startups & Business',
                                'other' => 'Other',
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Project Story & Vision')
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull()
                            ->helperText('Detailed project description'),
                        Forms\Components\RichEditor::make('problem_solving')
                            ->columnSpanFull()
                            ->helperText('What problem are you solving?'),
                        Forms\Components\RichEditor::make('vision_mission')
                            ->columnSpanFull()
                            ->helperText('Your vision and mission'),
                        Forms\Components\RichEditor::make('why_now')
                            ->columnSpanFull()
                            ->helperText('Why this matters now'),
                        Forms\Components\Repeater::make('team_members')
                            ->schema([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\TextInput::make('role')->required(),
                                Forms\Components\FileUpload::make('photo')
                                    ->image()
                                    ->directory('team-photos')
                                    ->maxSize(1024)
                                    ->nullable(),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Media & Assets')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->directory('funding-covers')
                            ->required()
                            ->maxSize(2048)
                            ->helperText('Project cover image (required)'),
                        Forms\Components\FileUpload::make('additional_images')
                            ->image()
                            ->directory('funding-gallery')
                            ->multiple()
                            ->maxFiles(5)
                            ->maxSize(1024)
                            ->helperText('Additional images (optional, up to 5)'),
                        Forms\Components\TextInput::make('pitch_video')
                            ->url()
                            ->helperText('Pitch video URL (YouTube, Vimeo, etc.)'),
                        Forms\Components\FileUpload::make('documents')
                            ->directory('funding-docs')
                            ->multiple()
                            ->maxFiles(10)
                            ->maxSize(5120)
                            ->helperText('Documents (pitch deck, financials, etc.)'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Funding Details')
                    ->schema([
                        Forms\Components\TextInput::make('funding_goal')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->prefix('$'),
                        Forms\Components\Select::make('currency')
                            ->options(['USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR'])
                            ->default('USD')
                            ->required(),
                        Forms\Components\TextInput::make('minimum_contribution')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->prefix('$')
                            ->helperText('Minimum contribution amount'),
                        Forms\Components\Select::make('funding_model')
                            ->options([
                                'donation' => 'Donation',
                                'reward' => 'Reward-based',
                                'equity' => 'Equity (future)',
                                'loan' => 'Loan-based (future)',
                                'hybrid' => 'Hybrid',
                            ])
                            ->required(),
                        Forms\Components\Repeater::make('use_of_funds')
                            ->schema([
                                Forms\Components\TextInput::make('item')->required(),
                                Forms\Components\TextInput::make('amount')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$'),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['item'] ?? null)
                            ->columnSpanFull()
                            ->helperText('Breakdown of how funds will be used'),
                        Forms\Components\Repeater::make('milestones')
                            ->schema([
                                Forms\Components\TextInput::make('milestone')->required(),
                                Forms\Components\DatePicker::make('expected_date')->required(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['milestone'] ?? null)
                            ->columnSpanFull()
                            ->helperText('Project timeline and milestones'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location & Contact')
                    ->schema([
                        Forms\Components\TextInput::make('country')->required(),
                        Forms\Components\TextInput::make('city'),
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->helperText('Project website'),
                        Forms\Components\Repeater::make('social_links')
                            ->schema([
                                Forms\Components\Select::make('platform')
                                    ->options([
                                        'facebook' => 'Facebook',
                                        'twitter' => 'Twitter',
                                        'linkedin' => 'LinkedIn',
                                        'instagram' => 'Instagram',
                                        'youtube' => 'YouTube',
                                        'other' => 'Other',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('url')
                                    ->url()
                                    ->required(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['platform'] ?? null)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Verification & Trust')
                    ->schema([
                        Forms\Components\FileUpload::make('identity_verification')
                            ->directory('verification-docs')
                            ->maxSize(2048)
                            ->helperText('ID verification document'),
                        Forms\Components\TextInput::make('business_registration_number')
                            ->helperText('Business registration number (if applicable)'),
                        Forms\Components\FileUpload::make('business_registration_document')
                            ->directory('business-docs')
                            ->maxSize(2048)
                            ->helperText('Business registration document'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Project Status & Promotion')
                    ->schema([
                        Forms\Components\DatePicker::make('funding_starts_at')
                            ->default(now())
                            ->helperText('When funding starts'),
                        Forms\Components\DatePicker::make('funding_ends_at')
                            ->helperText('When funding ends (optional)'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->helperText('Project is active and visible'),
                        Forms\Components\Toggle::make('is_verified')
                            ->helperText('Project is verified'),
                        Forms\Components\Toggle::make('is_featured')
                            ->helperText('Featured project'),
                        Forms\Components\Toggle::make('is_promoted')
                            ->helperText('Promoted project'),
                        Forms\Components\Toggle::make('is_sponsored')
                            ->helperText('Sponsored project'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(50),
                Tables\Columns\TextColumn::make('tagline')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('project_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'personal' => 'gray',
                        'startup' => 'blue',
                        'community' => 'green',
                        'creative' => 'purple',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('funding_goal')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount_raised')
                    ->money()
                    ->sortable()
                    ->getStateUsing(fn (FundingProject $record): string => 
                        '$' . number_format($record->amount_raised, 2)
                    ),
                Tables\Columns\TextColumn::make('funding_percentage')
                    ->label('Funded %')
                    ->getStateUsing(fn (FundingProject $record): string => 
                        round($record->funding_percentage, 1) . '%'
                    )
                    ->sortable()
                    ->color(fn (FundingProject $record): string => 
                        $record->funding_percentage >= 100 ? 'success' : 
                        ($record->funding_percentage >= 50 ? 'warning' : 'danger')
                    ),
                Tables\Columns\TextColumn::make('backer_count')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('days_remaining')
                    ->getStateUsing(fn (FundingProject $record): ?int => $record->days_remaining)
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_promoted')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_sponsored')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('funding_ends_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('project_type')
                    ->options([
                        'personal' => 'Personal Project',
                        'startup' => 'Startup',
                        'community' => 'Community/Charity',
                        'creative' => 'Creative/Innovation',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'technology' => 'Technology',
                        'creative_arts' => 'Creative Arts',
                        'community_social_impact' => 'Community & Social Impact',
                        'health_wellness' => 'Health & Wellness',
                        'education' => 'Education',
                        'real_estate' => 'Real Estate',
                        'environment' => 'Environment',
                        'startups_business' => 'Startups & Business',
                        'other' => 'Other',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Projects')
                    ->trueLabel('Only Active')
                    ->falseLabel('Only Inactive')
                    ->nullable(),
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Verified Projects')
                    ->trueLabel('Only Verified')
                    ->falseLabel('Only Unverified')
                    ->nullable(),
                Tables\Filters\SelectFilter::make('funding_model')
                    ->options([
                        'donation' => 'Donation',
                        'reward' => 'Reward-based',
                        'equity' => 'Equity',
                        'loan' => 'Loan-based',
                        'hybrid' => 'Hybrid',
                    ]),
                Tables\Filters\Filter::make('funding_goal_range')
                    ->form([
                        Forms\Components\TextInput::make('min_goal')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('max_goal')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->query(function (array $data): Builder {
                        return $query
                            ->when(
                                $data['min_goal'],
                                fn (Builder $query, $amount): Builder => $query->where('funding_goal', '>=', $amount)
                            )
                            ->when(
                                $data['max_goal'],
                                fn (Builder $query, $amount): Builder => $query->where('funding_goal', '<=', $amount)
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (FundingProject $record) {
                        // Check if project has pledges
                        if ($record->pledges()->count() > 0) {
                            // Prevent deletion if there are completed pledges
                            $completedPledges = $record->pledges()->where('status', 'completed')->count();
                            if ($completedPledges > 0) {
                                throw new \Exception('Cannot delete project with completed pledges. Consider archiving instead.');
                            }
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records) {
                            foreach ($records as $record) {
                                if ($record->pledges()->where('status', 'completed')->count() > 0) {
                                    throw new \Exception('Cannot delete projects with completed pledges.');
                                }
                            }
                        }),
                    Tables\Actions\BulkAction::make('mark_verified')
                        ->label('Mark Verified')
                        ->icon('heroicon-o-check-badge')
                        ->action(fn (Collection $records) => $records->each->update(['is_verified' => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('mark_featured')
                        ->label('Mark Featured')
                        ->icon('heroicon-o-star')
                        ->action(fn (Collection $records) => $records->each->update(['is_featured' => true]))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFundingProjects::route('/'),
            'create' => Pages\CreateFundingProject::route('/create'),
            'edit' => Pages\EditFundingProject::route('/{record}/edit'),
        ];
    }
}
