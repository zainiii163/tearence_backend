<?php

namespace App\Filament\CustomerPages;

use Filament\Pages\Dashboard as BaseDashboard;

class CustomerDashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Customer Admin Dashboard';

    protected static ?int $navigationSort = -2;

    protected static string $panel = 'customer';

    public static function getNavigationGroup(): ?string
    {
        return 'Customer Management';
    }
}
