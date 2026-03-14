<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeaturedAdvertResource\Pages;
use App\Models\FeaturedAdvert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;

class FeaturedAdvertResource extends Resource
{
    protected static ?string $model = FeaturedAdvert::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Featured Adverts';

    protected static ?string $modelLabel = 'Featured Advert';

    protected static ?string $pluralModelLabel = 'Featured Adverts';

    protected static ?string $navigationGroup = 'Advert Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Information')
                    ->description('Main advert details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                
                                Textarea::make('description')
                                    ->maxLength(5000)
                                    ->columnSpanFull(),
                                
                                Select::make('advert_type')
                                    ->required()
                                    ->options([
                                        'product' => 'Product / Item for Sale',
                                        'service' => 'Service / Business Offer',
                                        'property' => 'Property / Real Estate',
                                        'job' => 'Job / Recruitment',
                                        'event' => 'Event / Experience',
                                        'vehicle' => 'Vehicles / Motors',
                                        'business' => 'Business Opportunity',
                                        'education' => 'Education / Course',
                                        'travel' => 'Travel / Experience',
                                        'fashion' => 'Fashion / Beauty',
                                        'electronics' => 'Electronics',
                                        'pets' => 'Pets / Animals',
                                        'home' => 'Home / Garden',
                                        'health' => 'Health / Wellness',
                                        'misc' => 'Miscellaneous / Other',
                                    ])
                                    ->required(),
                                
                                Select::make('condition')
                                    ->options([
                                        'new' => 'New',
                                        'used' => 'Used',
                                        'refurbished' => 'Refurbished',
                                    ])
                                    ->nullable(),
                            ]),
                    ]),
                
                Section::make('Pricing & Payment')
                    ->description('Pricing details and payment status')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('price')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(999999.99)
                                    ->prefix('£'),
                                
                                Select::make('currency')
                                    ->options([
                                        'GBP' => 'GBP (£)',
                                        'USD' => 'USD ($)',
                                        'EUR' => 'EUR (€)',
                                    ])
                                    ->default('GBP'),
                                
                                Select::make('payment_status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'paid' => 'Paid',
                                        'failed' => 'Failed',
                                    ])
                                    ->default('pending')
                                    ->required(),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                Select::make('upsell_tier')
                                    ->required()
                                    ->options([
                                        'promoted' => 'Promoted (£29.99)',
                                        'featured' => 'Featured (£59.99)',
                                        'sponsored' => 'Sponsored (£99.99)',
                                    ])
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set) => 
                                        $set('upsell_price', match($state) {
                                            'promoted' => 29.99,
                                            'featured' => 59.99,
                                            'sponsored' => 99.99,
                                            default => 0,
                                        })
                                    ),
                                
                                TextInput::make('upsell_price')
                                    ->numeric()
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->maxValue(99999.99)
                                    ->prefix('£')
                                    ->required(),
                            ]),
                    ]),
                
                Section::make('Contact Information')
                    ->description('Seller contact details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('contact_name')
                                    ->required()
                                    ->maxLength(255),
                                
                                TextInput::make('contact_email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                
                                TextInput::make('contact_phone')
                                    ->maxLength(50),
                                
                                TextInput::make('website')
                                    ->url()
                                    ->maxLength(255),
                            ]),
                    ]),
                
                Section::make('Location')
                    ->description('Geographic information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('country')
                                    ->required()
                                    ->maxLength(100),
                                
                                TextInput::make('city')
                                    ->required()
                                    ->maxLength(100),
                                
                                TextInput::make('latitude')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->between(-90, 90),
                                
                                TextInput::make('longitude')
                                    ->numeric()
                                    ->step(0.00000001)
                                    ->between(-180, 180),
                            ]),
                    ]),
                
                Section::make('Media')
                    ->description('Images and video')
                    ->schema([
                        FileUpload::make('images')
                            ->multiple()
                            ->maxFiles(10)
                            ->image()
                            ->directory('featured-adverts')
                            ->visibility('public'),
                        
                        TextInput::make('video_url')
                            ->url()
                            ->maxLength(255)
                            ->label('Video URL'),
                    ]),
                
                Section::make('Schedule')
                    ->description('Advert activation and expiry')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->required()
                                    ->default(now()),
                                
                                DateTimePicker::make('expires_at')
                                    ->required()
                                    ->after('starts_at')
                                    ->default(now()->addDays(30)),
                            ]),
                    ]),
                
                Section::make('Admin Settings')
                    ->description('Administrative controls')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        
                        Toggle::make('is_verified_seller')
                            ->label('Verified Seller')
                            ->default(false),
                        
                        Textarea::make('admin_notes')
                            ->maxLength(1000)
                            ->label('Admin Notes'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(50),
                
                BadgeColumn::make('upsell_tier')
                    ->label('Tier')
                    ->colors([
                        'warning' => 'promoted',
                        'success' => 'featured',
                        'danger' => 'sponsored',
                    ]),
                
                BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                    ]),
                
                TextColumn::make('formatted_price')
                    ->label('Price')
                    ->sortable()
                    ->money('GBP'),
                
                TextColumn::make('country')
                    ->label('Country')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('contact_name')
                    ->label('Contact')
                    ->searchable()
                    ->limit(30),
                
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                
                IconColumn::make('is_verified_seller')
                    ->label('Verified')
                    ->boolean(),
                
                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime()
                    ->sortable(),
                
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable(),
                
                TextColumn::make('view_count')
                    ->label('Views')
                    ->sortable()
                    ->alignCenter(),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('upsell_tier')
                    ->options([
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                    ]),
                
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ]),
                
                Tables\Filters\SelectFilter::make('advert_type')
                    ->options([
                        'product' => 'Product',
                        'service' => 'Service',
                        'property' => 'Property',
                        'job' => 'Job',
                        'event' => 'Event',
                        'vehicle' => 'Vehicle',
                        'business' => 'Business',
                        'education' => 'Education',
                        'travel' => 'Travel',
                        'fashion' => 'Fashion',
                        'electronics' => 'Electronics',
                        'pets' => 'Pets',
                        'home' => 'Home',
                        'health' => 'Health',
                        'misc' => 'Miscellaneous',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                
                Tables\Filters\TernaryFilter::make('is_verified_seller')
                    ->label('Verified Seller'),
                
                Tables\Filters\Filter::make('expires_at')
                    ->form([
                        DatePicker::make('expires_from'),
                        DatePicker::make('expires_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['expires_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expires_at', '>=', $date),
                            )
                            ->when(
                                $data['expires_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expires_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                
                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (FeaturedAdvert $record): bool => !$record->is_active)
                    ->action(function (FeaturedAdvert $record) {
                        $record->update(['is_active' => true]);
                        Notification::make()
                            ->title('Featured advert activated')
                            ->success()
                            ->send();
                    }),
                
                Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (FeaturedAdvert $record): bool => $record->is_active)
                    ->action(function (FeaturedAdvert $record) {
                        $record->update(['is_active' => false]);
                        Notification::make()
                            ->title('Featured advert deactivated')
                            ->success()
                            ->send();
                    }),
                
                Action::make('mark_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (FeaturedAdvert $record): bool => $record->payment_status === 'pending')
                    ->action(function (FeaturedAdvert $record) {
                        $record->update(['payment_status' => 'paid']);
                        Notification::make()
                            ->title('Payment status updated')
                            ->success()
                            ->send();
                    }),
                
                Action::make('verify_seller')
                    ->label('Verify Seller')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (FeaturedAdvert $record): bool => !$record->is_verified_seller)
                    ->action(function (FeaturedAdvert $record) {
                        $record->update(['is_verified_seller' => true]);
                        Notification::make()
                            ->title('Seller verified')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                            Notification::make()
                                ->title('Selected featured adverts activated')
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                            Notification::make()
                                ->title('Selected featured adverts deactivated')
                                ->success()
                                ->send();
                        }),
                    
                    Tables\Actions\BulkAction::make('mark_paid')
                        ->label('Mark as Paid')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each->update(['payment_status' => 'paid']);
                            Notification::make()
                                ->title('Payment status updated')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListFeaturedAdverts::route('/'),
            'create' => Pages\CreateFeaturedAdvert::route('/create'),
            'view' => Pages\ViewFeaturedAdvert::route('/{record}'),
            'edit' => Pages\EditFeaturedAdvert::route('/{record}/edit'),
        ];
    }
}
