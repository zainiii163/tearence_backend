<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Filament\Resources\BannerResource\RelationManagers;
use App\Models\Banner;
use App\Models\Customer;
use App\Models\User;
use App\Models\AdPricingPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->label('Banner Title')
                    ->maxLength(100),
                Forms\Components\TextInput::make('url_link')
                    ->required()
                    ->label('Banner Link')
                    ->maxLength(100)
                    ->default('https://worldwideadverts.info'),
                Forms\Components\FileUpload::make('img')
                    ->image()
                    ->label('Banner Image')
                    ->maxSize(512)
                    ->columnSpan('full')
                    ->directory('banner'),
                Forms\Components\TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->prefix('$')
                    ->step(0.01)
                    ->default(0.00),
                Forms\Components\Select::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ])
                    ->default('pending'),
                Forms\Components\TextInput::make('payment_transaction_id')
                    ->label('Transaction ID')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('paid_at')
                    ->label('Paid At')
                    ->nullable(),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('Expires At')
                    ->nullable(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->options(Customer::all()->pluck('name', 'customer_id'))
                    ->default('')
                    ->searchable(),
                Forms\Components\Select::make('pricing_plan_id')
                    ->label('Pricing Plan')
                    ->options(AdPricingPlan::where('ad_type', 'banner')->active()->pluck('name', 'id'))
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $state ? $set('price', AdPricingPlan::find($state)?->price) : null)
                    ->required(),
                Forms\Components\Placeholder::make('plan_info')
                    ->label('Plan Details')
                    ->content(function ($get) {
                        $planId = $get('pricing_plan_id');
                        if (!$planId) return 'Select a pricing plan to see details';
                        
                        $plan = AdPricingPlan::find($planId);
                        if (!$plan) return 'Plan not found';
                        
                        return "Duration: {$plan->duration_days} days | Featured: " . ($plan->is_featured ? 'Yes' : 'No');
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
                    ->square(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Banner Title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url_link')
                    ->label('Banner Link')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('pricingPlan.name')
                    ->label('Pricing Plan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Payment Status')
                    ->colors([
                        'secondary' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
                Tables\Filters\SelectFilter::make('pricing_plan_id')
                    ->label('Pricing Plan')
                    ->options(AdPricingPlan::where('ad_type', 'banner')->active()->pluck('name', 'id')),
                Tables\Filters\Filter::make('expires_soon')
                    ->label('Expires Soon')
                    ->query(fn (Builder $query) => $query->where('expires_at', '<=', now()->addDays(7))
                        ->where('expires_at', '>', now())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'payment_status' => 'paid',
                            'paid_at' => now(),
                            'is_active' => true,
                        ]);
                    })
                    ->visible(fn ($record) => $record->payment_status === 'pending'),
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
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
