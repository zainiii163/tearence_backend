<?php

namespace App\Filament\Widgets;

use App\Models\FundingProject;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentFundingProjectsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getTableQuery(): Builder
    {
        return FundingProject::with(['user'])
            ->latest()
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            \Filament\Tables\Columns\TextColumn::make('title')
                ->searchable()
                ->limit(50)
                ->wrap(),
            \Filament\Tables\Columns\TextColumn::make('user.name')
                ->label('Creator')
                ->searchable(),
            \Filament\Tables\Columns\TextColumn::make('category')
                ->badge()
                ->color('primary'),
            \Filament\Tables\Columns\TextColumn::make('funding_goal')
                ->money()
                ->sortable(),
            \Filament\Tables\Columns\TextColumn::make('amount_raised')
                ->money()
                ->getStateUsing(fn (FundingProject $record): string => 
                    '$' . number_format($record->amount_raised, 2)
                ),
            \Filament\Tables\Columns\TextColumn::make('funding_percentage')
                ->label('Funded %')
                ->getStateUsing(fn (FundingProject $record): string => 
                    round($record->funding_percentage, 1) . '%'
                )
                ->color(fn (FundingProject $record): string => 
                    $record->funding_percentage >= 100 ? 'success' : 
                    ($record->funding_percentage >= 50 ? 'warning' : 'danger')
                ),
            \Filament\Tables\Columns\IconColumn::make('is_active')
                ->boolean(),
            \Filament\Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            \Filament\Tables\Actions\Action::make('view')
                ->label('View')
                ->icon('heroicon-o-eye')
                ->url(fn (FundingProject $record): string => route('filament.admin.resources.funding-projects.edit', $record)),
        ];
    }
}
