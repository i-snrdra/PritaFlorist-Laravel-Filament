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

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
