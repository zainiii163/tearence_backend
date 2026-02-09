<?php

namespace App\Filament\Resources\JobAlertResource\Pages;

use App\Filament\Resources\JobAlertResource;
use App\Models\Listing;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ViewMatchingJobs extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = JobAlertResource::class;

    protected static string $view = 'filament.resources.job-alert-resource.pages.view-matching-jobs';

    public $record;

    public function mount(int | string $record): void
    {
        $this->record = \App\Models\JobAlert::findOrFail($record);
    }

    public function table(Table $table): Table
    {
        $alert = $this->record;

        return $table
            ->query(
                function (Builder $query) use ($alert) {
                    $query = Listing::where('status', 'active')
                        ->where(function($q) {
                            $q->whereNull('end_date')
                              ->orWhere('end_date', '>=', now());
                        });

                    // Keyword search
                    if ($alert->keywords && count($alert->keywords) > 0) {
                        $query->where(function($q) use ($alert) {
                            foreach ($alert->keywords as $keyword) {
                                $q->orWhere('title', 'like', '%' . $keyword . '%')
                                  ->orWhere('description', 'like', '%' . $keyword . '%');
                            }
                        });
                    }

                    // Location filter
                    if ($alert->location_id) {
                        $query->where('location_id', $alert->location_id);
                    }

                    // Category filter
                    if ($alert->category_id) {
                        $query->where('category_id', $alert->category_id);
                    }

                    // Job type filter
                    if ($alert->job_type && count($alert->job_type) > 0) {
                        $query->whereIn('job_type', $alert->job_type);
                    }

                    // Salary range filter
                    if ($alert->salary_min) {
                        $query->where(function($q) use ($alert) {
                            $q->where('salary_min', '>=', $alert->salary_min)
                              ->orWhere('salary_max', '>=', $alert->salary_min);
                        });
                    }

                    if ($alert->salary_max) {
                        $query->where(function($q) use ($alert) {
                            $q->where('salary_max', '<=', $alert->salary_max)
                              ->orWhere('salary_min', '<=', $alert->salary_max);
                        });
                    }

                    return $query->orderByRaw("CASE WHEN is_featured = 1 AND (featured_expires_at IS NULL OR featured_expires_at > NOW()) THEN 0 ELSE 1 END")
                                 ->orderBy('created_at', 'desc');
                }
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Employer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('location.city')
                    ->label('Location')
                    ->sortable(),
                Tables\Columns\TextColumn::make('job_type')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('salary_range')
                    ->label('Salary Range')
                    ->formatStateUsing(function ($record) {
                        if ($record->salary_min && $record->salary_max) {
                            return '$' . number_format($record->salary_min, 0) . ' - $' . number_format($record->salary_max, 0);
                        }
                        return '-';
                    }),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->heading('Matching Jobs for: ' . $alert->name);
    }
}

