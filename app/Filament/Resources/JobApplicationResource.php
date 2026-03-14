<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobApplicationResource\Pages;
use App\Models\JobApplication;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobApplicationResource extends Resource
{
    protected static ?string $model = JobApplication::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Jobs & Vacancies';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Application Information')
                    ->schema([
                        Forms\Components\Select::make('job_listing_id')
                            ->relationship('jobListing', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('full_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\RichEditor::make('cover_letter')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('cv_file')
                            ->directory('job-applications/cv')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(5120),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Status & Notes')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'viewed' => 'Viewed',
                                'shortlisted' => 'Shortlisted',
                                'rejected' => 'Rejected',
                                'hired' => 'Hired',
                            ])
                            ->required(),
                        Forms\Components\RichEditor::make('employer_notes')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        Forms\Components\DateTimePicker::make('applied_at')
                            ->required()
                            ->default(now()),
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
                Tables\Columns\TextColumn::make('jobListing.title')
                    ->label('Job Title')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'viewed' => 'info',
                        'shortlisted' => 'success',
                        'rejected' => 'danger',
                        'hired' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('applied_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'viewed' => 'Viewed',
                        'shortlisted' => 'Shortlisted',
                        'rejected' => 'Rejected',
                        'hired' => 'Hired',
                    ]),
                Tables\Filters\Filter::make('applied_at')
                    ->form([
                        Forms\Components\DatePicker::make('applied_from')
                            ->label('Applied From'),
                        Forms\Components\DatePicker::make('applied_until')
                            ->label('Applied Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['applied_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('applied_at', '>=', $date),
                            )
                            ->when(
                                $data['applied_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('applied_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('mark_as_viewed')
                    ->label('Mark as Viewed')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->action(fn (JobApplication $record) => $record->markAsViewed())
                    ->visible(fn (JobApplication $record) => $record->status === 'pending'),
                Tables\Actions\Action::make('mark_as_shortlisted')
                    ->label('Shortlist')
                    ->icon('heroicon-o-star')
                    ->color('success')
                    ->action(fn (JobApplication $record) => $record->markAsShortlisted())
                    ->visible(fn (JobApplication $record) => in_array($record->status, ['pending', 'viewed'])),
                Tables\Actions\Action::make('mark_as_rejected')
                    ->label('Reject')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(fn (JobApplication $record) => $record->markAsRejected())
                    ->visible(fn (JobApplication $record) => !in_array($record->status, ['rejected', 'hired'])),
                Tables\Actions\Action::make('mark_as_hired')
                    ->label('Hire')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->action(fn (JobApplication $record) => $record->markAsHired())
                    ->visible(fn (JobApplication $record) => !in_array($record->status, ['hired'])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_as_viewed')
                        ->label('Mark as Viewed')
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each->markAsViewed()),
                    Tables\Actions\BulkAction::make('mark_as_shortlisted')
                        ->label('Shortlist')
                        ->icon('heroicon-o-star')
                        ->action(fn ($records) => $records->each->markAsShortlisted()),
                    Tables\Actions\BulkAction::make('mark_as_rejected')
                        ->label('Reject')
                        ->icon('heroicon-o-x-mark')
                        ->action(fn ($records) => $records->each->markAsRejected()),
                ]),
            ])
            ->defaultSort('applied_at', 'desc');
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
            'index' => Pages\ListJobApplications::route('/'),
            'create' => Pages\CreateJobApplication::route('/create'),
            'view' => Pages\ViewJobApplication::route('/{record}'),
            'edit' => Pages\EditJobApplication::route('/{record}/edit'),
        ];
    }
}
