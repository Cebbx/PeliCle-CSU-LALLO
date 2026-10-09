<?php

namespace App\Filament\Resources\VehicleRequests\Pages;

use App\Filament\Resources\VehicleRequests\VehicleRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicleRequest extends CreateRecord
{
    protected static string $resource = VehicleRequestResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['request_number']) || \App\Models\VehicleRequest::withTrashed()->where('request_number', $data['request_number'])->orWhere('request_number', 'VR-' . $data['request_number'])->exists()) {
            $data['request_number'] = \App\Models\VehicleRequest::generateNextRequestNumber();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
