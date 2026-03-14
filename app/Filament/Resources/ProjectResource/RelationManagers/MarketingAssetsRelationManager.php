<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MarketingAssetsRelationManager extends RelationManager
{
    protected static string $relationship = 'marketingAssets';

    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Section::make('Marketing Assets')
                    ->schema([
                        Forms\Components\TextInput::make('pitch_video_url')
                            ->label('Pitch Video URL')
                            ->url()
                            ->maxLength(500)
                            ->helperText('YouTube, Vimeo, or other video platform URL'),

                        Forms\Components\KeyValue::make('documents')
                            ->label('Documents')
                            ->columnSpanFull()
                            ->helperText('Additional marketing documents and materials'),
                    ])
                    ->columns(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Asset ID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('pitch_video_url')
                    ->label('Pitch Video')
                    ->formatStateUsing(fn ($state) => $state ? 'Available' : 'Not uploaded')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('documents')
                    ->label('Documents')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' documents' : 'None')
                    ->badge()
                    ->color(fn ($state) => is_array($state) && count($state) > 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
