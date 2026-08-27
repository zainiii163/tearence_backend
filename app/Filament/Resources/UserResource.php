<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Group;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Filament\Forms\Components\CountrySelect;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function canEdit($record): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        return $user->is_super_admin || $user->can_manage_users;
    }

    public static function canView($record): bool
    {
        return static::canEdit($record);
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        return $user->is_super_admin || $user->can_manage_users;
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        return $user->is_super_admin || $user->can_manage_users;
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        return $user->is_super_admin || $user->can_manage_users;
    }

    public static function canDeleteAny(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        return $user->is_super_admin || $user->can_manage_users;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_uid')
                    ->default(fn() => Str::random(13))
                    ->required()
                    ->maxLength(100)
                    ->hidden(),
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Select::make('group_id')
                    ->required()
                    ->label('Team / Sub-role')
                    ->options(fn () => Group::optionsGroupedByTeam())
                    ->searchable()
                    ->helperText('Assign to a department team sub-role (HR, Accounts, Legal, etc.). Only Super Admin / user managers can change membership.')
                    ->disabled(fn () => ! auth()->user()?->is_super_admin && ! auth()->user()?->can_manage_users)
                    ->dehydrated()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        if ($get('is_super_admin')) {
                            return;
                        }
                        $group = Group::find($state);
                        if (!$group || $group->isTeam()) {
                            return;
                        }
                        $set('can_manage_users', (bool) $group->can_manage_users);
                        $set('can_manage_categories', (bool) $group->can_manage_categories);
                        $set('can_manage_listings', (bool) $group->can_manage_listings);
                        $set('can_manage_dashboard', (bool) $group->can_manage_dashboard);
                        $set('can_view_analytics', (bool) $group->can_view_analytics);
                        if (is_array($group->permissions)) {
                            $set('permissions', $group->permissions);
                        }
                    }),
                Forms\Components\Section::make('Permissions')
                    ->description('Manage user permissions and access control')
                    ->schema([
                        Forms\Components\Toggle::make('is_super_admin')
                            ->label('Super Admin')
                            ->helperText('Super admin has full system access')
                            ->default(false),
                        Forms\Components\Toggle::make('can_manage_users')
                            ->label('Can Manage Users')
                            ->default(false),
                        Forms\Components\Toggle::make('can_manage_categories')
                            ->label('Can Manage Categories')
                            ->default(false),
                        Forms\Components\Toggle::make('can_manage_listings')
                            ->label('Can Manage Listings')
                            ->default(false),
                        Forms\Components\Toggle::make('can_manage_dashboard')
                            ->label('Can Manage Dashboard')
                            ->default(false),
                        Forms\Components\Toggle::make('can_view_analytics')
                            ->label('Can View Analytics')
                            ->default(false),
                        Forms\Components\Toggle::make('can_view_security_logs')
                            ->label('Can View Security / Login Logs')
                            ->helperText('IT / Super Admin: see all login attempts and locations')
                            ->default(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Deactivate to prevent login')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->collapsible(),
                Forms\Components\Section::make('Posting Limits & KYC')
                    ->description('Manage user posting restrictions and verification status')
                    ->schema([
                        Forms\Components\TextInput::make('posting_limit')
                            ->label('Posting Limit')
                            ->numeric()
                            ->default(5)
                            ->helperText('Number of posts allowed before KYC verification'),
                        Forms\Components\TextInput::make('post_count')
                            ->label('Current Post Count')
                            ->numeric()
                            ->default(0)
                            ->helperText('Number of posts the user has made'),
                        Forms\Components\Toggle::make('kyc_required')
                            ->label('KYC Required')
                            ->default(false)
                            ->helperText('Whether user needs KYC verification'),
                        Forms\Components\Select::make('kyc_status')
                            ->label('KYC Status')
                            ->options([
                                'pending' => 'Pending Review',
                                'submitted' => 'Submitted',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending'),
                        Forms\Components\DateTimePicker::make('kyc_verified_at')
                            ->label('KYC Verified At')
                            ->disabled(),
                        Forms\Components\Textarea::make('kyc_rejection_reason')
                            ->label('KYC Rejection Reason')
                            ->visible(fn ($get) => $get('kyc_status') === 'rejected')
                            ->maxLength(500),
                    ])
                    ->columns(2)
                    ->collapsible(),
                Forms\Components\Section::make('Contact details')
                    ->description('Email, phone and address — shown clearly on the users list and profile')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->required()
                            ->email()
                            ->maxLength(150)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('mobile_number')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(40)
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('address')
                            ->label('Address')
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(120),
                        CountrySelect::make('country'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Crypto payout wallet')
                    ->description('External receiving wallet. WWA does not hold user crypto balances.')
                    ->schema([
                        Forms\Components\Select::make('crypto_network')
                            ->options([
                                'trc20' => 'USDT → TRC20',
                                'erc20' => 'USDT → ERC20',
                                'polygon' => 'USDC → Polygon',
                            ]),
                        Forms\Components\TextInput::make('crypto_wallet_address')
                            ->label('Wallet address')
                            ->maxLength(191),
                        Forms\Components\DateTimePicker::make('crypto_wallet_verified_at')
                            ->label('Verified at'),
                    ])
                    ->columns(2)
                    ->collapsible(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(64)
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create'),
                Forms\Components\Select::make('timezone')
                    ->label('Timezone')
                    ->options(array_combine(timezone_identifiers_list(), timezone_identifiers_list()))
                    ->searchable(),
                Forms\Components\FileUpload::make('avatar')
                    ->maxSize(512)
                    ->columnSpan('full')
                    ->directory('avatar'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name'])
                    ->weight('medium')
                    ->description(fn ($record) => $record->user_uid)
                    ->wrap(),
                Tables\Columns\TextColumn::make('group.name')
                    ->label('Team / Role')
                    ->formatStateUsing(fn ($state, $record) => $record->group?->fullLabel() ?? $state)
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope')
                    ->wrap()
                    ->limit(40),
                Tables\Columns\TextColumn::make('mobile_number')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone')
                    ->placeholder('—')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Address')
                    ->searchable()
                    ->wrap()
                    ->limit(48)
                    ->placeholder('—')
                    ->description(fn ($record) => collect([$record->city, $record->country])->filter()->implode(', ') ?: null)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_super_admin')
                    ->label('Super')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('post_count')
                    ->label('Posts')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => ($record->post_count ?? $record->posts_count ?? 0) . '/' . ($record->posting_limit ?? $record->posts_limit ?? '—'))
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('kyc_status')
                    ->label('KYC')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'submitted',
                        'success' => 'verified',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        default => ($state ?? ''),
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_super_admin')
                    ->label('Super Admin'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\SelectFilter::make('group_id')
                    ->label('Team / Sub-role')
                    ->options(fn () => collect(Group::optionsGroupedByTeam())
                        ->flatMap(fn ($roles, $team) => collect($roles)->mapWithKeys(
                            fn ($label, $id) => [$id => "{$team} / {$label}"]
                        ))
                        ->all()),
                Tables\Filters\SelectFilter::make('kyc_status')
                    ->label('KYC Status')
                    ->options([
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\Filter::make('backend_staff')
                    ->label('Backend staff')
                    ->query(fn (Builder $query): Builder => $query->where(function ($q) {
                        $q->where('is_super_admin', true)
                            ->orWhere('can_manage_users', true)
                            ->orWhere('can_manage_dashboard', true)
                            ->orWhere('can_manage_listings', true)
                            ->orWhere('can_manage_categories', true)
                            ->orWhere('can_view_analytics', true);
                    })),
                Tables\Filters\Filter::make('posting_limit_reached')
                    ->label('Posting Limit Reached')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('post_count >= posting_limit')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('dashboard')
                    ->label('Dashboard')
                    ->icon('heroicon-o-computer-desktop')
                    ->url(fn ($record): string => static::getUrl('dashboard', ['record' => $record])),
            ])
            ->recordUrl(fn ($record): string => static::getUrl('view', ['record' => $record]))
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50]);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'dashboard' => Pages\UserDashboardPreview::route('/{record}/dashboard'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
