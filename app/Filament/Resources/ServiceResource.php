<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use App\Models\ServiceCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Services Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Service Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->options(fn () => \App\Models\User::all()->pluck('name', 'user_id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->label('Subcategory')
                            ->options(fn () => ServiceCategory::query()
                                ->leaves()
                                ->active()
                                ->with('parent')
                                ->orderBy('sort_order')
                                ->get()
                                ->mapWithKeys(fn (ServiceCategory $cat) => [
                                    $cat->id => ($cat->parent?->name ? $cat->parent->name.' → ' : '').$cat->name,
                                ]))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Service::class, 'slug', ignoreRecord: true),
                        Forms\Components\TextInput::make('tagline')
                            ->maxLength(80),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('whats_included')
                            ->label('What\'s Included')
                            ->placeholder('Add item')
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('whats_not_included')
                            ->label('What\'s Not Included')
                            ->placeholder('Add item')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('requirements')
                            ->label('Requirements from Buyer')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Service Details')
                    ->schema([
                        Forms\Components\Select::make('service_type')
                            ->options([
                                'freelance' => 'Freelance Service',
                                'local' => 'Local Service',
                                'business' => 'Business Service',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('starting_price')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\Select::make('currency')
                            ->options(\App\Support\ServiceFormHelper::CURRENCIES)
                            ->default('USD')
                            ->required(),
                        Forms\Components\TextInput::make('delivery_time')
                            ->numeric()
                            ->suffix('days'),
                        Forms\Components\TagsInput::make('languages')
                            ->label('Languages Spoken')
                            ->placeholder('Add language')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('country')
                            ->options(fn () => \App\Models\Country::pluck('name', 'name'))
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('city')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->step(0.000001),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->step(0.000001),
                        Forms\Components\TextInput::make('service_area_radius')
                            ->numeric()
                            ->suffix('km')
                            ->label('Service Area Radius'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status & Promotion')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options(\App\Support\ServiceFormHelper::STATUSES)
                            ->default('draft')
                            ->required(),
                        Forms\Components\Select::make('promotion_type')
                            ->options(\App\Support\ServiceFormHelper::PROMOTION_TYPES)
                            ->default('standard'),
                        Forms\Components\DateTimePicker::make('promotion_expires_at')
                            ->label('Promotion Expires At'),
                        Forms\Components\Toggle::make('is_verified')
                            ->label('Verified Provider'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Analytics')
                    ->schema([
                        Forms\Components\TextInput::make('views')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('enquiries')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('rating')
                            ->numeric()
                            ->step(0.1)
                            ->disabled(),
                        Forms\Components\TextInput::make('review_count')
                            ->numeric()
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
                    ->sortable(),
            ])
            ->filters([
                //
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
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PackagesRelationManager::class,
            RelationManagers\AddonsRelationManager::class,
            RelationManagers\PromotionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'view' => Pages\ViewService::route('/{record}'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
