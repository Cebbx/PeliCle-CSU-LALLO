<?php

namespace App\Filament\Resources\SecurityGuards\Pages;

use App\Filament\Resources\SecurityGuards\SecurityGuardResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSecurityGuard extends EditRecord
{
    protected static string $resource = SecurityGuardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Archive Guard')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->modalHeading('Archive Security Guard')
                ->modalDescription('Archived guards will no longer appear on the Gate Clearance Portal or be able to scan QR codes.')
                ->modalSubmitActionLabel('Yes, Archive'),
            RestoreAction::make()
                ->label('Restore Guard')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('success'),
        ];
    }
}
