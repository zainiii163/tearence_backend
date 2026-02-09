<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use Filament\Widgets\ChartWidget;

class TotalCustomerChart extends ChartWidget
{
    protected static ?string $heading = 'Total Customer';

    protected static string $color = 'gray';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Total customer created',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
