<?php

namespace App\Filament\Resources\JobSeekerResource\RelationManagers;

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
                Forms\Components\Select::make('job_id')
                    ->relationship('job', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'pending' => 'Pending',
                        'reviewed' => 'Reviewed',
                        'shortlisted' => 'Shortlisted',
                        'interviewed' => 'Interviewed',
                        'offered' => 'Offered',
                        'rejected' => 'Rejected',
                        'withdrawn' => 'Withdrawn',
                    ]),
                
                Forms\Components\Textarea::make('cover_letter')
                    ->columnSpanFull(),
                
                Forms\Components\Textarea::make('employer_notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('job.title')
            ->columns([
                Tables\Columns\TextColumn::make('job.title')
                    ->searchable()
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'reviewed' => 'blue',
                        'shortlisted' => 'green',
                        'interviewed' => 'orange',
                        'offered' => 'success',
                        'rejected' => 'danger',
                        'withdrawn' => 'warning',
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'reviewed' => 'Reviewed',
                        'shortlisted' => 'Shortlisted',
                        'interviewed' => 'Interviewed',
                        'offered' => 'Offered',
                        'rejected' => 'Rejected',
                        'withdrawn' => 'Withdrawn',
                    ]),
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
