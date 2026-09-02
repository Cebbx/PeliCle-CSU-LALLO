<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicle extends EditRecord
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Archive Vehicle')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->modalHeading('Archive Vehicle Record')
                ->modalDescription('Are you sure you want to archive this vehicle? The record will be moved to the archives and can be restored at any time.')
                ->modalSubmitActionLabel('Yes, Archive'),
            RestoreAction::make()
                ->label('Restore Vehicle')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success'),
        ];
    }
}
