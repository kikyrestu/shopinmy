<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Grafik Penjualan';
    protected ?string $description = 'Pendapatan & pesanan 30 hari terakhir';
    protected static ?int $sort = 2;
    protected ?string $maxHeight = '320px';
    protected int | string | array $columnSpan = 2;

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn ($i) => Carbon::today()->subDays($i));

        $revenueData = $days->map(fn ($day) =>
            Order::where('status', 'completed')
                ->whereDate('created_at', $day)
                ->sum('total')
        );

        $orderData = $days->map(fn ($day) =>
            Order::whereDate('created_at', $day)->count()
        );

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (RM)',
                    'data' => $revenueData->toArray(),
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 2,
                    'pointBackgroundColor' => '#fff',
                    'pointBorderColor' => 'rgb(245, 158, 11)',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 5,
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Orders',
                    'data' => $orderData->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 5,
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $days->map(fn ($d) => $d->format('d M'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => ['color' => '#f3f4f6'],
                    'position' => 'left',
                ],
                'y1' => [
                    'beginAtZero' => true,
                    'grid' => ['display' => false],
                    'position' => 'right',
                ],
            ],
        ];
    }
}
