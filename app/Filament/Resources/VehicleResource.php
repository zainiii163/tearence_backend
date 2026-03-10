<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use App\Models\AdPricingPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Vehicle Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Vehicle';

    protected static ?string $pluralModelLabel = 'Vehicles';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Owner')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->default(fn() => Auth::id()),
                        
                        Forms\Components\Select::make('business_id')
                            ->label('Business')
                            ->relationship('business', 'name')
                            ->searchable()
                            ->nullable(),
                        
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->options(VehicleCategory::active()->pluck('name', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('category_name', VehicleCategory::find($state)?->name)),
                        
                        Forms\Components\Select::make('make_id')
                            ->label('Make')
                            ->options(VehicleMake::active()->pluck('name', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('model_id', null)),
                        
                        Forms\Components\Select::make('model_id')
                            ->label('Model')
                            ->options(function (callable $get) {
                                $makeId = $get('make_id');
                                if (!$makeId) return [];
                                return VehicleModel::where('make_id', $makeId)->active()->pluck('name', 'id');
                            })
                            ->required()
                            ->reactive(),
                        
                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        
                        Forms\Components\TextInput::make('tagline')
                            ->label('Tagline')
                            ->maxLength(255)
                            ->columnSpan(2),
                        
                        Forms\Components\RichEditor::make('description')
                            ->label('Description')
                            ->columnSpan('full'),
                        
                        Forms\Components\Select::make('advert_type')
                            ->label('Advert Type')
                            ->options([
                                'sale' => 'For Sale',
                                'hire' => 'For Hire',
                                'lease' => 'For Lease',
                                'transport_service' => 'Transport Service'
                            ])
                            ->required(),
                        
                        Forms\Components\Select::make('condition')
                            ->label('Condition')
                            ->options([
                                'new' => 'New',
                                'used' => 'Used',
                                'excellent' => 'Excellent',
                                'good' => 'Good',
                                'fair' => 'Fair'
                            ])
                            ->required(),
                    ])
                    ->columns(4)
                    ->columnSpan('full'),

                Forms\Components\Section::make('Vehicle Specifications')
                    ->schema([
                        Forms\Components\TextInput::make('year')
                            ->label('Year')
                            ->numeric()
                            ->required()
                            ->min(1900)
                            ->max(date('Y') + 1),
                        
                        Forms\Components\TextInput::make('mileage')
                            ->label('Mileage')
                            ->numeric()
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('fuel_type')
                            ->label('Fuel Type')
                            ->maxLength(50)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('transmission')
                            ->label('Transmission')
                            ->maxLength(50)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('engine_size')
                            ->label('Engine Size')
                            ->maxLength(50)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('color')
                            ->label('Color')
                            ->maxLength(50)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('doors')
                            ->label('Doors')
                            ->numeric()
                            ->nullable()
                            ->min(1)
                            ->max(10),
                        
                        Forms\Components\TextInput::make('seats')
                            ->label('Seats')
                            ->numeric()
                            ->nullable()
                            ->min(1)
                            ->max(20),
                        
                        Forms\Components\TextInput::make('body_type')
                            ->label('Body Type')
                            ->maxLength(50)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('vin')
                            ->label('VIN')
                            ->maxLength(17)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('registration_number')
                            ->label('Registration Number')
                            ->maxLength(50)
                            ->nullable(),
                    ])
                    ->columns(4)
                    ->columnSpan('full'),

                Forms\Components\Section::make('Commercial Vehicle Specifications')
                    ->schema([
                        Forms\Components\TextInput::make('payload_capacity')
                            ->label('Payload Capacity')
                            ->numeric()
                            ->step(0.01)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('axles')
                            ->label('Axles')
                            ->numeric()
                            ->nullable()
                            ->min(1)
                            ->max(10),
                        
                        Forms\Components\TextInput::make('emission_class')
                            ->label('Emission Class')
                            ->maxLength(20)
                            ->nullable(),
                    ])
                    ->columns(3)
                    ->columnSpan('full'),

                Forms\Components\Section::make('Boat Specifications')
                    ->schema([
                        Forms\Components\TextInput::make('length')
                            ->label('Length')
                            ->numeric()
                            ->step(0.01)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('engine_type')
                            ->label('Engine Type')
                            ->maxLength(50)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('capacity')
                            ->label('Capacity')
                            ->numeric()
                            ->nullable()
                            ->min(1),
                        
                        Forms\Components\Toggle::make('trailer_included')
                            ->label('Trailer Included')
                            ->default(false),
                    ])
                    ->columns(4)
                    ->columnSpan('full'),

                Forms\Components\Section::make('Transport Service Specifications')
                    ->schema([
                        Forms\Components\Textarea::make('service_area')
                            ->label('Service Area')
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('operating_hours')
                            ->label('Operating Hours')
                            ->maxLength(100)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('passenger_capacity')
                            ->label('Passenger Capacity')
                            ->numeric()
                            ->nullable()
                            ->min(1),
                        
                        Forms\Components\TextInput::make('luggage_capacity')
                            ->label('Luggage Capacity')
                            ->numeric()
                            ->nullable()
                            ->min(0),
                        
                        Forms\Components\Toggle::make('airport_pickup')
                            ->label('Airport Pickup')
                            ->default(false),
                    ])
                    ->columns(3)
                    ->columnSpan('full'),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->nullable(),
                        
                        Forms\Components\Select::make('price_type')
                            ->label('Price Type')
                            ->options([
                                'fixed' => 'Fixed Price',
                                'per_day' => 'Per Day',
                                'per_week' => 'Per Week',
                                'per_month' => 'Per Month',
                                'per_hour' => 'Per Hour'
                            ])
                            ->required(),
                        
                        Forms\Components\Toggle::make('negotiable')
                            ->label('Negotiable')
                            ->default(false),
                        
                        Forms\Components\TextInput::make('deposit')
                            ->label('Deposit')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->nullable(),
                    ])
                    ->columns(4)
                    ->columnSpan('full'),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('main_image')
                            ->label('Main Image')
                            ->image()
                            ->maxSize(2048)
                            ->imageEditor()
                            ->directory('vehicles')
                            ->columnSpan('full')
                            ->required(),
                        
                        Forms\Components\FileUpload::make('additional_images')
                            ->label('Additional Images')
                            ->image()
                            ->maxSize(2048)
                            ->imageEditor()
                            ->directory('vehicles')
                            ->multiple()
                            ->maxFiles(15)
                            ->columnSpan('full'),
                        
                        Forms\Components\TextInput::make('video_link')
                            ->label('Video Link')
                            ->url()
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columnSpan('full'),

                Forms\Components\Section::make('Location')
                    ->schema([
                        Forms\Components\TextInput::make('country')
                            ->label('Country')
                            ->required()
                            ->maxLength(100),
                        
                        Forms\Components\TextInput::make('city')
                            ->label('City')
                            ->required()
                            ->maxLength(100),
                        
                        Forms\Components\Textarea::make('address')
                            ->label('Address')
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->step(0.000001)
                            ->between(-90, 90)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->step(0.000001)
                            ->between(-180, 180)
                            ->nullable(),
                        
                        Forms\Components\Toggle::make('show_exact_location')
                            ->label('Show Exact Location')
                            ->default(true),
                    ])
                    ->columns(3)
                    ->columnSpan('full'),

                Forms\Components\Section::make('Contact Information')
                    ->schema([
                        Forms\Components\TextInput::make('contact_name')
                            ->label('Contact Name')
                            ->maxLength(100)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('contact_phone')
                            ->label('Contact Phone')
                            ->maxLength(50)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('contact_email')
                            ->label('Contact Email')
                            ->email()
                            ->maxLength(100)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(4)
                    ->columnSpan('full'),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Repeater::make('features')
                            ->label('Features')
                            ->simple()
                            ->schema([
                                Forms\Components\TextInput::make('feature')
                                    ->label('Feature')
                                    ->required()
                                    ->maxLength(100)
                            ])
                            ->columnSpan('full'),
                        
                        Forms\Components\Textarea::make('service_history')
                            ->label('Service History')
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('mot_expiry')
                            ->label('MOT Expiry')
                            ->date()
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('road_tax_status')
                            ->label('Road Tax Status')
                            ->maxLength(50)
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('previous_owners')
                            ->label('Previous Owners')
                            ->numeric()
                            ->nullable()
                            ->min(0),
                    ])
                    ->columns(3)
                    ->columnSpan('full'),

                Forms\Components\Section::make('Status & Visibility')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Approval Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected'
                            ])
                            ->default('pending')
                            ->required(),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('is_promoted')
                            ->label('Promoted')
                            ->default(false),
                        
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),
                        
                        Forms\Components\Toggle::make('is_sponsored')
                            ->label('Sponsored')
                            ->default(false),
                        
                        Forms\Components\Toggle::make('is_top_of_category')
                            ->label('Top of Category')
                            ->default(false),
                    ])
                    ->columns(6)
                    ->columnSpan('full'),

                Forms\Components\Section::make('Payment & Expiry')
                    ->schema([
                        Forms\Components\Select::make('pricing_plan_id')
                            ->label('Pricing Plan')
                            ->options(AdPricingPlan::where('ad_type', 'vehicle')->active()->pluck('name', 'id'))
                            ->reactive()
                            ->nullable(),
                        
                        Forms\Components\Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed'
                            ])
                            ->default('pending')
                            ->required(),
                        
                        Forms\Components\TextInput::make('paid_amount')
                            ->label('Paid Amount')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('$')
                            ->nullable(),
                        
                        Forms\Components\TextInput::make('payment_transaction_id')
                            ->label('Transaction ID')
                            ->maxLength(255)
                            ->nullable(),
                        
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Paid At')
                            ->nullable(),
                        
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->nullable(),
                    ])
                    ->columns(3)
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image_url')
                    ->label('Image')
                    ->defaultImageUrl(url('/placeholder.png'))
                    ->square()
                    ->size(80),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Vehicle')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('advert_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sale' => 'success',
                        'hire' => 'info',
                        'lease' => 'warning',
                        'transport_service' => 'primary',
                    }),
                
                Tables\Columns\TextColumn::make('year')
                    ->label('Year')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_promoted')
                    ->label('Promoted')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_sponsored')
                    ->label('Sponsored')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('views')
                    ->label('Views')
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('saves')
                    ->label('Saves')
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('enquiries')
                    ->label('Enquiries')
                    ->sortable()
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->limit(20),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->color(fn ($record) => $record->expires_at && $record->expires_at->isPast() ? 'danger' : null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                
                Tables\Filters\SelectFilter::make('advert_type')
                    ->options([
                        'sale' => 'For Sale',
                        'hire' => 'For Hire',
                        'lease' => 'For Lease',
                        'transport_service' => 'Transport Service',
                    ]),
                
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(VehicleCategory::active()->pluck('name', 'id'))
                    ->searchable(),
                
                Tables\Filters\SelectFilter::make('make_id')
                    ->label('Make')
                    ->options(VehicleMake::active()->pluck('name', 'id'))
                    ->searchable(),
                
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ]),
                
                Tables\Filters\Filter::make('expires_soon')
                    ->label('Expires Soon')
                    ->query(fn (Builder $query) => $query->where('expires_at', '<=', now()->addDays(7))
                        ->where('expires_at', '>', now())),
                
                Tables\Filters\Filter::make('expired')
                    ->label('Expired')
                    ->query(fn (Builder $query) => $query->where('expires_at', '<', now())),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
                
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->placeholder('All')
                    ->trueLabel('Featured')
                    ->falseLabel('Not Featured'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                            'is_active' => true,
                        ]);
                    }),
                
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'rejected',
                            'is_active' => false,
                        ]);
                    }),
                
                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->payment_status === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'payment_status' => 'paid',
                            'paid_at' => now(),
                            'is_active' => true,
                        ]);
                    }),
                
                Tables\Actions\Action::make('extend')
                    ->label('Extend')
                    ->icon('heroicon-o-calendar')
                    ->color('warning')
                    ->form([
                        Forms\Components\DatePicker::make('new_expiry')
                            ->label('New Expiry Date')
                            ->required()
                            ->minDate(now())
                            ->default(fn ($record) => $record->expires_at ? $record->expires_at->addDays(30) : now()->addDays(30)),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'expires_at' => $data['new_expiry'],
                        ]);
                    })
                    ->visible(fn ($record) => $record->payment_status === 'paid'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('approve_bulk')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'approved',
                                        'is_active' => true,
                                    ]);
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('mark_paid_bulk')
                        ->label('Mark as Paid')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->payment_status === 'pending') {
                                    $record->update([
                                        'payment_status' => 'paid',
                                        'paid_at' => now(),
                                        'is_active' => true,
                                    ]);
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListVehicles::route('/'),
            // 'create' => Pages\CreateVehicle::route('/create'),
            // 'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
