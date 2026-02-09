<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentJobsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Listing::query()->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Job Title')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Employer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category'),
                Tables\Columns\TextColumn::make('job_type')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        'expired' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->heading('Recent Job Postings');
    }
}

