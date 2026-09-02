<?php

namespace App\Filament\Resources\Drivers\Pages;

use App\Filament\Resources\Drivers\DriverResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditDriver extends EditRecord
{
    protected static string $resource = DriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Archive Driver')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->modalHeading('Archive Driver Record')
                ->modalDescription('Are you sure you want to archive this driver? The record will be moved to the archives and can be restored at any time.')
                ->modalSubmitActionLabel('Yes, Archive'),
            RestoreAction::make()
                ->label('Restore Driver')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success'),
        ];
    }
}
