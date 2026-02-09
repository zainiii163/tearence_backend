<?php

namespace App\Filament\CustomerResources;

use App\Filament\CustomerResources\CustomerKycResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CustomerKycResource extends Resource
{
    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        return $record && $user && $record->id === $user->id;
    }

    public static function canCreate(): bool
    {
        return false; // KYC records are created through the main application
    }

    public static function canDelete($record): bool
    {
        return false; // KYC records should not be deleted
    }

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Account Settings';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('id', Auth::user()->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('KYC Verification Status')
                    ->schema([
                        Forms\Components\Placeholder::make('kyc_status_display')
                            ->label('Current Status')
                            ->content(function ($record) {
                                if (!$record) return 'No KYC record';
                                
                                $status = $record->kyc_status ?? 'pending';
                                $color = match($status) {
                                    'pending' => 'warning',
                                    'submitted' => 'info',
                                    'verified' => 'success',
                                    'rejected' => 'danger',
                                    default => 'gray'
                                };
                                
                                $label = match($status) {
                                    'pending' => 'Pending Review',
                                    'submitted' => 'Submitted',
                                    'verified' => 'Verified',
                                    'rejected' => 'Rejected',
                                    default => 'Unknown'
                                };
                                
                                return new \Illuminate\Support\HtmlString(
                                    "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{$color}-100 text-{$color}-800'>{$label}</span>"
                                );
                            })
                            ->columnSpanFull(),
                        
                        Forms\Components\Placeholder::make('kyc_verified_at_display')
                            ->label('Verification Date')
                            ->content(function ($record) {
                                return $record && $record->kyc_verified_at 
                                    ? $record->kyc_verified_at->format('M d, Y H:i')
                                    : 'Not verified';
                            }),

                        Forms\Components\Textarea::make('kyc_rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn ($record) => $record && $record->kyc_status === 'rejected')
                            ->disabled()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('kyc_documents_info')
                            ->label('Documents')
                            ->content(function ($record) {
                                if (!$record || !$record->kyc_documents) {
                                    return 'No documents submitted';
                                }

                                $documents = json_decode($record->kyc_documents, true);
                                $count = is_array($documents) ? count($documents) : 0;
                                
                                return "{$count} document(s) submitted";
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Posting Limits')
                    ->description('Your current posting limits and status')
                    ->schema([
                        Forms\Components\Placeholder::make('post_count_display')
                            ->label('Current Posts')
                            ->content(function ($record) {
                                return $record ? $record->post_count : 0;
                            }),

                        Forms\Components\Placeholder::make('posting_limit_display')
                            ->label('Posting Limit')
                            ->content(function ($record) {
                                return $record ? $record->posting_limit : 5;
                            }),

                        Forms\Components\Placeholder::make('remaining_posts_display')
                            ->label('Remaining Posts')
                            ->content(function ($record) {
                                if (!$record) return '0';
                                
                                $remaining = max(0, $record->posting_limit - $record->post_count);
                                $color = $remaining <= 1 ? 'danger' : ($remaining <= 2 ? 'warning' : 'success');
                                
                                return new \Illuminate\Support\HtmlString(
                                    "<span class='text-{$color}-600 font-semibold'>{$remaining}</span>"
                                );
                            }),

                        Forms\Components\Placeholder::make('kyc_required_info')
                            ->label('KYC Required')
                            ->content(function ($record) {
                                if (!$record) return 'Unknown';
                                
                                $required = $record->post_count >= $record->posting_limit;
                                $color = $required ? 'warning' : 'success';
                                $text = $required ? 'Yes - Limit reached' : 'No - Posts available';
                                
                                return new \Illuminate\Support\HtmlString(
                                    "<span class='text-{$color}-600'>{$text}</span>"
                                );
                            }),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->formatStateUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                    ->searchable(['first_name', 'last_name']),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('kyc_status')
                    ->label('KYC Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'submitted',
                        'success' => 'verified',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Pending Review',
                        'submitted' => 'Submitted',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        default => 'Unknown',
                    }),

                Tables\Columns\TextColumn::make('post_count')
                    ->label('Posts')
                    ->formatStateUsing(fn ($record) => "{$record->post_count}/{$record->posting_limit}")
                    ->sortable(),

                Tables\Columns\TextColumn::make('kyc_verified_at')
                    ->label('Verified At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kyc_status')
                    ->label('KYC Status')
                    ->options([
                        'pending' => 'Pending Review',
                        'submitted' => 'Submitted',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No bulk actions for customer KYC
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
            'index' => Pages\ViewCustomerKyc::route('/'),
        ];
    }
}
