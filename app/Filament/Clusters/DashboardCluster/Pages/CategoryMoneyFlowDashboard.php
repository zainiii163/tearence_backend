<?php

namespace App\Filament\Clusters\DashboardCluster\Pages;

use App\Filament\Clusters\DashboardCluster;
use App\Filament\Widgets\CategoryMoneyFlowStatsWidget;
use App\Filament\Widgets\CategoryMoneyFlowTableWidget;
use Filament\Pages\Page;

/**
 * Clive: per-category money flow — Our money / Seller payouts / Other.
 */
class CategoryMoneyFlowDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Category Money';

    protected static ?string $title = 'Category Money Flow';

    protected static ?string $cluster = DashboardCluster::class;

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.clusters.dashboard-cluster.pages.dashboard-topic';

    public function getSubheading(): ?string
    {
        return 'Per category: our money (products, fees, adverts & commissions), payouts to sellers/users, and other monies.';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CategoryMoneyFlowStatsWidget::class,
            CategoryMoneyFlowTableWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    public static function canAccess(): bool
    {
        $user = auth('admin-web')->user() ?? auth()->user();
        if (! $user) {
            return false;
        }

        return (bool) ($user->is_super_admin ?? false)
            || (method_exists($user, 'can') && $user->can('view-financial'));
    }
}
