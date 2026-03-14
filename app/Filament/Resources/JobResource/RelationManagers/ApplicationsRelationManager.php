<?php

namespace App\Filament\Resources\JobResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('full_name')
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(20),
                
                Forms\Components\Textarea::make('cover_letter')
                    ->columnSpanFull(),
                
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'submitted' => 'Submitted',
                        'viewed' => 'Viewed',
                        'shortlisted' => 'Shortlisted',
                        'interview_scheduled' => 'Interview Scheduled',
                        'rejected' => 'Rejected',
                        'hired' => 'Hired',
                        'withdrawn' => 'Withdrawn',
                    ]),
                
                Forms\Components\Textarea::make('employer_notes')
                    ->columnSpanFull(),
                
                Forms\Components\Textarea::make('next_steps')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'submitted' => 'blue',
                        'viewed' => 'yellow',
                        'shortlisted' => 'green',
                        'interview_scheduled' => 'purple',
                        'rejected' => 'red',
                        'hired' => 'emerald',
                        'withdrawn' => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('expected_salary')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('interview_date')
                    ->dateTime()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Applied At'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'viewed' => 'Viewed',
                        'shortlisted' => 'Shortlisted',
                        'interview_scheduled' => 'Interview Scheduled',
                        'rejected' => 'Rejected',
                        'hired' => 'Hired',
                        'withdrawn' => 'Withdrawn',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
