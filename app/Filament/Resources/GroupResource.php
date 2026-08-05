<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Filament\Resources\GroupResource\RelationManagers;
use App\Models\Group;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Teams & Roles';

    protected static ?string $modelLabel = 'Team / Role';

    protected static ?string $pluralModelLabel = 'Teams & Roles';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $slug = 'teams-roles';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return (bool) ($user->is_super_admin || $user->can_manage_users);
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();

        return (bool) ($user && $user->is_super_admin);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Team or sub-role')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'team' => 'Team (department)',
                                'role' => 'Sub-role (under a team)',
                            ])
                            ->required()
                            ->live()
                            ->default('role')
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state === 'team') {
                                    $set('parent_id', null);
                                }
                            }),
                        Forms\Components\Select::make('parent_id')
                            ->label('Parent team')
                            ->options(fn () => Group::query()
                                ->where('type', 'team')
                                ->orWhere(fn ($q) => $q->whereNull('parent_id')->where(function ($q2) {
                                    $q2->whereNull('type')->orWhere('type', 'team');
                                }))
                                ->orderBy('name')
                                ->pluck('name', 'group_id'))
                            ->searchable()
                            ->visible(fn (Get $get) => $get('type') === 'role')
                            ->required(fn (Get $get) => $get('type') === 'role')
                            ->helperText('Clive assigns people to teams via Users; create sub-roles under each team here.'),
                        Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation) {
                                if ($operation === 'create' && $state && blank($get('slug'))) {
                                    $prefix = '';
                                    if ($get('type') === 'role' && $get('parent_id')) {
                                        $team = Group::find($get('parent_id'));
                                        $prefix = ($team?->slug ?: Str::slug($team?->name ?? 'team')) . '-';
                                    }
                                    $set('slug', $prefix . Str::slug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('e.g. hr, hr-payroll-admin'),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Permissions')
                    ->description('What members of this team/role can do in the admin portal')
                    ->schema([
                        Forms\Components\Toggle::make('can_manage_users')->label('Manage users')->default(false),
                        Forms\Components\Toggle::make('can_manage_categories')->label('Manage categories')->default(false),
                        Forms\Components\Toggle::make('can_manage_listings')->label('Manage listings')->default(false),
                        Forms\Components\Toggle::make('can_manage_dashboard')->label('Manage dashboard')->default(false),
                        Forms\Components\Toggle::make('can_view_analytics')->label('View analytics')->default(false),
                        Forms\Components\TagsInput::make('permissions')
                            ->label('Extra permission keys')
                            ->helperText('Optional tags e.g. hr.payroll, ads.promo')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (?string $state, $record) => $record->isTeam() ? 'Team' : 'Role')
                    ->color(fn ($record) => $record->isTeam() ? 'info' : 'success'),
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Team')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Members'),
                Tables\Columns\IconColumn::make('can_manage_users')->label('Users')->boolean()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('can_manage_listings')->label('Listings')->boolean()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('can_view_analytics')->label('Analytics')->boolean()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('type')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(['team' => 'Team', 'role' => 'Sub-role']),
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Team')
                    ->options(fn () => Group::query()->where('type', 'team')->orderBy('name')->pluck('name', 'group_id')),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultGroup('type')
            ->groups([
                Tables\Grouping\Group::make('type')
                    ->label('Type')
                    ->getTitleFromRecordUsing(fn ($record): string => $record->isTeam() ? 'Teams' : 'Sub-roles'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubRolesRelationManager::class,
            RelationManagers\MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}
