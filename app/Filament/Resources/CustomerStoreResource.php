<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerStoreResource\Pages;
use App\Models\CustomerStore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CustomerStoreResource extends Resource
{
    protected static ?string $navigationLabel = 'Online Stores';

    protected static ?string $model = CustomerStore::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?string $modelLabel = 'Online Store';

    protected static ?string $pluralModelLabel = 'Online Stores';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Store profile')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'email')
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('store_name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(CustomerStore::class, 'slug', ignoreRecord: true),
                        Forms\Components\Select::make('category')
                            ->options([
                                'fashion' => 'Fashion',
                                'electronics' => 'Electronics',
                                'home' => 'Home & Living',
                                'food' => 'Food & Grocery',
                                'beauty' => 'Beauty',
                                'sports' => 'Sports',
                                'services' => 'Services',
                                'other' => 'Other',
                            ])
                            ->searchable(),
                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'pending' => 'Pending',
                            ])
                            ->default('active')
                            ->required(),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured store'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Company')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')->maxLength(255),
                        Forms\Components\TextInput::make('company_no')->maxLength(100),
                        Forms\Components\TextInput::make('vat')->maxLength(100),
                        Forms\Components\TextInput::make('store_address')->maxLength(500)->columnSpanFull(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Contact')
                    ->schema([
                        Forms\Components\TextInput::make('phone')->tel()->maxLength(50),
                        Forms\Components\TextInput::make('email')->email()->maxLength(255),
                        Forms\Components\TextInput::make('website')->url()->maxLength(500),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('store_logo')
                            ->image()
                            ->directory('stores/logos')
                            ->disk('public'),
                        Forms\Components\FileUpload::make('store_banner')
                            ->image()
                            ->directory('stores/banners')
                            ->disk('public'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store_id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('store_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('category')->badge()->sortable(),
                Tables\Columns\TextColumn::make('company_name')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('email')->toggleable(),
                Tables\Columns\TextColumn::make('phone')->toggleable(),
                Tables\Columns\ImageColumn::make('store_logo')->label('Logo'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->label('Featured'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'pending' => 'Pending',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'fashion' => 'Fashion',
                        'electronics' => 'Electronics',
                        'home' => 'Home & Living',
                        'food' => 'Food & Grocery',
                        'beauty' => 'Beauty',
                        'sports' => 'Sports',
                        'services' => 'Services',
                        'other' => 'Other',
                    ]),
                Tables\Filters\TernaryFilter::make('is_featured'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerStores::route('/'),
            'create' => Pages\CreateCustomerStore::route('/create'),
            'edit' => Pages\EditCustomerStore::route('/{record}/edit'),
        ];
    }
}
