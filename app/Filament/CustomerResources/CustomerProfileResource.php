<?php

namespace App\Filament\CustomerResources;

use App\Filament\CustomerResources\CustomerProfileResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CustomerProfileResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Account Settings';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false; // Customers cannot create new customer profiles
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('customer_id', Auth::user()->customer_id ?? 0);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('last_name')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(150)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\Textarea::make('bio')
                            ->label('Bio/Description')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('avatar')
                            ->image()
                            ->directory('customers')
                            ->avatar()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Business Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->label('Company Name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('business_type')
                            ->label('Business Type')
                            ->maxLength(100),
                        Forms\Components\Textarea::make('business_description')
                            ->label('Business Description')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                Forms\Components\Section::make('Preferences')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Account')
                            ->default(true),
                        Forms\Components\Toggle::make('email_notifications')
                            ->label('Email Notifications')
                            ->default(true),
                        Forms\Components\Toggle::make('sms_notifications')
                            ->label('SMS Notifications')
                            ->default(false),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar'),
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // No bulk actions for customer profile
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
            'index' => Pages\ManageCustomerProfile::route('/'),
            'edit' => Pages\EditCustomerProfile::route('/{record}/edit'),
        ];
    }
}
