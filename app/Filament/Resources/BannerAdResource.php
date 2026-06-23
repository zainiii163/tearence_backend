<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerAdResource\Pages;
use App\Filament\Resources\BannerAdResource\RelationManagers;
use App\Models\BannerAd;
use App\Models\BannerCategory;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class BannerAdResource extends Resource
{
    protected static ?string $model = BannerAd::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Banner Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Banner Ads';

    protected static ?string $modelLabel = 'Banner Ad';

    protected static ?string $pluralModelLabel = 'Banner Ads';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Business Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->label('Banner Title')
                            ->maxLength(255)
                            ->columnSpan(2),
                        
                        Forms\Components\TextInput::make('business_name')
                            ->required()
                            ->label('Business Name')
                            ->maxLength(255)
                            ->columnSpan(2),
                        
                        Forms\Components\TextInput::make('contact_person')
                            ->label('Contact Person')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('email')
                            ->required()
                            ->email()
                            ->label('Email')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone')
                            ->maxLength(50),
                        
                        Forms\Components\TextInput::make('website_url')
                            ->label('Website URL')
                            ->url()
                            ->maxLength(500),
                        
                        Forms\Components\FileUpload::make('business_logo')
                            ->label('Business Logo')
                            ->image()
                            ->disk('public')
                            ->directory('business-logos')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->nullable(),
                    ])
                    ->columns(4)
                    ->columnSpan('full'),
                
                Forms\Components\Section::make('Banner Details')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpan('full'),
                        
                        Forms\Components\Select::make('banner_category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->required(),
                        
                        Forms\Components\Select::make('banner_type')
                            ->label('Banner Type')
                            ->options([
                                'image' => 'Standard Image',
                                'animated' => 'Animated GIF',
                                'html5' => 'HTML5 Banner',
                                'video' => 'Video Banner'
                            ])
                            ->default('image')
                            ->required(),
                        
                        Forms\Components\Select::make('banner_size')
                            ->label('Banner Size')
                            ->options([
                                '728x90' => 'Leaderboard (728×90)',
                                '300x250' => 'Medium Rectangle (300×250)',
                                '160x600' => 'Skyscraper (160×600)',
                                '970x250' => 'Billboard (970×250)',
                                '468x60' => 'Classic Banner (468×60)',
                                '1080x1080' => 'Square Social (1080×1080)'
                            ])
                            ->required(),
                        
                        Forms\Components\FileUpload::make('banner_image')
                            ->label('Banner Image')
                            ->image()
                            ->required()
                            ->disk('public')
                            ->directory('banner-images')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->helperText('Upload the banner image (JPEG, PNG, GIF, or WebP).'),
                        
                        Forms\Components\TextInput::make('destination_link')
                            ->label('Destination Link')
                            ->url()
                            ->required()
                            ->prefix('https://'),
                        
                        Forms\Components\TextInput::make('call_to_action')
                            ->label('Call-to-Action')
                            ->placeholder('e.g., Shop Now, Learn More')
                            ->maxLength(100),
                        
                        Forms\Components\Textarea::make('key_selling_points')
                            ->label('Key Selling Points')
                            ->rows(2)
                            ->columnSpan('full'),
                        
                        Forms\Components\Textarea::make('offer_details')
                            ->label('Offer Details')
                            ->rows(2)
                            ->columnSpan('full'),
                    ])
                    ->columns(3)
                    ->columnSpan('full'),
                
                Forms\Components\Section::make('Location & Targeting')
                    ->schema([
                        Forms\Components\TextInput::make('country')
                            ->label('Country')
                            ->required()
                            ->maxLength(100),
                        
                        Forms\Components\TextInput::make('city')
                            ->label('City')
                            ->maxLength(100),
                        
                        Forms\Components\Textarea::make('target_countries')
                            ->label('Target Countries')
                            ->helperText('Enter countries separated by commas')
                            ->rows(2),
                        
                        Forms\Components\Textarea::make('target_audience')
                            ->label('Target Audience')
                            ->helperText('Describe your target audience')
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->columnSpan('full'),
                
                Forms\Components\Section::make('Promotion & Pricing')
                    ->schema([
                        Forms\Components\Select::make('promotion_tier')
                            ->label('Promotion Tier')
                            ->options([
                                'standard' => 'Standard',
                                'promoted' => 'Promoted',
                                'featured' => 'Featured',
                                'sponsored' => 'Sponsored',
                                'network_boost' => 'Network-Wide Boost'
                            ])
                            ->default('standard')
                            ->required(),
                        
                        Forms\Components\TextInput::make('promotion_price')
                            ->label('Promotion Price')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->default(0.00),
                        
                        Forms\Components\DatePicker::make('promotion_start')
                            ->label('Promotion Start')
                            ->nullable(),
                        
                        Forms\Components\DatePicker::make('promotion_end')
                            ->label('Promotion End')
                            ->nullable()
                            ->afterOrEqual('promotion_start'),
                        
                        Forms\Components\DatePicker::make('validity_start')
                            ->label('Validity Start')
                            ->nullable(),
                        
                        Forms\Components\DatePicker::make('validity_end')
                            ->label('Validity End')
                            ->nullable()
                            ->afterOrEqual('validity_start'),
                        
                        Forms\Components\Toggle::make('is_verified_business')
                            ->label('Verified Business')
                            ->default(false),
                    ])
                    ->columns(3)
                    ->columnSpan('full'),
                
                Forms\Components\Section::make('Status & Analytics')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending',
                                'active' => 'Active (visible on website)',
                                'rejected' => 'Rejected',
                                'expired' => 'Expired'
                            ])
                            ->default('active')
                            ->helperText('Only Active listings appear on the public banner marketplace.')
                            ->required(),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        
                        Forms\Components\TextInput::make('views_count')
                            ->label('Views Count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        
                        Forms\Components\TextInput::make('clicks_count')
                            ->label('Clicks Count')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Approved At')
                            ->disabled()
                            ->nullable(),
                    ])
                    ->columns(5)
                    ->columnSpan('full'),
                
                Forms\Components\Section::make('User Assignment')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->nullable(),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('banner_image')
                    ->label('Image')
                    ->disk('public')
                    ->getStateUsing(fn (BannerAd $record) => static::fileUploadPath(
                        $record->banner_image,
                        'banner-images'
                    ))
                    ->square()
                    ->size(60),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('business_name')
                    ->label('Business')
                    ->searchable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('promotion_tier')
                    ->label('Tier')
                    ->colors([
                        'secondary' => 'standard',
                        'info' => 'promoted',
                        'warning' => 'featured',
                        'success' => 'sponsored',
                        'danger' => 'network_boost',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'standard' => 'Standard',
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        'network_boost' => 'Network Boost',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('country')
                    ->label('Country')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'pending',
                        'success' => 'active',
                        'danger' => 'rejected',
                        'gray' => 'expired',
                    ]),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_verified_business')
                    ->label('Verified')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('clicks_count')
                    ->label('Clicks')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('promotion_price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                    ]),
                
                Tables\Filters\SelectFilter::make('promotion_tier')
                    ->label('Promotion Tier')
                    ->options([
                        'standard' => 'Standard',
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        'network_boost' => 'Network Boost',
                    ]),
                
                Tables\Filters\SelectFilter::make('banner_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('country')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
                
                Tables\Filters\TernaryFilter::make('is_verified_business')
                    ->label('Verified Business')
                    ->placeholder('All')
                    ->trueLabel('Verified')
                    ->falseLabel('Not Verified'),
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
                            'status' => 'active',
                            'is_active' => true,
                            'approved_at' => now(),
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
                
                Tables\Actions\Action::make('verify')
                    ->label('Verify Business')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => !$record->is_verified_business)
                    ->action(function ($record) {
                        $record->update([
                            'is_verified_business' => true,
                        ]);
                    }),
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
                                        'status' => 'active',
                                        'is_active' => true,
                                        'approved_at' => now(),
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

    /**
     * Path for Filament FileUpload / ImageColumn on the public disk.
     */
    public static function fileUploadPath(?string $stored, string $directory): ?string
    {
        if (!$stored) {
            return null;
        }

        if (str_starts_with($stored, 'http://') || str_starts_with($stored, 'https://')) {
            return null;
        }

        return str_contains($stored, '/')
            ? $stored
            : $directory . '/' . $stored;
    }

    /**
     * Normalize Filament upload state to the filename stored in banner_ads.
     */
    public static function normalizeUploadFilename(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = Arr::first(Arr::flatten($value));
        }

        if (!$value || !is_string($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $path = parse_url($value, PHP_URL_PATH);

            return $path ? basename($path) : null;
        }

        return basename($value);
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
            'index' => Pages\ListBannerAds::route('/'),
            'create' => Pages\CreateBannerAd::route('/create'),
            'edit' => Pages\EditBannerAd::route('/{record}/edit'),
        ];
    }
}
