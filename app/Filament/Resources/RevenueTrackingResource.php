<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RevenueTrackingResource\Pages;
use App\Models\RevenueTracking;
use App\Models\Banner;
use App\Models\Affiliate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RevenueTrackingResource extends Resource
{
    protected static ?string $model = RevenueTracking::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Monetization';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Revenue Details')
                    ->columns(2)
            ->schema([
                Forms\Components\Select::make('revenue_type')
                            ->label('Revenue type')
                    ->options([
                        'job_upsell' => 'Job Upsell',
                        'candidate_upsell' => 'Candidate Upsell',
                        'banner' => 'Banner Ad',
                        'affiliate' => 'Affiliate Ad',
                    ])
                            ->required()
                            ->placeholder('Select an option')
                            ->reactive()
                            ->afterStateUpdated(function (callable $set) {
                                $set('related_id', null);
                                $set('banner_id', null);
                                $set('affiliate_id', null);
                            }),
                        Forms\Components\Select::make('related_id')
                    ->label('Related ID')
                            ->options(function ($get, $record) {
                                $revenueType = $get('revenue_type');
                                
                                // When editing, use the record's revenue_type if not set
                                if (!$revenueType && $record) {
                                    $revenueType = $record->revenue_type;
                                }
                                
                                if ($revenueType === 'job_upsell') {
                                    return \App\Models\JobUpsell::with('listing')
                                        ->get()
                                        ->mapWithKeys(function ($upsell) {
                                            $label = 'Job Upsell #' . $upsell->job_upsell_id;
                                            if ($upsell->listing) {
                                                $label .= ' - ' . $upsell->listing->title;
                                            }
                                            $label .= ' (' . ucfirst($upsell->upsell_type) . ' - $' . number_format($upsell->price, 2) . ')';
                                            return [$upsell->job_upsell_id => $label];
                                        });
                                } elseif ($revenueType === 'candidate_upsell') {
                                    return \App\Models\CandidateUpsell::with('candidateProfile.customer')
                                        ->get()
                                        ->mapWithKeys(function ($upsell) {
                                            $label = 'Candidate Upsell #' . $upsell->candidate_upsell_id;
                                            if ($upsell->candidateProfile && $upsell->candidateProfile->customer) {
                                                $label .= ' - ' . $upsell->candidateProfile->customer->first_name . ' ' . $upsell->candidateProfile->customer->last_name;
                                            }
                                            $label .= ' (' . ucfirst(str_replace('_', ' ', $upsell->upsell_type)) . ' - $' . number_format($upsell->price, 2) . ')';
                                            return [$upsell->candidate_upsell_id => $label];
                                        });
                                }
                                
                                return [];
                            })
                            ->getOptionLabelUsing(function ($value, $get, $record) {
                                $revenueType = $get('revenue_type');
                                
                                // When editing, use the record's revenue_type if not set
                                if (!$revenueType && $record) {
                                    $revenueType = $record->revenue_type;
                                }
                                
                                if ($revenueType === 'job_upsell') {
                                    $upsell = \App\Models\JobUpsell::with('listing')->find($value);
                                    if ($upsell) {
                                        $label = 'Job Upsell #' . $upsell->job_upsell_id;
                                        if ($upsell->listing) {
                                            $label .= ' - ' . $upsell->listing->title;
                                        }
                                        $label .= ' (' . ucfirst($upsell->upsell_type) . ' - $' . number_format($upsell->price, 2) . ')';
                                        return $label;
                                    }
                                } elseif ($revenueType === 'candidate_upsell') {
                                    $upsell = \App\Models\CandidateUpsell::with('candidateProfile.customer')->find($value);
                                    if ($upsell) {
                                        $label = 'Candidate Upsell #' . $upsell->candidate_upsell_id;
                                        if ($upsell->candidateProfile && $upsell->candidateProfile->customer) {
                                            $label .= ' - ' . $upsell->candidateProfile->customer->first_name . ' ' . $upsell->candidateProfile->customer->last_name;
                                        }
                                        $label .= ' (' . ucfirst(str_replace('_', ' ', $upsell->upsell_type)) . ' - $' . number_format($upsell->price, 2) . ')';
                                        return $label;
                                    }
                                }
                                
                                return null;
                            })
                            ->required()
                            ->searchable()
                            ->placeholder('Select an option')
                            ->disabled(fn($get) => empty($get('revenue_type')))
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state, $get) {
                                $revenueType = $get('revenue_type');
                                
                                if ($revenueType === 'job_upsell' && $state) {
                                    $upsell = \App\Models\JobUpsell::find($state);
                                    if ($upsell) {
                                        $set('upsell_type', $upsell->upsell_type);
                                        $set('amount', $upsell->price);
                                        if ($upsell->listing) {
                                            $set('customer_id', $upsell->listing->customer_id);
                                        }
                                    }
                                } elseif ($revenueType === 'candidate_upsell' && $state) {
                                    $upsell = \App\Models\CandidateUpsell::find($state);
                                    if ($upsell && $upsell->candidateProfile) {
                                        $set('upsell_type', $upsell->upsell_type);
                                        $set('amount', $upsell->price);
                                        $set('customer_id', $upsell->candidateProfile->customer_id);
                                    }
                                }
                            })
                            ->helperText('Select the related upsell record'),
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'email')
                            ->getSearchResultsUsing(function (string $search) {
                                if (empty($search)) {
                                    return \App\Models\Customer::select(
                                        DB::raw("CONCAT(first_name,' ',last_name,' | ',email) AS full_name"),
                                        'customer_id'
                                    )
                                        ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                        ->pluck('full_name', 'customer_id');
                                }
                                
                                return \App\Models\Customer::select(
                                    DB::raw("CONCAT(first_name,' ',last_name,' | ',email) AS full_name"),
                                    'customer_id'
                                )
                                    ->where(function($q) use ($search) {
                                        $q->where('email', 'like', "%{$search}%")
                                          ->orWhere('first_name', 'like', "%{$search}%")
                                          ->orWhere('last_name', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->pluck('full_name', 'customer_id');
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $customer = \App\Models\Customer::find($value);
                                if ($customer) {
                                    return $customer->first_name . ' ' . $customer->last_name . ' | ' . $customer->email;
                                }
                                return null;
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select an option'),
                Forms\Components\Select::make('upsell_type')
                            ->label('Upsell type')
                    ->options([
                        'featured' => 'Featured Job',
                        'suggested' => 'Suggested Job',
                        'featured_profile' => 'Featured Profile',
                        'job_alerts_boost' => 'Job Alerts Boost',
                    ])
                            ->required()
                            ->placeholder('Select an option'),
                Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                Forms\Components\TextInput::make('currency')
                            ->label('Currency')
                    ->default('USD')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Currency is automatically set to USD'),
                    ]),
                Forms\Components\Section::make('Payment Information')
                    ->columns(2)
                    ->schema([
                Forms\Components\Select::make('payment_method')
                            ->label('Payment method')
                    ->options([
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                                'bank_transfer' => 'Bank Transfer',
                                'cash' => 'Cash',
                            ])
                            ->placeholder('Select an option'),
                Forms\Components\TextInput::make('payment_transaction_id')
                            ->label('Payment transaction id')
                            ->maxLength(255)
                            ->placeholder('Transaction ID from payment gateway'),
                Forms\Components\Select::make('payment_status')
                            ->label('Payment status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ])
                    ->default('pending')
                            ->required()
                            ->placeholder('Select an option'),
                        Forms\Components\DateTimePicker::make('payment_date')
                            ->label('Payment date')
                            ->displayFormat('m/d/Y H:i:s')
                            ->placeholder('mm/dd/yyyy --:--:--'),
                Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Additional notes about this revenue entry'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('revenue_type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'job_upsell' => 'success',
                        'candidate_upsell' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('upsell_type')
                    ->badge(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('revenue_type')
                    ->options([
                        'job_upsell' => 'Job Upsell',
                        'candidate_upsell' => 'Candidate Upsell',
                    ]),
                Tables\Filters\SelectFilter::make('upsell_type')
                    ->options([
                        'featured' => 'Featured Job',
                        'suggested' => 'Suggested Job',
                        'featured_profile' => 'Featured Profile',
                        'job_alerts_boost' => 'Job Alerts Boost',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                    ]),
                Tables\Filters\Filter::make('payment_date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Payment Date From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Payment Date Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRevenueTrackings::route('/'),
            'create' => Pages\CreateRevenueTracking::route('/create'),
            'view' => Pages\ViewRevenueTracking::route('/{record}'),
            'edit' => Pages\EditRevenueTracking::route('/{record}/edit'),
        ];
    }
}

