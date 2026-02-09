<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KycResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KycResource extends Resource
{
    public static function canViewAny(): bool
    {
        return true; // Temporarily allow all access
    }

    public static function canEdit($record): bool
    {
        return true; // Temporarily allow all access
    }

    public static function canCreate(): bool
    {
        return false; // KYC records should not be created manually
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        return $user && $user->is_super_admin;
    }

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNotNull('kyc_documents')
            ->orWhereNotNull('kyc_status');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('KYC Information')
                    ->schema([
                        Forms\Components\Select::make('kyc_status')
                            ->label('KYC Status')
                            ->options([
                                'pending' => 'Pending Review',
                                'submitted' => 'Submitted',
                                'verified' => 'Verified',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('kyc_rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn ($get) => $get('kyc_status') === 'rejected')
                            ->required(fn ($get) => $get('kyc_status') === 'rejected')
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('kyc_documents_preview')
                            ->label('Submitted Documents')
                            ->content(function ($record) {
                                if (!$record || !$record->kyc_documents) {
                                    return 'No documents submitted';
                                }

                                $documents = json_decode($record->kyc_documents, true);
                                if (empty($documents)) {
                                    return 'No documents submitted';
                                }

                                $html = '<div class="space-y-2">';
                                foreach ($documents as $doc) {
                                    $html .= sprintf(
                                        '<div class="flex items-center space-x-2">
                                            <span class="text-sm font-medium">%s:</span>
                                            <span class="text-sm text-gray-600">%s</span>
                                            <span class="text-xs text-gray-500">%s</span>
                                        </div>',
                                        ucfirst($doc['type'] ?? 'Document'),
                                        $doc['original_name'] ?? 'Unknown',
                                        $doc['uploaded_at'] ?? 'Unknown date'
                                    );
                                }
                                $html .= '</div>';

                                return new \Illuminate\Support\HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Name')
                    ->formatStateUsing(fn ($record) => $record->first_name . ' ' . $record->last_name)
                    ->searchable(['first_name', 'last_name']),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('kyc_status')
                    ->label('KYC Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'submitted' => 'info',
                        'verified' => 'success',
                        'rejected' => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pending Review',
                        'submitted' => 'Submitted',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    }),

                Tables\Columns\TextColumn::make('kyc_verified_at')
                    ->label('Verified At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kyc_rejection_reason')
                    ->label('Rejection Reason')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->kyc_rejection_reason),
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

                Tables\Filters\Filter::make('has_documents')
                    ->label('Has Documents')
                    ->query(fn (Builder $query) => $query->whereNotNull('kyc_documents'))
                    ->default(),

                Tables\Filters\Filter::make('verified_users')
                    ->label('Verified Only')
                    ->query(fn (Builder $query) => $query->where('kyc_status', 'verified')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('approve')
                    ->label('Approve KYC')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => in_array($record->kyc_status, ['pending', 'submitted']))
                    ->action(function ($record) {
                        $record->approveKyc();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Approve KYC Verification')
                    ->modalDescription('Are you sure you want to approve this user\'s KYC verification?'),

                Tables\Actions\Action::make('reject')
                    ->label('Reject KYC')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array($record->kyc_status, ['pending', 'submitted']))
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(500)
                            ->placeholder('Please provide a reason for rejection...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->rejectKyc($data['reason']);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Reject KYC Verification')
                    ->modalDescription('Please provide a reason for rejecting this KYC verification.'),

                Tables\Actions\Action::make('view_documents')
                    ->label('View Documents')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn ($record) => !empty($record->kyc_documents))
                    ->modalContent(function ($record) {
                        $documents = json_decode($record->kyc_documents, true) ?? [];
                        
                        $html = '<div class="space-y-4">';
                        foreach ($documents as $doc) {
                            $html .= sprintf(
                                '<div class="border rounded-lg p-4">
                                    <h4 class="font-semibold mb-2">%s</h4>
                                    <p class="text-sm text-gray-600 mb-2">File: %s</p>
                                    <p class="text-xs text-gray-500">Uploaded: %s</p>
                                    <img src="%s" alt="%s" class="mt-2 max-w-xs rounded" style="max-height: 200px; object-fit: cover;">
                                </div>',
                                ucfirst($doc['type'] ?? 'Document'),
                                $doc['original_name'] ?? 'Unknown',
                                $doc['uploaded_at'] ?? 'Unknown date',
                                asset('storage/' . $doc['path']) ?? '',
                                $doc['original_name'] ?? 'Document'
                            );
                        }
                        $html .= '</div>';

                        return new \Illuminate\Support\HtmlString($html);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_bulk')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->approveKyc();
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('reject_bulk')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Rejection Reason')
                                ->required()
                                ->maxLength(500)
                                ->placeholder('Please provide a reason for rejection...'),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->rejectKyc($data['reason']);
                            });
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
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
            'index' => Pages\ListKycs::route('/'),
            'create' => Pages\CreateKyc::route('/create'),
            'view' => Pages\ViewKyc::route('/{record}'),
            'edit' => Pages\EditKyc::route('/{record}/edit'),
        ];
    }
}
