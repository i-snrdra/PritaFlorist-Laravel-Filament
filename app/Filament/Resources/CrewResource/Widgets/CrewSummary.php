<?php

namespace App\Filament\Resources\CrewResource\Widgets;

use App\Models\Crew;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class CrewSummary extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected function getCards(): array
    {
        $activeCrew = Crew::where('status', 'active')->count();
        $inactiveCrew = Crew::where('status', 'inactive')->count();

        return [
            Card::make('Jumlah Crew', Crew::count())
                ->description('Jumlah crew yang ada')
                ->color('gray'),
            Card::make('Jumlah Crew Aktif', $activeCrew)
                ->description('Jumlah crew yang aktif')
                ->color('success'),
            Card::make('Jumlah Crew Tidak Aktif', $inactiveCrew)
                ->description('Jumlah crew yang tidak aktif')
                ->color('danger'),
        ];
    }
}
