<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessSocialPageResource\Pages;
use App\Models\Community;
use App\Models\CustomerBusiness;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class BusinessSocialPageResource extends Resource
{
    protected static ?string $model = Community::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationLabel = 'Business Social Pages';

    protected static ?string $modelLabel = 'Business Social Page';

    protected static ?string $pluralModelLabel = 'Business Social Pages';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?string $slug = 'business-social-pages';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return (bool) (
            ($user->is_super_admin ?? false)
            || ($user->is_admin ?? false)
            || (method_exists($user, 'isAdmin') && $user->isAdmin())
            || ($user->can_manage_users ?? false)
        );
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();

        return (bool) ($user && ($user->is_super_admin ?? false));
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['business', 'category', 'creator']);

        if (Schema::hasColumn('communities', 'business_id')) {
            $query->whereNotNull('business_id');
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query->withCount(['followers', 'members', 'posts']);
    }

    public static function frontendBaseUrl(): string
    {
        return rtrim(
            (string) (
                env('APP_FRONTEND_URL')
                ?: env('FRONTEND_URL')
                ?: 'https://worldwideadverts.info'
            ),
            '/'
        );
    }

    public static function socialUrl(Community $record): string
    {
        $id = $record->slug ?: $record->community_id;

        return static::frontendBaseUrl() . '/community/' . $id;
    }

    public static function businessUrl(Community $record): ?string
    {
        $biz = $record->business;
        if (!$biz && $record->business_id) {
            return static::frontendBaseUrl() . '/business/' . $record->business_id;
        }
        if (!$biz) {
            return null;
        }

        return static::frontendBaseUrl() . '/business/' . ($biz->slug ?: $biz->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Social Hub page')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Page name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('business_id')
                            ->label('Linked business')
                            ->options(fn () => CustomerBusiness::query()
                                ->orderBy('business_name')
                                ->limit(500)
                                ->pluck('business_name', 'id'))
                            ->searchable()
                            ->required()
                            ->helperText('One Social Hub page per business.'),
                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured'),
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Verified'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('business.business_name')
                    ->label('Business')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->description(fn (Community $record): ?string => $record->business_id
                        ? 'ID ' . $record->business_id
                        : null),
                Tables\Columns\TextColumn::make('name')
                    ->label('Social page')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Community $record): ?string => $record->slug),
                Tables\Columns\TextColumn::make('followers_count')
                    ->label('Followers')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('members_count')
                    ->label('Members')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('posts_count')
                    ->label('Posts')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('has_business')
                    ->label('Linked businesses only')
                    ->query(fn (Builder $query) => $query->whereNotNull('business_id'))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\Action::make('open_social')
                    ->label('Social')
                    ->icon('heroicon-o-share')
                    ->color('primary')
                    ->url(fn (Community $record): string => static::socialUrl($record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('open_business')
                    ->label('Business')
                    ->icon('heroicon-o-building-storefront')
                    ->color('gray')
                    ->url(fn (Community $record): ?string => static::businessUrl($record))
                    ->openUrlInNewTab()
                    ->visible(fn (Community $record): bool => filled(static::businessUrl($record))),
                Tables\Actions\Action::make('edit_business')
                    ->label('Edit business')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (Community $record): ?string => $record->business_id
                        ? CustomerBusinessResource::getUrl('edit', ['record' => $record->business_id])
                        : null)
                    ->visible(fn (Community $record): bool => (bool) $record->business_id),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => (bool) (auth()->user()?->is_super_admin ?? false)),
                ]),
            ])
            ->emptyStateHeading('No business Social Hub pages yet')
            ->emptyStateDescription('Owners create these from the public business page or business dashboard (Creator Feed & Promotions).')
            ->emptyStateIcon('heroicon-o-share');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinessSocialPages::route('/'),
            'view' => Pages\ViewBusinessSocialPage::route('/{record}'),
            'edit' => Pages\EditBusinessSocialPage::route('/{record}/edit'),
        ];
    }
}
