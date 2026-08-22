<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoginLogResource\Pages;
use App\Models\LoginLog;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LoginLogResource extends Resource
{
    protected static ?string $model = LoginLog::class;

    protected static ?string $slug = 'login-logs';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Login Activity';

    protected static ?string $modelLabel = 'Login log';

    protected static ?string $pluralModelLabel = 'Login activity';

    protected static ?string $navigationGroup = 'Security';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return static::userCanViewSecurityLogs();
    }

    public static function canView($record): bool
    {
        return static::userCanViewSecurityLogs();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();

        return $user instanceof User && (bool) $user->is_super_admin;
    }

    public static function canDeleteAny(): bool
    {
        $user = auth()->user();

        return $user instanceof User && (bool) $user->is_super_admin;
    }

    protected static function userCanViewSecurityLogs(): bool
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return false;
        }

        if ($user->is_super_admin) {
            return true;
        }

        return method_exists($user, 'canViewSecurityLogs')
            ? $user->canViewSecurityLogs()
            : (bool) ($user->can_view_security_logs ?? false);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('email')->disabled(),
            Forms\Components\TextInput::make('guard')->disabled(),
            Forms\Components\TextInput::make('event')->disabled(),
            Forms\Components\Toggle::make('successful')->disabled(),
            Forms\Components\TextInput::make('ip_address')->disabled(),
            Forms\Components\TextInput::make('location_label')->disabled()->columnSpanFull(),
            Forms\Components\Textarea::make('user_agent')->disabled()->columnSpanFull(),
            Forms\Components\TextInput::make('failure_reason')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->timezone(config('app.timezone')),
                Tables\Columns\IconColumn::make('successful')
                    ->label('OK')
                    ->boolean(),
                Tables\Columns\TextColumn::make('event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'login', '2fa_success' => 'success',
                        'logout' => 'gray',
                        '2fa_pending' => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('actor_type')
                    ->label('Type')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_admin_backend')
                    ->label('Admin')
                    ->boolean(),
                Tables\Columns\TextColumn::make('guard')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('location_label')
                    ->label('Location')
                    ->wrap()
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('country')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('city')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('failure_reason')
                    ->label('Reason')
                    ->toggleable(),
                Tables\Columns\IconColumn::make('alerted')
                    ->label('Alerted')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user_agent')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('successful')
                    ->label('Successful'),
                Tables\Filters\TernaryFilter::make('is_admin_backend')
                    ->label('Admin backend'),
                Tables\Filters\SelectFilter::make('actor_type')
                    ->options([
                        'customer' => 'Customer',
                        'user' => 'Admin user',
                        'unknown' => 'Unknown',
                    ]),
                Tables\Filters\SelectFilter::make('guard')
                    ->options([
                        'api' => 'API / website',
                        'admin' => 'Admin JWT',
                        'admin-web' => 'Filament /admin',
                        'web' => 'Web session',
                    ]),
                Tables\Filters\Filter::make('created_from')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => static::canDeleteAny()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoginLogs::route('/'),
            'view' => Pages\ViewLoginLog::route('/{record}'),
        ];
    }
}
