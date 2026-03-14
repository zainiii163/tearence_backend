<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobResource\Pages;
use App\Filament\Resources\JobResource\RelationManagers;
use App\Models\Job;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;

class JobResource extends Resource
{
    protected static ?string $model = Job::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Jobs & Vacancies';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Job Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('responsibilities')
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('requirements')
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('benefits')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('skills_needed')
                            ->helperText('Comma-separated skills'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Company Information')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('company_description')
                            ->columnSpanFull(),
                        
                        Forms\Components\Select::make('company_size')
                            ->options([
                                '1-10' => '1-10 employees',
                                '11-50' => '11-50 employees',
                                '51-200' => '51-200 employees',
                                '201-500' => '201-500 employees',
                                '500+' => '500+ employees',
                            ]),
                        
                        Forms\Components\TextInput::make('company_industry'),
                        
                        Forms\Components\TextInput::make('company_founded')
                            ->helperText('Year founded'),
                        
                        Forms\Components\TextInput::make('logo_url')
                            ->url()
                            ->helperText('Company logo URL'),
                        
                        Forms\Components\TextInput::make('company_website')
                            ->url(),
                        
                        Forms\Components\KeyValue::make('company_social')
                            ->keyLabel('Platform')
                            ->valueLabel('URL'),
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
                        
                        Forms\Components\TextInput::make('state'),
                        
                        Forms\Components\Textarea::make('address')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.000001),
                        
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.000001),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Job Details')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('work_type')
                            ->required()
                            ->options([
                                'Full-time' => 'Full-time',
                                'Part-time' => 'Part-time',
                                'Contract' => 'Contract',
                                'Freelance' => 'Freelance',
                                'Internship' => 'Internship',
                                'Temporary' => 'Temporary',
                            ]),
                        
                        Forms\Components\TextInput::make('salary_range')
                            ->helperText('e.g., 50000-75000'),
                        
                        Forms\Components\Select::make('currency')
                            ->default('USD')
                            ->options([
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                                'CAD' => 'CAD',
                                'AUD' => 'AUD',
                            ]),
                        
                        Forms\Components\Select::make('experience_level')
                            ->required()
                            ->options([
                                'entry' => 'Entry Level',
                                'mid' => 'Mid Level',
                                'senior' => 'Senior Level',
                                'executive' => 'Executive Level',
                            ]),
                        
                        Forms\Components\Select::make('education_level')
                            ->options([
                                'high_school' => 'High School',
                                'associate' => 'Associate Degree',
                                'bachelor' => 'Bachelor\'s Degree',
                                'master' => 'Master\'s Degree',
                                'doctorate' => 'Doctorate',
                            ]),
                        
                        Forms\Components\Toggle::make('remote_available'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Application Details')
                    ->schema([
                        Forms\Components\Select::make('application_method')
                            ->required()
                            ->options([
                                'email' => 'Email',
                                'website' => 'Website',
                                'phone' => 'Phone',
                                'in_person' => 'In Person',
                            ]),
                        
                        Forms\Components\TextInput::make('application_email')
                            ->email()
                            ->requiredIf('application_method', 'email'),
                        
                        Forms\Components\TextInput::make('application_phone'),
                        
                        Forms\Components\TextInput::make('application_website')
                            ->url()
                            ->requiredIf('application_method', 'website'),
                        
                        Forms\Components\Textarea::make('application_instructions')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Promotion')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->required()
                            ->options([
                                'pending_review' => 'Pending Review',
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'draft' => 'Draft',
                                'rejected' => 'Rejected',
                            ]),
                        
                        Forms\Components\Toggle::make('verified_employer'),
                        
                        Forms\Components\Select::make('promotion_type')
                            ->options([
                                'basic' => 'Basic',
                                'promoted' => 'Promoted',
                                'featured' => 'Featured',
                                'sponsored' => 'Sponsored',
                                'network' => 'Network-Wide Boost',
                            ]),
                        
                        Forms\Components\DateTimePicker::make('promotion_expires_at'),
                        
                        Forms\Components\DateTimePicker::make('posted_at'),
                        
                        Forms\Components\DateTimePicker::make('expires_at'),
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
                
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('work_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Full-time' => 'success',
                        'Part-time' => 'warning',
                        'Contract' => 'info',
                        'Freelance' => 'primary',
                        'Internship' => 'secondary',
                        'Temporary' => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending_review' => 'warning',
                        'active' => 'success',
                        'expired' => 'danger',
                        'draft' => 'gray',
                        'rejected' => 'danger',
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
                
                Tables\Columns\TextColumn::make('applications_count')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('posted_at')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending_review' => 'Pending Review',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'draft' => 'Draft',
                        'rejected' => 'Rejected',
                    ]),
                
                SelectFilter::make('work_type')
                    ->options([
                        'Full-time' => 'Full-time',
                        'Part-time' => 'Part-time',
                        'Contract' => 'Contract',
                        'Freelance' => 'Freelance',
                        'Internship' => 'Internship',
                        'Temporary' => 'Temporary',
                    ]),
                
                SelectFilter::make('experience_level')
                    ->options([
                        'entry' => 'Entry Level',
                        'mid' => 'Mid Level',
                        'senior' => 'Senior Level',
                        'executive' => 'Executive Level',
                    ]),
                
                SelectFilter::make('promotion_type')
                    ->options([
                        'basic' => 'Basic',
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        'network' => 'Network-Wide Boost',
                    ]),
                
                Filter::make('verified_employer')
                    ->query(fn (Builder $query): Builder => $query->where('verified_employer', true)),
                
                Filter::make('remote_available')
                    ->query(fn (Builder $query): Builder => $query->where('remote_available', true)),
                
                Filter::make('expired')
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<', now())),
                
                Filter::make('expires_soon')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('expires_at', [now(), now()->addDays(7)])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('view_applications')
                    ->label('Applications')
                    ->icon('heroicon-o-users')
                    ->url(fn (Job $record): string => route('filament.admin.resources.job-applications.index', ['job_id' => $record->id]))
                    ->openUrlInNewTab(),
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
            RelationManagers\SavesRelationManager::class,
            RelationManagers\ViewsRelationManager::class,
            RelationManagers\UpsellsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobs::route('/'),
            'create' => Pages\CreateJob::route('/create'),
            'view' => Pages\ViewJob::route('/{record}'),
            'edit' => Pages\EditJob::route('/{record}/edit'),
        ];
    }
}
