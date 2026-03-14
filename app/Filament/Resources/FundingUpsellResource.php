<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FundingUpsellResource\Pages;
use App\Models\FundingUpsell;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FundingUpsellResource extends Resource
{
    protected static ?string $model = FundingUpsell::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Fundraising';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Upsell Details')
                    ->schema([
                        Forms\Components\Select::make('funding_project_id')
                            ->relationship('fundingProject', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('type')
                            ->options([
                                'promoted' => 'Promoted Project',
                                'featured' => 'Featured Project',
                                'sponsored' => 'Sponsored Project',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $prices = [
                                    'promoted' => 29.99,
                                    'featured' => 59.99,
                                    'sponsored' => 99.99,
                                ];
                                $set('price', $prices[$state] ?? 0);
                            }),
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->step(0.01)
                            ->prefix('$')
                            ->helperText('Price will be set based on upsell type'),
                        Forms\Components\Select::make('currency')
                            ->options(['USD' => 'USD', 'GBP' => 'GBP', 'EUR' => 'EUR'])
                            ->default('USD')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Upsell Benefits')
                    ->schema([
                        Forms\Components\Placeholder::make('benefits_preview')
                            ->content(function (callable $get) {
                                $type = $get('type');
                                if ($type === 'promoted') {
                                    return '• Highlighted card<br>• Appears above standard listings<br>• "Promoted" badge<br>• 2× more visibility';
                                } elseif ($type === 'featured') {
                                    return '• Top of category pages<br>• Larger card design<br>• Priority in search results<br>• Included in weekly "Top Projects" email<br>• "Featured" badge<br>• Most Popular option';
                                } elseif ($type === 'sponsored') {
                                    return '• Homepage placement<br>• Category top placement<br>• Included in homepage slider<br>• Included in social media promotion<br>• "Sponsored" badge<br>• Maximum visibility';
                                }
                                return 'Select an upsell type to see benefits';
                            })
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'whitespace-pre-line']),
                    ]),

                Forms\Components\Section::make('Status & Timing')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending Payment',
                                'paid' => 'Paid & Active',
                                'cancelled' => 'Cancelled',
                                'expired' => 'Expired',
                            ])
                            ->required()
                            ->default('pending'),
                        Forms\Components\DateTimePicker::make('purchased_at')
                            ->helperText('When the upsell was purchased'),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->helperText('When the upsell expires (optional)'),
                        Forms\Components\TextInput::make('transaction_id')
                            ->maxLength(255)
                            ->helperText('Payment transaction ID'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->helperText('Additional notes about this upsell')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fundingProject.title')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'promoted' => 'blue',
                        'featured' => 'purple',
                        'sponsored' => 'gold',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                        'expired' => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->getStateUsing(fn (FundingUpsell $record): bool => $record->isActive())
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\TextColumn::make('purchased_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'promoted' => 'Promoted',
                        'featured' => 'Featured',
                        'sponsored' => 'Sponsored',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Upsells')
                    ->trueLabel('Only Active')
                    ->falseLabel('Only Inactive')
                    ->nullable()
                    ->query(function (Builder $query, array $data) {
                        if ($data['value'] === true) {
                            $query->where('status', 'paid')
                                  ->where(function (Builder $q) {
                                      $q->whereNull('expires_at')
                                        ->orWhere('expires_at', '>', now());
                                  });
                        } elseif ($data['value'] === false) {
                            $query->where(function (Builder $q) {
                                $q->where('status', '!=', 'paid')
                                  ->orWhere(function (Builder $subQ) {
                                      $subQ->whereNotNull('expires_at')
                                            ->where('expires_at', '<=', now());
                                  });
                            });
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('mark_paid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (FundingUpsell $record): bool => $record->status === 'pending')
                    ->action(function (FundingUpsell $record) {
                        $record->update([
                            'status' => 'paid',
                            'purchased_at' => now(),
                        ]);
                        
                        // Update project flags based on upsell type
                        $project = $record->fundingProject;
                        switch ($record->type) {
                            case 'promoted':
                                $project->update(['is_promoted' => true]);
                                break;
                            case 'featured':
                                $project->update(['is_featured' => true]);
                                break;
                            case 'sponsored':
                                $project->update(['is_sponsored' => true]);
                                break;
                        }
                    }),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (FundingUpsell $record): bool => in_array($record->status, ['pending', 'paid']))
                    ->action(function (FundingUpsell $record) {
                        $record->update(['status' => 'cancelled']);
                        
                        // Remove project flags
                        $project = $record->fundingProject;
                        switch ($record->type) {
                            case 'promoted':
                                $project->update(['is_promoted' => false]);
                                break;
                            case 'featured':
                                $project->update(['is_featured' => false]);
                                break;
                            case 'sponsored':
                                $project->update(['is_sponsored' => false]);
                                break;
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_paid_bulk')
                        ->label('Mark Paid')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records) {
                            $records->each(function (FundingUpsell $record) {
                                if ($record->status === 'pending') {
                                    $record->update([
                                        'status' => 'paid',
                                        'purchased_at' => now(),
                                    ]);
                                    
                                    // Update project flags
                                    $project = $record->fundingProject;
                                    switch ($record->type) {
                                        case 'promoted':
                                            $project->update(['is_promoted' => true]);
                                            break;
                                        case 'featured':
                                            $project->update(['is_featured' => true]);
                                            break;
                                        case 'sponsored':
                                            $project->update(['is_sponsored' => true]);
                                            break;
                                    }
                                }
                            });
                        }),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFundingUpsells::route('/'),
            'create' => Pages\CreateFundingUpsell::route('/create'),
            'edit' => Pages\EditFundingUpsell::route('/{record}/edit'),
        ];
    }
}
