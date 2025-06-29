<?php

namespace App\Filament\Resources\InventoryResource\Widgets;

use App\Models\Inventory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class InventorySummary extends BaseWidget
{
    protected function getCards(): array
    {
        $totalItem = Inventory::count();
        $itemMauHabis = Inventory::where('stock', '<=', 10)->count();
        $itemStokKosong = Inventory::where('stock', 0)->count();
        return [
            Card::make('Jumlah Barang', $totalItem)
                ->description('Jumlah barang yang ada')
                ->color('success'),
            Card::make('Barang Hampir Habis', $itemMauHabis)
                ->description('Jumlah barang yang hampir habis')
                ->color('warning'),
            Card::make('Barang Stok Kosong', $itemStokKosong)
                ->description('Jumlah barang yang stoknya kosong')
                ->color('danger'),
        ];
    }
}
