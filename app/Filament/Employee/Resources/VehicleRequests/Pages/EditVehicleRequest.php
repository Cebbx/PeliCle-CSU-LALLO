<?php

namespace App\Filament\Employee\Resources\VehicleRequests\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Employee\Resources\VehicleRequests\VehicleRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicleRequest extends EditRecord
{
    use HasBackButton;

    protected static string $resource = VehicleRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackButtonAction(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
