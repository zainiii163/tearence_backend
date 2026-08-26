<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobCategoryResource\Pages;
use App\Models\JobCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Schema;

class JobCategoryResource extends Resource
{
    protected static ?string $model = JobCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Jobs & Vacancies';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $activeField = Schema::hasColumn('job_categories', 'is_active') ? 'is_active' : 'active';

        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, string $state, Forms\Set $set) => $operation === 'create' ? $set('slug', str()->slug($state)) : null),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(120)
                    ->unique(JobCategory::class, 'slug', ignoreRecord: true),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('icon')
                    ->placeholder('heroicon-o-briefcase')
                    ->helperText('Heroicon name for the category icon'),
                Forms\Components\Toggle::make($activeField)
                    ->label('Active')
                    ->default(true),
                Forms\Components\TextInput::make(
                    Schema::hasColumn('job_categories', 'sort_order') ? 'sort_order' : 'id'
                )
                    ->label('Sort order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first')
                    ->visible(fn () => Schema::hasColumn('job_categories', 'sort_order')),
            ]);
    }

    public static function table(Table $table): Table
    {
        $activeField = Schema::hasColumn('job_categories', 'is_active') ? 'is_active' : 'active';
        $hasSortOrder = Schema::hasColumn('job_categories', 'sort_order');

        $columns = [
            Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('slug')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('active_jobs_count')
                ->label('Active Jobs')
                ->state(fn (JobCategory $record): int => (int) ($record->active_jobs_count ?? 0))
                ->sortable(false),
            Tables\Columns\IconColumn::make($activeField)
                ->label('Active')
                ->boolean(),
        ];

        if ($hasSortOrder) {
            $columns[] = Tables\Columns\TextColumn::make('sort_order')->sortable();
        }

        $columns[] = Tables\Columns\TextColumn::make('created_at')
            ->dateTime()
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        $table = $table
            ->columns($columns)
            ->filters([
                Tables\Filters\TernaryFilter::make($activeField)
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update([$activeField => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-mark')
                        ->action(fn ($records) => $records->each->update([$activeField => false])),
                ]),
            ]);

        if ($hasSortOrder) {
            $table = $table
                ->reorderable('sort_order')
                ->defaultSort('sort_order');
        } else {
            $table = $table->defaultSort('name');
        }

        return $table;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJobCategories::route('/'),
            'create' => Pages\CreateJobCategory::route('/create'),
            'view' => Pages\ViewJobCategory::route('/{record}'),
            'edit' => Pages\EditJobCategory::route('/{record}/edit'),
        ];
    }
}
