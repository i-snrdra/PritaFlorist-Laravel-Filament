<?php

namespace App\Filament\Resources\ManifestResource\Pages;

use App\Filament\Resources\ManifestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateManifest extends CreateRecord
{
    protected static string $resource = ManifestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pre-fill order_id dan type dari URL parameters
        $request = request();
        
        if ($request->has('order_id')) {
            $data['order_id'] = $request->get('order_id');
        }
        
        if ($request->has('type')) {
            $data['type'] = $request->get('type');
        }

        // Validasi stok inventory sebelum create
        if (isset($data['manifestItems'])) {
            foreach ($data['manifestItems'] as $item) {
                if (isset($item['inventory_id']) && isset($item['qty'])) {
                    $inventory = \App\Models\Inventory::find($item['inventory_id']);
                    if ($inventory && $item['qty'] > $inventory->stock) {
                        throw new \Exception("Quantity {$item['qty']} untuk {$inventory->name} melebihi stok yang tersedia ({$inventory->stock}).");
                    }
                }
            }
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
