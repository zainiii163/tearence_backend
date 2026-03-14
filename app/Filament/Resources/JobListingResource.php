<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobListingResource\Pages;
use App\Models\JobListing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobListingResource extends Resource
{
    protected static ?string $model = JobListing::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Jobs & Vacancies';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Job Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('job_category_id')
                            ->relationship('jobCategory', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('responsibilities')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('requirements')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('skills_needed')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Company Information')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_website')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('company_logo')
                            ->image()
                            ->directory('company-logos')
                            ->maxSize(2048),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Location & Work Type')
                    ->schema([
                        Forms\Components\TextInput::make('country')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(100),
                        Forms\Components\Select::make('work_type')
                            ->options([
                                'full_time' => 'Full Time',
                                'part_time' => 'Part Time',
                                'contract' => 'Contract',
                                'temporary' => 'Temporary',
                                'internship' => 'Internship',
                                'remote' => 'Remote',
                            ])
                            ->required(),
                        Forms\Components\Select::make('experience_level')
                            ->options([
                                'entry_level' => 'Entry Level',
                                'mid_level' => 'Mid Level',
                                'senior_level' => 'Senior Level',
                                'executive' => 'Executive',
                            ])
                            ->nullable(),
                        Forms\Components\Select::make('education_level')
                            ->options([
                                'high_school' => 'High School',
                                'associate' => 'Associate',
                                'bachelor' => 'Bachelor',
                                'master' => 'Master',
                                'phd' => 'PhD',
                            ])
                            ->nullable(),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Compensation & Benefits')
                    ->schema([
                        Forms\Components\TextInput::make('salary_range')
                            ->maxLength(100),
                        Forms\Components\Select::make('currency')
                            ->options([
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                                'CAD' => 'CAD',
                                'AUD' => 'AUD',
                            ])
                            ->default('USD'),
                        Forms\Components\Textarea::make('benefits')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Application Settings')
                    ->schema([
                        Forms\Components\Select::make('application_method')
                            ->options([
                                'email' => 'Email',
                                'website' => 'Website',
                                'platform' => 'Platform',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('application_email')
                            ->email()
                            ->requiredIf('application_method', 'email'),
                        Forms\Components\TextInput::make('application_url')
                            ->url()
                            ->requiredIf('application_method', 'website'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Status & Visibility')
                    ->schema([
                        Forms\Components\Toggle::make('is_urgent')
                            ->label('Urgent Hire'),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured'),
                        Forms\Components\Toggle::make('is_sponsored')
                            ->label('Sponsored'),
                        Forms\Components\Toggle::make('is_promoted')
                            ->label('Promoted'),
                        Forms\Components\Toggle::make('is_verified_employer')
                            ->label('Verified Employer'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active'),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->nullable(),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Statistics')
                    ->schema([
                        Forms\Components\TextInput::make('views_count')
                            ->label('Views Count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        Forms\Components\TextInput::make('applications_count')
                            ->label('Applications Count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jobCategory.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('country')
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_type')
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucwords($state, '_')))
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_urgent')
                    ->boolean(),
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable(),
                Tables\Columns\TextColumn::make('applications_count')
                    ->label('Applications')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('job_category_id')
                    ->relationship('jobCategory', 'name')
                    ->label('Category'),
                Tables\Filters\SelectFilter::make('work_type')
                    ->options([
                        'full_time' => 'Full Time',
                        'part_time' => 'Part Time',
                        'contract' => 'Contract',
                        'temporary' => 'Temporary',
                        'internship' => 'Internship',
                        'remote' => 'Remote',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                Tables\Filters\TernaryFilter::make('is_urgent')
                    ->label('Urgent'),
                Tables\Filters\Filter::make('expires_at')
                    ->form([
                        Forms\Components\DatePicker::make('expires_from')
                            ->label('Expires From'),
                        Forms\Components\DatePicker::make('expires_until')
                            ->label('Expires Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['expires_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expires_at', '>=', $date),
                            )
                            ->when(
                                $data['expires_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expires_at', '<=', $date),
                            );
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
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-mark')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            'applications' => \App\Filament\Relations\JobApplicationsRelationManager::class,
            'upsells' => \App\Filament\Relations\JobUpsellsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobListings::route('/'),
            'create' => Pages\CreateJobListing::route('/create'),
            'view' => Pages\ViewJobListing::route('/{record}'),
            'edit' => Pages\EditJobListing::route('/{record}/edit'),
        ];
    }
}
