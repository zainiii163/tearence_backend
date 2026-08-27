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
use App\Filament\Forms\Components\CountrySelect;

class CustomerBusinessResource extends Resource
{

    protected static ?string $navigationLabel = 'Businesses';
    protected static ?string $model = CustomerBusiness::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 6;

    protected static ?string $label = 'Businesses';

    protected static ?string $pluralLabel = 'Businesses';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['socialCommunity', 'customer', 'category']);
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return true;
    }

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
                        Forms\Components\Select::make('category_id')
                            ->label('Business Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select a category'),
                        Forms\Components\TextInput::make('slug')
                            ->maxLength(255)
                            ->helperText('Leave empty to auto-generate from business name'),
                        Forms\Components\TextInput::make('business_name')
                            ->label('Business Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('business_description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('business_category_slug')
                            ->label('Directory category')
                            ->options([
                                'retail' => 'Retail & Shopping',
                                'restaurants' => 'Restaurants & Food',
                                'professional-services' => 'Professional Services',
                                'healthcare-wellness' => 'Healthcare & Wellness',
                                'education-training' => 'Education & Training',
                                'automotive' => 'Automotive',
                                'real-estate' => 'Real Estate',
                                'entertainment' => 'Entertainment & Leisure',
                                'travel' => 'Travel & Hospitality',
                                'beauty' => 'Beauty & Personal Care',
                                'pets' => 'Pet Services',
                                'home-garden' => 'Home & Garden',
                                'technology-electronics' => 'Technology & Electronics',
                                'sports-fitness' => 'Sports & Fitness',
                                'industrial' => 'Industrial & Manufacturing',
                                'non-profit' => 'Non-Profit & Religious',
                            ])
                            ->searchable(),
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
                        Forms\Components\TextInput::make('city')
                            ->label('City')
                            ->maxLength(120),
                        CountrySelect::make('country'),
                        Forms\Components\TextInput::make('postal_code')
                            ->label('Postcode')
                            ->maxLength(32),
                        Forms\Components\TextInput::make('business_website')
                            ->label('Website')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('booking_url')
                            ->label('Booking URL')
                            ->url()
                            ->maxLength(500)
                            ->helperText('Reservations, MOT booking, appointments, etc.'),
                    ]),
                Forms\Components\Section::make('Category profile (hours, booking, extras)')
                    ->description('Category-specific fields shown on the public business page — opening times, booking slots, menu/services, etc.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('category_profile.opening_hours.monday')
                            ->label('Monday')
                            ->placeholder('09:00 – 18:00'),
                        Forms\Components\TextInput::make('category_profile.opening_hours.tuesday')
                            ->label('Tuesday')
                            ->placeholder('09:00 – 18:00'),
                        Forms\Components\TextInput::make('category_profile.opening_hours.wednesday')
                            ->label('Wednesday')
                            ->placeholder('09:00 – 18:00'),
                        Forms\Components\TextInput::make('category_profile.opening_hours.thursday')
                            ->label('Thursday')
                            ->placeholder('09:00 – 18:00'),
                        Forms\Components\TextInput::make('category_profile.opening_hours.friday')
                            ->label('Friday')
                            ->placeholder('09:00 – 18:00'),
                        Forms\Components\TextInput::make('category_profile.opening_hours.saturday')
                            ->label('Saturday')
                            ->placeholder('10:00 – 16:00'),
                        Forms\Components\TextInput::make('category_profile.opening_hours.sunday')
                            ->label('Sunday')
                            ->placeholder('Closed'),
                        Forms\Components\TagsInput::make('category_profile.booking_slots')
                            ->label('Booking slots')
                            ->placeholder('Add a slot and press Enter')
                            ->helperText('e.g. Lunch 12:00–14:30, Morning MOT, Consultation call')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('category_profile.booking_phone')
                            ->label('Booking phone')
                            ->tel(),
                        Forms\Components\TextInput::make('category_profile.whatsapp')
                            ->label('WhatsApp')
                            ->helperText('Phone number or https://wa.me/… link shown as WhatsApp button'),
                        Forms\Components\Repeater::make('category_profile.social_links')
                            ->label('Social & website links')
                            ->helperText('WWA Social Hub is automatic. Add Facebook, Instagram, or other brand sites (e.g. carservicesltd.com).')
                            ->schema([
                                Forms\Components\Select::make('platform')
                                    ->options([
                                        'custom' => 'Other site / brand page',
                                        'website' => 'Website',
                                        'facebook' => 'Facebook',
                                        'instagram' => 'Instagram',
                                        'linkedin' => 'LinkedIn',
                                        'twitter' => 'X / Twitter',
                                        'youtube' => 'YouTube',
                                        'tiktok' => 'TikTok',
                                    ])
                                    ->default('custom')
                                    ->required(),
                                Forms\Components\TextInput::make('label')
                                    ->label('Label')
                                    ->maxLength(120),
                                Forms\Components\TextInput::make('url')
                                    ->label('URL')
                                    ->url()
                                    ->required()
                                    ->maxLength(500),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->collapsible()
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('category_profile.highlights')
                            ->label('Highlights')
                            ->placeholder('Add highlight')
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('category_profile.services')
                            ->label('Services offered')
                            ->helperText('Automotive, beauty, pets, etc.')
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('category_profile.cuisine')
                            ->label('Cuisine (restaurants)')
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('category_profile.dietary')
                            ->label('Dietary options (restaurants)')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('category_profile.price_range')
                            ->label('Price range')
                            ->placeholder('££ / £££'),
                        Forms\Components\TextInput::make('category_profile.seating_capacity')
                            ->label('Seating capacity')
                            ->numeric(),
                        Forms\Components\Toggle::make('category_profile.outdoor_seating')
                            ->label('Outdoor seating'),
                        Forms\Components\Toggle::make('category_profile.delivery')
                            ->label('Delivery'),
                        Forms\Components\Toggle::make('category_profile.takeaway')
                            ->label('Takeaway'),
                        Forms\Components\Toggle::make('category_profile.emergency_tow')
                            ->label('Emergency / tow available'),
                        Forms\Components\TextInput::make('category_profile.tow_phone')
                            ->label('Tow phone')
                            ->tel(),
                        Forms\Components\TagsInput::make('category_profile.makes_serviced')
                            ->label('Makes serviced (automotive)')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('category_profile.warranties')
                            ->label('Warranties / guarantees')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\KeyValue::make('menu_samples_kv')
                            ->label('Menu samples (name → price)')
                            ->keyLabel('Dish')
                            ->valueLabel('Price')
                            ->helperText('Optional quick menu for restaurants — saved into category profile')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),
                Forms\Components\Section::make('Company Details')
                    ->description('Legal company fields shown on the public business page.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('business_company_name')
                            ->label('Company name')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('business_company_no')
                            ->label('Company number')
                            ->maxLength(50),
                        Forms\Components\DatePicker::make('incorporation_date')
                            ->label('Incorporation date'),
                        Forms\Components\TextInput::make('vat_number')
                            ->label('VAT number')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('duns_number')
                            ->label('DUNS')
                            ->maxLength(32),
                        Forms\Components\TextInput::make('business_company_registration')
                            ->label('Registration number')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('postal_code')
                            ->label('Postcode')
                            ->maxLength(32),
                    ])
                    ->collapsible(),
                Forms\Components\Section::make('Logo & cover')
                    ->schema([
                        Forms\Components\FileUpload::make('business_logo')
                            ->label('Business Logo')
                            ->image()
                            ->directory('business')
                            ->maxSize(2048)
                            ->helperText('Upload business logo (max 2MB)'),
                        Forms\Components\TextInput::make('cover_image')
                            ->label('Cover image URL')
                            ->url()
                            ->maxLength(500)
                            ->helperText('Optional full URL for cover photo'),
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
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->formatStateUsing(function ($record) {
                        return $record->customer ? $record->customer->first_name . ' ' . $record->customer->last_name : '-';
                    }),
                Tables\Columns\TextColumn::make('business_owner')
                    ->label('Owner')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('business_category_slug')
                    ->label('Directory')
                    ->badge()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('City')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('business_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('business_phone_number')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('booking_url')
                    ->label('Booking')
                    ->boolean()
                    ->getStateUsing(fn ($record) => filled($record->booking_url))
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
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
                Tables\Actions\Action::make('open_social')
                    ->label('Social')
                    ->icon('heroicon-o-share')
                    ->color('primary')
                    ->url(function (CustomerBusiness $record): ?string {
                        $community = $record->socialCommunity;
                        if (!$community) {
                            return null;
                        }
                        return BusinessSocialPageResource::socialUrl($community);
                    })
                    ->openUrlInNewTab()
                    ->visible(fn (CustomerBusiness $record): bool => (bool) $record->socialCommunity),
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

    /**
     * Convert Filament KeyValue menu rows into category_profile.menu_samples.
     */
    public static function normalizeCategoryProfileData(array $data): array
    {
        $kv = $data['menu_samples_kv'] ?? null;
        unset($data['menu_samples_kv']);

        if (! isset($data['category_profile']) || ! is_array($data['category_profile'])) {
            $data['category_profile'] = [];
        }

        if (is_array($kv)) {
            $samples = [];
            foreach ($kv as $name => $price) {
                if ($name === null || $name === '') {
                    continue;
                }
                $samples[] = [
                    'name' => (string) $name,
                    'price' => is_scalar($price) ? (string) $price : '',
                ];
            }
            $data['category_profile']['menu_samples'] = $samples;
        }

        if (! empty($data['category_profile']['opening_hours']) && is_array($data['category_profile']['opening_hours'])) {
            $data['category_profile']['opening_hours'] = array_filter(
                $data['category_profile']['opening_hours'],
                static fn ($value) => $value !== null && $value !== ''
            );
        }

        return $data;
    }

    /**
     * Hydrate Filament KeyValue from stored menu_samples array.
     */
    public static function extractMenuSamplesKv(?array $profile): array
    {
        $samples = $profile['menu_samples'] ?? [];
        if (! is_array($samples)) {
            return [];
        }

        $kv = [];
        foreach ($samples as $row) {
            if (! is_array($row) || empty($row['name'])) {
                continue;
            }
            $kv[$row['name']] = $row['price'] ?? ($row['note'] ?? '');
        }

        return $kv;
    }
}
