<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Concerns\HasBackButton;
use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicle extends ViewRecord
{
    use HasBackButton;

    protected static string $resource = VehicleResource::class;

    public function getTitle(): string
    {
        $plate = $this->record->plate_number ?? '';
        $brand = $this->record->brand ?? '';
        return "View Vehicle: {$brand} ({$plate})";
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->getBackButtonAction(),
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
