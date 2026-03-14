<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobViewResource\Pages;
use App\Models\JobView;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobViewResource extends Resource
{
    protected static ?string $model = JobView::class;

    protected static ?string $navigationIcon = 'heroicon-o-eye';

    protected static ?string $navigationGroup = 'Jobs Management';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('View Information')
                    ->schema([
                        Forms\Components\Select::make('job_id')
                            ->relationship('job', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->required()
                            ->maxLength(45),
                        Forms\Components\TextInput::make('user_agent')
                            ->label('User Agent')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('referrer')
                            ->label('Referrer URL')
                            ->maxLength(500)
                            ->url()
                            ->nullable(),
                        Forms\Components\Select::make('device_type')
                            ->options([
                                'desktop' => 'Desktop',
                                'mobile' => 'Mobile',
                                'tablet' => 'Tablet',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('country')
                            ->maxLength(100)
                            ->nullable(),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(100)
                            ->nullable(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Timestamps')
                    ->schema([
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Viewed At')
                            ->required()
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('updated_at')
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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('job.title')
                    ->label('Job Title')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn (JobView $record): string => $record->job->title ?? ''),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Viewer')
                    ->searchable()
                    ->placeholder('Guest'),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('device_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'desktop' => 'success',
                        'mobile' => 'warning',
                        'tablet' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('country')
                    ->searchable()
                    ->placeholder('Unknown'),
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->placeholder('Unknown'),
                Tables\Columns\TextColumn::make('referrer')
                    ->label('Referrer')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Viewed At')
                    ->dateTime()
                    ->sortable()
                    ->description(fn (JobView $record): string => 
                        $record->created_at->diffForHumans()
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('job')
                    ->relationship('job', 'title')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('device_type')
                    ->options([
                        'desktop' => 'Desktop',
                        'mobile' => 'Mobile',
                        'tablet' => 'Tablet',
                    ]),
                Tables\Filters\SelectFilter::make('country')
                    ->options(fn () => JobView::distinct('country')->pluck('country', 'country')->filter())
                    ->searchable(),
                Tables\Filters\Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->today()),
                Tables\Filters\Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn (Builder $query): Builder => $query->thisWeek()),
                Tables\Filters\Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn (Builder $query): Builder => $query->thisMonth()),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
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
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListJobViews::route('/'),
            'create' => Pages\CreateJobView::route('/create'),
            'view' => Pages\ViewJobView::route('/{record}'),
            'edit' => Pages\EditJobView::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
