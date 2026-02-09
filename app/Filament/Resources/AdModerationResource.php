<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdModerationResource\Pages;
use App\Models\Listing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AdModerationResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['customer', 'category', 'location']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ad Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'expired' => 'Expired',
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Approval & Moderation')
                    ->schema([
                        Forms\Components\Select::make('approval_status')
                            ->label('Approval Status')
                            ->options([
                                'pending' => 'Pending Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('post_type')
                            ->label('Post Type')
                            ->options([
                                'regular' => 'Regular',
                                'sponsored' => 'Sponsored',
                                'promoted' => 'Promoted',
                                'admin' => 'Admin Post',
                            ])
                            ->default('regular'),

                        Forms\Components\Toggle::make('is_admin_post')
                            ->label('Admin Post')
                            ->helperText('Mark this as an official admin post'),

                        Forms\Components\Toggle::make('is_harmful')
                            ->label('Harmful Content')
                            ->helperText('Mark as harmful content'),

                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->visible(fn ($get) => $get('approval_status') === 'rejected')
                            ->required(fn ($get) => $get('approval_status') === 'rejected')
                            ->maxLength(500),

                        Forms\Components\Textarea::make('moderation_notes')
                            ->label('Moderation Notes')
                            ->helperText('Internal notes for moderation team')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->title),

                Tables\Columns\TextColumn::make('customer.first_name')
                    ->label('Posted By')
                    ->formatStateUsing(fn ($record) => $record->customer?->first_name . ' ' . $record->customer?->last_name)
                    ->searchable(['customer.first_name', 'customer.last_name']),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('approval_status')
                    ->label('Approval Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pending Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    }),

                Tables\Columns\TextColumn::make('post_type')
                    ->label('Post Type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'regular' => 'gray',
                        'sponsored' => 'warning',
                        'promoted' => 'info',
                        'admin' => 'success',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'regular' => 'Regular',
                        'sponsored' => 'Sponsored',
                        'promoted' => 'Promoted',
                        'admin' => 'Admin',
                    }),

                Tables\Columns\IconColumn::make('is_admin_post')
                    ->label('Admin Post')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('is_harmful')
                    ->label('Harmful')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Posted')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_reposted_at')
                    ->label('Last Reposted')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Never'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('approval_status')
                    ->label('Approval Status')
                    ->options([
                        'pending' => 'Pending Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\SelectFilter::make('post_type')
                    ->label('Post Type')
                    ->options([
                        'regular' => 'Regular',
                        'sponsored' => 'Sponsored',
                        'promoted' => 'Promoted',
                        'admin' => 'Admin Post',
                    ]),

                Tables\Filters\Filter::make('is_admin_post')
                    ->label('Admin Posts Only')
                    ->query(fn (Builder $query) => $query->where('is_admin_post', true)),

                Tables\Filters\Filter::make('is_harmful')
                    ->label('Harmful Content')
                    ->query(fn (Builder $query) => $query->where('is_harmful', true)),

                Tables\Filters\Filter::make('old_ads')
                    ->label('Old Ads (21+ days)')
                    ->query(fn (Builder $query) => $query->where('created_at', '<', now()->subDays(21))),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->approval_status === 'pending')
                    ->form([
                        Forms\Components\Select::make('post_type')
                            ->label('Post Type')
                            ->options([
                                'regular' => 'Regular',
                                'sponsored' => 'Sponsored',
                                'promoted' => 'Promoted',
                                'admin' => 'Admin Post',
                            ])
                            ->default('regular')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->approve(auth()->id(), $data['post_type']);
                    })
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->approval_status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->maxLength(500)
                            ->placeholder('Please provide a reason for rejection...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->reject($data['reason']);
                    })
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('mark_harmful')
                    ->label('Mark Harmful')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('warning')
                    ->visible(fn ($record) => !$record->is_harmful)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->maxLength(500)
                            ->placeholder('Why is this content harmful?'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->markAsHarmful($data['reason']);
                    })
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('repost')
                    ->label('Repost')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function ($record) {
                        $record->update([
                            'created_at' => now(),
                            'last_reposted_at' => now(),
                            'approval_status' => 'pending',
                            'approved_by' => null,
                            'approved_at' => null
                        ]);
                    })
                    ->requiresConfirmation()
                    ->modalDescription('This will update the ad date and require re-approval.'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_bulk')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->visible(fn ($records) => $records ? $records->where('approval_status', 'pending')->isNotEmpty() : false)
                        ->form([
                            Forms\Components\Select::make('post_type')
                                ->label('Post Type')
                                ->options([
                                    'regular' => 'Regular',
                                    'sponsored' => 'Sponsored',
                                    'promoted' => 'Promoted',
                                    'admin' => 'Admin Post',
                                ])
                                ->default('regular')
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            if (!$records) return;
                            $records->where('approval_status', 'pending')->each(function ($record) use ($data) {
                                $record->approve(auth()->id(), $data['post_type']);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('reject_bulk')
                        ->label('Reject Selected')
                        ->icon('heroicon-o-x')
                        ->color('danger')
                        ->visible(fn ($records) => $records ? $records->where('approval_status', 'pending')->isNotEmpty() : false)
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Rejection Reason')
                                ->required()
                                ->maxLength(500)
                                ->placeholder('Please provide a reason for rejection...'),
                        ])
                        ->action(function ($records, array $data) {
                            if (!$records) return;
                            $records->where('approval_status', 'pending')->each(function ($record) use ($data) {
                                $record->reject($data['reason']);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('delete_old')
                        ->label('Delete Old Ads (21+ days)')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->action(function ($records) {
                            if (!$records) return;
                            $records->where('created_at', '<', now()->subDays(21))->each->delete();
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
            'index' => Pages\ListAdModerations::route('/'),
            'create' => Pages\CreateAdModeration::route('/create'),
            'view' => Pages\ViewAdModeration::route('/{record}'),
            'edit' => Pages\EditAdModeration::route('/{record}/edit'),
        ];
    }
}
