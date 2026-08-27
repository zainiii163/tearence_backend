<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobAlertResource\Pages;
use App\Models\JobAlert;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;

class JobAlertResource extends Resource
{
    protected static ?string $model = JobAlert::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'Jobs & Vacancies';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Job Alerts';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Alert Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->getSearchResultsUsing(function (string $search) {
                                if (empty($search)) {
                                    // Return recent customers when no search query
                                    return Customer::select(
                                        DB::raw("CONCAT(first_name,' ',last_name,' | ',email) AS full_name"),
                                        'customer_id'
                                    )
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->pluck('full_name', 'customer_id');
                                }
                                
                                // Search customers when query is provided
                                return Customer::select(
                                    DB::raw("CONCAT(first_name,' ',last_name,' | ',email) AS full_name"),
                                    'customer_id'
                                )
                                    ->where(function($q) use ($search) {
                                        $q->where('first_name', 'like', "%{$search}%")
                                          ->orWhere('last_name', 'like', "%{$search}%")
                                          ->orWhere('email', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->pluck('full_name', 'customer_id');
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $customer = Customer::find($value);
                                if ($customer) {
                                    return $customer->first_name . ' ' . $customer->last_name . ' | ' . $customer->email;
                                }
                                return null;
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select an option')
                            ->reactive(),
                        Forms\Components\TextInput::make('name')
                            ->label('Alert Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., PHP Developer Jobs'),
                        Forms\Components\TagsInput::make('keywords')
                            ->label('Keywords')
                            ->placeholder('Add keywords to search')
                            ->helperText('Enter keywords that should match job titles or descriptions')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('location_id')
                            ->label('Location')
                            ->relationship('location', 'city')
                            ->searchable()
                            ->preload()
                            ->visible(fn () => Schema::hasTable('job_alerts') && Schema::hasColumn('job_alerts', 'location_id')),
                        Forms\Components\Select::make('parent_category_id')
                            ->label('Parent Category')
                            ->options(function () {
                                return \App\Support\SafeSelectOptions::get(function () {
                                    $query = Category::query()->orderBy('name', 'ASC');
                                    if (Schema::hasColumn('category', 'parent_id')) {
                                        $query->whereNull('parent_id');
                                    }

                                    return $query->pluck('name', 'category_id');
                                });
                            })
                            ->reactive()
                            ->dehydrated(false)
                            ->searchable(),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->options(function ($get) {
                                $parentId = $get('parent_category_id');
                                if ($parentId) {
                                return \App\Support\SafeSelectOptions::get(
                                    fn () => Category::query()->where('parent_id', $parentId)->pluck('name', 'category_id')
                                );
                                }
                                return [];
                            })
                            ->searchable()
                            ->disabled(fn($get) => empty($get('parent_category_id')))
                            ->reactive(),
                        Forms\Components\TagsInput::make('job_type')
                            ->label('Job Types')
                            ->placeholder('Select job types')
                            ->suggestions([
                                'full-time',
                                'part-time',
                                'contract',
                                'freelance',
                                'internship',
                            ]),
                        Forms\Components\TextInput::make('salary_min')
                            ->label('Min Salary')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('salary_max')
                            ->label('Max Salary')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\Select::make('frequency')
                            ->label('Notification Frequency')
                            ->options([
                                'instant' => 'Instant',
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                            ])
                            ->default('daily')
                            ->required(),
                        Forms\Components\TextInput::make('notification_email')
                            ->label('Notification Email')
                            ->email()
                            ->maxLength(255)
                            ->helperText('Leave empty to use customer email'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active alerts will send notifications'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $alertsTable = 'job_alerts';
        $hasCustomer = Schema::hasTable($alertsTable) && Schema::hasColumn($alertsTable, 'customer_id');
        $hasLocationId = Schema::hasTable($alertsTable) && Schema::hasColumn($alertsTable, 'location_id');
        $hasCategoryId = Schema::hasTable($alertsTable) && Schema::hasColumn($alertsTable, 'category_id');
        $hasJobType = Schema::hasTable($alertsTable) && Schema::hasColumn($alertsTable, 'job_type');
        $activeField = Schema::hasTable($alertsTable) && Schema::hasColumn($alertsTable, 'is_active')
            ? 'is_active'
            : 'active';
        $nameField = Schema::hasTable($alertsTable) && Schema::hasColumn($alertsTable, 'name')
            ? 'name'
            : 'title';
        $ownerColumn = $hasCustomer ? 'customer.name' : 'user.name';

        $columns = [
            TextColumn::make($ownerColumn)
                ->label('Customer')
                ->formatStateUsing(function (JobAlert $record): string {
                    return $record->customer?->name ?? $record->user?->name ?? '—';
                }),
            TextColumn::make($nameField)
                ->label('Alert Name')
                ->searchable()
                ->sortable(),
            TextColumn::make('keywords')
                ->label('Keywords')
                ->badge()
                ->separator(',')
                ->limit(3),
        ];

        if ($hasLocationId) {
            $columns[] = TextColumn::make('location.city')->label('Location');
        }

        if ($hasCategoryId) {
            $columns[] = TextColumn::make('category.name')->label('Category');
        }

        if ($hasJobType) {
            $columns[] = TextColumn::make('job_type')
                ->label('Job Types')
                ->badge()
                ->separator(',')
                ->limit(2);
        }

        $columns = array_merge($columns, [
            TextColumn::make('frequency')
                ->badge()
                ->color(fn (?string $state): string => match ($state) {
                    'instant' => 'success',
                    'daily' => 'info',
                    'weekly' => 'warning',
                    default => 'gray',
                }),
            TextColumn::make('last_matched_count')
                ->label('Last Match Count')
                ->numeric()
                ->default(0)
                ->sortable(Schema::hasTable($alertsTable) && Schema::hasColumn($alertsTable, 'last_matched_count')),
            TextColumn::make('last_notified_at')
                ->label('Last Notified')
                ->dateTime()
                ->sortable(Schema::hasTable($alertsTable) && Schema::hasColumn($alertsTable, 'last_notified_at')),
            Tables\Columns\IconColumn::make(
                Schema::hasTable($alertsTable) && Schema::hasColumn($alertsTable, $activeField)
                    ? $activeField
                    : 'is_active'
            )
                ->label('Active')
                ->boolean(),
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ]);

        $filters = [
            Tables\Filters\SelectFilter::make('frequency')
                ->options([
                    'instant' => 'Instant',
                    'daily' => 'Daily',
                    'weekly' => 'Weekly',
                ]),
            Tables\Filters\TernaryFilter::make($activeField)
                ->label('Active Status'),
        ];

        if ($hasLocationId) {
            $filters[] = Tables\Filters\SelectFilter::make('location_id')
                ->label('Location')
                ->relationship('location', 'city')
                ->searchable()
                ->preload();
        }

        return $table
            ->columns($columns)
            ->filters($filters)
            ->actions([
                Action::make('viewMatchingJobs')
                    ->label('View Matching Jobs')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn (JobAlert $record): string => static::getUrl('matching-jobs', ['record' => $record])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update([$activeField => true]))
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each->update([$activeField => false]))
                        ->deselectRecordsAfterCompletion(),
                ]),
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
            'index' => Pages\ListJobAlerts::route('/'),
            'create' => Pages\CreateJobAlert::route('/create'),
            'edit' => Pages\EditJobAlert::route('/{record}/edit'),
            'matching-jobs' => Pages\ViewMatchingJobs::route('/{record}/matching-jobs'),
        ];
    }
}

