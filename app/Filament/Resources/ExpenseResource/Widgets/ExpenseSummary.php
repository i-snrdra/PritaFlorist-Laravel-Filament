<?php

namespace App\Filament\Resources\ExpenseResource\Widgets;

use App\Models\Expense;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class ExpenseSummary extends BaseWidget
{
    protected function getCards(): array
    {
        $todayTotal = Expense::whereDate('date', Carbon::today())->sum('amount');

        $weekTotal = Expense::whereBetween('date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ])->sum('amount');

        $monthTotal = Expense::whereMonth('date', Carbon::now()->month)->sum('amount');

        return [
            Card::make('Pengeluaran Hari Ini', 'Rp ' . number_format($todayTotal, 0, ',', '.'))
                ->description('Total pengeluaran hari ini')
                ->color('info'),

            Card::make('Pengeluaran Minggu Ini', 'Rp ' . number_format($weekTotal, 0, ',', '.'))
                ->description('Total pengeluaran minggu ini')
                ->color('warning'),

            Card::make('Pengeluaran Bulan Ini', 'Rp ' . number_format($monthTotal, 0, ',', '.'))
                ->description('Total pengeluaran bulan ini')
                ->color('success'),
        ];
    }
}