<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdManagementResource\Pages;
use App\Models\Advertisement;
use App\Models\Banner;
use App\Models\Listing;
use App\Models\ListingUpsell;
use App\Models\Customer;
use App\Models\AdPricingPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AdManagementResource extends Resource
{
    protected static ?string $model = Advertisement::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationLabel = 'Ad Management';

    protected static ?string $navigationGroup = 'Advertisement Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Advertisement Details')
                    ->description('Manage your advertisement content and settings')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(['lg' => 2, 'md' => 1, 'sm' => 1]),
                                Forms\Components\Select::make('type')
                                    ->options([
                                        'banner' => 'Banner Ad',
                                        'sponsored' => 'Sponsored Ad',
                                        'featured' => 'Featured Listing',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set) => 
                                        $set('pricing_plan_id', null))
                                    ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 1]),
                            ])
                            ->columns(['lg' => 3, 'md' => 2, 'sm' => 1]),
                        
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->rows(3),
                        
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('url')
                                    ->url()
                                    ->maxLength(255)
                                    ->prefix('https://')
                                    ->columnSpan(['lg' => 2, 'md' => 1, 'sm' => 1]),
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->directory('advertisements')
                                    ->imageEditor()
                                    ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 1]),
                            ])
                            ->columns(['lg' => 3, 'md' => 2, 'sm' => 1]),
                    ])
                    ->columnSpanFull(),
                
                Forms\Components\Section::make('Pricing & Payment')
                    ->description('Configure pricing and payment settings')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('pricing_plan_id')
                                    ->label('Pricing Plan')
                                    ->options(function (callable $get) {
                                        $type = $get('type') ?? 'banner';
                                        return AdPricingPlan::where('ad_type', $type)
                                            ->active()
                                            ->pluck('name', 'id');
                                    })
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set) => 
                                        $state ? $set('price', AdPricingPlan::find($state)?->price) : null)
                                    ->required()
                                    ->columnSpan(['lg' => 2, 'md' => 1, 'sm' => 1]),
                                
                                Forms\Components\TextInput::make('price')
                                    ->label('Price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->disabled()
                                    ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 1]),
                            ])
                            ->columns(['lg' => 3, 'md' => 2, 'sm' => 1]),
                        
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\Select::make('payment_status')
                                    ->label('Payment Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'paid' => 'Paid',
                                        'failed' => 'Failed',
                                        'refunded' => 'Refunded',
                                    ])
                                    ->default('pending')
                                    ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 1]),
                                
                                Forms\Components\TextInput::make('payment_transaction_id')
                                    ->label('Transaction ID')
                                    ->maxLength(255)
                                    ->columnSpan(['lg' => 2, 'md' => 1, 'sm' => 1]),
                            ])
                            ->columns(['lg' => 3, 'md' => 2, 'sm' => 1]),
                    ])
                    ->columnSpanFull(),
                
                Forms\Components\Section::make('Schedule & Status')
                    ->description('Set advertisement schedule and activation')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->schema([
                                Forms\Components\DateTimePicker::make('start_date')
                                    ->label('Start Date')
                                    ->required()
                                    ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 1]),
                                
                                Forms\Components\DateTimePicker::make('end_date')
                                    ->label('End Date')
                                    ->required()
                                    ->columnSpan(['lg' => 1, 'md' => 1, 'sm' => 1]),
                                
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->columnSpan(['lg' => 1, 'md' => 2, 'sm' => 1]),
                            ])
                            ->columns(['lg' => 3, 'md' => 2, 'sm' => 1]),
                        
                        Forms\Components\Placeholder::make('plan_info')
                            ->label('Plan Details')
                            ->content(function ($get) {
                                $planId = $get('pricing_plan_id');
                                if (!$planId) return 'Select a pricing plan to see details';
                                
                                $plan = AdPricingPlan::find($planId);
                                if (!$plan) return 'Plan not found';
                                
                                return "Duration: {$plan->duration_days} days | Featured: " . ($plan->is_featured ? 'Yes' : 'No');
                            })
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->defaultImageUrl(url('/placeholder.png'))
                    ->square()
                    ->size(60)
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->wrap(),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'banner',
                        'success' => 'sponsored',
                        'warning' => 'featured',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                
                Tables\Columns\TextColumn::make('pricingPlan.name')
                    ->label('Pricing Plan')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'secondary' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'warning' => 'refunded',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'banner' => 'Banner Ad',
                        'sponsored' => 'Sponsored Ad',
                        'featured' => 'Featured Listing',
                    ]),
                
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive'),
                
                Tables\Filters\SelectFilter::make('pricing_plan_id')
                    ->label('Pricing Plan')
                    ->options(AdPricingPlan::active()->pluck('name', 'id')),
                
                Tables\Filters\Filter::make('expires_soon')
                    ->label('Expires Soon (7 days)')
                    ->query(fn (Builder $query) => $query->where('end_date', '<=', now()->addDays(7))
                        ->where('end_date', '>', now())),
                
                Tables\Filters\Filter::make('expired')
                    ->label('Expired')
                    ->query(fn (Builder $query) => $query->where('end_date', '<', now())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'payment_status' => 'paid',
                            'is_active' => true,
                        ]);
                    })
                    ->visible(fn ($record) => $record->payment_status === 'pending'),
                
                Tables\Actions\Action::make('toggle_status')
                    ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->color(fn ($record) => $record->is_active ? 'warning' : 'success')
                    ->action(function ($record) {
                        $record->update(['is_active' => !$record->is_active]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('mark_paid')
                        ->label('Mark as Paid')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if ($record->payment_status === 'pending') {
                                    $record->update([
                                        'payment_status' => 'paid',
                                        'is_active' => true,
                                    ]);
                                }
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->poll('60s');
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
            'index' => Pages\ManageAds::route('/'),
            'create' => Pages\CreateAd::route('/create'),
            'view' => Pages\ViewAd::route('/{record}'),
            'edit' => Pages\EditAd::route('/{record}/edit'),
            'analytics' => Pages\AdAnalytics::route('/analytics'),
            'reports' => Pages\AdReports::route('/reports'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return Advertisement::where('is_active', true)->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
