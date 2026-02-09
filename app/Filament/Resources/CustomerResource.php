<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Group;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Customer Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer_uid')
                    ->default(fn () => Str::random(10))
                    ->required()
                    ->maxLength(10)
                    ->hidden(),
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(150),
                Forms\Components\TextInput::make('password_hash')
                    ->password()
                    ->label('Password')
                    ->maxLength(64),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(50),
                Forms\Components\Select::make('gender')
                    ->options(['M'=>'M', 'F'=>'F']),
                Forms\Components\DatePicker::make('birthday'),
                Forms\Components\Select::make('address_country')
                    ->label('Country')
                    ->options(Country::all()->pluck('name', 'country_id'))
                    ->searchable()
                    ->reactive() // Make it reactive to changes
                    ->afterStateUpdated(fn (callable $set) => $set('address_city', null)), // Reset the zone when country changes
                Forms\Components\Select::make('address_city')
                    ->options(function (callable $get) {
                        $countryId = $get('address_country');
                        if ($countryId) {
                            return Zone::where('country_id', $countryId)->pluck('name', 'zone_id');
                        }
                        return Zone::all()->pluck('name', 'zone_id'); // Return all zones if no country is selected
                    })
                    ->searchable(),
                Forms\Components\TextInput::make('address_street')
                    ->maxLength(128),
                Forms\Components\TextInput::make('address_house')
                    ->maxLength(32),
                Forms\Components\TextInput::make('address_flat')
                    ->maxLength(32),
                Forms\Components\Select::make('currency_id')
                    ->label('Currency')
                    ->options(Currency::all()->pluck('name', 'currency_id'))
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_uid')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('group.name')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('email_verified_at')
                //     ->dateTime()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
