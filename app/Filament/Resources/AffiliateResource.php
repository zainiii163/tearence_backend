<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AffiliateResource\Pages;
use App\Filament\Resources\AffiliateResource\RelationManagers;
use App\Models\Affiliate;
use App\Models\AdPricingPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AffiliateResource extends Resource
{
    protected static ?string $model = Affiliate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('position')
                    ->required()
                    ->maxLength(10),
                Forms\Components\TextInput::make('link')
                    ->required()
                    ->maxLength(200),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(200),
                Forms\Components\FileUpload::make('image_url')
                    ->image()
                    ->directory('affiliates'),
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
                Forms\Components\Select::make('status')
                    ->label('Display Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active'),
                Forms\Components\Select::make('pricing_plan_id')
                    ->label('Pricing Plan')
                    ->options(AdPricingPlan::where('ad_type', 'affiliate')->active()->pluck('name', 'id'))
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
                Tables\Columns\TextColumn::make('position')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('link')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Image')
                    ->defaultImageUrl(url('/placeholder.png'))
                    ->square(),
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
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Display Status')
                    ->colors([
                        'success' => 'active',
                        'secondary' => 'inactive',
                    ]),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->dateTime()
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
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
                Tables\Filters\SelectFilter::make('pricing_plan_id')
                    ->label('Pricing Plan')
                    ->options(AdPricingPlan::where('ad_type', 'affiliate')->active()->pluck('name', 'id')),
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
            'index' => Pages\ListAffiliates::route('/'),
            'create' => Pages\CreateAffiliate::route('/create'),
            'edit' => Pages\EditAffiliate::route('/{record}/edit'),
        ];
    }
}
