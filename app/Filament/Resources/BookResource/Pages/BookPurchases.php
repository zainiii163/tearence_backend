<?php

namespace App\Filament\Resources\BookResource\Pages;

use App\Models\BookPurchase;
use App\Models\Listing;
use App\Filament\Resources\BookResource;
use Filament\Actions;
use Filament\Resources\Pages\Page as ResourcePage;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class BookPurchases extends ResourcePage implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = BookResource::class;

    protected static string $view = 'filament.resources.book-resource.pages.book-purchases';

    protected static ?string $title = 'Book Purchases';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BookPurchase::query()
                    ->with(['listing', 'customer'])
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('purchase_id')
                    ->label('Purchase ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('listing.title')
                    ->label('Book Title')
                    ->limit(50)
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('price_paid')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'gray' => 'refunded',
                    ]),
                
                Tables\Columns\TextColumn::make('total_downloads')
                    ->label('Downloads')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Purchase Date'),
                
                Tables\Columns\TextColumn::make('download_token_expires_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Token Expires'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('From Date'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (BookPurchase $record): string => route('filament.admin.resources.books.view', ['record' => $record->listing_id])),
                
                Tables\Actions\Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (BookPurchase $record) => $record->payment_status === 'completed')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label('Refund Reason')
                            ->required(),
                    ])
                    ->action(function (BookPurchase $record, array $data) {
                        $record->refund($data['reason']);
                        $this->notify('success', 'Purchase refunded successfully');
                    }),
                
                Tables\Actions\Action::make('regenerate_token')
                    ->label('Regenerate Token')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (BookPurchase $record) => $record->payment_status === 'completed')
                    ->action(function (BookPurchase $record) {
                        $record->regenerateDownloadToken();
                        $this->notify('success', 'Download token regenerated');
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('refund')
                        ->label('Refund Selected')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('danger')
                        ->form([
                            \Filament\Forms\Components\Textarea::make('reason')
                                ->label('Refund Reason')
                                ->required(),
                        ])
                        ->action(function (array $selectedRecords, array $data) {
                            BookPurchase::whereIn('purchase_id', $selectedRecords)
                                ->where('payment_status', 'completed')
                                ->get()
                                ->each(function (BookPurchase $purchase) use ($data) {
                                    $purchase->refund($data['reason']);
                                });
                            $this->notify('success', 'Selected purchases refunded');
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BookStatsWidget::class,
        ];
    }
}
