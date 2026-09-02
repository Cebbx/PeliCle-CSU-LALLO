<?php

namespace App\Filament\Employee\Resources\VehicleRequests\Pages;

use App\Filament\Employee\Resources\VehicleRequests\VehicleRequestResource;
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
        $data['user_id'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
