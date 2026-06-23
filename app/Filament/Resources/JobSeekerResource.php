<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobSeekerResource\Pages;
use App\Filament\Resources\JobSeekerResource\RelationManagers;
use App\Models\JobSeeker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobSeekerResource extends Resource
{
    protected static ?string $model = JobSeeker::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Jobs & Vacancies';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(200),
                        
                        Forms\Components\Textarea::make('bio')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('profile_photo')
                            ->url()
                            ->helperText('Profile photo URL'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('country')
                            ->required()
                            ->maxLength(100),
                        
                        Forms\Components\TextInput::make('city')
                            ->required()
                            ->maxLength(100),
                        
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.000001),
                        
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.000001),
                        
                        Forms\Components\TextInput::make('location_name')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Professional Details')
                    ->schema([
                        Forms\Components\Select::make('experience_level')
                            ->options([
                                'entry' => 'Entry Level',
                                'junior' => 'Junior',
                                'mid' => 'Mid-Level',
                                'senior' => 'Senior',
                                'executive' => 'Executive',
                            ]),
                        
                        Forms\Components\TextInput::make('years_of_experience')
                            ->numeric(),
                        
                        Forms\Components\Select::make('education_level')
                            ->options([
                                'high_school' => 'High School',
                                'diploma' => 'Diploma',
                                'bachelor' => 'Bachelor\'s Degree',
                                'master' => 'Master\'s Degree',
                                'phd' => 'PhD',
                                'none' => 'None',
                            ]),
                        
                        Forms\Components\Textarea::make('key_skills')
                            ->helperText('Comma-separated skills')
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('desired_role')
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('industries_interested')
                            ->helperText('Comma-separated industries')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Job Preferences')
                    ->schema([
                        Forms\Components\Select::make('preferred_work_type')
                            ->options([
                                'full_time' => 'Full-time',
                                'part_time' => 'Part-time',
                                'contract' => 'Contract',
                                'temporary' => 'Temporary',
                                'internship' => 'Internship',
                                'remote' => 'Remote',
                                'any' => 'Any',
                            ]),
                        
                        Forms\Components\Toggle::make('remote_availability')
                            ->label('Remote Available'),
                        
                        Forms\Components\Toggle::make('willing_to_relocate')
                            ->label('Willing to Relocate'),
                        
                        Forms\Components\TextInput::make('salary_expectation_min')
                            ->numeric()
                            ->label('Min Salary Expectation'),
                        
                        Forms\Components\TextInput::make('salary_expectation_max')
                            ->numeric()
                            ->label('Max Salary Expectation'),
                        
                        Forms\Components\Select::make('salary_currency')
                            ->options([
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                            ])
                            ->default('USD'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Portfolio & Links')
                    ->schema([
                        Forms\Components\TextInput::make('portfolio_link')
                            ->url()
                            ->helperText('Portfolio website URL'),
                        
                        Forms\Components\TextInput::make('linkedin_url')
                            ->url()
                            ->helperText('LinkedIn profile URL'),
                        
                        Forms\Components\TextInput::make('github_url')
                            ->url()
                            ->helperText('GitHub profile URL'),
                        
                        Forms\Components\TextInput::make('website_url')
                            ->url()
                            ->helperText('Website URL'),
                        
                        Forms\Components\TextInput::make('cv_file')
                            ->helperText('CV file URL'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Promotion')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active'),
                        
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured'),
                        
                        Forms\Components\Toggle::make('is_sponsored')
                            ->label('Sponsored'),
                        
                        Forms\Components\Toggle::make('is_promoted')
                            ->label('Promoted'),
                        
                        Forms\Components\DateTimePicker::make('featured_until'),
                        
                        Forms\Components\DateTimePicker::make('sponsored_until'),
                        
                        Forms\Components\DateTimePicker::make('promoted_until'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Analytics')
                    ->schema([
                        Forms\Components\TextInput::make('views')
                            ->numeric()
                            ->disabled()
                            ->label('Views'),
                        
                        Forms\Components\TextInput::make('contact_count')
                            ->numeric()
                            ->disabled()
                            ->label('Profile Contacts'),
                        
                        Forms\Components\TextInput::make('saves_count')
                            ->numeric()
                            ->disabled()
                            ->label('Saves'),
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
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('experience_level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'entry' => 'gray',
                        'junior' => 'blue',
                        'mid' => 'green',
                        'senior' => 'orange',
                        'executive' => 'purple',
                    }),
                
                Tables\Columns\IconColumn::make('remote_availability')
                    ->boolean()
                    ->label('Remote'),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                
                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable()
                    ->label('Views'),
                
                Tables\Columns\TextColumn::make('contact_count')
                    ->numeric()
                    ->sortable()
                    ->label('Contacts'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('experience_level')
                    ->options([
                        'entry' => 'Entry Level',
                        'junior' => 'Junior',
                        'mid' => 'Mid-Level',
                        'senior' => 'Senior',
                        'executive' => 'Executive',
                    ]),
                
                Tables\Filters\SelectFilter::make('education_level')
                    ->options([
                        'high_school' => 'High School',
                        'diploma' => 'Diploma',
                        'bachelor' => 'Bachelor\'s Degree',
                        'master' => 'Master\'s Degree',
                        'phd' => 'PhD',
                        'none' => 'None',
                    ]),
                
                Tables\Filters\Filter::make('is_featured')
                    ->query(fn (Builder $query): Builder => $query->where('is_featured', true))
                    ->label('Featured'),
                
                Tables\Filters\Filter::make('is_sponsored')
                    ->query(fn (Builder $query): Builder => $query->where('is_sponsored', true))
                    ->label('Sponsored'),
                
                Tables\Filters\Filter::make('remote_availability')
                    ->query(fn (Builder $query): Builder => $query->where('remote_availability', true))
                    ->label('Remote Available'),
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
            RelationManagers\ApplicationsRelationManager::class,
            RelationManagers\UpsellsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobSeekers::route('/'),
            'create' => Pages\CreateJobSeeker::route('/create'),
            'view' => Pages\ViewJobSeeker::route('/{record}'),
            'edit' => Pages\EditJobSeeker::route('/{record}/edit'),
        ];
    }
}
