<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Revenue
        $thisMonthRevenue = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');
        $lastMonthRevenue = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total');
        $revenueChange = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        // Orders
        $thisMonthOrders = Order::whereMonth('created_at', now()->month)->count();
        $lastMonthOrders = Order::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $ordersChange = $lastMonthOrders > 0
            ? round((($thisMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 1)
            : 0;

        // Customers
        $totalCustomers = User::count();
        $newThisWeek = User::where('created_at', '>=', now()->startOfWeek())->count();

        // Avg Order Value
        $avgOrderValue = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->avg('total') ?? 0;
        $lastMonthAvg = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->avg('total') ?? 0;
        $avgChange = $lastMonthAvg > 0
            ? round((($avgOrderValue - $lastMonthAvg) / $lastMonthAvg) * 100, 1)
            : 0;

        // Weekly sparkline data
        $revenueChart = collect(range(6, 0))->map(fn ($i) =>
            Order::where('status', 'completed')
                ->whereDate('created_at', now()->subDays($i))
                ->sum('total')
        )->toArray();

        $ordersChart = collect(range(6, 0))->map(fn ($i) =>
            Order::whereDate('created_at', now()->subDays($i))->count()
        )->toArray();

        return [
            Stat::make('Total Revenue', 'RM ' . number_format($thisMonthRevenue, 2))
                ->description($revenueChange >= 0 ? "+{$revenueChange}% vs bulan lalu" : "{$revenueChange}% vs bulan lalu")
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart($revenueChart),

            Stat::make('Pesanan Bulan Ini', $thisMonthOrders)
                ->description($ordersChange >= 0 ? "+{$ordersChange}% vs bulan lalu" : "{$ordersChange}% vs bulan lalu")
                ->descriptionIcon($ordersChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersChange >= 0 ? 'success' : 'danger')
                ->chart($ordersChart),

            Stat::make('Total Pelanggan', number_format($totalCustomers))
                ->description("+{$newThisWeek} baru minggu ini")
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info'),

            Stat::make('Rata-rata Nilai Pesanan', 'RM ' . number_format($avgOrderValue, 2))
                ->description($avgChange >= 0 ? "+{$avgChange}% vs bulan lalu" : "{$avgChange}% vs bulan lalu")
                ->descriptionIcon($avgChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($avgChange >= 0 ? 'success' : 'danger'),
        ];
    }
}
