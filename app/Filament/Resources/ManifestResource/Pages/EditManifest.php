<?php

namespace App\Filament\Resources\ManifestResource\Pages;

use App\Filament\Resources\ManifestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditManifest extends EditRecord
{
    protected static string $resource = ManifestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Validasi stok inventory sebelum save (untuk manifest items yang baru atau berubah)
        if (isset($data['manifestItems'])) {
            $existingItems = $this->record->manifestItems()->get()->keyBy('id');
            
            foreach ($data['manifestItems'] as $item) {
                if (isset($item['inventory_id']) && isset($item['qty'])) {
                    $inventory = \App\Models\Inventory::find($item['inventory_id']);
                    
                    if ($inventory) {
                        $currentQtyUsed = 0;
                        
                        // Jika ini adalah item yang sudah ada, hitung qty yang sudah digunakan sebelumnya
                        if (isset($item['id']) && $existingItems->has($item['id'])) {
                            $existingItem = $existingItems->get($item['id']);
                            if ($existingItem->status === 'dipakai') {
                                $currentQtyUsed = $existingItem->qty;
                            }
                        }
                        
                        // Hitung available stock (stok current + qty yang sudah digunakan sebelumnya)
                        $availableStock = $inventory->stock + $currentQtyUsed;
                        
                        if ($item['qty'] > $availableStock) {
                            throw new \Exception("Quantity {$item['qty']} untuk {$inventory->name} melebihi stok yang tersedia ({$availableStock}).");
                        }
                    }
                }
            }
        }

        return $data;
    }
}
