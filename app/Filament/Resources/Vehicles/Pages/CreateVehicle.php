<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicle extends CreateRecord
{
    use HasBackButton;

    protected static string $resource = VehicleResource::class;

    protected static bool $canCreateAnother = false;

    public function canCreateAnother(): bool
    {
        return false;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
