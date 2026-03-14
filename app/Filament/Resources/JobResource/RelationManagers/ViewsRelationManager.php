<?php

namespace App\Filament\Resources\JobResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ViewsRelationManager extends RelationManager
{
    protected static string $relationship = 'views';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Viewed By')
                    ->searchable()
                    ->default('Anonymous'),
                
                Tables\Columns\TextColumn::make('ip_address')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('country')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('city')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('device_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'desktop' => 'success',
                        'mobile' => 'warning',
                        'tablet' => 'info',
                    }),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Viewed At'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('device_type')
                    ->options([
                        'desktop' => 'Desktop',
                        'mobile' => 'Mobile',
                        'tablet' => 'Tablet',
                    ]),
                
                Tables\Filters\SelectFilter::make('country')
                    ->searchable()
                    ->preload()
                    ->options(fn (): array => 
                        static::getRelation('views')->getQuery()->distinct()->pluck('country')->filter()->toArray()
                    ),
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
