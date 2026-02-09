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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Admin Management';

    protected static ?int $navigationSort = 1;

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
                    ->label('Group')
                    ->default(1)
                    ->options(Group::all()->pluck('name', 'group_id'))
                    ->searchable(),
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
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(150),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(64)
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create'),
                Forms\Components\Select::make('timezone')
                    ->label('Timezone')
                    ->options(array_combine(timezone_identifiers_list(), timezone_identifiers_list())) // Use the same value for keys and labels
                    ->searchable(), // Make the dropdown searchable
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
                Tables\Columns\ImageColumn::make('avatar'),
                Tables\Columns\TextColumn::make('user_uid')
                    ->searchable(),
                Tables\Columns\TextColumn::make('group.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_super_admin')
                    ->label('Super Admin')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('post_count')
                    ->label('Posts')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => "{$record->post_count}/{$record->posting_limit}"),
                Tables\Columns\BadgeColumn::make('kyc_status')
                    ->label('KYC')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'submitted',
                        'success' => 'verified',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_super_admin')
                    ->label('Super Admin'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\SelectFilter::make('kyc_status')
                    ->label('KYC Status')
                    ->options([
                        'pending' => 'Pending',
                        'submitted' => 'Submitted',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\Filter::make('posting_limit_reached')
                    ->label('Posting Limit Reached')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('post_count >= posting_limit')),
                Tables\Filters\Filter::make('can_manage_users')
                    ->label('Can Manage Users')
                    ->query(fn (Builder $query): Builder => $query->where('can_manage_users', true)),
                Tables\Filters\Filter::make('can_manage_categories')
                    ->label('Can Manage Categories')
                    ->query(fn (Builder $query): Builder => $query->where('can_manage_categories', true)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
