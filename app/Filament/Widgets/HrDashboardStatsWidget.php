<?php

namespace App\Filament\Widgets;

use App\Filament\Support\DashboardMetrics;
use App\Models\HrEmployee;
use App\Models\HrLeaveRequest;
use App\Models\HrPayrollRecord;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class HrDashboardStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;

    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        if (Schema::hasTable('hr_employees')) {
            $employees = HrEmployee::query()->count();
            $active = HrEmployee::query()->where('status', 'active')->count();
            $pendingLeave = Schema::hasTable('hr_leave_requests')
                ? HrLeaveRequest::query()->where('status', 'pending')->count()
                : 0;
            $payrollDraft = Schema::hasTable('hr_payroll_records')
                ? HrPayrollRecord::query()->where('payment_status', 'draft')->count()
                : 0;

            return [
                Stat::make('Employees', number_format($employees))
                    ->description(number_format($active).' active')
                    ->descriptionIcon('heroicon-m-identification')
                    ->color('primary'),
                Stat::make('Pending leave', number_format($pendingLeave))
                    ->description('Holiday & sick requests')
                    ->descriptionIcon('heroicon-m-calendar-days')
                    ->color('warning'),
                Stat::make('Payroll drafts', number_format($payrollDraft))
                    ->description('Awaiting approval / pay')
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('success'),
                Stat::make('On leave', number_format(HrEmployee::query()->where('status', 'on_leave')->count()))
                    ->description('Current status')
                    ->descriptionIcon('heroicon-m-user-minus')
                    ->color('info'),
            ];
        }

        $staff = DashboardMetrics::count('users', function ($q) {
            $q->where(function ($inner) {
                if (DashboardMetrics::columnExists('users', 'is_super_admin')) {
                    $inner->orWhere('is_super_admin', 1);
                }
                if (DashboardMetrics::columnExists('users', 'is_admin')) {
                    $inner->orWhere('is_admin', 1);
                }
                if (DashboardMetrics::columnExists('users', 'role')) {
                    $inner->orWhereIn('role', ['admin', 'super_admin', 'staff', 'moderator', 'manager']);
                }
            });
        });

        return [
            Stat::make('Team / Staff', number_format(max($staff, 0)))
                ->description('Run migrate for HR employees table')
                ->descriptionIcon('heroicon-m-identification')
                ->color('primary'),
        ];
    }
}
