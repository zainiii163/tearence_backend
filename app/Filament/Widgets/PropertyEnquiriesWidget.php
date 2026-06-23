<?php

namespace App\Filament\Widgets;

use App\Models\PropertyEnquiry;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PropertyEnquiriesWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                PropertyEnquiry::with(['property', 'user'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('medium'),
                    
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied')
                    ->copyMessageDuration(1500),
                    
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Phone copied')
                    ->copyMessageDuration(1500),
                    
                Tables\Columns\TextColumn::make('property.title')
                    ->searchable()
                    ->limit(30)
                    ->url(fn (PropertyEnquiry $record): string => route('filament.admin.resources.properties.view', $record->property))
                    ->openUrlInNewTab(),
                    
                Tables\Columns\IconColumn::make('is_read')
                    ->boolean()
                    ->label('Read'),
                    
                Tables\Columns\IconColumn::make('is_important')
                    ->boolean()
                    ->label('Important'),
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'responded' => 'success',
                        'closed' => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Received'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (PropertyEnquiry $record): string => route('filament.admin.resources.property-enquiries.view', $record))
                    ->icon('heroicon-m-eye'),
                    
                Tables\Actions\Action::make('mark_read')
                    ->icon('heroicon-m-check')
                    ->action(fn (PropertyEnquiry $record) => $record->update(['is_read' => true]))
                    ->requiresConfirmation()
                    ->hidden(fn (PropertyEnquiry $record): bool => $record->is_read),
            ])
            ->paginated([5, 10, 25])
            ->striped();
    }
}
