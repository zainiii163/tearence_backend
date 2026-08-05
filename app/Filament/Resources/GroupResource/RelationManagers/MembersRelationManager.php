<?php

namespace App\Filament\Resources\GroupResource\RelationManagers;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Members';

    protected static ?string $recordTitleAttribute = 'email';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return $ownerRecord->isTeam() ? 'Direct members' : 'Members';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\IconColumn::make('is_super_admin')->boolean()->label('Super'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\Action::make('assignUser')
                    ->label('Assign user')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search) => User::query()
                                ->where(function ($q) use ($search) {
                                    $q->where('email', 'like', "%{$search}%")
                                        ->orWhere('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('user_uid', 'like', "%{$search}%");
                                })
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (User $u) => [$u->user_id => "{$u->name} <{$u->email}>"])
                                ->all())
                            ->getOptionLabelUsing(fn ($value) => optional(User::find($value))->email)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $user = User::find($data['user_id']);
                        if (!$user) {
                            return;
                        }
                        $group = $this->getOwnerRecord();
                        $payload = ['group_id' => $group->group_id];
                        if (!$user->is_super_admin) {
                            $payload['can_manage_users'] = (bool) $group->can_manage_users;
                            $payload['can_manage_categories'] = (bool) $group->can_manage_categories;
                            $payload['can_manage_listings'] = (bool) $group->can_manage_listings;
                            $payload['can_manage_dashboard'] = (bool) $group->can_manage_dashboard;
                            $payload['can_view_analytics'] = (bool) $group->can_view_analytics;
                            if (is_array($group->permissions)) {
                                $payload['permissions'] = $group->permissions;
                            }
                        }
                        $user->update($payload);
                    })
                    ->visible(fn () => auth()->user()?->is_super_admin || auth()->user()?->can_manage_users),
            ])
            ->actions([
                Tables\Actions\Action::make('viewUser')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (User $record) => UserResource::getUrl('view', ['record' => $record])),
                Tables\Actions\Action::make('dashboard')
                    ->label('Dashboard')
                    ->icon('heroicon-o-computer-desktop')
                    ->url(fn (User $record) => UserResource::getUrl('dashboard', ['record' => $record])),
                Tables\Actions\Action::make('unassign')
                    ->label('Remove')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (User $record) => $record->update(['group_id' => null]))
                    ->visible(fn () => auth()->user()?->is_super_admin || auth()->user()?->can_manage_users),
            ])
            ->bulkActions([]);
    }
}
