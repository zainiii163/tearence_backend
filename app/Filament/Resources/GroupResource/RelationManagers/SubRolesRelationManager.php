<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SubRolesRelationManager extends RelationManager
{
    protected static string $relationship = 'subRoles';

    protected static ?string $title = 'Sub-roles';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return method_exists($ownerRecord, 'isTeam') && $ownerRecord->isTeam();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                        if ($operation === 'create' && $state) {
                            $team = $this->getOwnerRecord();
                            $prefix = ($team->slug ?: Str::slug($team->name)) . '-';
                            $set('slug', $prefix . Str::slug($state));
                        }
                    }),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')->default(true),
                Forms\Components\Toggle::make('can_manage_users')->label('Manage users')->default(false),
                Forms\Components\Toggle::make('can_manage_categories')->label('Manage categories')->default(false),
                Forms\Components\Toggle::make('can_manage_listings')->label('Manage listings')->default(false),
                Forms\Components\Toggle::make('can_manage_dashboard')->label('Manage dashboard')->default(false),
                Forms\Components\Toggle::make('can_view_analytics')->label('View analytics')->default(false),
                Forms\Components\TagsInput::make('permissions')
                    ->label('Extra permission keys')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->toggleable(),
                Tables\Columns\TextColumn::make('users_count')->counts('users')->label('Members'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\IconColumn::make('can_manage_users')->label('Users')->boolean()->toggleable(),
                Tables\Columns\IconColumn::make('can_manage_listings')->label('Listings')->boolean()->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['type'] = 'role';
                        $data['parent_id'] = $this->getOwnerRecord()->group_id;

                        return $data;
                    }),
            ])
            ->actions([
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
