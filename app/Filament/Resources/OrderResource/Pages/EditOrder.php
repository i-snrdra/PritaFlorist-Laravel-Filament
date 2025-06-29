<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Package;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Calculate total harga before saving
        $packagePrice = 0;
        if (isset($data['package_id']) && $data['package_id']) {
            $package = Package::find($data['package_id']);
            $packagePrice = $package ? $package->price : 0;
        }

        // Calculate extras total (hanya untuk non-studio foto)
        $extrasTotal = 0;
        if (isset($data['type']) && $data['type'] !== 'studio_foto' && isset($data['orderExtras'])) {
            foreach ($data['orderExtras'] as $extra) {
                $qty = (float) ($extra['qty'] ?? 0);
                $hargaSatuan = (float) ($extra['harga_satuan'] ?? 0);
                $extrasTotal += $qty * $hargaSatuan;
            }
        }

        // Set total harga
        $data['total_harga'] = $packagePrice + $extrasTotal;

        return $data;
    }

    protected function afterSave(): void
    {
        // Recalculate after all relationships are saved
        $this->record->calculateTotalHarga();
    }
}
