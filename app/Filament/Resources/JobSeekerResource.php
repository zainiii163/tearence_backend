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
                        
                        Forms\Components\TextInput::make('full_name')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('profession')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('bio')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('profile_photo_url')
                            ->url()
                            ->helperText('Profile photo URL'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('country')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('city')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('state')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.000001),
                        
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.000001),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Professional Details')
                    ->schema([
                        Forms\Components\Select::make('years_of_experience')
                            ->required()
                            ->options([
                                '0-1' => 'Less than 1 year',
                                '1-3' => '1-3 years',
                                '3-5' => '3-5 years',
                                '5-10' => '5-10 years',
                                '10+' => '10+ years',
                            ]),
                        
                        Forms\Components\TextInput::make('key_skills')
                            ->helperText('Comma-separated skills'),
                        
                        Forms\Components\Select::make('education_level')
                            ->options([
                                'high_school' => 'High School',
                                'associate' => 'Associate Degree',
                                'bachelor' => 'Bachelor\'s Degree',
                                'master' => 'Master\'s Degree',
                                'doctorate' => 'Doctorate',
                            ]),
                        
                        Forms\Components\Textarea::make('education_details')
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('experience_summary')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Job Preferences')
                    ->schema([
                        Forms\Components\TextInput::make('desired_role')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('salary_expectation')
                            ->helperText('e.g., 80000-120000'),
                        
                        Forms\Components\Select::make('work_type_preference')
                            ->options([
                                'Full-time' => 'Full-time',
                                'Part-time' => 'Part-time',
                                'Contract' => 'Contract',
                                'Freelance' => 'Freelance',
                            ]),
                        
                        Forms\Components\Toggle::make('remote_availability'),
                        
                        Forms\Components\Repeater::make('preferred_locations')
                            ->schema([
                                Forms\Components\TextInput::make('location')
                                    ->placeholder('e.g., New York, USA'),
                            ])
                            ->label('Preferred Locations'),
                        
                        Forms\Components\Repeater::make('preferred_industries')
                            ->schema([
                                Forms\Components\TextInput::make('industry')
                                    ->placeholder('e.g., Technology, Healthcare'),
                            ])
                            ->label('Preferred Industries'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Portfolio & Links')
                    ->schema([
                        Forms\Components\TextInput::make('portfolio_link')
                            ->url()
                            ->helperText('Portfolio website URL'),
                        
                        Forms\Components\TextInput::make('linkedin_link')
                            ->url()
                            ->helperText('LinkedIn profile URL'),
                        
                        Forms\Components\TextInput::make('github_link')
                            ->url()
                            ->helperText('GitHub profile URL'),
                        
                        Forms\Components\TextInput::make('cv_file_url')
                            ->url()
                            ->helperText('CV file URL'),
                        
                        Forms\Components\Repeater::make('additional_links')
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->placeholder('Link label'),
                                Forms\Components\TextInput::make('url')
                                    ->url()
                                    ->placeholder('Link URL'),
                            ])
                            ->label('Additional Links'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Promotion')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'hidden' => 'Hidden',
                            ]),
                        
                        Forms\Components\Toggle::make('terms_accepted'),
                        
                        Forms\Components\Toggle::make('accurate_info'),
                        
                        Forms\Components\Toggle::make('verified_profile'),
                        
                        Forms\Components\Select::make('promotion_type')
                            ->options([
                                'basic' => 'Basic',
                                'promoted' => 'Promoted',
                                'featured' => 'Featured',
                                'sponsored' => 'Sponsored',
                                'network' => 'Network-Wide Boost',
                            ]),
                        
                        Forms\Components\DateTimePicker::make('promotion_expires_at'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Analytics')
                    ->schema([
                        Forms\Components\TextInput::make('views')
                            ->numeric()
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('contact_count')
                            ->numeric()
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('profile_views')
                            ->numeric()
                            ->disabled(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('profession')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('years_of_experience')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '0-1' => 'gray',
                        '1-3' => 'blue',
                        '3-5' => 'green',
                        '5-10' => 'orange',
                        '10+' => 'purple',
                    }),
                
                Tables\Columns\IconColumn::make('remote_availability')
                    ->boolean()
                    ->label('Remote'),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        'hidden' => 'danger',
                    }),
                
                Tables\Columns\TextColumn::make('promotion_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'basic' => 'gray',
                        'promoted' => 'info',
                        'featured' => 'warning',
                        'sponsored' => 'success',
                        'network' => 'primary',
                    }),
                
                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('contact_count')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'hidden' => 'Hidden',
                    ]),
                
                Tables\Filters\SelectFilter::make('years_of_experience')
                    ->options([
                        '0-1' => 'Less than 1 year',
                        '1-3' => '1-3 years',
                        '3-5' => '3-5 years',
                        '5-10' => '5-10 years',
                        '10+' => '10+ years',
                    ]),
                
                Tables\Filters\SelectFilter::make('education_level')
                    ->options([
                        'high_school' => 'High School',
                        'associate' => 'Associate Degree',
                        'bachelor' => 'Bachelor\'s Degree',
                        'master' => 'Master\'s Degree',
                        'doctorate' => 'Doctorate',
                    ]),
                
                Tables\Filters\SelectFilter::make('promotion_type')
                    ->options([
                        'basic' => 'Basic',
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        'network' => 'Network-Wide Boost',
                    ]),
                
                Tables\Filters\Filter::make('verified_profile')
                    ->query(fn (Builder $query): Builder => $query->where('verified_profile', true)),
                
                Tables\Filters\Filter::make('remote_available')
                    ->query(fn (Builder $query): Builder => $query->where('remote_availability', true)),
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
