<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerBusinessResource\Pages;
use App\Filament\Resources\CustomerBusinessResource\RelationManagers;
use App\Models\Customer;
use App\Models\CustomerBusiness;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class CustomerBusinessResource extends Resource
{
    protected static ?string $model = CustomerBusiness::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Customer Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Business Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(function () {
                                return Customer::select(
                                    DB::raw("CONCAT(first_name,' ',last_name,' | ',email) AS full_name"),
                                    'customer_id'
                                )
                                    ->orderBy('created_at', 'desc')
                                    ->limit(100)
                                    ->pluck('full_name', 'customer_id');
                            })
                            ->searchable()
                            ->required()
                            ->preload(),
                        Forms\Components\TextInput::make('slug')
                            ->maxLength(255)
                            ->helperText('Leave empty to auto-generate from business name'),
                        Forms\Components\TextInput::make('business_name')
                            ->label('Business Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('business_owner')
                            ->label('Business Owner')
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
                Forms\Components\Section::make('Contact Information')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('business_email')
                            ->label('Business Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('business_phone_number')
                            ->label('Business Phone')
                            ->tel()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('personal_email')
                            ->label('Personal Email')
                            ->email()
                            ->maxLength(50),
                        Forms\Components\TextInput::make('personal_phone_number')
                            ->label('Personal Phone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('business_address')
                            ->label('Business Address')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('business_website')
                            ->label('Website')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Company Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('business_company_name')
                            ->label('Company Name')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('business_company_no')
                            ->label('Company Number')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('business_company_registration')
                            ->label('Registration Number')
                            ->maxLength(50)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                Forms\Components\Section::make('Logo')
                    ->schema([
                        Forms\Components\FileUpload::make('business_logo')
                            ->label('Business Logo')
                            ->image()
                            ->directory('business')
                            ->maxSize(2048)
                            ->helperText('Upload business logo (max 2MB)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('business_logo')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),
                Tables\Columns\TextColumn::make('business_name')
                    ->label('Business Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        return $record->customer ? $record->customer->first_name . ' ' . $record->customer->last_name : '-';
                    }),
                Tables\Columns\TextColumn::make('business_owner')
                    ->label('Owner')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('business_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('business_phone_number')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'first_name')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListCustomerBusinesses::route('/'),
            'create' => Pages\CreateCustomerBusiness::route('/create'),
            'view' => Pages\ViewCustomerBusiness::route('/{record}'),
            'edit' => Pages\EditCustomerBusiness::route('/{record}/edit'),
        ];
    }
}
