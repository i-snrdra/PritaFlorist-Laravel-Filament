<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Order;

class IncomeOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalIncomeToday = Order::whereDate('created_at', now()->today())->sum('total_harga');
        $totalIncomeThisMonth = Order::whereMonth('created_at', now()->month)->sum('total_harga');
        $totalIncomeThisYear = Order::whereYear('created_at', now()->year)->sum('total_harga');
        return [
            Card::make('Pendapatan Hari Ini', 'Rp ' . number_format($totalIncomeToday, 0, ',', '.'))
                ->description('Total pendapatan dari hari ini')
                ->color('info'),
            Card::make('Pendapatan Bulan Ini', 'Rp ' . number_format($totalIncomeThisMonth, 0, ',', '.'))
                ->description('Total pendapatan dari bulan ini')
                ->color('warning'),
            Card::make('Pendapatan Tahun Ini', 'Rp ' . number_format($totalIncomeThisYear, 0, ',', '.'))
                ->description('Total pendapatan dari tahun ini')
                ->color('success'),
        ];
    }
}
