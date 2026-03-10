<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Filament\Resources\BannerResource\RelationManagers;
use App\Models\Banner;
use App\Models\Customer;
use App\Models\User;
use App\Models\AdPricingPlan;
use App\Models\BannerCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Banner Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Banner Ad';

    protected static ?string $pluralModelLabel = 'Banner Ads';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Banner Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->label('Banner Title')
                            ->maxLength(255)
                            ->columnSpan(2),
                        
                        Forms\Components\TextInput::make('tagline')
                            ->label('Tagline')
                            ->maxLength(255)
                            ->columnSpan(2),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpan('full'),
                        
                        Forms\Components\Select::make('banner_type')
                            ->label('Banner Type')
                            ->options([
                                'standard' => 'Standard Image',
                                'gif' => 'Animated GIF',
                                'html5' => 'HTML5 Banner',
                                'video' => 'Video Banner'
                            ])
                            ->default('standard')
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
                        
                        Forms\Components\TextInput::make('destination_url')
                            ->label('Destination URL')
                            ->url()
                            ->required()
                            ->prefix('https://'),
                        
                        Forms\Components\TextInput::make('cta_text')
                            ->label('Call-to-Action Text')
                            ->placeholder('e.g., Shop Now, Learn More')
                            ->maxLength(100),
                    ])
                    ->columns(4)
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
                    ])
                    ->columns(2)
                    ->columnSpan('full'),
                
                Forms\Components\Section::make('Banner Image')
                    ->schema([
                        Forms\Components\FileUpload::make('img')
                            ->image()
                            ->label('Banner Image')
                            ->maxSize(2048)
                            ->imageEditor()
                            ->directory('banner')
                            ->columnSpan('full')
                            ->required(),
                    ])
                    ->columnSpan('full'),
                
                Forms\Components\Section::make('Pricing & Payment')
                    ->schema([
                        Forms\Components\Select::make('pricing_plan_id')
                            ->label('Pricing Plan')
                            ->options(AdPricingPlan::where('ad_type', 'banner')->active()->pluck('name', 'id'))
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $state ? $set('price', AdPricingPlan::find($state)?->price) : null)
                            ->required(),
                        
                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->default('pending')
                            ->required(),
                        
                        Forms\Components\TextInput::make('payment_transaction_id')
                            ->label('Transaction ID')
                            ->maxLength(255),
                        
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label('Paid At')
                            ->nullable(),
                        
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expires At')
                            ->nullable(),
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
                                'rejected' => 'Rejected',
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
                    ])
                    ->columns(5)
                    ->columnSpan('full'),
                
                Forms\Components\Section::make('User & Category')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('User')
                            ->options(Customer::all()->pluck('name', 'customer_id'))
                            ->default('')
                            ->searchable(),
                        
                        Forms\Components\Select::make('category_id')
                            ->label('Banner Category')
                            ->options(BannerCategory::where('is_active', true)->pluck('name', 'id'))
                            ->searchable(),
                        
                        Forms\Components\Select::make('service_id')
                            ->label('Related Service')
                            ->relationship('service', 'title')
                            ->searchable()
                            ->nullable(),
                    ])
                    ->columns(3)
                    ->columnSpan('full'),
                
                Forms\Components\Placeholder::make('plan_info')
                    ->label('Plan Details')
                    ->content(function ($get) {
                        $planId = $get('pricing_plan_id');
                        if (!$planId) return 'Select a pricing plan to see details';
                        
                        $plan = AdPricingPlan::find($planId);
                        if (!$plan) return 'Plan not found';
                        
                        return "Duration: {$plan->duration_days} days | Featured: " . ($plan->is_featured ? 'Yes' : 'No') . " | Price: $" . number_format($plan->price, 2);
                    })
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('img')
                    ->label('Image')
                    ->defaultImageUrl(url('/placeholder.png'))
                    ->square()
                    ->size(80),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Banner Title')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('banner_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'standard' => 'primary',
                        'gif' => 'success',
                        'html5' => 'warning',
                        'video' => 'danger',
                    }),
                
                Tables\Columns\TextColumn::make('banner_size')
                    ->label('Size')
                    ->badge()
                    ->color('secondary'),
                
                Tables\Columns\TextColumn::make('country')
                    ->label('Country')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('pricingPlan.name')
                    ->label('Pricing Plan')
                    ->searchable()
                    ->limit(20),
                
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'secondary' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                
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
                
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->color(fn ($record) => $record->expires_at && $record->expires_at->isPast() ? 'danger' : null),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->searchable()
                    ->limit(20),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ]),
                
                Tables\Filters\SelectFilter::make('banner_type')
                    ->options([
                        'standard' => 'Standard',
                        'gif' => 'GIF',
                        'html5' => 'HTML5',
                        'video' => 'Video',
                    ]),
                
                Tables\Filters\SelectFilter::make('country')
                    ->searchable()
                    ->preload(),
                
                Tables\Filters\SelectFilter::make('pricing_plan_id')
                    ->label('Pricing Plan')
                    ->options(AdPricingPlan::where('ad_type', 'banner')->active()->pluck('name', 'id')),
                
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
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
