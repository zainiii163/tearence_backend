<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Section::make('Document Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('file_url')
                            ->label('File URL')
                            ->url()
                            ->maxLength(500)
                            ->required(),

                        Forms\Components\TextInput::make('file_type')
                            ->label('File Type')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('file_size')
                            ->label('File Size (bytes)')
                            ->numeric(),

                        Forms\Components\TextInput::make('mime_type')
                            ->label('MIME Type')
                            ->maxLength(100),

                        Forms\Components\Select::make('document_type')
                            ->options([
                                'identity' => 'Identity Document',
                                'marketing' => 'Marketing Material',
                                'reward' => 'Reward Information',
                                'other' => 'Other',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('file_type')
                    ->label('File Type')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(fn ($state) => $state ? $this->formatFileSize($state) : 'N/A'),

                Tables\Columns\TextColumn::make('document_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'identity' => 'danger',
                        'marketing' => 'success',
                        'reward' => 'warning',
                        'other' => 'gray',
                    }),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('MIME Type')
                    ->limit(30),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->options([
                        'identity' => 'Identity Document',
                        'marketing' => 'Marketing Material',
                        'reward' => 'Reward Information',
                        'other' => 'Other',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn ($record): string => $record->file_url)
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    private function formatFileSize($bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return '1 byte';
        } else {
            return '0 bytes';
        }
    }
}
